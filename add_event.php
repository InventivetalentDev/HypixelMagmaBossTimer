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
if ($type !== "spawn" && $type !== "blaze" && $type !== "magma" && $type !== "music" && $type !== "death" && $type !== "restart") {
//    logf($date, "unknown event");
    die("unknown event");
}

$username = isset($_POST["username"]) ? $_POST["username"] : "";
$serverId = isset($_POST["serverId"]) ? $_POST["serverId"] : "";

//if (!isset($_POST["captcha"])) {
//    die("missing captcha");
//}

$confirmationCheckFactor = 100;

//die("*sigh* I said don't abuse plz :(");

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
if (!isset($ip)) {
    die("missing ip header");
}


include_once "common.php";

$date = date("Y-m-d H:i:s");
logf($date, "add_event START $type");

$ipv4 = isset($_POST["ipv4"]) ? $_POST["ipv4"] : null;
$ipv6 = isset($_POST["ipv6"]) ? $_POST["ipv6"] : null;

if ($ipv4 === $ipv6) {
    logf($date, "ipv4===ipv6: $ipv4");
}

if (isset($ipv4) && strlen($ipv4) > 16) {
    logf($date, "ipv4 is too long");
    $ipv4 = null;
}
if (isset($ipv6) && strlen($ipv6) < 20) {
    logf($date, "ipv6 is too short");
    $ipv6 = null;
}


if (strlen($ip) <= 16) {
    if (!$ipv4) {
        $ipv4 = $ip;
    } else if ($ipv4 !== $ip) {
        logf($date, "add_event ipv4 mismatch");
        die("ip mismatch");
    }
}

if (strlen($ip) > 20) {
    if (!$ipv6) {
        $ipv6 = $ip;
    } else if ($ipv6 !== $ip) {
        logf($date, "add_event ipv6 mismatch");
        die("ip mismatch");
    }
}

