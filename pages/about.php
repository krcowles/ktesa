<?php
/**
 * This page will give the user an overview of site features
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Tom & Ken's NM Hikes</title>
    <meta charset="utf-8" />
    <!-- if search engine keys are desired, 
        use: <meta name="keywords" content="key1, key2, .." -->
    <meta name="description" content="'About' Page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/about.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="active" style="display:none">About</p>

<div id="mainabout">
    <img id="tompic" src="../images/TomAndKenSummit.JPG"
        alt="Authors on Deception Peak"
        title="Authors on Deception Peak" />
    <p id="banner">Welcome to Tom and Ken's <strong>New Mexico Hiking
        Adventures!</strong>
    </p>
    <div id="intro">
        <p id="introp">The intention of this site is to provide you, the viewer,
        a look at a variety of hikes, all undertaken by the authors in 
        <em class="blue">The Land of Enchantment</em>. These hikes cover a broad,
        cross-state array of possibilities, all found in the diverse terrain o
         New Mexico - from short treks, to longer, uphill/downhill adventures.
        </p> 
        <p id="features">
            One of the features of this site 
            is that not all of the hikes listed can be found in popular hiking books,
            or even in on-line trail apps. Check out "What You Will See" below.
        </p>
    </div>
    <p id="expect">WHAT YOU WILL SEE:</p>
    <div id="expl">
        <p><img id="mrkrs" src="../images/mapmrkrs.jpg" alt="map symbols"
        align="left" />
        The home page for this site displays a large map with <em class="red">
        red</em>, <em class="blue">blue</em>, or <em class="green">green</em>
        'cluster' markers indicating the number of hikes to be found in that area.
        Clicking on a cluster marker will zoom in on the map and decompose the
        cluster markers into groups of smaller clusters, until they appear as
        non-reducible <em class="purple">purple</em> markers. When a purple marker
        is displayed, it cannot be further decomposed, and will indicate the actual
        number of hikes in that location. Clicking on a purple marker will display
        an information window about the hike(s). 
        </p>
        <p>The home page also shows a side table of all hikes appearing in the
        currently viewable portion of the map. Each hike has essential statistics
        listed, along with a 'preview' image on the right side. If you mouse over
        that image, you will see a bigger, more viewable image of it. You may zoom
        in on any given hike, mark it as a favorite, or go directly to the
        hike's page. You can also change the side table's width by dragging it's
        left edge.
        </p>
        
        <p>There are several menu options at the top of each page allowing you
        to use other portions of the site. The main menu item, the 'Explore' menu,
        provides several viewing options. In addition to the home page described
        above, a 'Table Only' page is available. This is further described below;
        for members (you can sign up for free, and with no obligations), you will
        also see a 'Show Favorites' page option in the 'Explore' menu, which will
        show on a map all hikes in your list of favorites, along with their hike
        tracks. Members have the added benefit of being able to create their own
        hike pages with our hike page editor.
        </p>
        <div id="m" class="twisty-right"></div>
        <p id="mapfeat" class="maindisp">&nbsp;Home Page Map Features
            
        <ul id="mul" class="dashed">
            <li>Maps are standard Google maps, which allow pan, zoom and full-screen
                    display.
            </li>
            <li>Marker pins (purple) display an "info-window" when clicked. That
                window shows hike details, and contains links to the corresponding
                hike page, as well as Google directions to the location of the hike's
                trail head.
            </li>
            <li>When zoomed in sufficiently, tracks indicating the trail or path
                    the authors took will display, with arrows indicating
                    the direction the authors traveled. Mousing over a track will
                    also display an info-window with a link to the hike page.
            </li>
        </ul>
        <div id="t" class="twisty-right"></div>
        <p id="tblfeat" class="maindisp">&nbsp;Table Only Features:</p>
        <ul id = 'tul' class="dashed">
            <li>The table of hikes is intended to provide a brief summary of hike
                statistics, and links to the corresponding hike page, along
                with Google driving directions.
            </li>
            <li>The table of hikes initially displayed is sorted in ascending
                alphabetic order, but clicking on any column header (excepting
                the 'By Car' directions column) allows alphabetical (or numerical)
                sorting of the column. The first click will sort in ascending
                order, each successive click will reverse the sort order.
            </li>
            <li>You will see a 'Table Options' header above the table, offering
                different ways to view the data, described next:
            <li>You can filter the table of hikes by radial distance from either
                a location (in the drop-down box), or a specific hike. The resulting
                table may then be sorted according to hike details provided. Simply
                click on the "Filter Hikes" button to show your options.
            </li>
            <li>You may wish to see selected hikes of your own choosing displayed on
                a separate map. Click on the 'Select' button, then simply type in 
                the hike names you wish - as you type, available options will appear
                which you may click on to select. When you have finished your 
                selections, click on the "Draw Map" button.
            </li>
            <li>You can convert between English and Metric units separately on the
                main table or on filtered tables. Use the "Show Metric [English]
                Units" button.
            </li>
            <li>
                As the table is rather large, a 'speedy scroller' drop-down is
                provided to quickly place you within easy reach of your target. When 
                using that option, a 'Return to Top' button will appear to get
                you back to the top of the table.
            </li>
        </ul>
        <div id="h" class="twisty-right"></div>
        <p id="hikefeat" class="maindisp">&nbsp;Hike Pages:</p>
        <ul id='hul' class="dashed"</ul>
            <li>The hikes pages include a local map showing the hike track
                and an interactive elevation chart, showing the elevation at any
                point along the track.
            </li>
            <li>As you cursor along the chart, the corresponding hike location
                shows up on the local map.
            </li>
            <li>For hikes with multiple tracks, the tracks will be available for
                display by clicking on their respective checkboxes in the 'track
                box' area, upper right corner of the map. The topmost track displayed
                will have its data reflected in the sidebar statistics, and will be
                the track displayed in the elevation chart.
            </li>
            <li>If there were photos included that had GPS data, they are viewable
                on the map at the marker points. Mousing over the marker displays a
                small preview of the image. Click on the marker to get a
                slightly larger image displayed. Clicking on that image will take
                you to a separate page with the photo further enlarged.
            </li>
            <li>Each page also includes trail descriptions,
                links to related materials, and sample photos taken on the hike,
                as well as other nearby hikes, and ocassionaly alternate routes.
            </li>
            <li>Note: to allow for a wider view of the map and elevation chart, the
                sidebar of statistics can be hidden (by clicking on the box with
                the "<" symbol). 
            </li>
            <li>The hike 'gpx' files may be downloaded, or you may view a 
                full page version of the hike track & photos.
            </li>
        </ul>
        </p>
        <p id="disclaimers">Some of the hikes occurred prior to
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
        etc... <a href="mailto:admin@nmhikes.com">email us!</a>
    </div>
</div>
<div id="addon"></div>
<script src="../scripts/about.js"></script>

</body>
</html>
