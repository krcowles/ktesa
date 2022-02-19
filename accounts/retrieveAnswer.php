<?php
/**
 * Retrieve the user's saved security question response. This script is ajaxed.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require_once "../php/global_boot.php";
verifyAccess('ajax');

chdir('../phpseclib1.0.20');
require "Crypt/RSA.php";
$publickey  = file_get_contents('../../ktprivate/publickey.pem');
$rsa = new Crypt_RSA();
$rsa->loadKey($publickey);

$options = array('options' => array('min_range' => 0, 'max_range' => 2));
$uid     = filter_input(INPUT_POST, 'ix');
$idx     = filter_input(INPUT_POST, 'rx', FILTER_VALIDATE_INT, $options);

$answer = 'an' . ++$idx;
$retAnsReq = "SELECT {$answer} FROM `USERS` WHERE `userid`=?;";
$retAns = $pdo->prepare($retAnsReq);
$retAns->execute([$uid]);
$selected_answer = $retAns->fetch(PDO::FETCH_NUM);

$cipher = hex2bin($selected_answer[0]);
$decrypted = $rsa->decrypt($cipher);
echo $decrypted;
