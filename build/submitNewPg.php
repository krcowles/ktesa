<?php
/**
 * This script establishes a new hike in EHIKES with only the data
 * entered in the form on startNewPg.php. The user is then 
 * redirected to the editor (editDB.php).
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';

$usr     = filter_input(INPUT_POST, 'uid');
$pgTitle = filter_input(INPUT_POST, 'newname');
$type    = filter_input(INPUT_POST, 'type');
$cname   = $type === 'Cluster' ? filter_input(INPUT_POST, 'clusters') : '';
$_SESSION['newcluster'] = isset($_POST['mknewgrp']) ? 'Yes' : 'No';

$query = "INSERT INTO `EHIKES` (`pgTitle`,`usrid`,`stat`,`cname`) VALUES " .
    "(?,?,'0',?)";
$newpg = $pdo->prepare($query);
$newpg->execute([$pgTitle, $usr, $cname]);
$last = $pdo->query("SELECT * FROM `EHIKES` ORDER BY 1 DESC LIMIT 1;");
$rowdat = $last->fetch(PDO::FETCH_NUM);
$hikeNo = $rowdat[0];
$redirect = "editDB.php?tab=1&hikeNo=" . $hikeNo . "&usr=" . $usr;

header("Location: {$redirect}");
