<?php
/**
 * This module present the admin with a form allowing addition of a book
 * to the database table "BOOKS"
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Add Book to Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Add Book to BOOKS Table</p>
<p id="page_id" style="display:none">Admin</p>

<div style="margin-left:24px;">
<p style="font-size:18px;"><strong>Enter the data below for the new
    book - both fields must be present:</strong></p>
<form action="updteBks.php" method="POST">
<fieldset>
<legend style="fint-size:16px;color:brown;">New Book Data</legend>
<label style="font-size:18px;">Enter the Author's Name: [200 Characters Max]</label>
<input style="font-size:14px;" id="auth" type="text" name="author" 
    size="40" maxlength="200" /><br /><br />
<label style="font-size:18px;">Enter the Book Title: [200 Characters Max</label>
<input style="font-size:14px;" id="title" type="text" name="title"
    size="65" maxlength="200" /><br/><br />
<input type="submit" name="updt" value="Add this book" /><br />
</fieldset>
</form>
</div>
<script src="../scripts/menus.js"></script>
</body>
</html>
