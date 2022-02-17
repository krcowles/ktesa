<?php
/**
 * Compare the current time to time when lockout has expired. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";

$ip = getIpAddress();
$ip = $ip === '::1' ? '127.0.0.1' : $ip; // Chrome localhost id is "different"
$now = new DateTime("now", new DateTimeZone('America/Denver'));
$result = "wait";

$tables = $pdo->query("SHOW TABLES LIKE 'Locks';")->fetchAll(PDO::FETCH_NUM);
if (count($tables) > 0) {
    $lockouts = "SELECT `ipaddr`,`lockout` FROM `Locks`;";
    $entries = $pdo->query($lockouts)->fetchAll(PDO::FETCH_KEY_PAIR);
    if (array_key_exists($ip, $entries)) {
        $expired = $entries[$ip];
        if (!empty($expired)) {
            $expiry = new DateTime($expired, new DateTimeZone('America/Denver'));
            $result = $now > $expiry ? 'ok' : 'wait';
        } else {
            $result = "ok";
        }
    } else {
        $result = "ok";
    }
} else {
    $result = "ok";
}
echo $result;
