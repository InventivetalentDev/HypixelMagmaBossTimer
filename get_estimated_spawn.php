<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include_once "common.php";
include_once "db_stuff.php";

$events = array("blaze", "magma", "music", "spawn", "death", "restart");
$event_times = array(
    "blaze" => 0,
    "magma" => 0,
    "music" => 0,
    "spawn" => 0,
    "death" => 0,
    "restart" => 0
);
$event_confirmations = array(
    "blaze" => 0,
    "magma" => 0,
    "music" => 0,
    "spawn" => 0,
    "death" => 0,
    "restart" => 0
);

$minConfirmations = 10;//TODO: make this relative to the amount of currently watching users
$startTime = strtotime("2 hours ago");
$startDate = date("Y-m-d H:i:s", $startTime);

if (!($stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE confirmations >= ? AND time_rounded > ? ORDER BY time_rounded DESC, confirmations DESC LIMIT 20"))) {
    die("unexpected sql error");
}
$stmt->bind_param("is", $minConfirmations, $startDate);
if (!$stmt->execute()) {
    die("unexpected sql error ");
}
$stmt->bind_result($type, $roundedDate, $confirmations, $averageDate);
//TODO: we can probably make this more efficient than iterating through every row
while ($row = $stmt->fetch()) {
    if ($event_times[$type] <= 0) {
        $averageTime = strtotime($averageDate);
        $event_times[$type] = $averageTime * 1000;
    }


    // Just using the first available one for now (TODO maybe)
    if ($event_confirmations[$type] <= 0) {
        $event_confirmations[$type] = $confirmations;
    }
}
$stmt->close();
unset($stmt);
$conn->close();

$twoHoursInMillis = 7.2e+6;
$twentyMinsInMillis = 1.2e+6;
$tenMinsInMillis = 600000;
$fiveMinsInMillis = 300000;
$twoMinsInMillis = 120000;


$now = time() * 1000;

$lastSpawn = $event_times["spawn"];// ~2hrs
$lastBlazeEvent = $event_times["blaze"];// ~20mins
$lastMagmaEvent = $event_times["magma"];// ~10mins
$lastMusicEvent = $event_times["music"];// ~2mins
$lastDeath = $event_times["death"];

$estSpawnsSinceLast = floor(($now - $lastSpawn) / $twoHoursInMillis);
$estSpawnsSinceLast += 1;// add the last known spawn
$estimate = $lastSpawn + (($estSpawnsSinceLast * $twoHoursInMillis));

$estimateFromSpawn = $estimate;
$estimateSource = "spawn";

$averageEstimate = 0;
$averageEstimateCounter = 0;

if ($lastSpawn) {
    $averageEstimate += $estimateFromSpawn;
    $averageEstimateCounter++;
}

$estimateFromDeath = 0;
if ($lastDeath > $lastSpawn) {
    $estSpawnsSinceLast = floor(($now - $lastDeath) / $twoHoursInMillis);
    $estSpawnsSinceLast += 1;// add the last known spawn
    $estimate = $lastDeath + (($estSpawnsSinceLast * $twoHoursInMillis));
    $estimateFromDeath = $estimate;
    $estimateSource = "death";

    $averageEstimate += $estimate;
    $averageEstimateCounter++;
}

$estimateFromBlaze = 0;
if ($lastBlazeEvent > $lastSpawn && $lastBlazeEvent > $lastDeath && $now - $lastBlazeEvent < $twentyMinsInMillis) {
    $estimate = $lastBlazeEvent + $twentyMinsInMillis;
    $estimateFromBlaze = $estimate;
    $estimateSource = "blaze";

    $averageEstimate += $estimate;
    $averageEstimateCounter++;
}
$estimateFromMagma = 0;
if ($lastMagmaEvent > $lastSpawn && $lastMagmaEvent > $lastDeath && $now - $lastMagmaEvent < $tenMinsInMillis) {
    $estimate = $lastMagmaEvent + $tenMinsInMillis;
    $estimateFromMagma = $estimate;
    $estimateSource = "magma";

    $averageEstimate += $estimate;
    $averageEstimateCounter++;
}
$estimateFromMusic = 0;
if ($lastMusicEvent > $lastSpawn && $lastMusicEvent > $lastDeath && $now - $lastMusicEvent < $twoMinsInMillis) {
    $estimate = $lastMusicEvent + $twoMinsInMillis;
    $estimateFromMusic = $estimate;
    $estimateSource = "music";

    $averageEstimate += $estimate;
    $averageEstimateCounter++;
}

$averageEstimate /= $averageEstimateCounter;

//array_reverse($spawn_times);


$relativeString = time2str($estimate / 1000);
$relativeAverageString = time2str($averageEstimate / 1000);

header("Content-Type: application/json");
echo json_encode(array(
    "estSpawnsSinceLast" => $estSpawnsSinceLast,
    "estimate" => $averageEstimate,
    "estimateRelative" => $relativeAverageString,
    "oldEstimate" => $estimate,
    "oldEstimateRelative" => $relativeString,
    "estimateSource" => $estimateSource,
    "estimates" => array(
        "fromSpawn" => $estimateFromSpawn,
        "fromBlaze" => $estimateFromBlaze,
        "fromMagma" => $estimateFromMagma,
        "fromMusic" => $estimateFromMusic,
        "fromDeath" => $estimateFromDeath
    ),
    "latest" => array(
        "spawn" => $lastSpawn,
        "blaze" => $lastBlazeEvent,
        "magma" => $lastMagmaEvent,
        "music" => $lastMusicEvent,
        "death" => $lastDeath
    ),
    "latestConfirmations" => $event_confirmations,
));