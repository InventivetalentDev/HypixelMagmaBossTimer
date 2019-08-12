<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die();
}

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$lastFocused = $_POST["lastFocused"];
$username = isset($_POST["minecraftUser"]) ? $_POST["minecraftUser"] : "";

include_once "db_stuff.php";


$date = date("Y-m-d H:i:s");
$activeDate = isset($lastFocused) ? date("Y-m-d H:i:s", $lastFocused) : $date;
$rel = -1;
$stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_pings (ip,time,active_time,minecraftName) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE time=?, active_time=?, minecraftName=?");
$stmt->bind_param("sssssss", $ip, $date, $activeDate, $username, $date, $activeDate, $username);
$stmt->execute();
$stmt->close();
unset($stmt);

echo "pong";