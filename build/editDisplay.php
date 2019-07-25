<?php
/**
 * This script will create a table of hikes available for editing
 * PHP Version 7.1
 * 
 * @package Edit
 * @author  Tom Sanderg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$usr = filter_input(INPUT_GET, 'usr');
$age = 'new';
$show = 'usr';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>In-Edit Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Form for updating new hike data" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="tables.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>
<?php require "../pages/pageTop.php"; ?>
<p id="trail">Select In-Edit Hike to Display</p>
<div style="margin-left:16px">
<h3>
    Click on the hike to display its web page in its current state. Note that
    the displayed format may not be the final format, depending on the amount of 
    information contained on the page.
</h3>
<?php
require '../php/makeTables.php';
?>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="editDisplay.js"></script>
</body>
</html>
