<?php
/**
 * This script allows an admin to remove a hike from the HIKES table,
 * including it's associated entries in GPSDAT, REFS and TSV.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$hikeNo = filter_input(INPUT_GET, 'hno');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Remove Hike from EHIKES</title>
    <meta charset="utf-8" />
    <meta name="description" content="Select hike to release from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/tables.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Remove EHIKE <?= $hikeNo;?></p>
<p id="active" style="display:none">Admin</p>

<div style="margin-left:16px;font-size:20px;">
<?php
    echo '<p style="font-size:24px;color:brown;">UNDER CONSTRUCTION</p>';
?>
</div>
<script src ="delete.js"></script>

</body>
</html>
