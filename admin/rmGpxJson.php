<?php
/**
 * This file is accessed via the form submit of cleanGpxJson.php. Any
 * checked files will be deleted from their respective directories.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$extGpx  = isset($_POST['egpx'])  ? $_POST['egpx'] :  false;
$extJSON = isset($_POST['ejson']) ? $_POST['ejson'] : false;
if ($extGpx !== false) {
    foreach ($extGpx as $gpx) {
        $loc = '../gpx/' . $gpx;
        unlink($loc);
    }
}
if ($extJSON !== false) {
    foreach ($extJSON as $json) {
        $loc = '../json/' . $json;
        unlink($loc);
    }
}
$nothing = !$extGpx && !$extJSON ? true : false;

?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Cleanup GPX and JSON files</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for extraneous photos" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <style type="text/css">#content {margin-left:24px;}</style>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Gpx & JSON File Cleanup</p>
<p id="active" style="display:none">Admin</p>

<div id="content">
<?php if ($nothing) : ?>
    <p>No files were removed</p>
<?php else : ?>
    <?php if ($extGpx !== false) : ?>
        <h5>The following gpx files were removed:</h5>
        <?php 
        foreach ($extGpx as $gpx) {
            echo $gpx . "<br />";
        }
        echo "<br />";
        ?>
    <?php endif; ?>
    <?php if ($extJSON !== false) : ?>
        <h5>The following JSON files were removed:</h5>
        <?php 
        foreach ($extJSON as $json) {
            echo $json . "<br />";
        }
        ?>
    <?php endif; ?>
<?php endif; ?>
</div>

</body>
</html>