$captchaRes = null;
$canContinue = false;
if (isset($_POST["captcha"])) {
    if ($captchaRes = checkCaptcha($_POST["captcha"])) {
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
    logf($date, "invalid request");
    die("invalid request");
}

$isMod = (isset($_POST["minecraftUser"]) && $_POST["isModRequest"] === "true" && strpos($_SERVER["HTTP_USER_AGENT"], "BossTimerMod/") === 0) ? 1 : 0;

if ($canContinue) {
    include_once "db_stuff.php";

    $time = time();

//    usleep(100 + rand(20, 500));

    // Check last time
    if (!isset($ipv6)) {
        if (!($stmt = $conn->prepare("SELECT time,type FROM hypixel_skyblock_magma_timer_ips WHERE  ipv4=? ORDER BY time DESC LIMIT 1"))) {
            logf($date, "sql error L104 " . $stmt->error);
            die("unexpected sql error");
        }
        $stmt->bind_param("s", $ipv4);
    } else if (!isset($ipv4)) {
        if (!($stmt = $conn->prepare("SELECT time,type FROM hypixel_skyblock_magma_timer_ips WHERE  ipv6=? ORDER BY time DESC LIMIT 1"))) {
            logf($date, "sql error L106 " . $stmt->error);
            die("unexpected sql error");
        }
        $stmt->bind_param("s", $ipv6);
    } else {
        if (!($stmt = $conn->prepare("SELECT time,type FROM hypixel_skyblock_magma_timer_ips WHERE ipv4=? OR ipv6=? ORDER BY time DESC LIMIT 1"))) {
            logf($date, "sql error L112 " . $stmt->error);
            die("unexpected sql error");
        }
        $stmt->bind_param("ss", $ipv4, $ipv6);
    }
    if (!$stmt->execute()) {
        logf($date, "sql error L70 " . $stmt->error);
        die("unexpected sql error ");
    }
    $stmt->bind_result($lastTime, $lastType);
    if ($stmt->fetch()) {
        $lastTime = strtotime($lastTime);
        $stmt->close();
        unset($stmt);


        if ($type === $lastType && $time - $lastTime < 3600) {
            $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_suspicious_ips (ip,time) VALUES(?,?) ON DUPLICATE KEY UPDATE time=?, counter=counter+1");
            $stmt->bind_param("sss", $ip, $date, $date);
            $stmt->execute();
            $stmt->close();
            unset($stmt);
            $conn->close();

            logf($date, "too soon A");

            die("nope. too soon.");
        }

        $throttle = ($type === "death" && $lastType === "spawn" ? 10 : $type === "spawn" && $lastType === "music" ? 40 : 120);
        header("X-Event-Throttle: $throttle");
        if ($time - $lastTime < $throttle) {
            $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_suspicious_ips (ip,time) VALUES(?,?) ON DUPLICATE KEY UPDATE time=?, counter=counter+1");
            $stmt->bind_param("sss", $ip, $date, $date);
            $stmt->execute();
            $stmt->close();
            unset($stmt);
            $conn->close();

            logf($date, "too soon C");
            die("nope. too soon.");
        }


    } else {
        $stmt->close();
    }
    unset($stmt);


//    // also check for overall requests without event type
//    if (!($stmt = $conn->prepare("SELECT time,type FROM hypixel_skyblock_magma_timer_ips WHERE ip=? ORDER BY time DESC LIMIT 1"))) {
//        logf($date, "sql error L90 " . $stmt->error);
//        die("unexpected sql error");
//    }
//    $stmt->bind_param("s", $ip);
//    if (!$stmt->execute()) {
//        logf($date, "sql error L151 " . $stmt->error);
//        die("unexpected sql error ");
//    }
//    $stmt->bind_result($lastTime, $lastType);
//    if ($stmt->fetch()) {
//        $lastTime = strtotime($lastTime);
//        $stmt->close();
//        unset($stmt);
//
//        // allow for less time when reporting death after spawn
//        if ($time - $lastTime < ($type === "death" && $lastType === "spawn" ? 30 : $type === "spawn" && $lastType === "music" ? 90 : 120)) {
//            $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_suspicious_ips (ip,time) VALUES(?,?) ON DUPLICATE KEY UPDATE time=?, counter=counter+1");
//            $stmt->bind_param("sss", $ip, $date, $date);
//            $stmt->execute();
//            $stmt->close();
//            unset($stmt);
//            $conn->close();
//
//            logf($date, "too soon B");
//            die("nope. too soon.");
//        }
//    } else {
//        $stmt->close();
//    }
//    unset($stmt);


    $confirmationsIncrease = 1;

    if (strlen($username) > 0) {
        if (verifyMinecraftUsername($username)) {
            $confirmationsIncrease += 2;
        } else {
            logf($date, "add_event wrong MC username: $username");
            $confirmationsIncrease -= 10;// supplied a wrong username
            die("added");
        }
    }


    $captchaScore = (isset($captchaRes) && isset($captchaRes["score"])) ? $captchaRes["score"] : 0;

    // Insert new request
    if (!($stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_ips (time,type,ipv4,ipv6,minecraftName,server,isMod,captcha_score) VALUES(?,?,?,?,?,?,?,?)"))) {
        logf($date, "sql error L206 " . $stmt->error);
        die("unexpected sql error");
    }
    if (!$stmt->bind_param("ssssssid", $date, $type, $ipv4, $ipv6, $username, $serverId, $isMod, $captchaScore)) {
        logf($date, "sql error L210 " . $stmt->error);
        die("unexpected sql error");
    }
    if (!$stmt->execute()) {
        logf($date, "sql error L214 " . $stmt->error);
        die("unexpected sql error");
    }
    $stmt->close();
    unset($stmt);

//    dumpRequest("./requestDumps/$date");

//    usleep(20 + rand(50, 1000));

    ////////////////////////////////

    $roundedTime = floor(floor($time / $confirmationCheckFactor) * $confirmationCheckFactor);
    $roundedDate = date("Y-m-d H:i:s", $roundedTime);
//    $confirmations = 1 + $isMod;



    if ($isMod) {
        $confirmationsIncrease += 2;
    }


    if (isset($ipv6)) {
        $confirmationsIncrease += 1;
    } else {
        $confirmationsIncrease -= 1;
    }

    logf($date, "add_event confirmationIncrease: $confirmationsIncrease");

    if ($confirmationsIncrease <= 0) {
        $conn->close();
        logf($date, "not worth it");
        die("added");
    }

    $hash = hash("md5", $type . $roundedDate);

    logf($date, "add_event upsert");
    if (!($stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_events2 (hash,type,time_rounded,time_average,confirmations,time_latest) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE confirmations=confirmations+?, time_latest=?"))) {
        logf($date, "sql error L155 " . $stmt->error);
        die("unexpected sql error");
    }
    if(!$stmt->bind_param("ssssisis", $hash,$type, $roundedDate, $date, $confirmationsIncrease, $date, $confirmationsIncrease, $date)){
        logf($date, "sql error L262 " . $conn->error);
        die("unexpected sql error ");
    }
    if (!$stmt->execute()) {
        logf($date, "sql error L266 " . $conn->error);
        die("unexpected sql error ");
    }
    if ($conn->errno !== 0) {
        logf($date, "sql errno: " . $conn->errno);
        die("unexpected sql error");
    }
    $stmt->close();
    unset($stmt);

//    if (!($stmt = $conn->prepare("SELECT confirmations,time_average FROM hypixel_skyblock_magma_timer_events2 WHERE type=? AND time_rounded=? ORDER BY time_rounded DESC LIMIT 1"))) {
//        logf($date, "sql error L139 " . $stmt->error);
//        die("unexpected sql error ");
//    }
//    $stmt->bind_param("ss", $type, $roundedDate);
//    if (!$stmt->execute()) {
//        logf($date, "sql error L151 " . $stmt->error);
//        die("unexpected sql error ");
//    }
//    $stmt->bind_result($confirmations, $averageDate);
//    if ($stmt->fetch()) {
//        $roundedTime = strtotime($roundedDate);
//        $averageTime = strtotime($averageDate);
//
//        $averageTime += time();
//        $averageTime /= 2;
//
//        $stmt->close();
//        unset($stmt);
//
//        $confirmations += 1;
//        $confirmations += $isMod * 5;
//        $averageDate = date("Y-m-d H:i:s", $averageTime);
//
//        logf($date, "add_event increase existing");
//        if (!($stmt = $conn->prepare("UPDATE hypixel_skyblock_magma_timer_events2 SET confirmations=?, time_average=? WHERE type=? AND time_rounded=?"))) {
//            logf($date, "sql error L158 " . $stmt->error);
//            die("unexpected sql error");
//        }
//        $stmt->bind_param("isss", $confirmations, $averageDate, $type, $roundedDate);
//        if (!$stmt->execute()) {
//            logf($date, "sql error L177 " . $stmt->error);
//            die("unexpected sql error ");
//        }
//        $stmt->close();
//        unset($stmt);
//
//
//    } else {
//
//        logf($date, "add_event add new");
//        if (!($stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_events2 (type,time_rounded,time_average,confirmations) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE confirmations=confirmations+1"))) {
//            logf($date, "sql error L169 " . $stmt->error);
//            die("unexpected sql error");
//        }
//        $stmt->bind_param("sssi", $type, $roundedDate, $date, $confirmations);
//        if (!$stmt->execute()) {
//            logf($date, "sql error L194 " . $stmt->error);
//            die("unexpected sql error ");
//        }
//        $stmt->close();
//        unset($stmt);
//    }

    echo "added";
    $conn->close();

    logf($date, "add_event ADDED $type");
} else {
    $conn->close();
    die();
}



