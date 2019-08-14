<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include_once "common.php";
include_once "db_stuff.php";

$events = array("blaze", "magma", "music", "spawn", "death");
$event_times = array(
    "blaze" => array(),
    "magma" => array(),
    "music" => array(),
    "spawn" => array(),
    "death" => array()
);
$event_confirmations = array(
    "blaze" => 0,
    "magma" => 0,
    "music" => 0,
    "spawn" => 0,
    "death" => 0
);

$minConfirmations = 6;//TODO: make this relative to the amount of currently watching users


if (!($stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE confirmations >= ? ORDER BY time_rounded DESC, confirmations DESC"))) {
    die("unexpected sql error");
}
$stmt->bind_param("i", $minConfirmations);
$stmt->execute();
$stmt->bind_result($type, $roundedDate, $confirmations, $averageDate);
//TODO: we can probably make this more efficient than iterating through every row
while ($row = $stmt->fetch()) {
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
$twoMinsInMillis = 120000;


$now = time() * 1000;

$lastSpawn = array_values($event_times["spawn"])[0];// ~2hrs
$lastBlazeEvent = array_values($event_times["blaze"])[0];// ~20mins
$lastMagmaEvent = array_values($event_times["magma"])[0];// ~10mins
$lastMusicEvent = array_values($event_times["music"])[0];// ~2mins
$lastDeath = array_values($event_times["death"])[0];

$estSpawnsSinceLast = floor(($now - $lastSpawn) / $twoHoursInMillis);
$estSpawnsSinceLast += 1;// add the last known spawn
$estimate = $lastSpawn + (($estSpawnsSinceLast * $twoHoursInMillis));

$estimateFromSpawn = $estimate;
$estimateSource = "spawn";

$estimateFromDeath = 0;
if ($lastDeath > $lastSpawn) {
    $estSpawnsSinceLast = floor(($now - $lastDeath) / $twoHoursInMillis);
    $estSpawnsSinceLast += 1;// add the last known spawn
    $estimate = $lastDeath + (($estSpawnsSinceLast * $twoHoursInMillis));
    $estimateFromDeath = $estimate;
    $estimateSource = "death";
}

$estimateFromBlaze = 0;
if ($lastBlazeEvent > $lastSpawn && $now - $lastBlazeEvent < $twentyMinsInMillis) {
    $estimate = $lastBlazeEvent + $twentyMinsInMillis;
    $estimateFromBlaze = $estimate;
    $estimateSource = "blaze";
}
$estimateFromMagma = 0;
if ($lastMagmaEvent > $lastSpawn && $now - $lastMagmaEvent < $tenMinsInMillis) {
    $estimate = $lastMagmaEvent + $tenMinsInMillis;
    $estimateFromMagma = $estimate;
    $estimateSource = "magma";
}
$estimateFromMusic = 0;
if ($lastMusicEvent > $lastSpawn && $now - $lastMusicEvent < $twoMinsInMillis) {
    $estimate = $lastMusicEvent + $twoMinsInMillis;
    $estimateFromMusic = $estimate;
    $estimateSource = "music";
}

//array_reverse($spawn_times);

$relativeString = time2str($estimate / 1000);

header("Content-Type: application/json");
echo json_encode(array(
    "eventTimes" => $event_times,
    "estSpawnsSinceLast" => $estSpawnsSinceLast,
    "estimate" => $estimate,
    "estimateRelative" => $relativeString,
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