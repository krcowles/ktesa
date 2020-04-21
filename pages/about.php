<?php
/**
 * This is the home page for the site. It utilizes php cookies, hence the 
 * .php extension instead of .html
 * PHP Version 7.1
 * 
 * @package Home
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Tom & Ken's NM Hikes</title>
    <meta charset="utf-8" />
    <!-- if search engine keys are desired, 
        use: <meta name="keywords" content="key1, key2, .." -->
    <meta name="description" content="KTESA Home Site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/about.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>
<?php require "ktesaPanel.php"; ?>
<p id="homeLabel">About This Site</p>
<p id="page_id" style="display:none">About</p>

<div id="container">
    <img id="tompic" src="../images/TomAndKenSummit.JPG" alt="Authors on Deception Peak"
        title="Authors on Deception Peak" />
    <p id="banner">Welcome to Tom and Ken's <strong>New Mexico Hiking
        Adventures!</strong>
    </p>
    <div id="intro">
        <p id="introp">The intention of this site is to provide you, the viewer,
        a look at a variety of hikes, all undertaken by the authors in 
        <em>The Land of Enchantment</em>. These hikes cover a broad, cross-state
        array of possibilities, all found in the diverse terrain of New Mexico -
        from short treks, to longer, uphill/downhill ascents.
        </p> 
        <p id="features">
            One of the features of this site 
            is that not all of the hikes listed can be found in popular hiking books,
            or even in on-line trail apps. Check out "What You Will See" below.
        </p>
    </div>
    <p id="expect">WHAT YOU WILL SEE:</p>
    <div id="expl">
        <p>
        Depending on your selection above, you will see either: a simple interactive
        'table of hikes', a large map with an interactive 'table of hikes' below it,
        or a full-page map (only). The maps include marker pins identifying the
        locations of the available hikes. More details about the maps, tables, and
        hike pages follow.
        </p>
        <div id="m" class="twisty-right"></div>
        <p id="mapfeat" class="maindisp">&nbsp;Map features
            (both Full-Page and Map-With-Table):</p>
        <ul id="mul" class="dashed">
            <li>Maps are standard Google maps, which allow pan, zoom and full-screen
                    display.
            </li>
            <li>Marker pins display "info-windows" when clicked, showing hike
                    details, and containing links to the corresponding website and
                    directions to the location of the hike.
            </li>
            <li>When zoomed in sufficiently, tracks indicating the trail or path
                    the authors took will display, with arrows indicating
                    the direction the authors traveled.
            </li>
            <li>Yellow pins indicate Visitor Center locations for National or
                    State Parks and Monuments, or other public/private information
                    center buildings (e.g. Ghost Ranch). These markers may also
                    contain a list of hikes, when the hikes begin at the center.
            </li>
            <li>Blue pins indicate trailheads in close proximity to each other. When
                    clicking on a blue pin, all hikes are shown with their
                    corresponding links, and one link to the most common trailhead.
            </li>
            <li>Red pins represent all other hikes, and will show hike stats and
                    site links.
            </li>
        </ul>
        <div id="t" class="twisty-right"></div>
        <p id="tblfeat" class="maindisp">&nbsp;Table features:</p>
        <ul id = 'tul' class="dashed">
            <li>The table of hikes is intended to provide a brief summary of hike
                statistics, and also links to the corresponding website, google
                directions, or that hike's Flickr album.
            </li>
            <li>The table of hikes initially displayed is not sorted in any
                particular order, but clicking on a column header allows alphabetical
                (or numerical) sorting. The first click will sort in ascending order,
                each successive click will reverse the sort order.
            </li>
            <li><em id="itals">As you zoom in and/or pan to different locations</em>,
                the table of hikes below the map will list only those hikes that
                can currently be seen on the map.
            </li>
            <li>As you move your mouse over different rows, that row will be
                highlighted for easy reference.
            </li>
        </ul>
        <div id="h" class="twisty-right"></div>
        <p id="hikefeat" class="maindisp">&nbsp;Hike pages:</p>
        <ul id='hul' class="dashed"</ul>
            <li>The hikes pages include a local map showing the hike track
                (where available), and an interactive elevation chart, showing
                the elevation along the track.
            </li>
            <li>As you cursor along the chart, the corresponding hike location
                shows up on the local map.
            </li>
            <li>Each page also includes trail descriptions,
                links to related materials, and sample photos taken on the hike.
            </li>
            <li>Note: to allow for a wider view of the map and elevation chart, the
                sidebar of statistics can be hidden (by clicking on the box with
                the "<" symbol). To see the photo albums containing the authors'
                photographs, click the corresponding photo album symbol in the table
                of hikes, or on the hike page.
            </li>
        </ul>
        <p id="linkNote">
            <em>Note: All of the links in the table (underlined hike or trail name)
            will open new windows (or tabs)</em>.
        </p>
        <p id="disclaimers">A number of the
            hikes occurred before the authors' acquisition of a GPS system, and
            hence will not have geomaps available to view. In those cases, a static
            map will be provided. Furthermore, some of the hikes occurred prior to
            certain environmental changes which have rendered them no longer
            accessible, or accessible only in modified form. Primarily:</p>
        <ul id="disasters">
            <li> Fires: Cerro Grande (Los Alamos, 2000); Los Conchas (Valles Caldera,
            2011); Gila (2012); Dog Head (Monzano Mtns, 2016)</li>
            <li> Floods: Bandelier (2011); Rinconada Canyon (2013)</li>
            <li> Volcanoes & Earthquakes: we hope not!</li>
        </ul>
    </div>
    <div id="caveat"> If you see something that you'd like
        to comment on - suggestions, improvements, things not working,
        etc... <a href="mailto:krcowles29@gmail.com">email us!</a>
    </div>
</div>  <!-- CONTAINER END -->
<div id="addon"></div>
<script src="../scripts/main.js"></script>
<script src="../scripts/menus.js"></script>
</html>
