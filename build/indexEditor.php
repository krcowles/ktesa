<!DOCTYPE html>
<html lang="en-us">
<head>
	<title>Select Index Page To Edit</title>
	<meta charset="utf-8" />
	<meta name="description"
            content="Edit a given hike" />
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
<p id="trail">Save Index Page Changes</p>

<div style="padding:16px;">Select the index page you wish to edit by clicking on the "Web Pg" for the
index as listed in the table below.</div>
<div><br />
<?php 
		require "../php/TblConstructor.php";
?>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="indexEditor.js"></script>
</body>
</html>