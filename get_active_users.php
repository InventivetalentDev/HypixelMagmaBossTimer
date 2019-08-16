<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once "db_stuff.php";
include_once "common.php";

//TODO: make this return some more detailed info (currently active users, users who have it open in the background, total unique users, etc.)

$users = getActiveUsers($conn);
$conn->close();
echo $users;