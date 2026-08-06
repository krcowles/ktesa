<?php
/**
 * This script sets the 'app' field in MEMBER_PREFS to the
 * posted value.
 * PHP Version 8.3.9 
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$value = FILTER_INPUT(INPUT_POST, 'value');
$setValueReq = "UPDATE `MEMBER_PREFS` SET `app`=? WHERE `userid`=?;";
$setValueUpdate = $pdo->prepare($setValueReq);
$setValueUpdate->execute([$value, $_SESSION['userid']]);
echo "DONE";
