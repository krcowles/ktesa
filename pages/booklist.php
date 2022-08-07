<?php
/**
 * Create a list of all hiking books contained in the database.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$bookReq = "SELECT `title`,`author` FROM BOOKS;";
$books = $pdo->query($bookReq)->fetchAll(PDO::FETCH_ASSOC);
$list = '<ol>';
foreach ($books as $book) {
    $list .= '<li><span class="title">' . $book['title'] . '</span> by: ' .
        $book['author'] . '</li>';
}
$list .= '</ul>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Mexico Hikes</title> 
    <meta charset="utf-8">
    <meta name="description"
          content="List of NM Hiking books" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" /> 
    <link href="../styles/books.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<!-- body tag must be read prior to invoking bootstrap.js -->
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Hiking Books Used on This Site</p>

<div id="content">
    <p>The following list comprises all book references used on this site</p>
    <?=$list;?>
</div><br />
