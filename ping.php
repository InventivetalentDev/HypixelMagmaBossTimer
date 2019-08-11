<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die();
}

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$lastFocused = $_POST["lastFocused"];

include_once "db_stuff.php";


$date = date("Y-m-d H:i:s");
$activeDate = isset($lastFocused) ? date("Y-m-d H:i:s", $lastFocused) : $date;
$rel = -1;
$stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_pings (ip,time,active_time) VALUES(?,?,?) ON DUPLICATE KEY UPDATE time=?, active_time=?");
$stmt->bind_param("sssss", $ip, $date, $activeDate, $date, $activeDate);
$stmt->execute();
$stmt->close();
unset($stmt);

echo "pong";