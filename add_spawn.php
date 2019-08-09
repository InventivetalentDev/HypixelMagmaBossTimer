<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    return;
}

include_once "db_stuff.php";

$date = date("Y-m-d H:i:s");
$rel=-1;
$stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_spawns (rel,time) VALUES(?,?)");
$stmt->bind_param("is", $rel, $date);
$stmt->execute();
$stmt->close();

echo "added";