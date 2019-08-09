<?php


include_once "db_stuff.php";

$spawn_times = array();

$stmt = $conn->prepare("SELECT id,rel,time FROM hypixel_skyblock_magma_timer_spawns WHERE rel=-1 ORDER BY time DESC");
$stmt->execute();
$stmt->bind_result($id, $rel, $time);
$stmt->fetch();
while ($row = $stmt->fetch()) {
    $spawn_times[] = strtotime($time) * 1000;
}
$stmt->close();

//array_reverse($spawn_times);

header("Content-Type: application/json");
echo json_encode(array(
    "spawnTimes" => $spawn_times
));