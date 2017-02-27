<?php
session_start();
$tsvname = $_POST['tsv'];
$tsvFile = '../gpsv/' . $tsvname;
/* 
	--------------- THE FOLLOWING DATA IS IMPORTED FROM VALIDATEHIKE.PHP --------------
*/
$pgTitle = $_POST['hTitle'];
$locale = $_POST['area'];
$hikeType = $_POST['htype'];
if ($hikeType === "oab") {
	$htype = "Out-and-back";
} else if ($hikeType === "loop") {
	$htype = "Loop";
} else {
	$htype = "Two-Cars";
}
$ctrHikeLoc = $_POST['vcList'];
/*
	If $ctrHikeLoc not empty, find the Index Page for the assoc. hike and update it
*/
if ($ctrHikeLoc !== '') {
	$database = '../data/database.csv';
	$dbHandle = fopen($database,"r");
	/* $ctrHikeLoc holds the index number of the Visitor Center associated with this hike;	
	   This new hike will have the next available index no, which number is to be added to
	   the Visitor Center's "Cluster Str", array index [4] */
	$wholeDB = array();
	$dbindx = 0;
	while ( ($hikeLine = fgetcsv($dbHandle)) !== false ) {
		$wholeDB[$dbindx] = $hikeLine;
		$dbindx++;
		$lastIndxNo = $hikeLine[0];
	}
	fclose($dbHandle);
	$nxtIndxNo = intval($lastIndxNo) + 1;
	# find the associated Visitor Center:
	foreach ($wholeDB as &$hikeInfo) {
		if ($hikeInfo[0] == $ctrHikeLoc) {
			$currentStr = $hikeInfo[4];
			if ($currentStr == '') {
				$hikeInfo[4] = $nxtIndxNo;
			} else {
				$hikeInfo[4] = $hikeInfo[4] . "." . $nxtIndxNo;
			}
			break;
		}
	}
	# write the wholeDB back out
	$dbHandle = fopen($database,"w");
	foreach ($wholeDB as $outArray) {
		fputcsv($dbHandle,$outArray);
	}
	fclose($dbHandle);
}
/*
	End of ctrHikeLoc processing
*/
$clusGrp = $_POST['clusgrp'];
$clusTip = '';  // default: may change below
/*
	With clusGrp, find the associated tooltip
*/
if ($clusGrp !== '') {
	$str2find = $clusGrp . "$";
	$lgthOfGrp = strlen($str2find);
	$clusString = $_SESSION['allTips'];
	$strLoc = strpos($clusString,$str2find);
	$tipStrt = $strLoc + $lgthOfGrp;
	$strEnd = strlen($clusString) - $tipStrt;
	$firstHalf = substr($clusString,$tipStrt,$strEnd);
	$grpEndPos = strpos($firstHalf,";");
	$clusTip = substr($firstHalf,0,$grpEndPos);
}
/* 
	End of cluster tooltip processing
*/
$distance = $_POST['lgth'];
$elevation = $_POST['elev'];
$difficulty = $_POST['diffi'];
$lat = $_POST['lati'];
$lon = $_POST['long'];
$facilities = $_POST['facil'];
$hikePg = $_POST['webpg'];
$wowFactor = $_POST['wow'];
$seasons = $_POST['seasn'];
$exp = $_POST['expo'];
if ($exp === "sun") {
	$exposure = "Full sun";
} else if ($exp === "shade") {
	$exposure = "Good shade";
} else {
	$exposure = "Mixed sun/shade";
}
# AT This point, geomaps & charts are assumed to be at most, 1 per page.
# IF this ever changes, change these to arrays & modify html creation loop
$gpsvMap = $_POST['geomp'];
if ($gpsvMap === '') { 
	$noOfIframes = 0;
} else {
	$map = '../maps/gpsvMapTemplate.php?map_name=' . $gpsvMap;
	$noOfIframes = 1;
}
$elevChart = $_POST['chart'];
if ($elevChart === '') {
	$noOfCharts = 0;
} else {
	$echart = "../images/" . $elevChart;
	$chartDat = getimagesize($echart);
	$elevWidth = $chartDat[0];
	$elevHeight = $chartDat[1];
	$noOfCharts = 1;
}
$gpxFname = $_POST['gpx'];
$trackFname = $_POST['json'];
$addonImg[0] = $_POST['img1'];
$imgIndx = 0;
if ($addonImg[0] === '') {
	$noOfOthr = 0;
} else {
	$noOfOthr = 1;
	$firstimg = getimagesize("../images/" . $addonImg[0]);
	$othrWidth[$imgIndx] = $firstimg[0];
	$othrHeight[$imgIndx] = $firstimg[1];
	$imgIndx += 1;
}
$addonImg[1] = $_POST['img2'];
if ($addonImg[1] !== '') {
	$noOfOthr += 1;
	$secondimg = getimagesize("../images/" . $addonImg[1]);
	$othrWidth[$imgIndx] = $secondimg[0];
	$othrHeight[$imgIndx] = $secondimg[1];
}
$marker = $_POST['mrkr'];
$purl1 = $_POST['phot1'];
$purl2 = $_POST['phot2'];
if ($purl2 == '' ) {
	$twoLinks = false;
} else {
	$twoLinks = true;
}
if ($_POST['allPix'] == 'useAll') {
}
$googledirs = $_POST['gdirs'];
$tips = $_SESSION['hikeTips'];
# the passed tips may be an empty string
$info = $_SESSION['hikeDetails'];
$refs = $_POST['refstr'];
$pdat = $_POST['pstr'];
$adat = $_POST['astr'];
$picarray = $_POST['pix'];
$noOfPix = count($picarray);
$forceLoad = $_POST['setForce'];
$useAllPix = $_POST['allPix'];
/*
    ------------------------------ END OF IMPORTING DATA -------------------------
*/

