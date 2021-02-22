<?php
/**
 * This script collects the data from the database needed to construct the html 
 * that is used to display various tables of hikes via 'makeTables.php'.
 * The 'makeTables.php' script can be invoked in three different scenarios:
 *  1.  By 'pages/tableOnly.php' ['Explore->Table Only]
 *      $pageType = 'FullTable'
 *      Here it is used to display ALL hikes and cluster pages regardless of 
 *      userid; [show=all, table=HIKES (ie age=old)];
 *  2.  By 'build/hikeEditor.php' [Contribute->...]
 *      Here it is used to display ONLY hikes which can be edited by the userid;
 *      $pageType = 'Editor'
 *        a. Editing of newly created hikes or already in-edit hikes;
 *           [...Continue Editing Your Hike]
 *           [show=usr, table=EHIKES (ie age=new)];
 *           NOTE: The admin can see all hikes in edit (modified to show=all)
 *        b. Editing of a published hike (not currently in edit mode)
 *           [...Edit Your Published Hike]
 *           NOTE: The admin can edit any published hike.
 *           [show=usr, table=HIKES (ie age=old): show=all for admin]      
 *  3.  By 'admin/reldel.php'
 *      $pageType = 'Publish'
 *      Here it is used to list ALL EHIKES (for master) to release or delete:
 *      [show=all, table=EHIKES (ie age=new)]
 *  Each 'calling' script must set the $show, $age (table), and $pageType
 *  parameters; In all cases, the .js will direct the web page link to the proper
 * location.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
// Icons used for table display:
$mapIcon
    = 'class="gotomap" src="../images/mapit.png" alt="Zoom-to-map symbol" />';
$dirIcon = '<img src="../images/dirs.png" alt="google driving directions" />';
$sunIcon = '<img class="expShift" src="../images/fullSun.jpg" alt="Sunny icon" />';
$partialIcon = '<img class="expShift" src="../images/partShade.jpg" '
    . 'alt="Partial shade icon" />';
$shadeIcon = '<img class="expShift" src="../images/goodShade.jpg" '
    . 'alt="Partial sun/shade icon" />';
if ($show !== 'all') {
    $userid = $_SESSION['userid'];
}
// Get Cluster Data:
// NOTE: New Cluster Pages in-edit will have negative integers in 'page'
$clusReq = "SELECT `group`,`page` FROM `CLUSTERS` WHERE `page` <> 0;";
$clusters = $pdo->query($clusReq)->fetchAll(PDO::FETCH_KEY_PAIR);

// HTML data- attributes, not visible to user
$hikeHiddenDat = array();
// displayed data:
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
$hikeGpx = array();
// formulate the database query based on predefined variables:
if ($age === 'new') {
    $status = '[';  // editing new hikes requires gathering the 'stat' field
    $enos = '[';    // and their corresponding EHIKES indxNo's
    $query = "SELECT * FROM `EHIKES`ORDER BY `pgTitle`";
    if ($show === 'usr') {
        $query .= " WHERE `usrid` = :userid";
    }
} elseif ($age === 'old') {
    $query = "SELECT * FROM `HIKES` ORDER BY `pgTitle`";
    if ($show === 'usr') {
        $query .= " WHERE usrid = :userid";
    }
    $status = '[]';
    $enos = '[]';
} else {
    throw new Exception("Unrecognized age parameter: " . $age);
}
$query .= ';';
// Now execute the query:
if ($show === 'all') {
    $tblquery = $pdo->query($query);
} else {
    $tblquery = $pdo->prepare($query);
    $tblquery->bindValue("userid", $userid);
    $tblquery->execute();
}
$entries = $tblquery->rowCount();
// adjust link based on caller location
$url_prefix = '';
if ($show !== 'all') {
    $url_prefix = '../pages/';
}
// assign row data
for ($i=0; $i<$entries; $i++) {
    $row = $tblquery->fetch(PDO::FETCH_ASSOC);
    if ($age === 'new') {
        $status .= '"' . $row['stat'] . '",';
        $enos .= '"' . $row['indxNo'] . '",';
    }
    $indx    = $row['indxNo'];
    $hikeLat = $row['lat']/LOC_SCALE;
    $hikeLon = $row['lng']/LOC_SCALE;
    $hikeTrk = $row['trk'];
    // HTML data- attributes (not visible to user)
    $hikeHiddenDat[$i] = 'data-indx="' . $indx . '" data-lat="' . $hikeLat .
        '" data-lon="' . $hikeLon . '" data-track="' . $hikeTrk . '"';

    $hikeLocale[$i] = $row['locale'];
    $hikeWow[$i]    = $row['wow'];
    $hikeName[$i]   = $row['pgTitle'];
    $clusPg = array_key_exists($hikeName[$i], $clusters) ?
        $clusters[$hikeName[$i]] : false;
    $pgLink[$i] = $url_prefix . 'hikePageTemplate.php?hikeIndx=' . $indx;
    // Include additional query string parm if 'Cluster Page':
    if ($clusPg) {
        $pgLink[$i] .= '&clus=y';
    }
    $mapLink[$i]  = '<img id="' . $indx . '" ' . $mapIcon;
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
    $hikeGpx[$i] = $row['gpx'];
}
if ($age === 'new') { // forming javascript array data
    if (strlen($status) !== 1) {
        $status = substr($status, 0, strlen($status)-1);
    }
    $status .= ']';
    if (strlen($enos) !== 1) {
        $enos = substr($enos, 0, strlen($enos)-1);
    }
    $enos .= ']';
}
