<?php

include_once "vars.php";

function checkCaptcha($response)
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $fields = array(
        'secret' => CAPTCHA_SECRET,
        'response' => urlencode($response)
    );

    $response = curl_post($url, $fields);
    $response = json_decode($response, true);
//    var_dump($response);
    if ($response["success"]) {
        return $response;
    }
    return false;
}


function getActiveUsers($conn)
{
    $total = 0;

    if (!($stmt = $conn->prepare("SELECT COUNT(*) AS total FROM hypixel_skyblock_magma_timer_pings WHERE active_time > NOW() - INTERVAL 2 MINUTE"))) {
        return 0;
    }
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    unset($stmt);

    return $total;
}

function logf($date, $line)
{
    $fp = fopen('./logs/log', 'a');//opens file in append mode
    fwrite($fp, "[$date] ".$line."\n");
    fclose($fp);
}

function verifyMinecraftUsername($username)
{
    $json = file_get_contents('https://api.mojang.com/users/profiles/minecraft/' . $username);
    if (!empty($json)) {
        $data = json_decode($json, true);
        if (is_array($data) and !empty($data)) {
            return $data;
        }
    }

    return false;
}

function getHypixelProfile($uuid)
{
    $json = file_get_contents('https://api.hypixel.net/player?key=' . HYPIXEL_API_KEY . '&uuid=' . $uuid);
    if (!empty($json)) {
        $data = json_decode($json, true);
        if (is_array($data) and !empty($data)) {
            return $data;
        }
    }
}

function curl_post($url, $fields)
{

    $fields_string = "";
//url-ify the data for the POST
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');

//open connection
    $ch = curl_init();

//set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//execute post
    $result = curl_exec($ch);

//close connection
    curl_close($ch);

    return $result;
}

// https://gist.github.com/mattytemple/3804571
function time2str($ts)
{
    $diff = time() - $ts;
    if ($diff == 0) {
        return 'now';
    } elseif ($diff > 0) {
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0) {
            if ($diff < 60) return 'just now';
            if ($diff < 120) return '1 minute ago';
            if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if ($diff < 7200) return '1 hour ago';
            if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if ($day_diff == 1) {
            return 'Yesterday';
        }
        if ($day_diff < 7) {
            return $day_diff . ' days ago';
        }
        if ($day_diff < 31) {
            return ceil($day_diff / 7) . ' weeks ago';
        }
        if ($day_diff < 60) {
            return 'last month';
        }
        return date('F Y', $ts);
    } else {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0) {
            if ($diff < 120) {
                return 'in a minute';
            }
            if ($diff < 3600) {
                return 'in ' . floor($diff / 60) . ' minutes';
            }
            if ($diff < 7200) {
                return 'in an hour';
            }
            if ($diff < 86400) {
                return 'in ' . floor($diff / 3600) . ' hours';
            }
        }
        if ($day_diff == 1) {
            return 'Tomorrow';
        }
        if ($day_diff < 4) {
            return date('l', $ts);
        }
        if ($day_diff < 7 + (7 - date('w'))) {
            return 'next week';
        }
        if (ceil($day_diff / 7) < 4) {
            return 'in ' . ceil($day_diff / 7) . ' weeks';
        }
        if (date('n', $ts) == date('n') + 1) {
            return 'next month';
        }
        return date('F Y', $ts);
    }
}


function dumpRequest($targetFile)
{

    $data = sprintf(
        "%s %s %s\n\nHTTP headers:\n",
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $_SERVER['SERVER_PROTOCOL']
    );
    foreach (getHeaderList() as $name => $value) {
        $data .= $name . ': ' . $value . "\n";
    }
    $data .= "\nRequest body:\n";
    file_put_contents(
        $targetFile,
        $data . file_get_contents('php://input') . "\n"
    );
}

function getHeaderList()
{
    $headerList = [];
    foreach ($_SERVER as $name => $value) {
        if (preg_match('/^HTTP_/', $name)) {
            // convert HTTP_HEADER_NAME to Header-Name
            $name = strtr(substr($name, 5), '_', ' ');
            $name = ucwords(strtolower($name));
            $name = strtr($name, ' ', '-');
            // add to list
            $headerList[$name] = $value;
        }
    }
    return $headerList;
}