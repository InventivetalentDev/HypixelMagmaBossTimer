<?php

include_once "db_stuff.php";

$minConfirmations = 3;


$colorMap = array(
    "blaze"=>"yellow",
    "magma"=>"orange",
    "music"=>"magenta",
    "spawn"=>"red"
);

$chartData = array();

$stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE confirmations >= ? AND time_rounded > NOW() - INTERVAL 4 HOUR ORDER BY time_rounded ASC, confirmations DESC");
$stmt->bind_param("i", $minConfirmations);
$stmt->execute();
$stmt->bind_result($type, $roundedDate, $confirmations, $averageDate);
while ($row = $stmt->fetch()) {
    $averageTime = strtotime($averageDate)* 1000;


$chartData[] = array(
    "x"=>$averageTime,
    "name"=>$type,
    "y"=>$confirmations,
    "color"=>$colorMap[$type]
);
}
$stmt->close();
unset($stmt);

header("Content-Type: application/json");
echo json_encode($chartData);