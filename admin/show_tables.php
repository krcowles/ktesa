<?php
/**
 * A simple script to list all the tables currently residing in the
 * connected database.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.php>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$list = showTables($pdo, '');
$show = $list[0];
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Show Database Tables</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the USERS Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <style type='text/css'>
        body { 
            background-color: #eaeaea;
            margin: 0px; }
    </style>
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">SHOW Database Tables</p>
<p id="active" style="display:none">Admin</p>

<div style="margin-left:16px;font-size:18px;">
    <p>Results from SHOW TABLES:</p>
    <ul>
    <?php for ($i=0; $i<count($show); $i++) : ?>
        <li><?=$show[$i];?></li>
    <?php endfor; ?>
    </ul>
    <p>DONE</p>
</div>

</body>
</html>