/*  
    ---------------------------  BEGIN IMAGE ROW PROCESSING ----------------------
*/
$month = array("Jan","Feb","Mar","Apr","May","Jun",
				"Jul","Aug","Sep","Oct","Nov","Dec");
define("SPACING", 14, true);
define("MAXWIDTH", 960, true);
define("ROWHT", 260, true);
define("TOOMUCHMARGIN", 80, true);
define("MIN_IFRAME_SIZE", 270, true);
$startChartWidth = floor(RowHt/$elevHeight * $elevWidth) + 7; # allow for margin
$closingDiv = "</div>";

#	Read in the tsv file and extract ALL usable data:
/* NOTE: For some older files, the fields in the tsv file vary considerably and may
   omit key data that later files contain. Look for these special files when
   executing a page rebuild. The only fields required for row-filling are:
		desc
		name
		date
		n-size
*/
$handle = fopen($tsvFile, "r");
	if ($handle !== false) {
	$lineno = 0;
	$picno = 0;
	while ( ($line = fgets($handle)) !== false ) {
		$tsvArray = str_getcsv($line,"\t");
		if ($lineno !== 0) {
			$picName[$picno] = $tsvArray[1];
			$picDesc[$picno] = $tsvArray[2];
			$picAlbm[$picno] = $tsvArray[6];
			$picDate[$picno] = $tsvArray[7];
			$nsize[$picno] = $tsvArray[8]; 
			$picno++;
		}
		$lineno++;
	}
	$lineno--;
} else {
	die( "Could not open tsv file for this hike" );
}
# Pull out the index numbers of the chosen few: (or maybe all!)
$k = 0;
for ($i=0; $i<$noOfPix; $i++) {
	$targ = $picarray[$i];
	for ($j=0; $j<$lineno; $j++) {
		if( $targ === $picName[$j] ) {
			$indx[$k] = $j;
			$k++;
			break;
		}
	}
}
# for each of the <user-selected> pix, define needed arrays
for ($i=0; $i<$noOfPix; $i++) {
	$x = $indx[$i];
	$picYear = substr($picDate[$x],0,4);
	$picMoDigits = substr($picDate[$x],5,2) - 1;
	$picMonth = $month[$picMoDigits];
	$picDay = substr($picDate[$x],8,2);
	if (substr($picDay,0,1) === '0') {
		$picDay = substr($picDay,1,1);
	}
	$caption[$i] = "{$picMonth} {$picDay}, {$picYear}: {$picDesc[$x]}";
	$picSize = getimagesize($nsize[$x]); # PROVIDE THIS IN GPSV FILE??
	$picWidth[$i] = $picSize[0];
	/*  -DEBUG
	if (is_numeric($picWidth[$i]) === false) {
		echo "MAJOR PROBLEM WITH " . $i . "th ELEMENT!";
	} */
	$picHeight[$i] = $picSize[1];
	$name[$i] = $picName[$x];
	$desc[$i] = $picDesc[$x];
	$album[$i] = $picAlbm[$x];
	$photolink[$i] = $nsize[$x];
}
$noOfCaps = count($caption);
#echo "Found " . $noOfCaps . " captions in tsv file";
$capStr = $noOfCaps . '^' . implode("^",$caption);
$capStr = preg_replace("/\n\t\r/"," ",$capStr);
$noOfAlbumLinks = count($album);
$albStr = $noOfAlbumLinks . '^' . implode("^",$album);
$albStr = preg_replace("/\n\t\r/"," ",$albStr);
# Preliminary setup complete, begin row-filling algorithm:
$imgRows = array(6);
$maxRowHt = 260;	# change as desired
$rowWidth = 950;	# change as desired, current page width is 960
# start by calculating the various images' widths when rowht = maxRowHt
# PHOTOS:
for ($i=0; $i<$noOfPix; $i++) {
	$widthAtMax[$i] = floor($picWidth[$i] * ($maxRowHt/$picHeight[$i]));
}
# IFRAME(s):
for ($j=0; $j<$noOfIframes; $j++) {  # for now, only one assumed, multiple not tested
	$indx = $noOfPix + $j;
	$widthAtMax[$indx] = $maxRowHt - 6;  # iframes: have default border width; assume square shape
}
# CHART(s):    NOTE: Modify if multiple charts per page, currently only one expected
for ($k=0; $k<$noOfCharts; $k++) {
	$indx = $noOfPix + $noOfIframes;  // else + $k
	$widthAtMax[$indx] = floor($elevWidth * ($maxRowHt/$elevHeight));
}
# OTHER IMAGES: 
for ($l=0; $l<$noOfOthr; $l++) {
	$indx = $noOfPix + $noOfIframes + $noOfCharts + $l;
	$widthAtMax[$indx] = floor($othrWidth[$l] * ($maxRowHt/$othrHeight[$l]));
}
$items = $noOfPix + $noOfIframes + $noOfCharts + $noOfOthr;
# initialize starting rowWidth, counters, and starting point for html creation
$curWidth = 0;	# row Width as it's being built
$startIndx = 0;	# when creating html, index to set loop start
$rowHtml = '';
$rowNo = 0;
$totalProcessed = 0;
$othrIndx = 0;	 # counter for number of other images being loaded
$leftMostImg = true;
$frameFlag = false;  # flag the row containing the iframe so space can be allowed for link
$rowStr = array();
for ($i=0; $i<$items; $i++) {
	if ($leftMostImg === false) {  # modify width for added pic margins for all but first img
		$curWidth += 1;
	}
	$rowCompleted = false;
	$curWidth += $widthAtMax[$i];
	$leftMostImg = false;
	if ($i < $noOfPix) {
		$itype[$i] = "picture";
	}
	else if ($i >= $noOfPix && $i < ($noOfPix + $noOfIframes)) {
		$itype[$i] = "iframe";
	}
	else if ($i >= ($noOfPix + $noOfIframes) && $i < ($noOfPix + $noOfIframes + $noOfCharts)){
		$itype[$i] = "chart";
	} else {
		$itype[$i] = "image";
	}
	if ($curWidth > $rowWidth) {
		$rowItems = $i - $startIndx + 1;
		$totalProcessed += $rowItems;
		$scaleFactor = $rowWidth/$curWidth;
		$actualHt = floor($scaleFactor * $maxRowHt);
		# ALL rows concatenated in $rowHtml
		if ($frameFlag) { # class Solo is a misnomer, but allows space for iframe link
			$rowHtml = $rowHtml . '<div id="row' . $rowNo . '" class="ImgRow Solo">';
			$frameFlag = false;
		} else {
			$rowHtml = $rowHtml . '<div id="row' . $rowNo . '" class="ImgRow">';
		}
		/* Creating a row unconcatenated to be used for $rowHtml, or passed solo via php */
		$thisRow = '';
		$imgCnt = 0;
		$imel = '';
		for ($n=$startIndx; $n<=$i; $n++) {
			if ($n === $startIndx)
				$styling = '';
			else
				$styling = 'margin-left:1px;';
			# don't add left-margin to leftmost image
			if ($itype[$n] === "picture") {
				$picWidth[$n] = floor($scaleFactor * $widthAtMax[$n]);
				$picHeight[$n] = $actualHt;
				$thisRow = $thisRow . '<img id="pic' .$n . '" style="' . $styling . '" width="' .
					$picWidth[$n] . '" height="' . $actualHt . '" src="' . $photolink[$n] . 
					'" alt="' . $desc[$n] . '" />';	
				$imel .= 'p^' . $picWidth[$n] . '^' . $photolink[$n] . '^' . $desc[$n];
			} else if ($itype[$n] === "iframe") {
				$mapDims = floor($scaleFactor * $widthAtMax[$n]); # subtracts border
				$thisRow = $thisRow . '<iframe id="theMap" style="' . $styling . '" height="' .
					$mapDims . '" width="' . $mapDims . '" src="' . $map . '"></iframe>';
				$imel .= 'f^' . $mapDims . '^' . $map;
				$frameFlag = true;
			} else if ($itype[$n] === "chart") {
				$elevWidth = floor($scaleFactor * $widthAtMax[$n]);
				$thisRow = $thisRow . '<img class="chart" style="' . $styling . '" width="' .
					$elevWidth . '" height="' . $actualHt . '" src="' . $echart .
					'" alt="Elevation Chart" />';
				$imel .= 'n^' . $elevWidth . '^' . $echart;	
			} else {
				$othrWidth[$othrIndx] = floor($scaleFactor * $widthAtMax[$n]);
				$othrHeight[$othrIndx] = $actualHt;
				$thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$n] .
					'" height="' . $actualHt . '" src="../images/' . $addonImg[$othrIndx] .
					'" alt="Additional non-captioned image" />';
				$imel .= 'n^' . $othrWidth[$n] . '^' . $addonImg[$othrIndx];
				$othrIndx += 1;
			}
			$imgCnt++;
			$imel .= '^';
		}
		# thisRow is completed and will be used below in different ways:
		$imel = $imgCnt . '^' . $actualHt . '^' . $imel;
		array_push($rowStr,$imel);
		$rowHtml = $rowHtml . $thisRow . '</div>';
		$rowNo += 1;
		$startIndx += $rowItems;
		$curWidth = 0;
		$rowCompleted = true;
		$leftMostImg = true;
	}  # end of if currentWidth > rowWidth
} # end of for loop creating initial rows
# last row may not be filled, and will be at maxRowHt
# last item index was "startIndx"; coming into last row as $leftMostImg = true
if ($rowCompleted === false) {
	$itemsLeft = $items - $totalProcessed;
	$leftMostImg = true;
	if ($frameFlag) {
		$thisRow = '<div id="row' . $rowNo . '" class="ImgRow Solo">';
		$frameFlag = false;
	} else {
		$thisRow = '<div id="row' . $rowNo . '" class="ImgRow">';
	}
	$imel = '';
	$imgCnt = 0;
	for ($i=0; $i<$itemsLeft; $i++) {
		if ($leftMostImg) {
			$styling = ''; 
			$leftMostImg = false;
		} else {
			$styling = 'margin-left:1px;';
		}
		if ($itype[$startIndx] === "picture") {
			$picWidth[$startIndx] = $widthAtMax[$startIndx];
			$picHeight[$startIndx] = $maxRowHt;
			$thisRow = $thisRow . '<img id="pic' . $startIndx . '" style="' . $styling .
				'" width="' . $picWidth[$startIndx] . '" height="' . $maxRowHt . '" src="' . 
				$photolink[$startIndx] . '" alt="' . $desc[$startIndx] . '" />';
			$imel .= 'p^' . $picWidth[$startIndx] . '^' . $photolink[$startIndx] . 
				'^' . $desc[$startIndx];
			$startIndx += 1;
		} else if ($itype[$startIndx] === "iframe") {
			$thisRow = $thisRow . '<iframe id="theMap" style="' . $styling . '" height="' . $maxRowHt .
				'" width="' . $maxRowHt . '" src="' . $map . '"></iframe>';
			$imel .= 'f^' . $maxRowHt . '^' . $map;
			$startIndx += 1;
		} else if ($itype[$startIndx] === "chart") {
			$elevWidth = $widthAtMax[$startIndx];
			$thisRow = $thisRow . '<img class="chart" style="' . $styling . '" width="' . $elevWidth . 
				'" height="' . $maxRowHt . '" src="' . $echart . '" alt="Elevation Chart" />';
			$imel .= 'n^' . $elevWidth . '^' . $echart;
			$startIndx += 1;
		} else {
			$othrWidth[$othrIndx] = $widthAtMax[$startIndx];
			$othrHeight[$othrIndx] = $maxRowHt;
			$thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$othrIndx] . '" height="' .
				$maxRowHt . '" src="../images/' . $addonImg[$othrIndx] .
				'" alt="Additional page image" />';
			$imel .= 'n^' . $othrWidth[$othrIndx] . '^' . $addonImg[$othrIndx];
			$othrIndx += 1;
			$startIndx += 1;
		}
		$imgCnt++;
		if ($i !== $itemsLeft - 1) {
			$imel .=  '^';
		}
	} // end of for loop processing
	$imel = $imgCnt . '^' . $maxRowHt . '^' . $imel;
	array_push($rowStr,$imel);
	$imgRows[$rowNo] = $thisRow . "</div>";
	$rowHtml = $rowHtml . $thisRow . "</div>";

} // end of last row conditional
# all items have been processed and actual width/heights retained
# Create the list of captions
$captionHtml = '<div class="captionList"><ol>';
for ($j=0; $j<$noOfPix; $j++) {
	$captionHtml = $captionHtml . "<li>{$caption[$j]}</li>";
}
$captionHtml = $captionHtml . "</ol></div>";
# Create the list of album links
$albumHtml = '<div class="lnkList"><ol>';
for ($k=0; $k<$noOfPix; $k++ ) {
	$albumHtml = $albumHtml . "<li>{$album[$k]}</li>";
}
$albumHtml = $albumHtml . "</ol></div>";

