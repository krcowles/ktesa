<?php
/**
 * This page will display the passed kml file in google maps
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$kml = filter_input(INPUT_GET, 'kml');
$kml_url = 'https://nmhikes.com/gpx/' . $kml;
$callBack = str_replace('initMap', 'initKml', Google_Map);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>KML Map</title>
    <meta charset="utf-8" />
    <meta name="description" content="Display incoming kml file on map" />
    <meta name="author" content="Ken Cowles" />
    <style type="text/css">
        #kmap {
            width: 90%;
            min-height: 800px;
        }
    </style>
</head>
     
<body>
<p id="kmlfile" style="display:none;"><?=$kml_url;?></p>
<div id="kmap"></div>
<script src="../maps/kmlmap.js"></script>
<script async defer src="<?=$callBack;?>"></script>
</body>
</html>
