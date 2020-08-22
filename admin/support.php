<?php
/**
 * This script sends user questions/feedback to the admins.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$admin   = filter_input(INPUT_POST, 'admin');
$message = filter_input(INPUT_POST, 'feedback');

$to = ($admin == 0) ? 'tjsandberg@yahoo.com' : 'krcowles29@gmail.com';
$subject = 'Visitor Question/Comment';
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
// Mail it
mail($to, $subject, $message, $headers);
