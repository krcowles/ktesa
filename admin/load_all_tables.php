<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Load All Tables</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        #progress { width: 420px; height: 36px; background-color: #ace600; }
        #bar { width: 0px; height: 36px; background-color: #aa0033; }
    </style>
    <script src="../scripts/jquery-1.12.1.js"></script>
<body>
<?php require "../pages/pageTop.php"; ?>
<p id="trail">Loading Database</p>
<div style="margin-left:16px;">
<p>Please wait until the 'DONE' message appears below</p>
<div id="progress">
    <div id="bar"></div>
</div>
<p id="done" style="display:none;color:brown;">DONE: Tables imported successfully</p>
<script src="load_progress.js"></script>
<?php
/**
 * The loader.php script performs the actual uploading of the database.sql file.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require '../php/global_boot.php';
require 'loader.php';
?>
</div>
