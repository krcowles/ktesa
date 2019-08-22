<?php
/**
 * This script inserts a new book entered on the form "addBook.php" into
 * the BOOKS table in MySQL.
 * PHP Version 7.1
 * 
 * @package ADMIN
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require_once "../php/global_boot.php";
$author = filter_input(INPUT_POST, 'author');
$title = filter_input(INPUT_POST, 'title');
$bkReq ="INSERT INTO BOOKS (title,author) VALUES(:title,:author);";
$addbk = $pdo->prepare($bkReq);
$addbk->bindValue(":title", $title);
$addbk->bindValue(":author", $author);
$addbk->execute();
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Book Added</title>
    <meta charset="utf-8" />
    <meta name="description" content="Add book to BOOKS table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">New Book Added</p>
<p id="page_id" style="display:none">Admin</p>

<div style="margin-left:24px;font-size:18px;">
    <p>New Book Successfully Added to BOOKS Table</p>
    <p style="color:brown;"><?= $author;?> ; <?= $title;?></p>
</div>
<script src="../scripts/menus.js"></script>

</body>
</html>
