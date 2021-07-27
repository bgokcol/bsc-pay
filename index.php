<?php
header('Content-Type: application/json');

function bcdechex($dec)
{
    $hex = '';
    do {
        $last = bcmod($dec, 16);
        $hex = dechex($last) . $hex;
        $dec = bcdiv(bcsub($dec, $last), 16);
    } while ($dec > 0);
    return $hex;
}

function decimal_notation($float)
{
    $parts = explode('E', $float);
    if (count($parts) === 2) {
        $exp = abs(end($parts)) + strlen($parts[0]);
        $decimal = number_format($float, $exp);
        return rtrim($decimal, '.0');
    } else {
        return $float;
    }
}

function bc_number_format($number, $precision)
{
    return bcdiv(decimal_notation($number), 1, $precision);
}

function wei_to_eth($amount)
{
    return bcdiv(strval($amount), '1000000000000000000', 18);
}

function eth_to_wei($amount)
{
    return bcmul(floatval($amount), '1000000000000000000');
}

function gwei_to_wei($amount)
{
    return bcmul($amount, '1000000000');
}

function json_response(array $data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    die;
}

function get_post(string $key)
{
    return isset($_POST[$key]) ? $_POST[$key] : null;
}

$_CONFIG = require 'inc/config.php';

if($_CONFIG['baseUrl'] == '{baseUrl}') {
    header('Location: ./install');
    die;
}

$_CONFIG['baseUrl'] = rtrim($_CONFIG['baseUrl'], '/') . '/';

$baseSSL = strpos($_CONFIG['baseUrl'], 'https') === 0;
$currentSSL = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

if (!$baseSSL && $currentSSL) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    die;
}
if ($baseSSL && !$currentSSL) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    die;
}
if ($_SERVER['HTTP_HOST'] !== parse_url($_CONFIG['baseUrl'])['host']) {
    header('Location: http' . ($currentSSL ? 's' : '') . '://' . parse_url($_CONFIG['baseUrl'])['host'] . $_SERVER['REQUEST_URI']);
    die;
}

$currentUrl = 'http' . ($currentSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (isset($_GET['cron'])) {
    if ($_GET['cron'] === $_CONFIG['cronKey']) {
        require './inc/database.php';
        require './api/cron.php';
        die;
    } else {
        die('Cron key is invalid.');
    }
}

$apiActions = ['version', 'create_payment', 'get_payment'];

if (strpos($currentUrl, $_CONFIG['baseUrl']) === 0) {
    require './inc/database.php';
    $apiKey = get_post('api_key');
    $user = null;
    if (!empty($apiKey)) {
        $user = $db->prepare('SELECT * from users WHERE api_key = ?');
        $user->execute([$apiKey]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
    }
    if (!empty($user)) {
        $apiAction = get_post('action');
        if (empty($apiAction)) {
            $apiAction = 'version';
        }
        if (in_array($apiAction, $apiActions) && ($filePath = './api/' . $apiAction . '.php') && file_exists($filePath)) {
            $data = require $filePath;
            json_response($data);
        } else {
            json_response([
                'success' => false,
                'message' => 'Action parameter is empty or invalid!'
            ]);
        }
    } else {
        json_response([
            'success' => false,
            'message' => 'Api key parameter is empty or invalid!'
        ]);
    }
} else {
    json_response([
        'success' => false,
        'message' => 'Base URL is invalid!'
    ]);
}
