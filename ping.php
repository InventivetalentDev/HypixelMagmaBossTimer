<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die();
}

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
$lastFocused = isset($_POST["lastFocused"]) ? $_POST["lastFocused"] : 0;
$username = isset($_POST["minecraftUser"]) ? $_POST["minecraftUser"] : "";

include_once "db_stuff.php";

$isMod = (isset($_POST["minecraftUser"]) && strpos($_SERVER["HTTP_USER_AGENT"], "BossTimerMod/") === 0) ? 1 : 0;

$date = date("Y-m-d H:i:s");
$activeDate = isset($lastFocused) ? date("Y-m-d H:i:s", $lastFocused) : $date;
$rel = -1;
$stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_pings (ip,time,active_time,minecraftName,isMod) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE time=?, active_time=?, minecraftName=?, isMod=?");
$stmt->bind_param("ssssisssi", $ip, $date, $activeDate, $username, $isMod, $date, $activeDate, $username, $isMod);
$stmt->execute();
$stmt->close();
unset($stmt);

$conn->close();
echo "pong";
