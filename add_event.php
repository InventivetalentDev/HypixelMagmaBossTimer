<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    return;
}

if (!isset($_POST["type"])) {
    die("missing type");
}

$type = $_POST["type"];
if ($type !== "blaze" && $type !== "magma"&&$type!=="music") {
    die("unknown event");
}

include_once "db_stuff.php";

$date = date("Y-m-d H:i:s");
$rel = -1;
$stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_events (rel,time,type) VALUES(?,?,?)");
$stmt->bind_param("iss", $rel, $date, $type);
$stmt->execute();
$stmt->close();

echo "added";