<?php
/**
 * A simple script to list all the tables currently residing in the
 * connected database.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.php>
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
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <style type='text/css'>
        body { 
            background-color: #eaeaea;
            margin: 0px; }
    </style>
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
    <p id="trail">SHOW Database Tables</p>
    <p id="page_id" style="display:none">Admin</p>

    <div style="margin-left:16px;font-size:18px;">
    <p>Results from SHOW TABLES:</p>
    <ul>
    <?php for ($i=0; $i<count($show); $i++) : ?>
        <li><?=$show[$i];?></li>
    <?php endfor; ?>
    </ul>
    <p>DONE</p>
</div>
<script src="../scripts/menus.js"></script>

</body>
</html>
