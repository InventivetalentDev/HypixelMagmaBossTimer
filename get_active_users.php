<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once "db_stuff.php";

$stmt = $conn->prepare("SELECT ip,time FROM hypixel_skyblock_magma_timer_pings WHERE time > NOW() - INTERVAL 2 MINUTE");
$stmt->execute();
$stmt->bind_result($ip, $time);
$c=0;
while ($row = $stmt->fetch()) {
$c++;
}
$stmt->close();
unset($stmt);

echo $c;