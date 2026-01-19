<?php
/**
 * This script is the landig page when the visitor is
 * not a registered member: offline tools are unavailable.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Mobile site for New Mexico Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php require "../pages/favicon.html";?>
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/landing.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>

<div id="logo">
    <div id="pattern">
    </div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike</span>
        </div>
        
        <!-- minimal functionality "navbar" -->
        <div id="ctr">
            <select id="membership">
                <option id="sao"    value="sao">Member Options:</option>
                <option id="login"  value="login">Login          </option>
                <option id="logout" value="logout">Logout        </option>
                <option id="bam"    value="bam">Become a member  </option>>      
            </select>
        </div>

        <div id="rightside">
            <span id="logo_right">w/Tom &amp; Ken</span>
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        </div>
    </div>   
</div>

<p id="cookie_state">NOLOGIN</p>
    
<h2 id="welcome">The New Mexico Hiking Site</h2>
<div class="landing_content">
    <p id="opts">Choose from the following
        <span id="vopts"> viewing options:</span></p>

    <div class="usr_choices">
        <div class="flexitem">
                <div class="pair" id="choice1">
                    <p id="tbldesc">Hike Table</p>
                    <img id="table" class="icons" src="../images/Tbl.png" 
                        alt="image of table of hikes" />
                </div>
                <div class="pair" id="choice2">
                    <p id="home">Map &amp; markers</p>
                    <img id="map" class="icons" src="../images/MapsNmrkrs.png"
                        alt="map with markers" />
                </div>
                <div class="pair" id="choice3">
                    <p class="dbl">Members Only</p>
                    <img id="nosave" class="icons" src="../images/NoSave.png"
                        alt="offlinemap icon" />
                </div>
                    <div class="pair" id="choice4">
                    <p class="dbl">Members Only</p>
                    <img id="nouse" class="icons" src="../images/NoUse.png"
                        alt="offlinemap icon" />
                </div>
        </div>
    </div>
    <div id="bennies">
        Membership is free!<br />Benefits include :
        <ul id="memlist">
            <li>Save/Display Favorites</li>
            <li>Create/Edit hike pages<br /><em>[Laptops/desktops only]</em></li>
            <li>Offline maps</li>
        </ul>
    </div>
</div>

<script src="../scripts/bootstrap.min.js"></script>
<script src="../scripts/ktesaOfflineDB.js"></script>
<script src="../scripts/loginState.js"></script>
<script src="../scripts/viewMgr.js"></script>
<script src="../scripts/cacheDeleteFct.js"></script>
<script src="../scripts/landing.js"></script>

<!-- May be unnecessary... -->
<script src="../uninstall_service_workers.js"></script>

</body>
</html>
