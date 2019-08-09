<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die();
}

if (!isset($_POST["captcha"])) {
    die("missing captcha");
}


include_once "common.php";


if ($res = checkCaptcha($_POST["captcha"])) {
    include_once "db_stuff.php";

    $date = date("Y-m-d H:i:s");
    $rel = -1;
    $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_spawns (rel,time) VALUES(?,?)");
    $stmt->bind_param("is", $rel, $date);
    $stmt->execute();
    $stmt->close();

    echo "added";
} else {
    die("failed to verify captcha");
}


