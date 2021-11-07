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
    <meta name="description" content="'About' Page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/responsiveAbout.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>
<body>
    
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaNavbar.php"; ?>

<div>
    <p id="banner">Authors on Deception Pk</p>
    <div id="authors">
        <img id="tompic" src="../images/TomAndKenSummit.JPG" 
            alt="Authors on Deception Peak" title="Authors on Deception Peak" />
    </div>
    <div id="intro">
        <p id="introp" class="lht" ><span id="hikes">The Hikes:</span> all hikes
            have been undertaken by the authors. They cover a broad, cross-state
            array of possibilities in the diverse terrain of New Mexico -
            from short treks, to longer, uphill/downhill ascents.
        </p> 
        <p id="features" class="lht">
            One of the features of this site 
            is that not all of the hikes listed can be found in popular hiking books,
            or even in on-line trail apps.
        </p>
    </div>
    <p id="expect">The Mobile Site:</p>
    <div id="expl">
        <p class="lht">
        More features are available when viewed on a laptop or desktop. Hike
        page creation/editing is one example.
        </p>
        
        <div id="m" class="twisty-right"></div>
        <p id="mapfeat" class="maindisp">&nbsp;Map Features
            
        <ul id="mul" class="dashed lht">
            <li>Maps are standard Google maps, which allow pan, zoom and full-screen
                    display.
            </li>
            <li>Marker pins (purple) display "info-windows" when clicked, showing
                    hike details, and containing links to the corresponding website
                    and Google directions to the location of the hike's trail head.
            </li>
            <li>When zoomed in sufficiently, tracks indicating the trail or path
                    the authors took will display, with arrows indicating
                    the direction the authors traveled. Mousing over a track will
                    also display an info-window with a link to the hike page.
            </li>
        </ul>
        <div id="t" class="twisty-right"></div>
        <p id="tblfeat" class="maindisp lht">&nbsp;Table Only Features:</p>
        <ul id = 'tul' class="dashed">
            <li>A brief summary of hike statistics is presented, and also links
                to the corresponding hike page.
            </li>
            <li>You can filter the table of hikes by radial distance from a
                set of pre-defined regions.
            </li>
        </ul>
        <div id="h" class="twisty-right"></div>
        <p id="hikefeat" class="maindisp lht">&nbsp;Hike Pages:</p>
        <ul id='hul' class="dashed lht">
            <li>The hikes pages include a local map showing the hike track
                and an interactive elevation chart, showing the elevation at any
                point along the track.
            </li>
            <li>As you cursor along the chart, the corresponding hike location
                shows up on the local map.
            </li>
            <li>You can mark a hike as a favorite - and show all favorites from
                the menu.
            <li>Each page also includes trail descriptions,
                links to related materials, and sample photos taken on the hike,
                as well as other nearby hikes, and ocassionaly alternate routes.
            </li>
            <li>The hike 'gpx' files may be downloaded, or you may view a 
                full page version of the hike track & photos.
            </li>
        </ul>
        <p id="disclaimers" class="lht">Some of the hikes occurred prior to
            certain environmental changes which have rendered them no longer
            accessible, or accessible only in modified form. Primarily:</p>
        <ul id="disasters" class="lht">
            <li> Fires: Cerro Grande (Los Alamos, 2000); Los Conchas (Valles Caldera,
            2011); Gila (2012); Dog Head (Monzano Mtns, 2016)</li>
            <li> Floods: Bandelier (2011); Rinconada Canyon (2013)</li>
            <li> Volcanoes & Earthquakes: we hope not!</li>
        </ul>
        <p class="lht">If you see something that you'd like to comment on -
            suggestions, improvements, things not working, etc...</p>
        <div id="caveat"> <a href="mailto:admin@nmhikes.com">email us!</a>
        </div>
    </div>
</div>

<script src="../scripts/logo.js"></script>
<script src="../scripts/about.js"></script>
</html>
