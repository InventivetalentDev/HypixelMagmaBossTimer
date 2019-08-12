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
if ($type !== "spawn" && $type !== "blaze" && $type !== "magma" && $type !== "music") {
    die("unknown event");
}

if (!isset($_POST["captcha"])) {
    die("missing captcha");
}

$confirmationCheckFactor = 80;

//die("*sigh* I said don't abuse plz :(");

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

include_once "common.php";

if ($res = checkCaptcha($_POST["captcha"])) {
    include_once "db_stuff.php";

    $date = date("Y-m-d H:i:s");
    $time = time();

    // Check last time
    $stmt = $conn->prepare("SELECT time FROM hypixel_skyblock_magma_timer_ips WHERE type=? AND ip=? ORDER BY time DESC");
    $stmt->bind_param("ss", $type, $ip);
    $stmt->execute();
    $stmt->bind_result($lastTime);
    if ($stmt->fetch()) {
        $lastTime = strtotime($lastTime);
        $stmt->close();
        unset($stmt);
        if ($time - $lastTime < 3600) {
            die("nope. too soon.");
        }
    }else{
        $stmt->close();
    }
    unset($stmt);

    // Insert new request
    $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_ips (time,type,ip) VALUES(?,?,?)");
    $stmt->bind_param("sss", $date, $type, $ip);
    $stmt->execute();
    $stmt->close();
    unset($stmt);

    ////////////////////////////////

    $roundedTime = floor(floor($time / $confirmationCheckFactor) * $confirmationCheckFactor);
    $roundedDate = date("Y-m-d H:i:s", $roundedTime);
    $confirmations = 1;

    $stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE type=? AND time_rounded=? ORDER BY time_rounded DESC");
    $stmt->bind_param("ss", $type, $roundedDate);
    $stmt->execute();
    $stmt->bind_result($type, $roundedDate, $confirmations, $averageDate);
    if ($stmt->fetch()) {
        $roundedTime = strtotime($roundedDate);
        $averageTime = strtotime($averageDate);

        $averageTime += time();
        $averageTime /= 2;

        $stmt->close();
        unset($stmt);

        $confirmations += 1;
        $averageDate = date("Y-m-d H:i:s", $averageTime);

        $stmt = $conn->prepare("UPDATE hypixel_skyblock_magma_timer_events2 SET confirmations=?, time_average=? WHERE type=? AND time_rounded=?");
        $stmt->bind_param("isss", $confirmations, $averageDate, $type, $roundedDate);
        $stmt->execute();
        $stmt->close();
        unset($stmt);


    } else {
        $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_events2 (type,time_rounded,confirmations,time_average) VALUES(?,?,?,?)");
        $stmt->bind_param("ssis", $type, $roundedDate, $confirmations, $date);
        $stmt->execute();
        $stmt->close();
        unset($stmt);
    }

    echo "added";
} else {
    die("failed to verify captcha");
}



