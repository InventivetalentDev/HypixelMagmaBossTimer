<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die();
}

if (!isset($_POST["captcha"])) {
    die("missing captcha");
}

//die("*sigh* I said don't abuse plz :(");

include_once "common.php";

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

if ($res = checkCaptcha($_POST["captcha"])) {
    include_once "db_stuff.php";

    $stmt = $conn->prepare("SELECT time FROM hypixel_skyblock_magma_timer_spawns WHERE ip=?");
    $stmt->bind_param("s", $ip);
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
    $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_spawns (rel,time,ip) VALUES(?,?,?)");
    $stmt->bind_param("iss", $rel, $date, $ip);
    $stmt->execute();
    $stmt->close();
    unset($stmt);

    echo "added (this route is deprecated!)";
} else {
    die("failed to verify captcha");
}


