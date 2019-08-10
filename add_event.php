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

if (!isset($_POST["captcha"])) {
    die("missing captcha");
}

//die("*sigh* I said don't abuse plz :(");

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

include_once "common.php";

if ($res = checkCaptcha($_POST["captcha"])) {
    include_once "db_stuff.php";

    $stmt = $conn->prepare("SELECT time FROM hypixel_skyblock_magma_timer_events WHERE type=? AND ip=?");
    $stmt->bind_param("ss", $type,$ip);
    $stmt->execute();
    $stmt->bind_result($lastTime);
    if ($stmt->fetch()) {
        $lastTime = strtotime($lastTime);
        if (time() - $lastTime < 3600) {
            die("nope. too soon.");
        }
    }
    $stmt->close();
    unset($stmt);

    $date = date("Y-m-d H:i:s");
    $rel = -1;
    $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_events (rel,time,type,ip) VALUES(?,?,?,?)");
    $stmt->bind_param("isss", $rel, $date, $type,$ip);
    $stmt->execute();
    $stmt->close();
    unset($stmt);

    echo "added";
}else{
    die("failed to verify captcha");
}



