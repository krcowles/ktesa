<?php
/**
 * ----- FOR RESPONSIVE DESIGN -----
 * This script collects the data from the database needed to construct the html 
 * that is used to display various tables of hikes on the responsive page:
 * 'pages/responsiveTable.php'. It is used to display ALL hikes and cluster
 * pages for the userid userid; [show=all, table=HIKES (ie age=old)];
 * PHP Version 7.4
 * 
 * @package Ktesa
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
$locales = [];
$area_hikes = []; // for creating groups of area hikes
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

    //locale requires extra processing
    $loc = $row['locale'];
    $hikeLocale[$i] = $loc; 
    if (!in_array($loc, $locales)) {
        array_push($locales, $loc);
        $area_hikes[$row['locale']] = array($indx);  
    } else {
        array_push($area_hikes[$row['locale']], $indx);
    }

    $hikeWow[$i]    = $row['wow'];
    $hikeName[$i]   = $row['pgTitle'];
    $clusPg = array_key_exists($hikeName[$i], $clusters) ?
        $clusters[$hikeName[$i]] : false;
    $pgLink[$i] = $url_prefix . 'responsivePage.php?hikeIndx=' . $indx;
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
$ddlist = '';
$regions = '<select id="regions">' . PHP_EOL;
$regions .= '<option value="">Select One</option>' . PHP_EOL;
sort($locales, SORT_STRING);
for ($j=0; $j<count($locales); $j++) {
    $regions .= '<option value="' . $locales[$j] . '">' . $locales[$j] .
        '</option>' . PHP_EOL;
    $ddlist .= '<li><a id="dd' . $j . '" class="dropdown-item" href="#">' .
        $locales[$j] . '</a></li>' . PHP_EOL;
}
$regions .= '</select>' . PHP_EOL;
$locale_groups = json_encode($area_hikes);
