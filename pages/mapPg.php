<!DOCTYPE html>
<html>

<?php
	/* PAGE DISPLAY DEPENDS ON SETTING SELECTED BY USER */
	$geoVar = $_GET["geo"];
	$tblVar = $_GET["tbl"];
	if($geoVar == "ON") {
		$geoloc = true;
		// $locbox is the map overlay button
    	$locBox = '<div id="geoCtrl">Geolocate Me!</div>';
   	} else {
		$geoloc = false;
	}
	
	if($tblVar === "T" || $tblVar === "D") {
		$tbls = true;
		$pgDivStrt = '<div class="container">';
		if ($tblVar === 'T') {
			$pgDivStrt .= '<div id="logoBlock">
				<p id="pgLogo"></p>
				<p id="logoLeft">Hike New Mexico</p>
				<p id="logoRight">w/ Tom &amp; Ken</p>
				<p id="page_title" class="grid_16">Sortable Index Table of
					Tom &amp; Ken\'s New Mexico Hikes</p>
				</div>';
		}
		/* IF the user chooses the "dynamically sized" user table for this page,
		   there must be two tables: a table which contains only the viewport items;
		   and a reference table (full-sized, invisible) which holds all the rows so
		   that the dynamic sizing has a source from which to draw its info */
		
		if ($tblVar === 'D') {
				$pgDivStrt .= '<div id="map"></div>';
		}
		$pgDivStrt .= '<p id="dbug"></p>
		<div id="refTbl">';
		$pgDivEnd = '</div>';  // end of refTbl
		if ($tblVar === 'D') {
			$pgDivEnd .= '<div id="usrTbl"></div>';
		} else {  // provide metric button for table-only page:
			$pgDivEnd .= '<div><p id="metric" class="dressing">Click here for metric units</p></div>';
		}
		$pgDivEnd .= '</div>';  // last </div> is for "container" class div
	} else {
		$tbls = false;
		/* The full page map also needs a reference table (invisible) from which to derive
		information for the google map info windows */
		$pgDivStrt = ' <div id="map" style="width:100%"></div>
    			<div id="refTbl">';
    	$pgDivEnd = '</div>';
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
	<link href="../styles/<?php
		 if($tblVar === 'D') {
		 	echo 'mapTblPg.css"'; 
		 } elseif ($tblVar === 'T') { 
		 	echo 'tblPg.css"';
		 } else {
		 	echo 'mapPg.css"';
		 }?>
		type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
</head>

<body>

<?php
	if ($tblVar !== 'T') {
		echo '<p id="geoSetting">';
		if($geoloc === true) {
			echo 'ON</p>';
			echo $locBox;
		} else {
			echo 'OFF</p>';
		}
		echo '<div id="newHikeBox">New Hike!<br><em id="winner"></em></div>';
	}
	echo $pgDivStrt;
	require "../php/TblConstructor.php";
	echo $pgDivEnd;
?>
		
<script src="../scripts/modernizr-custom.js"></script>
<?php
	if ($tblVar !== 'T') {
		echo '<script src="../scripts/animMap.js"></script>';
		echo '<script src="../scripts/phpDynamicTbls.js"></script>';
		echo '<script async defer
			src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap">';
		echo '</script>';
	} else {
		echo '<script src="../scripts/tblOnlySort.js"></script>';
	}
?>
    
</body>

</html>
				
				
				
				
				
				
				
  			