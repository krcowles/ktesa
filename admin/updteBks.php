<?php
/**
 * This script inserts a new book entered on the form "addBook.html" into
 * the BOOKS table in MySQL
 * 
 * @package ADMIN
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$auth = filter_input(INPUT_POST, 'author');
$author = mysqli_real_escape_string($link, $auth);
$name = filter_input(INPUT_POST, 'title');
$title = mysqli_real_escape_string($link, $name);
$bkReq ="INSERT INTO BOOKS (title,author) VALUES('{$title}','{$author}');";
$addbk = mysqli_query($link, $bkReq) or die(
    "Failed to add book to BOOKS table: " . mysqli_error($link)
);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Book Added</title>
    <meta charset="utf-8" />
    <meta name="description" content="Add book to BOOKS table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">New Book Added</p>
<div style="margin-left:24px;font-size:18px;">
    <p>New Book Successfully Added to BOOKS Table</p>
    <p style="color:brown;"><?= $author;?> ; <?= $title;?></p>
</div>
</body>
</html>