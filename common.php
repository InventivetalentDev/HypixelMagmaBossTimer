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
    var_dump($response);
    if ($response["success"]) {
        return $response;
    }
    return false;
}

function curl_post($url, $fields)
{


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