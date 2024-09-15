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
    <link href="../styles/about.css" type="text/css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="../scripts/popper.min.js"></script>
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
            a look at a variety of hikes, all undertaken by the authors and
            contributing members in 
            <em class="blue">The Land of Enchantment</em>. These hikes cover a broad,
            cross-state array of possibilities, all found in the diverse terrain of
            New Mexico - from short treks, to longer, uphill/downhill adventures.
        </p> 
        <p id="features">
            One of the features of this site 
            is that many of the hikes listed cannot be found in popular hiking books,
            or even in on-line trail apps! Check out "What You Will See" below.
        </p>
    </div>
    <p id="expect">WHAT YOU WILL SEE:</p>
    <div id="expl">
        <p><img id="mrkrs" src="../images/newMap.jpg" alt="map symbols"
        align="left" />
            The home page for this site displays a large map with <em class="red">
            red</em>&nbsp;&nbsp;'starbursts', or <em class="blue">blue</em>
            map markers. Each
            icon contains text indicating the number of hikes to be found there.
            Blue markers are 'end point' markers, and indicate trail heads.
            Starbursts are groups of markers - click on any starburst and it will
            zoom in and decompose into map markers (and possibly more starbursts). 
        </p>
        <p>The home page also shows a side table of all hikes appearing in the
            currently viewable portion of the map. Each hike has essential statistics
            listed, along with a 'preview' image on the right side. If you mouse over
            that image, you will see a bigger, more viewable image of it. The icons
            in the side table provide an easy way to zoom into a hike (magnifying
            glass icon) or mark it as a favorite (yellow star - members only). All
            of your favorites can be displayed on a separate page by selecting 
            <em>Explore->Show Favorites</em> in the menu bar. Clicking on the
            trailname hyperlink takes you directly to the hike page. You can also
            change the side table's width by dragging it's left edge.
        </p>

        <div id="n" class="twisty-right"></div>
        <p id="navfeat" class="maindisp">&nbsp;Navigation Bar</p>
        <ul id="nul" class="dashed">
            <li>The navigation bar/menu appears at the top of every viewable page.
                Some options are enabled only by becoming a member (free!).
            </li>
            <li>The 'Explore' menu allows a user to select the home page or the
                table page (see below). When a user becomes a member, a third option
                is enabled: 'Show Favorites', which brings up a page showing only the
                member's marked favorite hikes. 
            </li>
            <li>The 'Contribute' menu is only enabled for members to: 1) Create a 
                new hike page; 2) Continue editing a page already in-edit; 3) Edit
                an existing, published page (that will remain unchanged on the site
                until the edited version is published); 4) Request a hike to be 
                pubished when editing is completed.
            </li>
            <li>The 'Filter/Sort' menu offers options for displaying the side table
                on the home page. The menu item only appears when the home page is
                displayed.
            <li>Any user or member can upload and edit a gpx file - delete, add, or
                move trackpoints - and save the edited version without changing the
                original.
            </li>
            <li>A user may sign up for free with no obligations by selecting the
                '<em>Members->Become a member</em>' menu item, and existing members
                will find other options under the 'Members' menu.
            </li>
            <li>To get a list of the 10 most recent hikes taken by the authors,
                select the corresponding 'More...' menu item, where a few other
                miscellaneous options appear
            </li>
            <li>If you are interested in stewardship of this site, click on 
                "Own This Site"!
            </li>
        </ul>

        <div id="m" class="twisty-right"></div>
        <p id="mapfeat" class="maindisp">&nbsp;Home Page Map Features</p>
        <ul id="mul" class="dashed">
            <li>Maps are standard Google maps. Some of the standard google controls
                appear on the page, such as the 'full-page' map option, a +/- zoom
                control, and a drop-down box allowing the user to select either the
                'Terrain' display or the 'Satellite' view.
            </li>
            <li>Marker pins (blue) display an "info-window" when clicked. That
                window shows hike details, and contains links to the corresponding
                hike page, as well as Google directions to the location of the hike's
                trail head.
            </li>
            <li>When zoomed in sufficiently, tracks indicating the trail or path
                the authors took will display, with arrows indicating
                the direction the authors traveled. Mousing over a track will
                also display an info-window with a link to the hike page.
            </li>
            <li>The side table of hikes may be filtered or sorted via the
                'Filter/Sort' menu item. When filtered, the list of hikes in the
                side table and the viewport will be adjusted
                to show only the filtered hikes. Sorting applies only to the
                hikes currently showing in the side table.
            </li>
            <li>The width of the side table can be changed to allow more or less
                viewing space for the map. Simply mouse over the vertical
                double-bar at the left side of the table, click and hold, and 
                drag the bar left or right.
            </li>
            <li>A powerful feature of the home page is the search bar, where a 
                user may begin typing and a list of hikes will appear whose
                name contains the sequence of letters typed. It is not necessary
                to capitalize names. Selecting one of the available choices will
                populate the searchbar and zoom to the hike selected, also showing
                its info window.
            </li>
            <li>Users may zoom in on any hike by clicking the magnifying glass
                icon adjacent to a hike in the side table. Members (only) can also 
                designate a 'favorite' by clicking the star above that icon.
                A yellow star is not marked as a favorite, while a red star
                indicates that the member has marked the hike as a favorite.
                A separate page showing only the favorites can be displayed by
                selecting the <em>Explore->Show Favorites</em> meny item.
            </li>
        </ul>

        <div id="t" class="twisty-right"></div>
        <p id="tblfeat" class="maindisp">&nbsp;Table Only Features:</p>
        <ul id = 'tul' class="dashed">
            <li>The table of hikes is intended to provide a brief summary of hike
                statistics, and links to the corresponding hike page, along
                with Google map driving directions.
            </li>
            <li>The table of hikes initially displayed is sorted in ascending
                alphabetic order, but clicking on any column header (excepting
                the 'By Car' Google directions column) allows alphabetical (or
                numerical) sorting of the column. The first click will sort in
                ascending order, each successive click will reverse the sort order.
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
            <li>As you cursor along the elevation chart, the corresponding location
                on the trail shows up on the interactive map.
            </li>
            <li>For hikes with multiple tracks, the tracks will be available for
                display by clicking on their respective checkboxes in the 'track
                box' area, located in the upper right corner of the map. The topmost
                track checked will have its data reflected in the sidebar
                statistics, and will also be the track displayed in the elevation
                chart.
            </li>
            <li>If there were photos included that had GPS metadata, they are
                viewable on the map as marker icons. Mousing over the marker
                displays a small preview of the image. Click on the marker to get a
                slightly larger image displayed. Clicking on the larger image will
                take you to a separate page with the photo further enlarged.
            </li>
            <li>Each page also includes trail descriptions, a list of any available
                references to the hike - including links to websites or online apps,
                sample photos taken on the hike, a list of nearby hikes (if any),
                and ocassionaly, alternate routes.
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
            accessible, or accessible only in modified form. These are not
            always marked as such, so please check on a trail's accessibility
            prior to undertaking it. Notably, the following events have
            occurred altering accessibility:
        </p>
        <ul id="disasters">
            <li> Fires: Cerro Grande (Los Alamos, 2000); Los Conchas (Valles Caldera,
            2011); Gila (2012); Dog Head (Monzano Mtns, 2016); Cerro Pelado, Cook's
            Peak, Calf Canyon/Hermit's Peak (SF Nat'l Forest, 2022), Black (Gila
            Nat'l Forest, 2022); Ruidoso/Salt Fire (Lincoln Nat'l Forest, 2024); and
            other smaller fires
        </li>
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
