<?php
/**
 * This script collects the data from the database needed to construct the html 
 * used to display various tables of hikes via 'makeTables.php'.
 * 'makeTables.php' can be invoked in four different scenarios:
 *  1.  By 'mapPg.php' from the main/index page, 
 *      Here it is used to display ALL hikes and index pages regardless of 
 *      usrid; [show=all, usr=x, table=HIKES (ie age=old)];
 *  2.  By 'hikeEditor.php' from the 'Display Options: Edit Hikes' buttons
 *      on the main/index page;
 *      Here it is used to display ONLY hikes which can be edited by the usrid;
 *        a. Editing of newly created hikes or in-edit hikes;
 *           [show=usr, usr=usr, table=EHIKES (ie age=new)]
 *        b. Editing of a published hike which is not currently in 
 *           edit mode [show=usr, table=HIKES (ie age=old): if usr='mstr, show=all]
 *  3.  By 'editDisplay.php' from the 'Display Options: Preview In-Edit Hike'
 *      on the main/index page; display ONLY hikes which are in-edit by the 
 *      usrid. [show=usr, table=EHIKES (ie age=new)]
 *  4.  By 'admintools.php' : 'reldel.php'
 *      Here it is used to list ALL EHIKES (for master) to release or delete:
 *      [show=all, usr='mstr', table=EHIKES (ie age=new)]
 *  Each 'calling' script must set the $show, $usr, and $age (table) parameters;
 *  In all cases, the .js will direct the web page link to the proper location.
 *  PHP Version 7.0
 * 
 * @package Hike_Table
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../mysql/dbFunctions.php";
// Icons used for table display:
$indxIcon
    = '<img class="webShift" src="../images/indxCheck.png" alt="index checkbox" />';
$webIcon = '<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" />';
$dirIcon = '<img src="../images/dirs.png" alt="google driving directions" />';
$mapIcon
    = 'class="gotomap" src="../images/mapit.png" alt="Zoom-to-map symbol" />';
$sunIcon = '<img class="expShift" src="../images/sun.jpg" alt="Sunny icon" />';
$partialIcon = '<img class="expShift" src="../images/greenshade.jpg" '
    . 'alt="Partial shade icon" />';
$shadeIcon = '<img class="expShift" src="../images/shady.png" '
    . 'alt="Partial sun/shade icon" />';
// undisplayed data:
$hikeHiddenDat = array();
$hikeMarker = array();
$hikeColl = array();
$hikeGroup = array();
// displayed data:
$groupName = array();
$hikeLocale = array();
$hikeName = array();
$hikeWow = array(); 
$pgLink = array();
$mapLink = array();    
$hikeLgth = array();
$hikeElev = array();
$hikeDiff = array();
$hikeExpIcon = array();
$hikeDirections = array();
$hikeAlbum = array();
// formulate the database query based on predefined variables:
if ($age === 'new') {
    $status = '[';  // editing new hikes requires gathering the 'stat' field
    $enos = '[';    // and their corresponding EHIKES indxNo's
    $query = 'SELECT * FROM EHIKES';
    if ($show === 'usr') {
        $query .= " WHERE usrid = '{$usr}'";
    }
} elseif ($age === 'old') {
    $query = 'SELECT * FROM HIKES';
    if ($show === 'usr' && $usr !== 'mstr') {
        $query .= " WHERE usrid = '{$usr}'";
    }
    $status = '[]';
    $enos = '[]';
} else {
    die("Unrecognized age parameter: " . $age);
}
$query .= ';';
$link = connectToDb(__FILE__, __LINE__);
// Now execute the query:
$tblquery = mysqli_query($link, $query) or die(
    __FILE__ . " Line " . __LINE__ . " Failed to select data from table: " .
        mysqli_error($link)
);
$entries = mysqli_num_rows($tblquery);
// adjust link based on caller location
if ($show !== 'all') {
    $url_prefix = '../pages/';
} else {
    $url_prefix = '';
}
// assign row data
for ($i=0; $i<$entries; $i++) {
    $row = mysqli_fetch_assoc($tblquery);
    if ($age === 'new') {
        $status .= '"' . $row['stat'] . '",';
        $enos .= '"' . $row['indxNo'] . '",';
    }
    $indx = $row['indxNo'];
    $hikeLat = $row['lat'];
    $hikeLon = $row['lng'];
    $hikeTrk = $row['trk'];
    $hikeHiddenDat[$i] = 'data-indx="' . $indx . '" data-lat="' . $hikeLat .
        '" data-lon="' . $hikeLon . '" data-track="' . $hikeTrk . '"';
    $hikeMarker[$i] = $row['marker'];
    $hikeColl[$i] = $row['collection'];
    $hikeGroup[$i] = $row['cgroup'];
    $groupName[$i] = $row['cname'];
    $hikeLocale[$i]= $row['locale'];
    $hikeName[$i] = $row['pgTitle'];
    $hikeWow[$i] = $row['wow'];
    // link to page depends on marker type:
    if ($hikeMarker[$i] === 'Visitor Ctr') {
        $pgLink[$i] = $url_prefix . 'indexPageTemplate.php?hikeIndx=' . $indx;
    } else {
        $pgLink[$i] = $url_prefix . 'hikePageTemplate.php?hikeIndx=' . $indx;
    }
    $mapLink[$i] = '<img id="' . $indx . '" ' . $mapIcon;
    $hikeLgth[$i] = $row['miles'];
    $hikeElev[$i] = $row['feet'];
    $hikeDiff[$i] = $row['diff'];
    $hikeExposure = $row['expo'];
    if ($hikeExposure == 'Full sun') {
        $hikeExpIcon[$i] = $sunIcon;
    } elseif ($hikeExposure == 'Mixed sun/shade') {
        $hikeExpIcon[$i] = $partialIcon;
    } else {
        $hikeExpIcon[$i] = $shadeIcon;
    }
        //$hikeLinkIcon = $webIcon;
    $hikeDirections[$i] = $row['dirs'];
    $hikeAlbum[$i] = $row['purl1'];
}
if ($age === 'new') { // forming javascript array data
    $status = substr($status, 0, strlen($status)-1);
    $status .= ']';
    $enos = substr($enos, 0, strlen($enos)-1);
    $enos .= ']';
}
mysqli_free_result($tblquery);