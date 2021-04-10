<?php
/**
 * Report back a list of all current cluster groups already having pages
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
require "../php/global_boot.php";

$groupReq = "SELECT `group` FROM `CLUSTERS` WHERE `page` <> 0;";
$groups   = $pdo->query($groupReq)->fetchAll(PDO::FETCH_COLUMN);
$jsonGroups = json_encode($groups);
echo $jsonGroups;
