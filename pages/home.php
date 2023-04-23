<?php
/**
 * This is the home page for the ktesa site when not being viewed by a mobile
 * device. It consists of a google map with markers indicating hike locations,
 * and a side table showing all the hikes in the viewing area, along with some
 * links, info, and a thumbnail for each hike in the side table.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();

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
    <link href="../styles/home.css" rel="stylesheet" />    
    <link href="../styles/jquery-ui.css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
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
    <input id="search" class="search" placeholder="Search for a Hike" />
    <span id="clear">X</span>
</div>
 
<button id="advantages" type="button" class="btn btn-success">
    Why not AllTrails?</button>

<?php
require "../php/mapJsData.php";
require "getFavorites.php";
?>
<div id="alltrails" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Why Not Use AllTrails?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                AllTrails is a great app developed by a great team of designers. Why
                would anyone (in New Mexico) therefore, choose to use this app? While
                the authors (just two of us) cannot and are not competing with
                AllTrails, there are a few pluses to using this site:
                <ol id="nmhikes">
                    <li>The site contains more than a dozen New Mexico hikes not
                        listed on AllTrails</li>
                    <li>Many of the hike pages offer alternative hikes:
                        longer, shorter, or unique extensions or shortcuts to
                        existing hikes
                    </li>
                    <li>A member can add photos which will appear on the trail map at
                        the location where they were taken
                    </li>
                    <li>A member can add flags or other markers to indicate special 
                        features or points of interest, or useful information at
                        various points on the map. (These might be, for example, 
                        Waypoints from a Garmin or other device)
                    </li>
                    <li>You can edit a gpx file with the gpxEditor GUI
                    </li>
                    <li>The hike page maps have many (over 50) map 'overlays'
                        available - these allow looking at the trail from aerial,
                        hybrid, relief, topographic or other views from Open Source
                        Maps, Google Maps, CalTopo maps, National Geogrpahic maps,
                        ArcGIS, USGS, etc.
                    </li>
                    <li>The 'Table Page' (menu option) allows searching, sorting, and
                        filtering of hikes a selectable distance from either a
                        location, or another hike. It also allows a user the
                        opportunity to view several user-selected hikes on a separate
                        page.
                    </li>
                    <li>Hike pages also list references - books, websites, blogs,
                        apps, magazine articles, on-line maps, etc.
                    </li>
                </ol>
                These are features not found on AllTrails. The author still uses
                AllTrails often for hikes not yet documented on nmhikes, and it is a
                great app, for sure. It contains useful information not provided by
                this site. Sometimes both sites can be useful when taken together.
                <p>This website was a retirement project for two engineers who like
                    to stay busy, and is limited to the authors' residence state of
                    New Mexico
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
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
    var hikeSources = <?=$jsItems;?>;
    window.newBounds = false;
</script>
<script src="../scripts/markerclusterer.js"></script>
<script src="../scripts/map.js"></script>
<script src="../scripts/sideTables.js"></script>
<script src="../scripts/homepg_filter.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>

</body>
</html>