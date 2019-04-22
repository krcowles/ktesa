<?php
/**
 * This script establishes a new hike in EHIKES with only the data
 * entered in the form on startNewPg.php. The user is then 
 * redirected to the editor (editDB.php).
 * PHP Version 7.1
 * 
 * @package Page_Creation
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require '../php/global_boot.php';
$usr = filter_input(INPUT_POST, 'uid');
$pg = filter_input(INPUT_POST, 'newname');
$mrkr = filter_input(INPUT_POST, 'marker');
$newclus = isset($_POST['mknewgrp']) ? true : false;
// Form query based on marker type
$qfields = "(pgTitle,usrid,stat,";
$qdata = array($pg, $usr, "0");
$vals = "(?,?,?";
if ($mrkr === 'At VC') {
    $vhike = filter_input(INPUT_POST, 'vchike');
    $qfields .= "collection,";
    array_push($qdata, $vhike);
    $vals .= ",?";
} elseif ($mrkr === 'Cluster' && !$newclus) {
    $chike = filter_input(INPUT_POST, 'clus');
    $nmepos = strpos($chike, ":") + 1;
    $clusName = substr($chike, $nmepos, strlen($chike)-$nmepos);
    $clusLtr = substr($chike, 0, $nmepos-1);
    $qfields .= "cgroup,cname,";
    array_push($qdata, $clusLtr);
    array_push($qdata, $clusName);
    $vals .= ",?,?";
} 
$qfields .= "marker)";
array_push($qdata, $mrkr);
$vals .= ",?)";
$query = "INSERT INTO EHIKES {$qfields} VALUES {$vals}";
$newpg = $pdo->prepare($query);
$newpg->execute($qdata);
$last = $pdo->query("SELECT * FROM EHIKES ORDER BY 1 DESC LIMIT 1;");
$rowdat = $last->fetch(PDO::FETCH_NUM);
$hikeNo = $rowdat[0];
$redirect = "editDB.php?tab=1&hikeNo=" . $hikeNo . "&usr=" . $usr;
header("Location: {$redirect}");
