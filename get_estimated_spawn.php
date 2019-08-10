<?php


include_once "db_stuff.php";

$confirmationCheckFactor = 30;
$requiredConfirmations = 3;

$spawn_times = array();
$spawn_confirmations = array();
$event_times = array(
    "blaze" => array(),
    "magma" => array(),
    "music" => array()
);
$event_confirmations = array();

$stmt = $conn->prepare("SELECT id,rel,time FROM hypixel_skyblock_magma_timer_spawns WHERE rel=-1 ORDER BY time DESC");
$stmt->execute();
$stmt->bind_result($id, $rel, $time);
while ($row = $stmt->fetch()) {
//    echo "spawn: " . $time . "\n";
    $t = strtotime($time);
    $spawn_times[] = $t * 1000;

    $t2 = floor($t / $confirmationCheckFactor);
    if (!isset($spawn_confirmations["$t2"])) {
        $spawn_confirmations["$t2"] = 1;
    }
    $spawn_confirmations["$t2"]++;

}
$stmt->close();
unset($stmt);

$lastSpawn = $spawn_times[0];
foreach ($spawn_confirmations as $k => $v) {
    if ($v >= $requiredConfirmations) {
        $lastSpawn = intval($k) * $confirmationCheckFactor * 1000;
        break;
    }
}


$stmt = $conn->prepare("SELECT id,rel,type,time FROM hypixel_skyblock_magma_timer_events WHERE rel=-1 ORDER BY time DESC");
$stmt->execute();
$stmt->bind_result($id, $rel, $type, $time);
while ($row = $stmt->fetch()) {
//    echo "$type: " . $time . "\n";
    $t = strtotime($time);
    $event_times[$type][] = $t * 1000;


    $t2 = floor($t / $confirmationCheckFactor);
    if (!isset($event_confirmations[$type]["$t2"])) {
        $event_confirmations[$type]["$t2"] = 1;
    }
    $event_confirmations[$type]["$t2"]++;
}
$stmt->close();
unset($stmt);


$twoHoursInMillis = 7.2e+6;
$twentyMinsInMillis = 1.2e+6;
$tenMinsInMillis = 600000;
$fiveMinsInMillis = 300000;


$now = time() * 1000;

$estSpawnsSinceLast = floor(($now - $lastSpawn) / $twoHoursInMillis);
$estSpawnsSinceLast += 1;// add the last known spawn
$estimate = $lastSpawn + (($estSpawnsSinceLast * $twoHoursInMillis));

$estimateFromSpawn = $estimate;
$estimateSource = "spawn";

$lastBlazeEvent = array_values($event_times["blaze"])[0];// ~20mins
$lastMagmaEvent = array_values($event_times["magma"])[0];// ~10mins
$lastMusicEvent = array_values($event_times["music"])[0];// ~5mins

foreach ($event_confirmations["blaze"] as $k => $v) {
    if ($v >= $requiredConfirmations) {
        $lastBlazeEvent = intval($k) * $confirmationCheckFactor * 1000;
        break;
    }
}
foreach ($event_confirmations["magma"] as $k => $v) {
    if ($v >= $requiredConfirmations) {
        $lastMagmaEvent = intval($k) * $confirmationCheckFactor * 1000;
        break;
    }
}
foreach ($event_confirmations["music"] as $k => $v) {
    if ($v >= $requiredConfirmations) {
        $lastMusicEvent = intval($k) * $confirmationCheckFactor * 1000;
        break;
    }
}


if ($lastBlazeEvent > $lastSpawn && $now - $lastBlazeEvent < $twentyMinsInMillis) {
    $estimate = $lastBlazeEvent + $twentyMinsInMillis;
    $estimateFromBlaze = $estimate;
    $estimateSource = "blaze";
}
if ($lastMagmaEvent > $lastSpawn && $now - $lastMagmaEvent < $tenMinsInMillis) {
    $estimate = $lastMagmaEvent + $tenMinsInMillis;
    $estimateFromMagma = $estimate;
    $estimateSource = "magma";
}
if ($lastMusicEvent > $lastSpawn && $now - $lastMusicEvent < $fiveMinsInMillis) {
    $estimate = $lastMusicEvent + $fiveMinsInMillis;
    $estimateFromMusic = $estimate;
    $estimateSource = "music";
}

//array_reverse($spawn_times);

header("Content-Type: application/json");
echo json_encode(array(
    "spawnTimes" => $spawn_times,
    "spawnConfirmations" => $spawn_confirmations,
    "eventTimes" => $event_times,
    "eventConfirmations" => $event_confirmations,
    "estSpawnsSinceLast" => $estSpawnsSinceLast,
    "estimate" => $estimate,
    "estimateSource" => $estimateSource,
    "estimates" => array(
        "fromSpawn" => $estimateFromSpawn,
        "fromBlaze" => $estimateFromBlaze,
        "fromMagma" => $estimateFromMagma,
        "fromMusic" => $estimateFromMusic
    ),
    "latest" => array(
        "spawn" => $lastSpawn,
        "blaze" => $lastBlazeEvent,
        "magma" => $lastMagmaEvent,
        "music" => $lastMusicEvent
    )
));