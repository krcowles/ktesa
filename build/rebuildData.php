<?php session_start();
# reset previous SESSION variables...
$_SESSION['row0'] = '';
$_SESSION['row1'] = '';
$_SESSION['row2'] = '';
$_SESSION['row3'] = '';
$_SESSION['row4'] = '';
$_SESSION['row5'] = '';
$_SESSION['tips'] = '';
$_SESSION['hInfo'] = '';
$_SESSION['hrefs'] = '';
$_SESSION['prop'] = '';
$_SESSION['act'] = '';
 ?>
<!DOCTYPE html>
<html>

<head>
	<title>Extracting From Table</title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
			content="Form to allow selection of hike for rebuilding" />
	<meta name="author"
			content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
			content="nofollow" />
	<link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
</head>

<body>
<form action="displayHikePg.php" method="POST">

<div>
<p style="margin-left:12px;">Building the input required to display the page with filled rows...</p>
<?php
/* The purpose of this code is to prepare all of the inputs needed by "displayHikePg.php"
	in order for that page to create the new image rows, captions and photo links */
	$hikeIndexNo = $_GET['hikeNo'];
	/* Use the common database (excel csv file) to extract info */
	$dataTable = '../data/test.csv';
	$dbLines = file($dataTable);
	foreach ($dbLines as $hike) {
		$lineDat = str_getcsv($hike,",");
		if ($lineDat[0] == $hikeIndexNo) {
			$hikeName = $lineDat[1];
			$hikeLocale = $lineDat[2];
			$hikeMarker = $lineDat[3];
			# force agreement with displayHikePg expectations...
			if ($hikeMarker == 'Visitor Ctr') {
				$hikeMarker = "center";
			} elseif ($hikeMarker == 'At VC') {
				$hikeMarker = "ctrhike";
			} elseif ($hikeMarker == 'Cluster') {
				$hikeMarker = "cluster";
			} else {
				$hikeMarker = "other";
			}
			$hikeClusterStr = '';
			$hikeClusGrp = $lineDat[5];
			$hikeType = $lineDat[6]; // old values need to be converted to recognizable
			if ($hikeType == 'out-and-back') {
				$hikeType = 'oab';
			} 
			$hikeLength = $lineDat[7];
			$hikeElevation = $lineDat[8];
			$hikeDifficulty = $lineDat[9];
			$hikeFacilities = $lineDat[10];
			$hikeWow = $lineDat[11];
			$hikeSeasons = $lineDat[12];
			$hikeExposure = $lineDat[13];
			$hikeTsv = $lineDat[14];
			# the next items may or may not be present depending on hike state
			$hikeMap = $lineDat[15];
			$hikeEChart = $lineDat[16];
			$hikeGpx = $lineDat[17];
			$hikeTrk = $lineDat[18];
			$hikeLat = $lineDat[19];
			$hikeLon = $lineDat[20];
			$hikeOthrImage1 = $lineDat[21];
			$hikeOthrImage2 = $lineDat[22];
			$hikeMainURL = $lineDat[23];
			$hikeSecURL = $lineDat[24];
			$hikeDirs = $lineDat[25];
			# [26] trailTips defined below
			$hikeHtml = $lineDat[27];
			echo "Hike empty? " . $hikeHtml;
			if ($hikeHtml == '') {
				die ("NO HTML FILE SPECIFIED IN DATABASE FOR THIS HIKE");
			}
			echo '<p style="margin-left:12px;">The html file being processed is "' . $hikeHtml .
				'". The data below is extracted from that file.</p>';
			echo '<p style="margin-left:12px;">Please review the data below for correspondence to the rebuild
			page before submitting.</p>';
			$hikeGrpNm = $lineDat[28];
			break;
		}
	}
	$dbLines = array();  #reassign as empty to free memory
	# Now retrieve remaining info from existing html page:
	$htmlFile = '../pages/' . $hikeHtml;
	$html = file($htmlFile);
	$noOfLines = count($html);
	# See if there are tip notes to process:
	$trailTips = 'N';
	$tipNotes = ''; # text only: no html tags
	$infoStart = 0;	# if no tips text, start looking through array at 0 for hikeInfo
	for ($t=0; $t<$noOfLines; $t++) {
		if (preg_match("/tipNotes/",$html[$t]) == 1) {
			$tipStart = strpos($html[$t],">") + 1;
			$tipLgth = strlen($html[$t]) - $tipStart;
			$tipNotes = substr($html[$t],$tipStart,$tipLgth);
			for ($h=$t+1; $h<$noOfLines; $h++) {
				if (preg_match("/p>/",$html[$h]) == 1) {
					$lgthToEnd = strlen($html[$h]) - 5;
					$lastline = substr($html[$h],0,$lgthToEnd);
					$tipNotes = $tipNotes . $lastline;
					$infoStart = $h + 1;
					break;
				}
				$tipNotes = $tipNotes . $html[$h];
			}
		}
	}
	if ($infoStart !== 0) {
		$trailTips = 'Y';
		echo '<div id="trailTips">' . '<img id="tipPic" src="../images/tips.png" alt="special notes icon" />' .
			"\n\t\t" . '<p id="tipHdr">TRAIL TIPS!</p>' . "\n\t\t" . '<p id="tipNotes">';
		echo $tipNotes;
		echo '</p>' ."\n\t" . '</div>';
		$_SESSION['tips'] = rawurlencode($tipNotes);
	}
	# Next: hikeInfo
	$hikeInfo = '';
	for ($i=$infoStart; $i<$noOfLines; $i++) {
		if (preg_match("/hikeInfo/",$html[$i]) == 1) {
			# build the hikeInfo div:
			$infoStart = strpos($html[$i],">") + 1;
			$infoLgth = strlen($html[$i]) - $infoStart;
			$hikeInfo = substr($html[$i],$infoStart,$infoLgth);
			for ($j=$i+1; $j<$noOfLines; $j++) {
				#advance until the last line...
				if ( preg_match("/p>/",$html[$j]) == 1 ) {
					$lgthToEnd = strlen($html[$j]) - 5;
					$lastline = substr($html[$j],0,$lgthToEnd);
					$hikeInfo = $hikeInfo . $lastline;
					$startRefs = $j+1;
					break;
				}
				$hikeInfo = $hikeInfo . $html[$j];
			}
		}
	}
	echo '<p id="hikeInfo">' . $hikeInfo . '</p>';
	$_SESSION['hInfo'] = rawurlencode($hikeInfo);
	# Next, any references: (note - all hikes should have at least "refs")
	$refsList = '';
	for ($k=$startRefs; $k<$noOfLines; $k++) {
		if (preg_match('/id="refs">/',$html[$k]) == 1) {
			# build the references div
			$refsList = $html[$k];
			for ($l=$k+1; $l<$noOfLines; $l++) {
				if (preg_match("/ul>/",$html[$l]) == 1) {
					$refsList = $refsList . $html[$l];
					$startDatSects = $l + 1;
					break;
				}
				$refsList = $refsList . $html[$l];
			}
			break;
		}
	}
	echo '<fieldset>' . "\n" . '<legend id="fldrefs">References &amp; Links</legend>';
	echo $refsList;
	echo '</fieldset>';
	$_SESSION['hrefs'] = rawurlencode($refsList);
	# Next, any maps & gpx data: (again, if empty, nothing happens)
	$propDat = '';
	$actDat = '';
	for ($m=$startDatSects; $m<$noOfLines; $m++) {
		if (preg_match('/id="plinks"/',$html[$m]) == 1) {
			for ($n=$m; $n<$noOfLines; $n++) {
				if (preg_match("/ul>/",$html[$n]) == 1) {
					$propDat = $propDat . $html[$n];
					break;
				}
				$propDat = $propDat . $html[$n];
			}
		}
	}
	for ($p=$startDatSects; $p<$noOfLines; $p++) {
		if (preg_match('/id="alinks"/',$html[$p]) == 1) {
			for ($q=$p; $q<$noOfLines; $q++) {
				if (preg_match("/ul>/",$html[$q]) == 1) {
					$actDat = $actDat . $html[$q];
					break;
				}
				$actDat = $actDat . $html[$q];
			}
		}
	}
	if ($propDat !== '' || $actDat !== '') {
		echo '<fieldset>' . "\n" . '<legend id="flddat">GPS Maps &amp; Data</legend>';
		if ($propDat !== '') {
			echo '<div id="proposed"><p id="proptitle">- Proposed Hike Data</p>';
			echo $propDat;
			echo '</div>';
			$_SESSION['prop'] = rawurlencode($propDat);
		}
		if ($actDat !== '') {
			echo '<div id="actual"><p id="acttitle">- Actual Hike Data</p>';
			echo $actDat;
			echo '</div>';
			$_SESSION['act'] = rawurlencode($actDat);
		}
		echo '</fieldset>';
	}
