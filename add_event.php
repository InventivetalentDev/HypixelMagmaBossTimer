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
if ($type !== "spawn" && $type !== "blaze" && $type !== "magma" && $type !== "music" && $type !== "death") {
    die("unknown event");
}

$username = isset($_POST["username"]) ? $_POST["username"] : "";

//if (!isset($_POST["captcha"])) {
//    die("missing captcha");
//}

$confirmationCheckFactor = 120;

//die("*sigh* I said don't abuse plz :(");

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

include_once "common.php";

$canContinue = false;
if (isset($_POST["captcha"])) {
    if ($res = checkCaptcha($_POST["captcha"])) {
        $canContinue = true;
    } else {
        $canContinue = false;
        die("failed to verify captcha");
    }
} else if (strpos($_SERVER["HTTP_USER_AGENT"], "BossTimerMod/") === 0 && $_POST["isModRequest"] === "true" && isset($_POST["minecraftUser"])) {
    $username = $_POST["minecraftUser"];
    $canContinue = true;
} else {
    $canContinue = false;
    die("invalid request");
}

$isMod = (isset($_POST["minecraftUser"]) && strpos($_SERVER["HTTP_USER_AGENT"], "BossTimerMod/") === 0) ? 1 : 0;

if ($canContinue) {
    include_once "db_stuff.php";

    $date = date("Y-m-d H:i:s");
    $time = time();

    // Check last time
    if (!($stmt = $conn->prepare("SELECT time FROM hypixel_skyblock_magma_timer_ips WHERE type=? AND ip=? ORDER BY time DESC"))) {
        die("unexpected sql error");
    }
    $stmt->bind_param("ss", $type, $ip);
    $stmt->execute();
    $stmt->bind_result($lastTime);
    if ($stmt->fetch()) {
        $lastTime = strtotime($lastTime);
        $stmt->close();
        unset($stmt);
        if ($time - $lastTime < 3600) {
            $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_suspicious_ips (ip,time) VALUES(?,?) ON DUPLICATE KEY UPDATE time=?, counter=counter+1");
            $stmt->bind_param("sss", $ip, $date, $date);
            $stmt->execute();
            $stmt->close();
            unset($stmt);

            die("nope. too soon.");
        }
    } else {
        $stmt->close();
    }
    unset($stmt);


    // also check for overall requests without event type
    if (!($stmt = $conn->prepare("SELECT time,type FROM hypixel_skyblock_magma_timer_ips WHERE ip=? ORDER BY time DESC"))) {
        die("unexpected sql error");
    }
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->bind_result($lastTime, $lastType);
    if ($stmt->fetch()) {
        $lastTime = strtotime($lastTime);
        $stmt->close();
        unset($stmt);

        // allow for less time when reporting death after spawn
        if ($time - $lastTime < ($type === "death" && $lastType === "spawn" ? 20 : $type === "spawn" && $lastType === "music" ? 90 : 120)) {
            $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_suspicious_ips (ip,time) VALUES(?,?) ON DUPLICATE KEY UPDATE time=?, counter=counter+1");
            $stmt->bind_param("sss", $ip, $date, $date);
            $stmt->execute();
            $stmt->close();
            unset($stmt);

            die("nope. too soon.");
        }
    } else {
        $stmt->close();
    }
    unset($stmt);

    // Insert new request
    if (!($stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_ips (time,type,ip,minecraftName,isMod) VALUES(?,?,?,?,?)"))) {
        die("unexpected sql error");
    }
    $stmt->bind_param("ssssi", $date, $type, $ip, $username, $isMod);
    if (!$stmt->execute()) {
        die("unexpected sql error");
    }
    $stmt->close();
    unset($stmt);

    ////////////////////////////////

    $roundedTime = floor(floor($time / $confirmationCheckFactor) * $confirmationCheckFactor);
    $roundedDate = date("Y-m-d H:i:s", $roundedTime);
    $confirmations = 1;

    if (!($stmt = $conn->prepare("SELECT type,time_rounded,confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE type=? AND time_rounded=? ORDER BY time_rounded DESC"))) {
        die("unexpected sql error");
    }
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

        if (!($stmt = $conn->prepare("UPDATE hypixel_skyblock_magma_timer_events2 SET confirmations=confirmations+1, time_average=? WHERE type=? AND time_rounded=?"))) {
            die("unexpected sql error");
        }
        $stmt->bind_param("sss", $averageDate, $type, $roundedDate);
        $stmt->execute();
        $stmt->close();
        unset($stmt);


    } else {
        if (!($stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_events2 (type,time_rounded,time_average) VALUES(?,?,?)"))) {
            die("unexpected sql error");
        }
        $stmt->bind_param("sss", $type, $roundedDate, $date);
        $stmt->execute();
        $stmt->close();
        unset($stmt);
    }

    echo "added";
} else {
    die();
}



