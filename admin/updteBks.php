<?php
/**
 * This script inserts a new book entered on the form "addBook.php" into
 * the BOOKS table in MySQL.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">New Book Added</p>
<p id="active" style="display:none">Admin</p>

<div style="margin-left:24px;font-size:18px;">
    <p>New Book Successfully Added to BOOKS Table</p>
    <p style="color:brown;"><?= $author;?> ; <?= $title;?></p>
</div>

</body>
</html>
