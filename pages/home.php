<?php
/**
 * This is the home page for the ktesa site when not being viewed by a mobile
 * device. It consists of a google map with markers indicating hike locations,
 * and a side table showing all the hikes in the viewing area, along with some
 * links, info, and a thumbnail for each.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
$GLOBALS[$entitiesISO8859];
$entitiesISO8859 = array(
    'Agrave' => '#192',
    'Aacute' => '#193',
    'Acirc'  => '#194',
    'Atilde' => '#195',
    'Auml'   => '#196',
    'Aring'  => '#197',
    'AElig'  => '#198',
    'Ccedil' => '#199',
    'Egrave' => '#200',
    'Eacute' => '#201',
    'Ecirc'  => '#202',
    'Euml'   => '#203',
    'Igrave' => '#204',
    'Iacute' => '#205',
    'Icirc'  => '#206',
    'Iuml'   => '#207',
    'ETH'    => '#208',
    'Ntilde' => '#209',
    'Ograve' => '#210',
    'Oacute' => '#211',
    'Ocirc'  => '#212',
    'Otilde' => '#213',
    'Ouml'   => '#214',
    'Oslash' => '#216',
    'Ugrave' => '#217',
    'Uacute' => '#218',
    'Ucirc'  => '#219',
    'Uuml'   => '#220',
    'Yacute' => '#221',
    'THORN'  => '#222',
    'szlig'  => '#223',
    'agrave' => '#224',
    'aacute' => '#225',
    'acirc'  => '#226',
    'atilde' => '#227',
    'auml'   => '#228',
    'aring'  => '#229',
    'aelig'  => '#230',
    'ccedil' => '#231',
    'egrave' => '#232',
    'eacute' => '#233',
    'ecirc'  => '#234',
    'euml'   => '#235',
    'igrave' => '#236',
    'iacute' => '#237',
    'icirc'  => '#238',
    'iuml'   => '#239',
    'eth'    => '#240',
    'ntilde' => '#241',
    'ograve' => '#242',
    'oacute' => '#243',
    'ocirc'  => '#244',
    'otilde' => '#245',
    'ouml'   => '#246',  // there is no #247
    'oslash' => '#248',
    'ugrave' => '#249',
    'uacute' => '#250',
    'ucirc'  => '#251',
    'uuml'   => '#252',
    'yacute' => '#253',
    'thorn'  => '#254',
    'yuml'   => '#255'
);
require "../php/global_boot.php";
require "autoComplHikes.php";
// find level at which pictures directory resides
$current = getcwd();
$startDir = $current;
$ups = 0;
$rels = '';
while (!in_array('pictures', scandir($current))) {
    chdir('..');
    $current = getcwd();
    $ups++;
    $rels .= '../';  // used in passing info to javascript, below
    if ($ups > 5) { 
        throw new Exception("Can't find pictures directory!");
    }
}
chdir($startDir);  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Mexico Hikes</title> 
    <meta charset="utf-8">
    <meta name="description"
          content="Listing of hikes the authors have undertaken in New Mexico" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/home.css" type="text/css" rel="stylesheet" />    
    <link rel="stylesheet" href="../styles/jquery-ui.css" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>

</head>

<body>
<!-- body tag must be read prior to invoking bootstrap.js -->
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Find Your Hike!</p>
<p id="active" style="display:none">Home</p>

<div id="map"></div>

<div id="adjustWidth" class="custom"></div>
<div id="sideTable"></div>

<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<div class="ui-widget">
  <style type="text/css">
      ul.ui-widget {
        width: 300px;
        clear: both;
      }
  </style>
  <input id="search" placeholder="Search for a Hike" />
</div>
 
<div id="pgnote">Click on clusters to zoom in!</div>

<ul id="specchars" style="display:none">
    <?=$charli;?>
</ul>
<?php
require "../php/mapJsData.php";
require "getFavorites.php";
?>

<script>
    // data required for map and side tables (from mapJsData.php)
    var CL = <?=$jsClusters;?>;    // cluster hikes
    var NM = <?=$jsHikes;?>;       // normal hikes
    var tracks = <?=$jsTracks;?>;
    var allHikes = <?=$jsIndx;?>;
    var locations = <?=$jsLocs;?>;
    var pages = <?=$jsPages;?>;    // page indxNo for non-hikes
    var pgnames = <?=$jsPageNames;?>;
    var favlist = <?=$favlist;?>;
    var thumb    = '<?=$rels;?>' + 'pictures/thumbs/';
    var preview  = '<?=$rels;?>' + 'pictures/previews';
    var loadSpreader; // interval timer for spacing out thumbnail loads
    var cluster_click = false; // linked to clicking a clusterer marker
    var hikeSources = <?=$jsItems;?>;
</script>
<script src="../scripts/markerclusterer.js"></script>
<script src="../scripts/map.js"></script>
<script src="../scripts/sideTables.js"></script>
<script async defer src="<?=Google_Map;?>"></script>

</body>
</html>