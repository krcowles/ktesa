<!DOCTYPE html>
<html>

<?php
	$geoVar = $_GET["geo"];
	$tblVar = $_GET["tbl"];
	if($geoVar == "ON") {
		$geoloc = true;
		// $locbox is the map overlay button
    	$locBox = '<div id="geoCtrl">Geolocate Me!</div>';
   	} else {
		$geoloc = false;
	}
	if($tblVar == "ON") {
		$tbls = true;
		/* IF the user chooses the "dynamically sized" user table for this page,
		   there must be two tables: a table which contains only the viewport items;
		   and a reference table (full-sized, invisible) which holds all the rows so
		   that the dynamic sizing has a source from which to draw its info */
		$mapDivStrt = '<div class="container">
			<div id="map"></div>
			<p id="dbug"></p>
			<div id="refTbl">';
		$mapDivEnd = '</div>
				<div id="usrTbl"></div></div>';  // last </div> is for "container" class div

	} else {
		$tbls = false;
		/* The full page map also needs a reference table (invisible) from which to derive
		information for the google map info windows */
		$mapDivStrt = ' <div id="map" style="width:100%"></div>
    			<div id="refTbl">';
    	$mapDivEnd = '</div>';
	}
	$mstyle = "<style type='text/css'>
    	html, body { height: 100%; margin: 0; padding: 0; }
    	#map { height: 100%; } </style>";
?> 

<head>
	<title>New Mexico Hikes</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
			content="Listing of hikes the authors have undertaken in New Mexico" />
	<meta name="author"
			content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
			content="nofollow" />
	<?php if($tbls === false) echo $mstyle;?>
	<link href="../styles/<?php if($tbls === true) echo 'mapTblPg.css'; else echo 'mapPg.css';?>"
		type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
</head>

<body>

<p id="geoSetting"><?php
	if($geoloc === true) {
		echo "ON";
		echo $locBox;
	} else {
		echo "OFF";
	}?></p>
<div id="newHikeBox">New Hike!<br><em id="winner"></em></div>
<?php
	if($tbls === false) {
		echo $mapDivStrt;
		require "../php/TblConstructor.php";
		echo $mapDivEnd;
	} else {
		echo $mapDivStrt;
		require "../php/TblConstructor.php";
		echo $mapDivEnd;
	}
?>
		
<script src="../scripts/modernizr-custom.js"></script>
<script src="../data/hikeArrays.js"></script>
<script src="../scripts/phpDynamicTbls.js"></script>
<script src="../scripts/newMap.js"></script>
<script async defer
	src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap">
</script>
    
</body>

</html>
				
				
				
				
				
				
				
  			