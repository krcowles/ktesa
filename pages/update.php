<?php
/** 
 * Whenever the admin initiates a software update for mobile users,
 * this page will appear when the user next enters the site.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$userid = filter_input(INPUT_GET, 'usr');
$latest = filter_input(INPUT_GET, 'ver');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title>Software Update</title>
    <meta charset="utf-8" />
    <meta name="description" content="Auto updates for mobile offline" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <style type="text/css">
        #process {margin-left: 3rem;}
        #proc_hdr {color: darkmagenta; font-size: 1.3rem;}
        #userid {display: none;}
        #latest_ver {display: none}
        #adminmsg {color: brown;}
    </style>
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<dialog id="update">
    <p>You now have the latest offline-maps software</p><br />
    <button id="ok" type="button" class="btn btn-sm btn-success">
        OK
    </button><br /><br />
</dialog> 
<dialog id="noupdate">
    <div id="errormail">A software upgrade did not complete.<br />
        Unfortuantely automated email cannot be guaranteed.<br />
        Please email <span id="adminmail"></span> and attach the
        following message (we will work to resolve this issue):<br />
        <span id="adminmsg"></span><br /><br />Thank you!
    </div><br />
    <button id="notok" type="button" class="btn btn-sm btn-success">
        Close
    </button><br /><br />
</dialog> 

<p id="userid"><?=$userid;?></p>
<p id="latest_ver"><?=$latest;?></p>
<div id="process">
    <p id="proc_hdr">
        Checking software versions &hellip;
    </p>
</div>

<script src="../scripts/cacheDeleteFct.js"></script>
<script src="../scripts/update.js"></script>
</body>

</html>
