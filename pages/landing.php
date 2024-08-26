<?php
/**
 * This is the mobile landing site for New Mexico Hikes.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require_once "../php/global_boot.php";
require_once "../accounts/getLogin.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Mobile site for New Mexico Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/landing.css" type="text/css" rel="stylesheet" />
    <style>
        #membership {
            width: 200px; height: 34px; color: darkgreen;
            font-size: 18px; font-weight: bold;
        }
    </style>
</head>

<body>

<div id="logo">
    <div id="pattern"></div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike New Mexico</span>
        </div>
        
        <!-- minimal functionality menu -->
        <div id="center">
            <label for="membership">Membership</label>&nbsp;&nbsp;
            <select id="membership">
                <option id="sao"    value="sao">Select An Option:</option>
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

<p id="cookie_state"><?= $_SESSION['cookie_state'];?></p>
<?php if (isset($_SESSION['cookies'])) : ?>
<p id="cookies_choice"><?= $_SESSION['cookies'];?></p>
<?php endif; ?>
<?php if (isset($admin) && $admin) : ?>
<p id="admin">admin</p>
<?php endif; ?>

<div class="container">
    <h2 id="welcome">Welcome to New Mexico Hikes!</h2>
    <h3>A free site where members can create their own hikes</h3>  
    <p id="usrview">Choose from the following viewing options:</p>
    <div>
        <div id="choice1">
            <p>Hike Table</p>
            <img id="table" src="../images/tbl.jpg"  alt="image of table of hikes" />
        </div>
        <div id="choice2">
            <p>Map &amp; markers</p>
            <img id="map" src="../images/newMap.jpg" alt="map with markers" />
        </div>
    </div><br />
</div>

<script src="../scripts/jquery.js"></script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/landing.js"></script>
<script src="../scripts/loginState.js"></script>
</body>

</html>
