<?php
/**
 * The landing page is the point of entry for mobile users.
 * This version eliminates the former service_worker-laden
 * code and offers members a separate offline maps/gps tracking
 * app.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "../accounts/getLogin.php";
$member_id = $_SESSION['userid'] ?? 0; // also see legacy.js
$startup = "not_member";
// If visitor is a member, check to see if the userid is in MEMBER_PREFS:
// insert defaults if not; then read value of 'app' field
if ($member_id > 0) {
    $appExists = "INSERT IGNORE INTO `MEMBER_PREFS` (userid, wpt_format) " .
    "VALUES (?,?)";
    $existsQuery = $pdo->prepare($appExists);
    $existsQuery->execute([$member_id, 'deg']);
    // retrieve the current value of 'app': may be NULL
    $appRequest = "SELECT `app` FROM `MEMBER_PREFS` WHERE `userid`=?";
    $appQuery = $pdo->prepare($appRequest);
    $appQuery->execute([$member_id]);
    $appField = $appQuery->fetch(PDO::FETCH_ASSOC);
    // if not NULL, will be 'noshow', 'ios', or 'android'
    $startup = $appField['app'] ?? 'show';
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Mobile site for New Mexico Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
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
        
        <!-- minimal functionality "navbar" (all enabled by default) -->
        <div id="ctr">
            <select id="membership">
                <option id="sao"     value="sao">Member Options:</option>
                <option id="login"   value="login">Login</option>
                <option id="logout"  value="logout">Logout</option>
                <option id="offline_app" value="offline_app">Get Mobile App</option> 
                <option id="bam"     value="bam">Become a member</option>    
            </select>
        </div>

        <div id="rightside">
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <span id="logo_right">w/Tom &amp; Ken</span>
        </div>
    </div>   
</div>
<p id="cookie_state"><?=$_SESSION['cookie_state'];?></p>
<p id="appMode"><?=$appMode;?></p>
<p id="active">Landing</p>
<p id="member"><?=$member_id;?></p>
<p id="startup_modal" style="display:none;"><?=$startup;?></p>

<dialog id="sw_issue">
    <p>Service worker could not be deleted; admin notified</p><br />
    <button id="sw_ok_btn" type="button" class="btn btn-sm btn-success">
        OK
    </button><br /><br />
</dialog> 
<dialog id="cache_issue">
    <p>Service worker deleted; CACHE not deleted; admin notified</p><br />
    <button id="cache_ok_btn" type="button" class="btn btn-sm btn-success">
        OK
    </button><br /><br />
</dialog> 

<h2 id="welcome">The New Mexico Hiking Site</h2>
<div class="landing_content">
    <p id="opts">Choose from the following
        <span id="vopts"> viewing options:</span></p>

    <div id="usr_choices">
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
        </div>
    </div>
    <div id="bennies">
        <span id="mem_intro">Membership is free!</span><br />Benefits include :
        <ul id="memlist">
            <li>Free Offline Map & GPS Tracking App</li>
            <li>Save/Display Favorites</li>
            <li>Create/Edit hike pages<br /><em>[Laptops/desktops only]</em></li>
        </ul>
    </div>
</div>
<?php require "../pages/mobileAppModals.html";?>

<script src="../scripts/bootstrap.min.js"></script>
<script src="../scripts/loginState.js"></script>
<script src="../scripts/viewMgr.js"></script>
<script src="../scripts/landing.js"></script>
<script src="../scripts/ktesaOfflineDB.js"></script>
<script src="../scripts/cacheDeleteFct.js"></script>
<script src="../scripts/legacy.js"></script>

</body>
