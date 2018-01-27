<?php
/**
 * This script establishes a new hike in EHIKES with only the data
 * entered in the form on startNewPg.php. The user is then 
 * redirected to the editor (editDB.php).
 * 
 * @package Page_Creation
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/**
 * Database functions are required
 */
require '../mysql/dbFunctions.php';
$link = connectToDb(__FILE__, __LINE__);
$user = filter_input(INPUT_POST, 'uid');
$newname = filter_input(INPUT_POST, 'newname');
$pg = mysqli_real_escape_string($link, $newname);
$mrkr = filter_input(INPUT_POST, 'marker');
$qfields = "(pgTitle,usrid,stat,";
$qdata = "('{$pg}','{$user}','0',";
if ($mrkr === 'At VC') {
    $vhike = filter_input(INPUT_POST, 'vchike');
    $qfields .= "collection,";
    $qdata .= "'{$vhike}',";
} elseif ($mrkr === 'Cluster') {
    $chike = filter_input(INPUT_POST, 'clus');
    $nmepos = strpos($chike, ":") + 1;
    $clusName = substr($chike, $nmepos, strlen($chike)-$sep);
    $clusLtr = substr($chike, 0, $nmepos-1);
    $qfields .= "cgroup,cname,";
    $qdata .= "'{$clusLtr}','{$clusName}',";
}
$qfields .= "marker)";
$qdata .= "'{$mrkr}')";
$link = connectToDb(__FILE__, __LINE__);
$query = "INSERT INTO EHIKES {$qfields} VALUES {$qdata};";
$newPg = mysqli_query($link, $query) or die(
    __FILE__ . ": Failed to create new page for {$pg}: " . mysqli_error($link)
);
$hikeNo = getDbRowNum($link, 'EHIKES', __FILE__, __LINE__);
$redirect = "editDB.php?hno=" . $hikeNo . "&usr=" . $user;
header("Location: {$redirect}");
?>