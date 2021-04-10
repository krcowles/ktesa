<?php
/**
 * This script establishes a new hike in EHIKES with only the data
 * entered in the form on startNewPg.php. The user is then 
 * redirected to the editor (editDB.php). If a cluster group was selected,
 * that selection is saved in the `cname` field of EHIKES. If a new cluster
 * group was specified, that is (also) saved in `cname`, and the new group is
 * entered into the CLUSTERS table. In either case, since the hike is
 * associated with the cluster (old or new), it is entered into the CLUSHIKES
 * table.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
verifyAccess('post');

$userid     = $_SESSION['userid'];
$pgTitle    = filter_input(INPUT_POST, 'hikename');
$type       = filter_input(INPUT_POST, 'type');
$cluster    = filter_input(INPUT_POST, 'clusters');
$newgroup   = filter_input(INPUT_POST, 'newgroup');

// new group takes priority
$cname = !empty($newgroup) ? $newgroup : $cluster;
$cname = $type === 'Cluster' ? $cname : '';
// make sure newgroup didn't just get posted...
if ($type === 'Cluster' && $cname == $newgroup) { 
    $currGroups = "SELECT `group` FROM `CLUSTERS`;";
    $newclusReq = "INSERT INTO `CLUSTERS` (`group`,`pub`,`page`) " .
        "VALUES (?,'N','0');";
    $pdo->beginTransaction();
    $allgroups = $pdo->query($currGroups)->fetchAll(PDO::FETCH_COLUMN);
    if (in_array($cname, $allgroups)) {
        $_SESSION['pgtitle'] = "Group name '{$cname}' was recently submitted " .
            "by another user";
        header("Location: startNewPg.php");
        exit;
    }
    $newclus = $pdo->prepare($newclusReq);
    $newclus->execute([$cname]);
    $pdo->commit();
}

$ehTitleReq = "SELECT `pgTitle` FROM `EHIKES`;";
$phTitleReq = "SELECT `pgTitle` FROM `HIKES`;";

// Use transactions to ensure no pgTitle came in just prior to posting:
try {
    $pdo->beginTransaction();
    $ehikes = $pdo->query($ehTitleReq)->fetchAll(PDO::FETCH_COLUMN);
    $phikes = $pdo->query($phTitleReq)->fetchAll(PDO::FETCH_COLUMN);
    $pgTitles = array_merge($ehikes, $phikes);
    if (in_array($pgTitle, $pgTitles)) {
        $_SESSION['pgtitle'] = "Hike name '{$pgTitle}' was recently submitted " .
            "by another user";
        header("Location: startNewPg.php");
        exit;
    } else {
        // populate minimum data into EHIKES to record a new hike
        $query = "INSERT INTO `EHIKES` (`pgTitle`,`usrid`,`stat`,`cname`) VALUES " .
            "(?,?,'0',?)";
        $newpg = $pdo->prepare($query);
        $newpg->execute([$pgTitle, $userid, $cname]);
    }
} catch (Exception $e) {
    $_SESSION['pgtitle'] = $e->getMessage();
    header("Location: startNewPg.php");
    exit;
} catch (PDOException $pdoe) {
    $_SESSION['pgtitle'] = $pdoe->getMessage();
    header("Location: startNewPg.php");
    exit;
}
$pdo->commit();

// get new EHIKES indxNo
$newhikeReq = "SELECT `indxNo` FROM `EHIKES` WHERE `pgTitle`=? AND `usrid`=?;";
$newhike    = $pdo->prepare($newhikeReq);
$newhike->execute([$pgTitle, $userid]);
$newhikeno  = $newhike->fetch(PDO::FETCH_ASSOC);
$hikeNo     = $newhikeno['indxNo'];

if ($type === 'Cluster') {
    $clusidReq = "SELECT `clusid` FROM `CLUSTERS` WHERE `group`=?;";
    $clusid = $pdo->prepare($clusidReq);
    $clusid->execute([$cname]);
    $id = $clusid->fetch(PDO::FETCH_ASSOC);
    $clushikeReq = "INSERT INTO `CLUSHIKES` (`indxNo`,`pub`,`cluster`) " .
    "VALUES (?,'N',?);";
    $clushike = $pdo->prepare($clushikeReq);
    $clushike->execute([$hikeNo, $id['clusid']]);
}

$redirect = "editDB.php?tab=1&hikeNo=" . $hikeNo;
header("Location: {$redirect}");
