<?php
/**
 * If the panel has determined that a session no has not been used for
 * the specified amount of time, the user will be notified to that effect.
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
    <title>Session Expired</title>
    <meta charset="utf-8" />
    <meta name="description" content="User session has expired" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/ktesaNavbar.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #eaeaea; }
        #msg { margin-left: 24px; }
    </style>
</head>
<body>

<div id="logo">
    <div id="pattern"></div> <!-- ktesa pattern bar -->
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>

<div id="msg">
    <h2>Your login session has expired</h2>
    <h3>As a member, you may automatically re-login:
        <a href="../pages/home.php" target="_self">Click here</a></h3>
    <h3>[Mobile or other:] Otherwise use this link to log in:
        <a href="unifiedLogin.php?form=log" target="_self">Login Page</a></h3>
</div>

</body>
</html>