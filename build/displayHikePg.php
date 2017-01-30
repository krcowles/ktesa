<?php
session_start();
$tsvFile = $_POST['tsv'];
# Is this a rebuild?
$redo = $_POST['rbld'];
if ($redo === "YES") {
	$rebuild = true;
	$hikeNo = $_POST['indx'];
	$tsvFile = '../gpsv/' . $tsvFile;
} else {
	$rebuild = false;
}
#	All this data from validateHike.php
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
	$database = '../data/test.csv';
	$wholeDB = file($database);
	$lastline = $wholeDB[count($wholeDB) - 1];
	$llArray = str_getcsv($lastline,",");
	$nxtHikeNo = intval($llArray[0]) + 1;
	foreach($wholeDB as &$dbLine) {
		$hikeLine = str_getcsv($dbLine,",");
		if ($hikeLine[0] == $ctrHikeLoc) {
			$currentStr = $hikeLine[4];
			if ($currentStr == '') {
				$hikeLine[4] = $nxtHikeNo;
			} else {
				$hikeLine[4] = $hikeLine[4] . "." .$nxtHikeNo;
			}
			$dbLine = implode(",",$hikeLine);
			break;
		}
	}
	$newFile = implode($wholeDB);
	$output = fopen($database,"w");
	if ($output !== false) {
		fputs($output,$newFile);
	} else {
		echo "Could not open file to update index pg cluster string";
	}
}

