<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once "db_stuff.php";

$events = array("blaze", "magma", "music", "spawn");
$event_times = array(
    "blaze" => array(),
    "magma" => array(),
    "music" => array(),
    "spawn" => array()
);
$event_confirmations = array(
    "blaze"=>0,
    "magma"=>0,
    "music"=>0,
    "spawn"=>0
);

$minConfirmations = 3;//TODO: make this relative to the amount of currently watching users


$stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE confirmations >= ? ORDER BY time_rounded DESC, confirmations DESC");
$stmt->bind_param("i", $minConfirmations);
$stmt->execute();
$stmt->bind_result($type, $roundedDate, $confirmations, $averageDate);
//TODO: we can probably make this more efficient than iterating through every row
while ($row = $stmt->fetch()) {
    echo "$type: " . $averageDate . "\n";
    $averageTime = strtotime($averageDate);
    $event_times[$type][] = $averageTime * 1000;


    // Just using the first available one for now (TODO maybe)
    if ($event_confirmations[$type] <= 0) {
        $event_confirmations[$type] = $confirmations;
    }
}
$stmt->close();
unset($stmt);


$twoHoursInMillis = 7.2e+6;
$twentyMinsInMillis = 1.2e+6;
$tenMinsInMillis = 600000;
$fiveMinsInMillis = 300000;


$now = time() * 1000;

$lastSpawn = array_values($event_times["spawn"])[0];// ~2hrs
$lastBlazeEvent = array_values($event_times["blaze"])[0];// ~20mins
$lastMagmaEvent = array_values($event_times["magma"])[0];// ~10mins
$lastMusicEvent = array_values($event_times["music"])[0];// ~5mins

$estSpawnsSinceLast = floor(($now - $lastSpawn) / $twoHoursInMillis);
$estSpawnsSinceLast += 1;// add the last known spawn
$estimate = $lastSpawn + (($estSpawnsSinceLast * $twoHoursInMillis));

$estimateFromSpawn = $estimate;
$estimateSource = "spawn";


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
    "eventTimes" => $event_times,
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
    ),
    "latestConfirmations"=>$event_confirmations,
));