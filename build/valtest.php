<?php
session_start();
$_SESSION['gpx'] = '../gpx/fred.GPX';
$_SESSION['havgpx'] = true;
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
	<title>Test Driver</title>
	<link href="validateHike.css" type="text/css" rel="stylesheet" />
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>
<body>

 <div id="logo">
	<img id="hikers" src="../images/hikers.png" alt="hikers icon" />
	<p id="logo_left">Hike New Mexico</p>	
	<img id="tmap" src="../images/trail.png" alt="trail map icon" />
	<p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Test Driver</p>
<div style="margin-left:24px;font-size:18px;">
<?php
echo "<h3 style='color:darkBlue;'>Please Note!</h3>\n" . '<p>You have '
    . 'saved new data. Do not go back to the previous page and repeat '
    . 'this step or duplicate data will be created!<br />'
    . 'If it is necessary to "back up" due to an error or omission, '
    . 'please use the "Un-Validate" button below. This will open the '
    . 'enterHike form and delete data from uploaded files which would '
    . 'otherwise be duplicated.<br /><br />';
echo '<button id="unval">Un-Validate</button><br /><br />'
    . 'OTHERWISE: If you wish to stop here and return later to '
    . 'finish the page, please return to the main page and select '
    . '"Edit Hikes" (New/Active)<br /><br /><br />';
?>
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="valtest.js"></script>
</body>
</html>

