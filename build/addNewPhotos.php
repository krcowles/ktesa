<?php
/**
 * This script saves the selected data to the table and redirects
 * back to the editor.
 * 
 * @package Edit
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
session_start();
$_SESSION['activeTab'] = 2;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'xno');
$usr = filter_input(INPUT_POST, 'xid');
$usepix = $_POST['incl'];
foreach ($usepix as $newphoto) {
    $findReq = "INSERT INTO ETSV (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) SELECT indxNo,folder," .
        "title,hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr," .
        "org FROM tmpPix WHERE title = '{$newphoto}'";
    $xfrpix = mysqli_query($link, $findReq) or die(
        "addNewPhotos.php: Failed to transfer pix from tmpPix to ETSV: " .
        mysqli_error($link)
    );
}
$tmpDrop = mysqli_query($link, "DROP TABLE tmpPix");
if (!$tmpDrop) {
    die("addNewPhotos.php: Failed to DROP tmpPix");
}
$redirect = "editDB.php?hno={$hikeNo}&usr={$usr}";
header("Location: {$redirect}");
