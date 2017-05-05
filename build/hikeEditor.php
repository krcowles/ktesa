<!DOCTYPE html>
<html lang="en-us">
<head>
	<title>Select Hike To Edit</title>
	<meta charset="utf-8" />
	<meta name="description"
		content="Select hike to edit from table" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/mapTblPg.css" type="text/css" rel="stylesheet" />
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />

</head>

<body>

<div id="logo">
	<img id="hikers" src="../images/hikers.png" alt="hikers icon" />
	<p id="logo_left">Hike New Mexico</p>
	
	<img id="tmap" src="../images/trail.png" alt="trail map icon" />
	<p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Select The Hike You Wish To Edit</p>

<div><p style="text-align:center;">When you click on the "Web Pg" link in the table
        below, you will be presented with an editable version of the hike page.</p>
</div>

<div><br />
<?php 
		require "../php/TblConstructor.php";
?>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="hikeEditor.js"></script>
</body>
</html>