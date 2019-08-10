<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die();
}

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

include_once "db_stuff.php";


$date = date("Y-m-d H:i:s");
$rel = -1;
$stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_pings (ip,time) VALUES(?,?) ON DUPLICATE KEY UPDATE time=?");
$stmt->bind_param("sss", $ip, $date, $date);
$stmt->execute();
$stmt->close();
unset($stmt);

echo "pong";