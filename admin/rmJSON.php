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

$extraneous  = isset($_POST['ext'])  ? $_POST['ext'] :  false;
if ($extraneous !== false) {
    foreach ($extraneous as $json) {
        $loc = '../json/' . $json;
        unlink($loc);
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Cleanup JSON files</title>
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
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">JSON File Cleanup</p>
<p id="active" style="display:none">Admin</p>

<div id="content">
<?php if (!$extraneous) : ?>
    <p>No files were removed</p>
<?php else : ?>
    <h5>The following JSON files were removed:</h5>
    <?php 
    foreach ($extraneous as $json) {
        echo $json . "<br />";
    }
    echo "<br />";
    ?>
<?php endif; ?>
</div>

</body>
</html>