$noOfRows = count($rowStr);
for ($x=$noOfRows; $x<6; $x++) {
	$rowStr[$x] = '';
}
for ($y=0; $y<6; $y++) {
	if ($rowStr[$y] !== '') {
		$rlgth = strlen($rowStr[$y]) - 1;
		$rowStr[$y] = substr($rowStr[$y],0,$rlgth);
	}
}
$_SESSION['row0'] = $rowStr[0];
$_SESSION['row1'] = $rowStr[1];
$_SESSION['row2'] = $rowStr[2];
$_SESSION['row3'] = $rowStr[3];
$_SESSION['row4'] = $rowStr[4];
$_SESSION['row5'] = $rowStr[5];
/*  
    ---------------------------  END OF IMAGE ROW PROCESSING ----------------------
*/
?>
<!DOCTYPE html>
<html>

<head>
	<title><?php echo $pgTitle;?></title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Details about the <?php echo $pgTitle;?> hike" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/960_16_col.css"
		type="text/css" rel="stylesheet" />
	<link href="../styles/hikes.css"
		type="text/css" rel="stylesheet" />
</head>

<body>
<div class="container_16 clearfix">

<div id="logoBlock">
	<p id="pgLogo"></p>
	<p id="logoLeft">Hike New Mexico</p>
	<p id="logoRight">w/ Tom &amp; Ken</p>
	<p id="page_title" class="grid_16"><?php echo $pgTitle;?></p>
