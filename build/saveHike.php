<?php 
	session_start();
 ?>
<!DOCTYPE html>
<html>

<head>
	<title>Write Hike File</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Write hike data to TblDB.csv" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>
<div style="margin-left:12px;padding:8px;">
<?php
	/* get last used hike No.. */
	$database = '../data/test.csv';
	$handle = fopen($database, "c+");
	if ($handle !== false) {
		while ( ($line = fgets($handle)) !== false ) {
			$dbLineArray = str_getcsv($line,",");
		}
	} else {
		echo "<p>Could not open database file</p>";
	}
	echo " ...Start import...";
	# NEW HIKE INDX STARTS AT LAST INDX + 1:
	$newHike[0] = intval($dbLineArray[0]) + 1;
	$newHike[1] = $_POST['hname'];
	$newHike[2] = $_POST['hlocale'];
	$newHike[3] = $_POST['hmarker'];
	# define text for marker type
	if ($newHike[3] == 'center') {
		$newHike[3] = 'Visitor Ctr';
		$msg = "New Yellow Marker will be added on the map page for this Visitor Center Index;" .
			" the page will begin with no hikes listed in its table";
	} elseif ($newHike[3] == 'ctrhike') {
		$newHike[3] = "At VC";
		$msg = "No Marker will be added to the map page for this hike, as it will be " .
			"listed in the Visitor Center Index Page, and appear in the info window for " .
			"the Center's yellow marker. The hike will also initially appear at the " .
			"bottom of the Index Table of Hikes as a separate hike";
	} elseif ($newHike[3] == 'cluster') {
		$newHike[3] = 'Cluster';
		$msg = "This hike will be added to the others in the group: " . $_POST['htool'] .
			", which is currently already indicated by a Blue Marker";
	} else {
		$newHike[3] = 'Normal';
		$msg = "A Red Marker will be added to the map page for this hike. The hike will " .
			"initially appear at the bottom of the Index Table of Hikes";
	}
	$newHike[4] = '';  // NOTE: Index page cluster string updated in previous displayHikePg.php
	$newHike[5] = $_POST['hclus'];
	$newHike[6] = $_POST['htype'];
	$newHike[7] = $_POST['hmiles'];
	$newHike[8] = $_POST['hfeet'];
	$newHike[9] = $_POST['hdiff'];
	$newHike[10] = $_POST['hfac'];
	$newHike[11] = $_POST['hwow'];
	$newHike[12] = $_POST['hseas'];
	$newHike[13] = $_POST['hexp'];
	$newHike[14] = $_POST['htsv'];
	$newHike[15] = $_POST['hmap'];
	$newHike[16] = $_POST['hchart'];
	$newHike[17] = $_POST['hgpx'];
	$newHike[18] = $_POST['htrk'];
	$newHike[19] = $_POST['hlat'];
	$newHike[20] = $_POST['hlon'];
	$newHike[21] = $_POST['hadd1'];
	$newHike[22] = $_POST['hadd2'];
	$newHike[23] = $_POST['hphoto1'];
	$newHike[24] = $_POST['hphoto2'];
	$newHike[25] = $_POST['hdir'];
	/* Tips [Y/N] & HikePg.html OBSOLETE: [26], [27] */
	$newHike[26] = '';
	$newHike[27] = '';
	$newHike[28] = $_POST['htool'];
	$newHike[29] = $_SESSION['row0'];
	$newHike[30] = $_SESSION['row1'];
	$newHike[31] = $_SESSION['row2'];
	$newHike[32] = $_SESSION['row3'];
	$newHike[33] = $_SESSION['row4'];
	$newHike[34] = $_SESSION['row5'];
	$newHike[35] = $_POST['hcaps'];
	# COMMAS IN THE ABOVE DATA KILL THE SAVE...
	$newHike[36] = $_POST['hplnks'];
	$newHike[37] = $_POST['httxt'];
	$newHike[38] = $_POST['hinfo'];
	$newHike[39] = $_POST['href'];
	$newHike[40] = $_POST['hpdat'];
	$newHike[41] = $_POST['hadat'];
	ksort($newHike, SORT_NUMERIC);
	#$csvData = implode(',',$newHike);
	#fputs($handle, $csvData."\n");
	fputcsv($handle,$newHike);
	echo "<h1>HIKE SUCCESSFULLY SAVED!</h1>";
	echo "<h2>" . $msg . "</h2>";
# DEBUG OUTPUT ---
	/*
	$listOut = array("Hike Index No.","Hike Name","Locale","Marker","Indx. Cluster String","Cluster Letter",
		"Hike Type","Length","Elevation Change","Difficulty","Facilities","Wow Factor",
		"Seasons","Exposure","tsv File","Geomap","Elevation Chart","Geomap GPX",
		"Track File","Latitude","Longitude","Additonal Image1","Additional Image2",
		"Ken's Photo Album","Tom's Photo Album","Google Directions","OBS: Trail Tips?","OBS: Page.html",
		"Cluster Group Label","Row0 HTML","Row1 HTML","Row2 HTML","Row 3HTML","Row4 HTML",
		"Row5 HTML","Captions","Photo Links","Tips Text","Hike Info","References","Proposed Data",
		"Actual Data");
	echo "<br />NEW: ";
	for ($i=0; $i<42; $i++) {
		if ($i === 29 || $i === 30 || $i === 31 || $i === 32 || $i === 33 || $i === 34) {
			echo "Not outputting row" . ($i - 29) . " ;";
		} else {
			echo $listOut[$i] . "-> " . $newHike[$i] . "<br />";
		}
	}
	*/
?>
</div>
<div data-ptype="hike" data-indxno="<?php echo $newHike[0];?>" style="padding:16px;" id="more">
	<button style="font-size:16px;color:DarkBlue;" id="same">Edit this hike</button><br />
	<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different hike</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="saveHike.js"></script>
<script src="postEdit.js"></script>
</body>
</html>