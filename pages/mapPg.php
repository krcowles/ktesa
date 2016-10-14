<!DOCTYPE html>
<html>

<?php
	if($_GET["geo"] == "ON") {
		$geoloc = true;
    	$locBox = '<div id="geoCtrl">Geolocate Me!</div>';
   	}
	else
		$geoloc = false;
	if($_GET["tbl"] == "ON") {
		$tbls = true;
		$mapDiv = '<div class="container">
				<div id="map"></div>
				<p id="dbug"></p>
				<div id="refTbl"></div>
				<div id="usrTbl"></div></div>';
	}
	else {
		$tbls = false;
		$mapDiv = ' <div id="map" style="width:100%"></div>
    			<div id="refTbl"></div>';
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
	if($geoloc === true)
		echo "ON";
	else
		echo "OFF";?></p>
<?php if($geoloc === true) echo $locBox;?>
<div id="anbox">New Hike!<br><em id="winner"></em></div>
<?php echo $mapDiv;?>
		
<script src="../scripts/modernizr-custom.js"></script>
<script src="../data/hikeArrays.js"></script>
<script src="../scripts/dynamicTbls.js"></script>
<script src="../scripts/map.js"></script>
<script async defer
	src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap">
</script>
    
</body>

</html>
				
				
				
				
				
				
				
  			