/*
	End of ctrHikeLoc processing
*/
$clusGrp = $_POST['tipLtr'];
if ($rebuild) {
	$clusTip = $_POST['tooltip'];
} else {
	$clusTip = '';  // default: may change below
	/*
		With clusGrp, find the associated tooltip
	*/
	if ($clusGrp !== '') {
		$str2find = $clusGrp . "$";  // this may not work if double-letter groups...?
		$lgthOfGrp = strlen($str2find);
		$clusString = $_SESSION['allTips'];
		$strLoc = strpos($clusString,$str2find);
		$tipStrt = $strLoc + $lgthOfGrp;
		$strEnd = strlen($clusString) - $tipStrt;
		$firstHalf = substr($clusString,$tipStrt,$strEnd);
		$grpEndPos = strpos($firstHalf,";");
		$clusTip = substr($firstHalf,0,$grpEndPos);
	}
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
if ($gpsvMap === '') 
	$noOfIframes = 0;
else
	$noOfIframes = 1;
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
if ($addonImg[0] === '')
	$noOfOthr = 0;
else {
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
if ($purl2 == '' ) 
	$twoLinks = false;
else
	$twoLinks = true;
if ($_POST['allPix'] == 'useAll') {
}
$googledirs = $_POST['gdirs'];
$picarray = $_POST['pix'];
$noOfPix = count($picarray);
$forceLoad = $_POST['setForce'];
$useAllPix = $_POST['allPix'];
# end of form data, start local page data & routines

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
if ($rebuild) {
	# find out how where key fields are located
	$wholeTSV = file($tsvFile);
	$tsvRecs = count($wholeTSV);
	# parse the header line for field-types:
	$headers = str_getcsv($wholeTSV[0],"\t");
	for ($j=0; $j<count($headers); $j++) {
		$item = $headers[$j];
		#echo $item;
		switch ($item) {  // note that name & desc terminology are opp of tsv file
			case 'desc':
				$rname = $j;
				break;
			case 'name':
				$rdesc = $j;
				break;
			case 'date':
				$rdate = $j;
				break;
			case 'n-size':
				$rsize = $j;
				break;
			case 'album-link':
			case 'url':
				$ralb = $j;
				break;
			default:
				# don't care
				break;
		}
	}
	$picno = 0;
	for ($w=1; $w<$tsvRecs; $w++) {
		$fieldDat = str_getcsv($wholeTSV[$w],"\t");
		$picName[$picno] = $fieldDat[$rname];
		$picDesc[$picno] = $fieldDat[$rdesc];
		$picAlbm[$picno] = $fieldDat[$ralb];
		$picDate[$picno] = $fieldDat[$rdate];
		$nsize[$picno] = $fieldDat[$rsize];
		#echo "Pic link: " . $nsize[$picno];
		$picno++;
	}
	$lineno = count($picName);
} else {
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
# ROW-FILLING ALGORITHM:
$imgRows = array(6);
$maxRowHt = 260;	# change as desired
$rowWidth = 950;	# change as desired, current page width is 960
# start by calculating the various images' widths when rowht = maxRowHt
# PHOTOS:
for ($i=0; $i<$noOfPix; $i++) {
	$widthAtMax[$i] = floor($picWidth[$i] * ($maxRowHt/$picHeight[$i]));
}
# IFRAME(s):
for ($j=0; $j<$noOfIframes; $j++) {
	$indx = $noOfPix + $j;
	$widthAtMax[$indx] = $maxRowHt - 6;  # iframes: have default border width; assume square shape
}
# CHART(s):    NOTE: Modify if multiple charts per page, currently only one expected
for ($k=0; $k<$noOfCharts; $k++) {
	$indx = $noOfPix + $noOfIframes;
	$widthAtMax[$indx] = floor($elevWidth * ($maxRowHt/$elevHeight));
}
# OTHER IMAGES: 
for ($l=0; $l<$noOfOthr; $l++) {
	$indx = $noOfPix + $noOfIframes + $noOfCharts;
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
		$rowHtml = $rowHtml . '<div id="row' . $rowNo . '" class="ImgRow">';
		/* Creating a row unconcatenated to be used for $rowHtml, or passed solo via php */
		$thisRow = '';
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
			} else if ($itype[$n] === "iframe") {
				$mapDims = floor($scaleFactor * $widthAtMax[$n]); # subtracts border
				$thisRow = $thisRow . '<iframe id="theMap" style="' . $styling . '" height="' .
					$mapDims . '" width="' . $mapDims . '" src="../maps/' . $gpsvMap . '"></iframe>';
			} else if ($itype[$n] === "chart") {
				$elevWidth = floor($scaleFactor * $widthAtMax[$n]);
				$thisRow = $thisRow . '<img class="chart" style="' . $styling . '" width="' .
					$elevWidth . '" height="' . $actualHt . '" src="../images/' . $elevChart .
					'" alt="Elevation Chart" />';
			} else {
				$othrWidth[$othrIndx] = floor($scaleFactor * $widthAtMax[$n]);
				$othrHeight[$othrIndx] = $actualHt;
				$thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$n] .
					'" height="' . $actualHt . '" src="../images/' . $addonImg[$othrIndx] .
					'" alt="' . $desc[$n] . '" />';
				$othrIndx += 1;
			}
		}
		# thisRow is completed and will be used below in different ways:
		$rowHtml = $rowHtml . $thisRow . '</div>';
		$imgRows[$rowNo] = '<div id="row' . $rowNo . '" class="ImgRow">' . $thisRow . '</div>';	
		$rowNo += 1;
		$startIndx += $rowItems;
		$curWidth = 0;
		$rowCompleted = true;
		$leftMostImg = true;
	}
} # end of for loop creating initial rows
# last row may not be filled, and will be at maxRowHt
# last item index was "startIndx"; coming into last row as $leftMostImg = true
if ($rowCompleted === false) {
	$itemsLeft = $items - $totalProcessed;
	if ($itemsLeft === 1)
		$thisRow = '<div id="row' . $rowNo . '" class="ImgRow Solo">';
	else
		$thisRow = '<div id="row' . $rowNo . '" class="ImgRow">';
	for ($i=0; $i<$itemsLeft; $i++) {
			if ($leftMostImg) {
				$styling = ''; 
			} else {
				$styling = 'margin-left:1px;';
			}
		if ($itype[$startIndx] === "picture") {
			$picWidth[$startIndx] = $widthAtMax[$startIndx];
			$picHeight[$startIndx] = $maxRowHt;
			$thisRow = $thisRow . '<img id="pic' . $startIndx . '" style="' . $styling .
				'" width="' . $picWidth[$startIndx] . '" height="' . $maxRowHt . '" src="' . 
				$photolink[$startIndx] . '" alt="' . $desc[$startIndx] . '" />';
			$startIndx += 1;
		} else if ($itype[$startIndx] === "iframe") {
			$thisRow = $thisRow . '<iframe id="theMap" style="' . $styling . '" height="' . $maxRowHt .
				'" width="' . $maxRowHt . '" src="../maps/' . $gpsvMap . '"></iframe>';
			$startIndx += 1;
		} else if ($itype[$startIndx] === "chart") {
			$elevWidth = $widthAtMax[$startIndx];
			$thisRow = $thisRow . '<img class="chart" style="' . $styling . '" width="' . $elevWidth . 
				'" height="' . $maxRowHt . '" src="../images/' . $elevChart .
				'" alt="Elevation Chart" />';
			$startIndx += 1;
		} else {
			$othrWidth[$othrIndx] = $widthAtMax[$startIndx];
			$othrHeight[$othrIndx] = $maxRowHt;
			$thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$othrIndx] . '" height="' .
				$maxRowHt . '" src="../images/' . $addonImg[$othrIndx] .
				'" alt="Additional page image" />';
			$othrIndx += 1;
			$startIndx += 1;
		}
		$leftMostImg = false;
	}
	$imgRows[$rowNo] = $thisRow . "</div>";
	$rowHtml = $rowHtml . $thisRow . "</div>";

}
# all items have been processed and actual width/heights retained
# Create the list of captions
$captionHtml = '<div class="captionList"><ol>';
for ($j=0; $j<$noOfPix; $j++) {
	$captionHtml = $captionHtml . "<li>{$caption[$j]}</li>";
}
$captionHtml = $captionHtml . "</ol></div>";
$csvCap = rawurlencode($captionHtml);
# Create the list of album links
$albumHtml = '<div class="lnkList"><ol>';
for ($k=0; $k<$noOfPix; $k++ ) {
	$albumHtml = $albumHtml . "<li>{$album[$k]}</li>";
}
$albumHtml = $albumHtml . "</ol></div>";
$csvAlb = rawurlencode($albumHtml);
$_SESSION['row0'] = $imgRows[0];
$_SESSION['row1'] = $imgRows[1];
$_SESSION['row2'] = $imgRows[2];
$_SESSION['row3'] = $imgRows[3];
$_SESSION['row4'] = $imgRows[4];
$_SESSION['row5'] = $imgRows[5];
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
/* REVAMP */
		if($trailTips == 'Y') {
			echo '<div id="trailTips">' . "\n\t\t" .
				'<img id="tipPic" src="../images/tips.png" alt="special notes icon" />' . "\n\t\t" .
				'<p id="tipHdr">TRAIL TIPS!</p>' . "\n\t\t" . '<p id="tipNotes">';
			if ($rebuild) {
				$tipsTxt = $_SESSION['tips'];
				echo rawurldecode($tipsTxt) . '</p></div>';
				echo '<input type="hidden" value="' . $tipTxt . '" />';
			} else {
				echo '<textarea id="tips" name="trailtiptxt" rows="6" cols="130" maxlength="1500">' .
					'Enter Trail Tips text here...</textarea></p>' ."\n\t" . '</div>';
			}
		}