?>
		
<h4 style="text-indent:8px">Please check the boxes corresponding to the pictures you wish
	to include on the new page:</h4>
<p style="text-indent:8px;font-size:16px"><em style="position:relative;top:-20px">Note:
these names were extracted from the .tsv file</em><br />
<input style="margin-left:8px" id="all" type="checkbox" name="allPix" value="useAll" />Use All Photos</p>

<?php
	$picFile = '../gpsv/' . $hikeTsv;
	$farray = file($picFile);
	$icount = count($farray) - 1;  // no. of rows in csv file
	$handle = fopen($picFile, "r");
	if ($handle !== false) {
		$lineno = 0;
		$picno = 0;
		while ( ($line = fgets($handle)) !== false ) {
			$tsvArray = str_getcsv($line,"\t");
			if ($lineno !== 0) {
				$picarray[$picno] = $tsvArray[$indx];
				$thumb[$picno] = $tsvArray[$indx+4];
				$picno += 1;
			} else {
				if (strcmp($tsvArray[0],"folder") == 0) {
					$indx = 1;
					//echo "<p>This tsv file has 'folder' field description</p>";
				} else {
					$indx = 0;
					//echo "<p>Older tsv file - no 'folder' field</p>";
				}
			}
			$lineno += 1;
		}
		$lineno -= 1;
	} else {
		echo "<p>Could not open {$fname}</p>";
	} 
	$nmeno = 0;
	for ($i=0; $i<$icount; $i++) {
		echo '<div class="selPic" style="width:150px;float:left;margin-left:2px;margin-right:2px;margin-bottom:20px;">';
		echo '<input type="checkbox" name="pix[]" value="' .  $picarray[$nmeno] .
			'" />' . substr($picarray[$nmeno],0,10) . '...<br />';
		echo '<img height="150px" width="150px" src="' . $thumb[$nmeno] . '" alt="pic choice" />';
		echo '</div>';
		$nmeno +=1;
	}
	echo '<br />';
	echo '<div style="width:200;position:relative;top:90px;left:20px;float:left;"><input type="submit" value="Use Selected Pics" /></div>';
