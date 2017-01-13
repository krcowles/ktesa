<?php 
	session_start();
	$redo = $_POST['remake'];
	if ($redo === 'YES') {
		$rebuild = true;
		$hikeNo = $_POST['rhno'];
	} else {
		$rebuild = false;
	}
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
	/* imported data from displayHikePg.php */
	if ($rebuild) {
		$newHike[0] = $hikeNo;
	} else {
		$newHike[0] = intval($dbLineArray[0]) + 1;
	}
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
	$tmpTsv = $_POST['htsv'];
	$pageLgth = strlen($tmpTsv) -  8;
	# strip off the relative path leader:
	$pageStrt = strpos($tmpTsv,"gpsv");
	if ($pageStrt !== -1) {
		$newHike[14] = substr($tmpTsv,$pageStrt+5,$pageLgth);
	}
	$newHike[15] = $_POST['hmap'];
	$newHike[16] = $_POST['hchart'];
	$newHike[17] = $_POST['hgpx'];
	$newHike[18] = $_POST['htrk'];
	$newHike[19] = $_POST['hlat'];
	$newHike[20] = $_POST['hlon'];
	$newHike[21] = $_POST['hadd1'];
	$newHike[22] = $_POST['hadd2'];
	$plink1 = $_POST['hphoto1'];
	$plink2 = $_POST['hphoto2'];
	$newHike[23] = rawurlencode($plink1);
	$newHike[24] = rawurlencode($plink2);
	$gdirs = $_POST['hdir'];
	$newHike[25] = rawurlencode($gdirs);
	/* don't need Y/N on tips */
	$newHike[26] = $_POST['htyn'];
	/* page.html becoming obsolete */
	$newHike[27] = '';
	$newHike[28] = $_POST['htool'];
	$newHike[29] = rawurlencode($_SESSION['row0']);
	$newHike[30] = rawurlencode($_SESSION['row1']);
	$newHike[31] = rawurlencode($_SESSION['row2']);
	$newHike[32] = rawurlencode($_SESSION['row3']);
	$newHike[33] = rawurlencode($_SESSION['row4']);
	$newHike[34] = rawurlencode($_SESSION['row5']);
	$newHike[35] = $_POST['hcaps'];  // already encoded in displayHike.php
	$newHike[36] = $_POST['hplnks'];  // already encoded in displayHike.php
	if ($rebuild) {
		$newHike[37] = $_SESSION['tips']; // already encoded in rebuildData.php
		$newHike[38] = $_SESSION['hInfo'];  // already encoded in rebuildData.php
		$newHike[39] = $_SESSION['hrefs'];  // already encoded in rebuildData.php
		$newHike[40] = $_SESSION['prop'];   // already encoded in rebuildData.php
		$newHike[41] = $_SESSION['act'];   // already encoded in rebuildData.php
	
	} else {
		$tipImport = $_POST['trailtiptxt'];
		$newHike[37] = rawurlencode($tipImport);
		$infoImport = $_POST['hiketxt'];
		$newHike[38] = rawurlencode($infoImport);
		/* COLLECT REFERENCES FROM PREVIOUS PAGE */
		$refsHtml = '<ul id="refs">';
		$any = false;
		$bks = $_POST['bk'];
		$auths = $_POST['auth'];
		for ($j=0; $j<3; $j++) {
			if ($bks[$j] !== '') {
				$refsHtml = $refsHtml . '<li>Book: <em>' . $bks[$j] . '</em>, ' . $auths[$j] . '</li>';
				$any = true;
			}
		}
		$webs = $_POST['web'];
		$wtxt = $_POST['webtxt'];
		for ($k=0; $k<3; $k++) {
			if ($webs[$k] !== '') {
				$refsHtml = $refsHtml . '<li>Website: <a href="' . $webs[$k] . '" target="_blank">' . 
					$wtxt[$k] . '</a></li>';
				$any = true;
			}
		}
		$apps = $_POST['app'];
		$apptxt = $_POST['apptxt'];
		for ($n=0; $n<3; $n++) {
			if ($apps[$n] !== '') {
				$refsHtml = $refsHtml . '<li>App: <a href="' . $apps[$n] . '" target="_blank">' . 
					$apptxt[$n] . '</a></li>';
				$any = true;
			}
		}
		if ($any) {
			$refsHtml = $refsHtml . '</ul>';
			$refEnc = rawurlencode($refsHtml);
			$newHike[39] = $refEnc;
		}

		/* COLLECT PROPOSED DATA FROM PREVIOUS PAGE */
		$any = false;
		$propMaps = $_POST['pmap'];
		$propTxt = $_POST['pmtxt'];
		$propGpx = $_POST['pgpx'];
		$propGpxTxt = $_POST['pgpxtxt'];
		$propRefs = '<ul id="plinks">';
		# NOTE: assuming that a map & gpx file coexist - this may not be so!
		for ($i=0; $i<2; $i++) {
			if ($propMaps[$i] !== '' || $propGpx[$i] !== '') {
				$propRefs = $propRefs . '<li>Map: <a hef="' . $propMaps[$i] . '" target="_blank">' .
					$propTxt[$i] . '</a></li>';
				$propRefs = $propRefs . '<li>GPX: <a href="' . $propGpx[$i] . '" target="_blank">' .
					$propGpxTxt[$i] . '</a></li>';
				$any = true;
			}
		}
		if ($any) {
			$propRefs = $propRefs . '</ul>';
			$propEnc = rawurlencode($propRefs);
			$newHike[40] = $propEnc;
		}
		/* COLLECT ACTUAL DATA FROM PREVIOUS PAGE */
		$any = false;
		$actMaps = $_POST['amap'];
		$actTxt = $_POST['amtxt'];
		$actGpx = $_POST['agpx'];
		$actGpxTxt = $_POST['agpxtxt'];
		$actRefs = '<ul id="alinks">';
		for ($i=0; $i<2; $i++) {
			if ($actMaps[$i] !== '' || $actGpx[$i] !== '') {
				$actRefs = $actRefs . '<li>Map: <a hef="' . $actMaps[$i] . '" target="_blank">' .
					$actTxt[$i] . '</a></li>';
				$actRefs = $actRefs . '<li>GPX: <a href="' . $actGpx[$i] . '" target="_blank">' .
					$actGpxTxt[$i] . '</a></li>';
				$any = true;
			}
		}
		if ($any) {
			$actRefs = $actRefs . '</ul>';
			$actEnc = rawurlencode($actRefs);
			$newHike[41] = $actEnc;
		}
	
	}
	ksort($newHike, SORT_NUMERIC);
	$csvData = implode(',',$newHike);
	if ($rebuild) {
		$rbdFile = file($database);
		foreach($rbdFile as &$hline) {
			$hikeLine = str_getcsv($hline);
			if ($hikeLine[0] == $hikeNo) {
				$hline = $csvData."\n";
			}
		}
		$newFile = implode($rbdFile);
		fclose($handle);
		$rbd = fopen($database,"w");
		fputs($rbd, $newFile."\n");
		echo "<h1>HIKE NO. " . $newHike[0] . " SUCCESSFULLY UPDATED!</h1>";
		echo "<h2>Click below to continue, or else exit this page to finish</h2>";
		echo '<button style="height:24px;" id="cont">Click here to rebuild another page</button>';
	} else {
		fputs($handle, $csvData."\n");
		echo "<h1>HIKE SUCCESSFULLY SAVED!</h1>";
		echo "<h2>" . $msg . "</h2>";
	}
/*  DEBUG OUTPUT ---
	$listOut = array("Hike Index No.","Hike Name","Locale","Marker","Indx. Cluster String","Cluster Letter",
		"Hike Type","Length","Elevation Change","Difficulty","Facilities","Wow Factor",
		"Seasons","Exposure","tsv File","Geomap","Elevation Chart","Geomap GPX",
		"Track File","Latitude","Longitude","Additonal Image1","Additional Image2",
		"Ken's Photo Album","Tom's Photo Album","Google Directions","Trail Tips?","NO PAGE HTML FILE",
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
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="saveHike.js"></script>
</body>
</html>