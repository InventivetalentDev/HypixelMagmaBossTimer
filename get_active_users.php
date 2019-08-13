<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once "db_stuff.php";

//TODO: make this return some more detailed info (currently active users, users who have it open in the background, total unique users, etc.)

$stmt = $conn->prepare("SELECT ip,time,active_time FROM hypixel_skyblock_magma_timer_pings WHERE active_time > NOW() - INTERVAL 2 MINUTE");
$stmt->execute();
$stmt->bind_result($ip, $time, $activeTime);
$c=0;
while ($row = $stmt->fetch()) {
$c++;
}
$stmt->close();
unset($stmt);

echo $c;