</div> <!-- end of logoBlock -->
<div id="hikeSummary">
	 <table id="topper">
		 <thead>
			 <tr>
				<th>Difficulty</th>
				<th>Round-trip</th>
				<th>Type</th>
				<th>Elev. Chg.</th>
				<th>Exposure</th>
				<th>Wow Factor</th>
				<th>Facilities</th>
				<th>Seasons</th>
				<?php if($twoLinks === false) echo "<th>Photos</th>\n";?>
<th>By Car</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $difficulty;?></td>
				<td><?php echo $distance;?> miles</td>
				<td><?php echo $htype;?></td>
				<td><?php echo $elevation;?> ft</td>
				<td><?php echo $exposure?></td>
				<td><?php echo $wowFactor;?></td>
				<td><?php echo $facilities;?></td>
				<td><?php echo $seasons;?></td>
				<?php if($twoLinks === false) echo '<td><a href="' . $purl1 . '" target="_blank">' . "\n\t\t\t\t" .
					'<img style="margin-bottom:0px;border-style:none;" src="../images/album_lnk.png" alt="photo album link icon" /></a></td>' .
					"\n";?>
<td><a href="<?php echo $googledirs;?>" target="_blank">
				<img style="margin-bottom:0px;padding-bottom:0px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
			</tr>
		</tbody>
	</table>
