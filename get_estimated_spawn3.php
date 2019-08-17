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
$raw_confirmations = array(
    "blaze" => 0,
    "magma" => 0,
    "music" => 0,
    "spawn" => 0,
    "death" => 0,
    "restart" => 0
);

$minConfirmations = 10;//TODO: make this relative to the amount of currently watching users
$startTime = strtotime("1 hour ago");
$startDate = date("Y-m-d H:i:s", $startTime);

if (!($stmt = $conn->prepare("SELECT ipv4,ipv6,type,time,minecraftName,server,isMod,captcha_score FROM hypixel_skyblock_magma_timer_ips WHERE time > NOW() - INTERVAL 1 HOUR ORDER BY time DESC"))) {
    die("unexpected sql error");
}
//$stmt->bind_param("s", $startDate);
if (!$stmt->execute()) {
    die("unexpected sql error ");
}
$stmt->bind_result($ipv4, $ipv6, $type, $time, $minecraftName, $server, $isMod, $captchaScore);
while ($row = $stmt->fetch()) {
    $time = strtotime($time);

    $confidence = 1;

    if ($isMod) {
        $confidence += 5;
    } else if ($captchaScore >= 0.9) {
        $confidence += 1;
    }
    if (strlen($server) > 0) {
        $confidence += 1;
    }
    if (strlen($minecraftName) > 0) {
        $confidence += 2;
    }
    if (strlen($ipv6) > 0) {
        $confidence += 1;
    }

    if ($event_times[$type] <= 0) {
        $event_times[$type] = $time;
    } else {
        $event_times[$type] = floor(($event_times[$type] + $confidence * $time) / ($confidence + 1));
    }


    $event_confirmations[$type] += $confidence;
    $raw_confirmations[$type]++;
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

$lastSpawn = $event_times["spawn"]*1000;// ~2hrs
$lastBlazeEvent = $event_times["blaze"]*1000;// ~20mins
$lastMagmaEvent = $event_times["magma"]*1000;// ~10mins
$lastMusicEvent = $event_times["music"]*1000;// ~2mins
$lastDeath = $event_times["death"]*1000;

$estSpawnsSinceLast = floor(($now - $lastSpawn) / $twoHoursInMillis);
$estSpawnsSinceLast += 1;// add the last known spawn
$estimate = $lastSpawn + (($estSpawnsSinceLast * $twoHoursInMillis));

$estimateFromSpawn = $estimate;
$estimateSource = "spawn";

$averageEstimate = 0;
$averageEstimateCounter = 0;

if ($lastSpawn) {
    $averageEstimate += $estimateFromSpawn * ($event_confirmations["spawn"]+10);
    $averageEstimateCounter += $event_confirmations["spawn"]+10;
}

$estimateFromDeath = 0;
if ($lastDeath > $lastSpawn) {
    $estSpawnsSinceLast = floor(($now - $lastDeath) / $twoHoursInMillis);
    $estSpawnsSinceLast += 1;// add the last known spawn
    $estimate = $lastDeath + (($estSpawnsSinceLast * $twoHoursInMillis));
    $estimateFromDeath = $estimate;
    $estimateSource = "death";

    $averageEstimate += $estimate * ($event_confirmations["death"]+10);
    $averageEstimateCounter += $event_confirmations["death"]+10;
}

$estimateFromBlaze = 0;
if ($lastBlazeEvent > $lastSpawn && $lastBlazeEvent > $lastDeath && $now - $lastBlazeEvent < $twentyMinsInMillis) {
    $estimate = $lastBlazeEvent + $twentyMinsInMillis;
    $estimateFromBlaze = $estimate;
    $estimateSource = "blaze";

    $averageEstimate += $estimate * $event_confirmations["blaze"];
    $averageEstimateCounter += $event_confirmations["blaze"];
}
$estimateFromMagma = 0;
if ($lastMagmaEvent > $lastSpawn && $lastMagmaEvent > $lastDeath &&$lastMagmaEvent > $lastBlazeEvent && $now - $lastMagmaEvent < $tenMinsInMillis) {
    $estimate = $lastMagmaEvent + $tenMinsInMillis;
    $estimateFromMagma = $estimate;
    $estimateSource = "magma";

    $averageEstimate += $estimate * $event_confirmations["magma"];
    $averageEstimateCounter += $event_confirmations["magma"];
}
$estimateFromMusic = 0;
if ($lastMusicEvent > $lastSpawn && $lastMusicEvent > $lastDeath  && $lastMusicEvent > $lastMagmaEvent && $now - $lastMusicEvent < $twoMinsInMillis) {
    $estimate = $lastMusicEvent + $twoMinsInMillis;
    $estimateFromMusic = $estimate;
    $estimateSource = "music";

    $averageEstimate += $estimate * $event_confirmations["music"];
    $averageEstimateCounter += $event_confirmations["music"];
}

if ($averageEstimateCounter > 0) {
    $averageEstimate = floor($averageEstimate / $averageEstimateCounter);
}

//array_reverse($spawn_times);


$relativeAverageString = time2str($averageEstimate / 1000);

header("Content-Type: application/json");
echo json_encode(array(
    "estSpawnsSinceLast" => $estSpawnsSinceLast,
    "estimate" => $averageEstimate,
    "estimateRelative" => $relativeAverageString,
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
    "latestConfirmationsNonWeighed" => $raw_confirmations
));