/* END TIPS */
		if ($rebuild) {
			$hikeInfo = $_SESSION['hInfo'];
			echo '<p id="hikeInfo">' . rawurldecode($hikeInfo) . '</p>';
		} else {
			echo  '<p id="hikeInfo"><textarea id="hdesc" name="hiketxt" cols="145" rows="12" maxlength="3000">' .
				'ENTER TRAIL DESCRIPTION AND NOTES HERE...</textarea></p>';
		}
		if ($rebuild) {
			$references = $_SESSION['hrefs'];
			echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
			echo rawurldecode($references);
			echo '</fieldset>';
		} else {	
			echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
			echo '<ul id="refs">';
			echo '<li>Book: <input name="bk[]" type="text" size="40" placeholder="Title of book reference" />' .
				'<input name="auth[]" type="text" size="40" placeholder="Name of author(s)" /></li>';
			echo '<li>Book: <input name="bk[]" type="text" size="40" placeholder="Title of book reference" />' .
				'<input name="auth[]" type="text" size="40" placeholder="Name of author(s)" /></li>';
			echo '<li>Book: <input name="bk[]" type="text" size="40" placeholder="Title of book reference" />' .
				'<input name="auth[]" type="text" size="40" placeholder="Name of author(s)" /></li>';
			echo '<li>Website: <input name="web[]" type="text" size="100" placeholder="Paste link to app here" />' .
				 '<input name="webtxt[]" type="text" size="20" placeholder="Text to click on" /></li>';
			echo '<li>Website: <input name="web[]" type="text" size="100" placeholder="Paste link to app here" />' .
				'<input name="webtxt[]" type="text" size="20" placeholder="Text to click on" /></li>';
			echo '<li>Website: <input name="web[]" type="text" size="100" placeholder="Paste link to app here" />' .
				'<input name="webtxt[]" type="text" size="20" placeholder="Text to click on" /></li>';
			echo '<li>App: <input name="app[]" type="text" size="100" placeholder="Paste link to app here" />' .
				'<input name="apptxt[]" type="text" size="20" placeholder="Text to click on" /></li>';
			echo '<li>App: <input name="app[]" type="text" size="100" placeholder="Paste link to app here" />' .
				'<input name="apptxt[]" type="text" size="20" placeholder="Text to click on" /></li>';
			echo '<li>App: <input name="app[]" type="text" size="100" placeholder="Paste link to app here" />' .
				'<input name="apptxt[]" type="text" size="20" placeholder="Text to click on" /></li>';
			echo '</ul></fieldset>';
		}
		if ($rebuild) {
			$propDat = $_SESSION['prop'];
			$actDat = $_SESSION['act'];
			if ($propDat !== '' || $actDat !== '') {
				echo '<fieldset><legend id="flddat">GPS Maps &amp; Data</legend>';
				if ($propDat !== '') {
					echo '<div id="proposed"><p id="proptitle">- Proposed Hike Data</p>';
					echo rawurldecode($propDat);
					echo '</div>';
				}
				if ($actDat !== '') {
					echo '<div id="actual"><p id="acttitle">- Actual Hike Data</p>';
					echo rawurldecode($actDat);
					echo '</div>';
				}
				echo '</fieldset>';
			}
		} else {
			echo '<div id="proposed"><p id="proptitle">- Proposed Hike Data</p><ul id="plinks">';
			echo '<li>Map: <input name="pmap[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="pmtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '<li>GPX: <input name="pgpx[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="pgpxtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '<li>Map: <input name="pmap[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="pmtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '<li>GPX: <input name="pgpx[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="pgpxtxt[]" type="text" size="40" placeholder="Text to click on" /></li>' ;
			echo '</ul></div>';
			echo '<div id="actual"><p id="acttitle">- Actual Hike Data</p><ul id="alinks">';
			echo '<li>Map: <input name="amap[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="amtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '<li>GPX: <input name="agpx[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="agpxtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '<li>Map: <input name="amap[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="amtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '<li>GPX: <input name="agpx[]" type="text" size="40" placeholder="Path to map" />' .
					'<input name="agpxtxt[]" type="text" size="40" placeholder="Text to click on" /></li>';
			echo '</ul></div>';
		}
	?>
	<!-- Hidden Data Passed to hikeCSV.php -->
	<input type="hidden" name="hname" value="<?php echo $pgTitle;?>" />
	<input type="hidden" name="hlocale" value="<?php echo $locale;?>" />
	<input type="hidden" name="hmarker" value="<?php echo $marker;?>" />
	<input type="hidden" name="hvcgrp" value="<?php echo $ctrHikeLoc;?>" />
	<input type="hidden" name="hclus" value="<?php echo $clusGrp;?>" />
	<input type="hidden" name="htype" value="<?php echo $htype;?>" />
	<input type="hidden" name="hmiles" value="<?php echo $distance;?>" />
	<input type="hidden" name="hfeet" value="<?php echo $elevation;?>" />
	<input type="hidden" name="hdiff" value="<?php echo $difficulty;?>" />
	<input type="hidden" name="hfac" value="<?php echo $facilities;?>" />
	<input type="hidden" name="hwow" value="<?php echo $wowFactor;?>" />
	<input type="hidden" name="hseas" value="<?php echo $seasons;?>" />
	<input type="hidden" name="hexp" value="<?php echo $exposure;?>" />
	<input type="hidden" name="htsv" value="<?php echo $tsvFile;?>" />
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
	<input type="hidden" name="hrow0" value="<?php $imgRows[0]?>" />
	<input type="hidden" name="hrow1" value="<?php $imgRows[1]?>" />
	<input type="hidden" name="hrow2" value="<?php $imgRows[2]?>" />
	<input type="hidden" name="hrow3" value="<?php $imgRows[3]?>" />
	<input type="hidden" name="hrow4" value="<?php $imgRows[4]?>" />
	<input type="hidden" name="hrow5" value="<?php $imgRows[5]?>" />
	<input type="hidden" name="hcaps" value='<?php echo $csvCap;?>' />
	<input type="hidden" name="hplnks" value='<?php echo $csvAlb;?>' />
	<input type="hidden" name="httxt" value="<?php echo $tipsTxt;?>" />
	<input type="hidden" name="hinfo" value="<?php echo $hikeInfo;?>" />
	<input type="hidden" name="href" value="<?php echo $references;?>" />
	<input type="hidden" name="hpdat" value="<?php echo $propDat;?>" />
	<input type="hidden" name="hadat" value="<?php echo $actDat;?>" />
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