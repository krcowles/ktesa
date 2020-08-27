<?php
/**
 * Temporarily extend the user's expiration date for this php session
 * to avoid seeing the 'Renew' popup on each page accessed. After the
 * session expires, login checks will re-establish user credentials.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

$tempdate = date('Y-m-d');
$date = explode("-", $tempdate);
$date[0] += 1;
$newdate = implode("-", $date);

$_SESSION['expire'] = $newdate;
