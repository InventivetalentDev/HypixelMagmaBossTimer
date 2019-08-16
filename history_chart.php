<?php

include_once "db_stuff.php";

$minConfirmations = 10;
$sinceHours = isset($_GET["hours"]) ? max(4, min(24, intval($_GET["hours"]))) : 4;

$colorMap = array(
    "blaze" => "yellow",
    "magma" => "orange",
    "music" => "magenta",
    "spawn" => "red",
    "death" => "green"
);

$chartData = array();

$stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE confirmations >= ? AND time_rounded > NOW() - INTERVAL ? HOUR ORDER BY time_rounded ASC, confirmations DESC");
$stmt->bind_param("ii", $minConfirmations, $sinceHours);
$stmt->execute();
$stmt->bind_result($type, $roundedDate, $confirmations, $averageDate);
while ($row = $stmt->fetch()) {
    $averageTime = strtotime($averageDate) * 1000;

    $chartData[] = array(
        "x" => $averageTime,
        "name" => $type,
        "confirmations" => $confirmations,
        "color" => $colorMap[$type]
    );
}
$stmt->close();
unset($stmt);
$conn->close();

header("Content-Type: application/json");
echo json_encode($chartData);