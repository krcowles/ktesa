<?php
/**
 * This module present the admin with a form allowing addition of a book
 * to the database table "BOOKS"
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Add Book to Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Add Book to BOOKS Table</p>
<p id="active" style="display:none">Admin</p>

<div style="margin-left:24px;">
<p style="font-size:18px;"><strong>Enter the data below for the new
    book - both fields must be present:</strong></p>
<form action="updteBks.php" method="POST">
<fieldset>
<legend style="font-size:16px;color:brown;">New Book Data</legend>
<label style="font-size:18px;">Enter the Author's Name: [200 Characters Max]</label>
<input style="font-size:14px;" id="auth" type="text" name="author" 
    size="40" maxlength="200" /><br /><br />
<label style="font-size:18px;">Enter the Book Title: [200 Characters Max</label>
<input style="font-size:14px;" id="title" type="text" name="title"
    size="65" maxlength="200" /><br/><br />

<input class="btn btn-secondary" type="submit" value="Add this book">
</fieldset>
</form>
</div>

</body>
</html>
