<?php
/**
 * This page will appear only if the site is in 'Production' mode and an
 * error/exception is encountered [Production mode is where the error
 * and exception handlers are specified]. Since it is not guaranteed that
 * the ktesaPanel can be successfully invoked, only the ktesa logo appears
 * on this page.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>

<html lang="en-us">
<head>
    <title>Error Encountered</title>
    <meta charset="utf-8" />
    <meta name="description" content="User notice of problem encountered" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
        body { 
            background-color: #eaeaea;
            margin: 0px;
        }
        #logo {
            display: inline-block;
            vertical-align: top;
            margin: 0px;
            padding: 0px;
            width: 100%;
            height: 52px;
            margin-bottom: 6px;
            margin-right: 12px;
            background-color: #ffe866;
            z-index: 1;
            text-align: center;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
        }
        #pattern {
            background-image: url("../images/southwest_pattern.png");
            background-repeat: repeat-x;
            background-attachment: scroll;
            margin-top: 2px;
            padding: 0px;
            height: 16px;
        }
        .logo_items {
            display: inline-block;
        }
        #leftside {
            float: left;
            margin-bottom: 0px;
        }
        #ctr {
            clear: both;
            margin-top: 4px;
            margin-bottom: 0px;
        }
        #rightside {
            float: right;
            margin-bottom: 0px;
        }
        #hikers {
            float: left;
            margin-top: 3px;
            margin-left: 10px;
            z-index: 100;
        }
        #tmap {
            float: right;
            margin-right: 8px;
            margin-top: 4px;
            z-index: 100;
        }
        #logo_left {
            float: left;
            margin-left: 12px;
            margin-top: 4px;
            color: darkslategray;
            z-index: 5;
        }
        #ctr {
            font-size: 18px;
            font-weight: bold;
        }
        #logo_right {
            float: right;
            margin-right: 10px;
            margin-top: 5px;
            color: darkslategray;
            z-index: 5;
        }
    </style>
    <script src="../scripts/jquery.js"></script>
</head>

<body>

<div id="logo">
    <div id="pattern"></div> <!-- ktesa pattern bar -->
    <div id="leftside" class="logo_items">
        <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
        <p id="logo_left">Hike New Mexico</p>
    </div>
    <div id="ctr" class="logo_items">
        Problem Encountered
    </div>
    <div id="rightside" class="logo_items">
        <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        <p id="logo_right">w/Tom &amp; Ken</p>
    </div>
</div>

<div style="margin-left:16px;font-size:20px;color:brown">
    <p style="margin:0;font-weight:bold;">We are sorry, but a problem has
        occurred while processing your request.</p>
    <p style="margin:0;text-indent:30px;color:black;">An email has been
        sent to the web master with details, and the problem will be
        investigated promptly.
    </p>
    <p>You may wish to try again at a later date/time.
        Thanks for your patience!</p> 
    <p>NOTE: If you continue to encounter this issue after several days, please
        send an email to the system admin with details about what you were
        trying to do when the issue occurred. Include info like hike name,
        whether viewing or editing, how you arrived at the problem page, etc.
        Send to admin@nmhikes.com</p>
</div>

<script src="../scripts/logo.js"></script>
</body>
</html>