?>
</div>

<div id="displayDat"> <!-- Additional info to be passed to displayPage.php  -->
<input type="hidden" name="indx" value="<?php echo $hikeIndexNo;?>" />
<input type="hidden" name="hTitle" value="<?php echo $hikeName;?>" />
<input type="hidden" name="area"  value="<?php echo $hikeLocale;?>" />
<input type="hidden" name="mrkr"  value="<?php echo $hikeMarker;?>" />
<input type="hidden" name="vclist"  value="<?php echo $hikeClusterStr;?>" />
<input type="hidden" name="clusLtr"  value="<?php echo $hikeClusGrp;?>" />
<input type="hidden" name="htype" value="<?php echo $hikeType;?>" />
<input type="hidden" name="lgth"  value="<?php echo $hikeLength;?>" />
<input type="hidden" name="elev"  value="<?php echo $hikeElevation;?>" />
<input type="hidden" name="diffi" value="<?php echo $hikeDifficulty;?>" />
<input type="hidden" name="facil" value="<?php echo $hikeFacilities;?>" />
<input type="hidden" name="wow"   value="<?php echo $hikeWow;?>" />
<input type="hidden" name="seasn" value="<?php echo $hikeSeasons;?>" />
<input type="hidden" name="expo"  value="<?php echo $hikeExposure;?>" />
<input type="hidden" name="tsv" value="<?php echo $hikeTsv;?>" />
<input type="hidden" name="geomp" value="<?php echo $hikeMap;?>" />
<input type="hidden" name="chart" value="<?php echo $hikeEChart;?>" />
<input type="hidden" name="gpx"  value="<?php echo $hikeGpx;?>" />
<input type="hidden" name="json"  value="<?php echo $hikeTrk;?>" />
<input type="hidden" name="lati"  value="<?php echo $hikeLat;?>" />
<input type="hidden" name="long"  value="<?php echo $hikeLon;?>" />
<input type="hidden" name="img1"  value="<?php echo $hikeOthrImage1;?>" />
<input type="hidden" name="img2"  value="<?php echo $hikeOthrImage2;?>" />
<input type="hidden" name="phot1" value="<?php echo $hikeMainURL;?>" />
<input type="hidden" name="phot2" value="<?php echo $hikeSecURL;?>" />
<input type="hidden" name="gdirs" value="<?php echo $hikeDirs;?>" />
<input type="hidden" name="TT" value="<?php echo $trailTips;?>" />
<input type="hidden" name="tooltip" value="<?php echo $hikeGrpNm;?>" />

<!-- remainder of data is calculated either here or in displayHikePg.php -->
<input type="hidden" name="rbld" value="YES" />
</div>

</form>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="validateHike.js"></script>
    
</body>

</html>