</div>  <!-- end of hikeSummary table -->

<?php if($twoLinks === true) echo '<div style="margin-bottom:8px;"><em>-- To see more photos:</em> click on ' .
	'<a href="' . $purl2 . '" target="_blank">Tom' . "'" . 's Flickr Album</a> or ' .
	'<a href="' . $purl1 . '" target="_blank">Ken' . "'" . 's Flickr Album</a></div>';
?>

</div>  <!-- end of container 16 -->

<?php 
	echo $rowHtml;
	echo $captionHtml;
	echo $albumHtml;
	if ($forceLoad == 'force') {
		echo '<div id="pgLoad" style="display:none">force</div>';
	}
?>
<form action="saveHike.php" method="POST">

<div id="postPhoto">
	<?php 
		/* ------ TIPS TEXT PROCESSING ----- */
		if($tips !== '') {
			echo '<div id="trailTips">' . "\n\t\t" .
				'<img id="tipPic" src="../images/tips.png" alt="special notes icon" />' . "\n\t\t" .
				'<p id="tipHdr">TRAIL TIPS!</p>' . "\n\t\t" . '<p id="tipNotes">' . 
				$tips . '</p></div>';
		}
		/* ----- HIKE INfORMATION PROCESSING ---- */
		echo  '<p id="hikeInfo">' . $info . '</p>';
		/* ----- REFERENCES PROCESSING ----- */
		echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
		echo '<ul id="refs">';
		$dispRefs = explode("^",$refs);
		$noOfRefs = intval($dispRefs[0]);
		array_shift($dispRefs);
		$nxt = 0;
		$listel = '';
		#echo 'Number of references found: ' . $noOfRefs;
		for ($i=0; $i<$noOfRefs; $i++) {
			switch ($dispRefs[$nxt]) {
				case 'b':
					$listel .= '<li>Book: <em>' . $dispRefs[$nxt+1] . '</em> ' . $dispRefs[$nxt+2] . '</li>';
					$nxt += 3;
					break;
				case 'p':
					$listel .= '<li>Photo Essay: <em>' . $dispRefs[$nxt+1] . '</em> ' . $dispRefs[$nxt+2] . '</li>';
					$nxt += 3;
					break;
				case 'w':
					$lbl ='Website: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'a':
					$lbl = 'App: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'd':
					$lbl = 'Downloadable Doc: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'l':
					$lbl = 'Blog: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'r':
					$lbl = 'Related Link: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'o':
					$lbl = 'On-Line Map: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'm':
					$lbl = 'Magazine: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 's':
					$lbl = 'News Article: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'g':
					$lbl = 'Meetup Group: ';
					$listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
						'" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>';
					$nxt += 3;
					break;
				case 'n':
					$listel .= '<li>' . $dispRefs[$nxt+1] . '</li>';
					break;
				default:
					echo "Unrecognized reference type passed";
			}  // end of switch
			
			
		} // end of for loop - refs processing
		echo $listel . '</ul></fieldset>';
		/* ----- PROPOSED AND/OR ACTUAL DATA PROCESSING ---- */
		if ($pdat !== '' || $adat !== '') {
			echo '<fieldset><legend id="flddat">GPS Maps &amp; Data</legend>';
			if ($pdat !== '') {
				$listel = '';
				echo '<p id="proptitle">- Proposed Hike Data</p><ul id="plinks">';
				# get no. of pdats:
				$prop = explode("^",$pdat);
				$noOfProps = intval($prop[0]);
				array_shift($prop);
				$nxt = 0;
				for ($i=0; $i<$noOfProps; $i++) {
					$listel .= '<li>' . $prop[$nxt] . ' <a href="' . $prop[$nxt+1] .
						'" target="_blank">' . $prop[$nxt+2] . '</a></li>';
					$nxt += 3;
				}
				echo $listel . '</ul>';
			}
			if ($adat !== '') {
				$listel = '';
				echo '<p id="acttitle">- Actual Hike Data</p><ul id="alinks">';
				# get no of adats:
				$act = explode("^",$adat);
				$noOfActs = intval($act[0]);
				array_shift($act);
				$nxt = 0;
				for ($j=0; $j<$noOfActs; $j++) {
					$listel .= '<li>' . $act[$nxt] . ' <a href="' . $act[$nxt+1] .
						'" target="_blank">' . $act[$nxt+2] . '</a></li>';
					$nxt += 3;
				}
				echo $listel . '</ul>';
			}
			echo '</fieldset';
		}
	?>
	
	<!-- Hidden Data Passed to hikeCSV.php -->
	<input type="hidden" name="hname" value="<?php echo $pgTitle;?>" />
	<input type="hidden" name="hlocale" value="<?php echo $locale;?>" />
	<input type="hidden" name="hmarker" value="<?php echo $marker;?>" />
	<input type="hidden" name="hclus" value="<?php echo $clusGrp;?>" />
	<input type="hidden" name="htype" value="<?php echo $htype;?>" />
	<input type="hidden" name="hmiles" value="<?php echo $distance;?>" />
	<input type="hidden" name="hfeet" value="<?php echo $elevation;?>" />
	<input type="hidden" name="hdiff" value="<?php echo $difficulty;?>" />
	<input type="hidden" name="hfac" value="<?php echo $facilities;?>" />
	<input type="hidden" name="hwow" value="<?php echo $wowFactor;?>" />
	<input type="hidden" name="hseas" value="<?php echo $seasons;?>" />
	<input type="hidden" name="hexp" value="<?php echo $exposure;?>" />
	<input type="hidden" name="htsv" value="<?php echo $tsvname;?>" />
	<input type="hidden" name="hmap" value="<?php echo $gpsvMap;?>" />
	<input type="hidden" name="hchart" value="<?php echo $elevChart;?>" />
	<input type="hidden" name="hgpx" value="<?php echo $gpxFname;?>" />
	<input type="hidden" name="htrk" value="<?php echo $trackFname;?>" />
	<input type="hidden" name="hlat" value="<?php echo $lat;?>" />
	<input type="hidden" name="hlon" value="<?php echo $lon;?>" />
	<input type="hidden" name="hadd1" value="<?php echo $addonImg[0];?>" />
	<input type="hidden" name="hadd2" value="<?php echo $addonImg[1];?>" />
	<input type="hidden" name="hphoto1" value="<?php echo $purl1;?>" />
	<input type="hidden" name="hphoto2" value="<?php echo $purl2;?>" />
	<input type="hidden" name="hdir" value="<?php echo $googledirs;?>" />
	<input type="hidden" name="htool" value="<?php echo $clusTip;?>" />
	<input type="hidden" name="hcaps" value="<?php echo $capStr;?>" />
	<input type="hidden" name="hplnks" value="<?php echo $albStr;?>" />
	<input type="hidden" name="href" value="<?php echo $refs;?>" />
	<input type="hidden" name="hpdat" value="<?php echo $pdat;?>" />
	<input type="hidden" name="hadat" value="<?php echo $adat;?>" />
	<input type="hidden" name="remake" value="<?php echo $redo;?>" />
	<input type="hidden" name="rhno" value="<?php echo $hikeNo;?>" />
	
	<input style="margin-left:8px;margin-bottom:10px;" type="submit" value="Make New Hike Page" />
	
</form>
	
	<div id="dbug"></div>
</div>  <!-- end of postPhoto -->

<div class="popupCap"></div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/hikes.js"></script>

</body>
</html>