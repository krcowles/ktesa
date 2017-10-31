<!DOCTYPE html>
<html lang="en-us">

<?php
/* PAGE DISPLAY DEPENDS ON SETTING SELECTED BY USER */
$geoVar = filter_input(INPUT_GET,"geo");
$tblVar = filter_input(INPUT_GET,"tbl");
if($geoVar == "ON") {
    $geoloc = true;
    // $locbox is the map overlay button
    $locBox = '<div id="geoCtrl">Geolocate Me!</div>';
} else {
    $geoloc = false;
}
$pgDivStrt = '';	
if($tblVar === "T" || $tblVar === "D") {
    $tbls = true;
    if ($tblVar === 'T') {
        $pgDivStrt .= '<div id="logo">' . "\n" .
        '<img id="hikers" src="../images/hikers.png" alt="hikers icon" />' . "\n" .
        '<p id="logo_left">Hike New Mexico</p>' . "\n" .
        '<img id="tmap" src="../images/trail.png" alt="trail map icon" />' . "\n" .
        '<p id="logo_right">w/Tom &amp; Ken</p>' . "\n" . '</div>' . "\n" .
        '<p id="trail">Sortable Table of Hikes</p>' . "\n";
    }
    /* IF the user chooses the "dynamically sized" user table for this page,
       there must be two tables: a table which contains only the viewport items;
       and a reference table (full-sized, invisible) which holds all the rows so
       that the dynamic sizing has a source from which to draw its info */

    if ($tblVar === 'D') {
        $pgDivStrt .= '<div id="map"></div>';
    }
    $pgDivStrt .= '<p id="dbug"></p>' . PHP_EOL . '<div id="refTbl">';
    $pgDivEnd = '</div>';  // end of refTbl
    if ($tblVar === 'D') {
            $pgDivEnd .= '<div id="usrTbl"></div>';
    }
    $pgDivEnd .= '<div style="margin-top:20px;"><p id="metric" ' .
            'class="dressing">Click here for metric units</p></div>';
} else {
    $tbls = false;
    /* The full page map also needs a reference table (invisible) from which to derive
    information for the google map info windows */
    $pgDivStrt .= ' <div id="map" style="width:100%"></div>
            <div id="refTbl">';
    $pgDivEnd = '</div>';
}
$mstyle = '<style type="text/css">' . "\n" .
    'html, body { height: 100%; margin: 0; padding: 0; }' . "\n" .
    '#map { height: 100%; }' . "\n" . '</style>';
?> 

<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Listing of hikes the authors have undertaken in New Mexico" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <?php 
        if ($tblVar === 'T') {
            echo '<link href="../styles/logo.css" type="text/css" ' .
                'rel="stylesheet" />' . "\n";
        }
        if ($tbls === false) { 
            echo $mstyle; }
    ?>
    <link href="../styles/<?php
        if($tblVar === 'D') {
                echo 'mapTblPg.css'; 
        } elseif ($tblVar === 'T') { 
                echo 'tblPg.css';
        } else {
                echo 'mapPg.css';
        }?>" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
</head>

<body>

<?php
if ($tblVar !== 'T') {
    echo '<p id="geoSetting">';
    if($geoloc === true) {
        echo 'ON</p>';
        echo $locBox;
    } else {
        echo 'OFF</p>';
    }
    echo '<div id="newHikeBox">New Hike!<br><em id="winner"></em></div>';
}
echo $pgDivStrt;
# required for ALL cases:
$usr = 'mstr';
$age = 'old';
$show = 'all';
require "../php/TblConstructor.php";
echo $pgDivEnd;
?>
		
<script src="../scripts/modernizr-custom.js"></script>
<?php
if ($tblVar !== 'T') {
    echo '<script src="../scripts/animMap.js"></script>';
    echo '<script src="../scripts/phpDynamicTbls.js"></script>';
    echo '<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap">';
    echo '</script>';
} else {
    echo '<script src="../scripts/tblOnlySort.js"></script>';
}
?>
    
</body>

</html>
				
				
				
				
				
				
				
  			