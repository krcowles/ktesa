<?php
/**
 * When a user modifies (or simply reviews) the security questions via the
 * 'Members->Update Sec. Questions' submenu, the questions list and 
 * corresponding answers are updated in the Users table.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require_once "../php/global_boot.php";

chdir('../phpseclib1.0.20');
require "Crypt/RSA.php";
$rsa = new Crypt_RSA();
$keyfile = $sitePrivateDir . '/privatekey.pem';
$privatekey  = file_get_contents($keyfile);
$rsa->loadKey($privatekey);

$ques_str = filter_input(INPUT_POST, 'questions');
$answer1  = strToLower(filter_input(INPUT_POST, 'an1'));
$answer2  = strToLower(filter_input(INPUT_POST, 'an2'));
$answer3  = strToLower(filter_input(INPUT_POST, 'an3'));
$userid   = isset($_POST['ix']) ? filter_input(INPUT_POST, 'ix') :
    $_SESSION['userid'];

$cipher1 =  $rsa->encrypt($answer1);
$an1 = bin2hex($cipher1);
$cipher2 =  $rsa->encrypt($answer2);
$an2 = bin2hex($cipher2);
$cipher2 =  $rsa->encrypt($answer3);
$an3 = bin2hex($cipher2);

$UpdateReq = "UPDATE `USERS` SET `questions`=?,`an1`=?,`an2`=?,`an3`=? WHERE ".
    "`userid`=?;";
$update = $pdo->prepare($UpdateReq);
$update->execute([$ques_str, $an1, $an2, $an3, $userid]);
echo "ok";
