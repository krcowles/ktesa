<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
	<title>Create CSV File</title>
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
	$newOrgHike = $_POST['hvcgrp'];
	if ($newOrgHike !== '') {
		$addLoc = intval($newOrgHike);
	} else {
		$addLoc = 0; // there is no 0 hike index...
	}
	/* get last used hike No.. */
	$listOut = array("Hike Index No.","Hike Name","Locale","Marker","Indx. Cluster String","Cluster Letter",
		"Hike Type","Length","Elevation Change","Difficulty","Facilities","Wow Factor",
		"Seasons","Exposure","tsv File","Geomap","Elevation Chart","Geomap GPX",
		"Track File","Latitude","Longitude","Additonal Image1","Additional Image2",
		"Ken's Photo Album","Tom's Photo Album","Google Directions","Trail Tips?","NO PAGE HTML FILE",
		"Cluster Group Label","Row0 HTML","Row1 HTML","Row2 HTML","Row 3HTML","Row4 HTML",
		"Row5 HTML","Captions","Photo Links","Tips Text","Hike Info","References","Proposed Data",
		"Actual Data");
	$database = '../data/test.csv';
	$handle = fopen($database, "c+");
	if ($handle !== false) {
		$newHike[4] = '';
		while ( ($line = fgets($handle)) !== false ) {
			$dbLineArray = str_getcsv($line,",");
			if ($addLoc > 0 && intval($dbLineArray[0]) == $addLoc) {
				$newHike[4] = $dbLineArray[4];  // may be empty
				echo "Found string: " . $newHike[4];
			}
		}
		echo "last hike is " . $dbLineArray[0];
	} else {
		echo "Could not open database file ../data/TblDB.csv";
	}
	echo " ...Start import...";
	/* imported data from step3 */
	$newHike[0] = intval($dbLineArray[0]) + 1;
	$newHike[1] = $_POST['hname'];
	$newHike[2] = $_POST['hlocale'];
	$newHike[3] = $_POST['hmarker'];
	# define text for marker type
	if ($newHike[3] == 'center') {
		$newHike[3] = 'Visitor Ctr';
	} else if ($newHike[3] == 'cluster') {
		$newHike[3] = 'Cluster';
	} else {
		$newHike[3] = 'Normal';
	}
	# [4]is index page reference only: Cluster String
	
	/* NEED TO WRITE TO INDEX, NOT THIS HIKE!!! */
	if ($newHike[4] !== '') {
		$newHike[4] = $newHike[4] . "." . $newHike[0];
	}  else {
		$newHike[4] = $newHike[0];
	}
	if ($addLoc === 0) {
		$newHike[4] = '';
	}
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
	$newHike[25] = rawurlencode($_POST['hdir']);
	/* don't need Y/N on tips */
	$newHike[26] = $_POST['htyn'];
	/* page.html becoming obsolete */
	$newHike[27] = '';
	$newHike[28] = $_POST['htool'];
	echo "Passed cluster group letter " . $newHike[5] . ", tip is " . $newHike[28];
	$newHike[29] = $_SESSION['row0'];
	$newHike[30] = $_SESSION['row1'];
	$newHike[31] = $_SESSION['row2'];
	$newHike[32] = $_SESSION['row3'];
	$newHike[33] = $_SESSION['row4'];
	$newHike[34] = $_SESSION['row5'];
	$newHike[35] = $_POST['hcaps'];
	$newHike[36] = $_POST['hplnks'];
	$newHike[37] = $_POST['trailtiptxt'];
	$newHike[38] = $_POST['hiketxt'];
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
	ksort($newHike, SORT_NUMERIC);
	$csvData = implode(',',$newHike);
	fputs($handle, $csvData."\n");
	echo "<br />NEW: ";
	for ($i=0; $i<42; $i++) {
		if ($i === 29 || $i === 30 || $i === 31 || $i === 32 || $i === 33 || $i === 34) {
			echo "Not outputting row" . ($i - 29) . " ;";
		} else {
			echo $listOut[$i] . "-> " . $newHike[$i] . "<br />";
		}
	}
?>
</div>

</body>
</html>