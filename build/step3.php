<?php
#	All this data from step 2: (eventually in database, not passed via form!)
$tsvFile = $_POST['whose'];
$pgTitle = $_POST['hTitle'];
$locale = $_POST['area'];
$hikeType = $_POST['htype'];
if ($hikeType === "oab") {
	$htype = "Out-and-back";
} else if ($hikeType === "loop") {
	$htype = "Loop";
} else {
	$htype = "2-Cars";
}
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
$googledirs = $_POST['gdirs'];
$picarray = $_POST['pix'];
$noOfPix = count($picarray);
$trailTips = $_POST['TT'];
$forceLoad = $_POST['setForce'];
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
	die( "<p>Could not open {$fname}</p>" );
}

# Pull out the index numbers of the chosen few:
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
	$picHeight[$i] = $picSize[1];
	$name[$i] = $picName[$x];
	$desc[$i] = $picDesc[$x];
	$album[$i] = $picAlbm[$x];
	$photolink[$i] = $nsize[$x]; 
}

# ROW-FILLING ALGORITHM:
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
		$rowHtml = $rowHtml . "\n" . '<div id="row' . $rowNo . '" class="ImgRow">' . "\n";
		for ($n=$startIndx; $n<=$i; $n++) {
			if ($n === $startIndx)
				$styling = '';
			else
				$styling = 'margin-left:1px;';
			# don't add left-margin to leftmost image
			if ($itype[$n] === "picture") {
				$picWidth[$n] = floor($scaleFactor * $widthAtMax[$n]);
				$picHeight[$n] = $actualHt;
				$rowHtml = $rowHtml . '<img id="pic' .$n . '" style="' . $styling . '" width="' .
					$picWidth[$n] . '" height="' . $actualHt . '" src="' . $photolink[$n] . 
					'" alt="' . $desc[$n] . '" />';
			} else if ($itype[$n] === "iframe") {
				$mapDims = floor($scaleFactor * $widthAtMax[$n]); # subtracts border
				$rowHtml = $rowHtml . '<iframe id="theMap" style="' . $styling . '" height="' .
					$mapDims . '" width="' . $mapDims . '" src="../maps/' . $gpsvMap . '"></iframe>';
			} else if ($itype[$n] === "chart") {
				$elevWidth = floor($scaleFactor * $widthAtMax[$n]);
				$rowHtml = $rowHtml . '<img class="chart" style="' . $styling . '" width="' .
					$elevWidth . '" height="' . $actualHt . '" src="../images/' . $elevChart .
					'" alt="Elevation Chart" />';
			} else {
				$othrWidth[$othrIndx] = floor($scaleFactor * $widthAtMax[$n]);
				$othrHeight[$othrIndx] = $actualHt;
				$rowHtml = $rowHtml . '<img style="' . $styling . '" width="' . $othrWidth[$n] .
					'" height="' . $$actualHt . '" src="../images/' . $addonImg[$othrIndx] .
					'" alt="' . $desc[$n] . '" />';
				$othrIndx += 1;
			}
		}	
		$rowHtml = $rowHtml . '</div>';
		$rowNo += 1;
		$startIndx += $rowItems;
		$curWidth = 0;
		$rowCompleted = true;
		$leftMostImg = true;
	}
}
# last row may not be filled, and will be at maxRowHt
# last item index was "startIndx"
if ($rowCompleted === false) {
	$itemsLeft = $items - $totalProcessed;
	if ($itemsLeft === 1)
		$rowHtml = $rowHtml . "\n" . '<div id="row' . $rowNo . '" class="ImgRow Solo">';
	else
		$rowHtml = $rowHtml . "\n" . '<div id="row' . $rowNo . '" class="ImgRow">';
	for ($i=0; $i<$itemsLeft; $i++) {
		if ($itype[$startIndx] === "picture") {
			$picWidth[$startIndx] = $widthAtMax[$startIndx];
			$picHeight[$startIndx] = $maxRowHt;
			$rowHtml = $rowHtml . '<img id="pic' . $startIndx . '" width="' . 
				$picWidth[$startIndx] . '" height="' . $maxRowHt . '" src="' . 
				$photolink[$startIndx] . '" alt="' . $desc[$startIndx] . '" />';
			$startIndx += 1;
		} else if ($itype[$startIndx] === "iframe") {
			$rowHtml = $rowHtml . '<iframe id="theMap" height="' . $maxRowHt .
				'" width="' . $maxRowHt . '" src="../maps/' . $gpsvMap . '"></iframe>';
			$startIndx += 1;
		} else if ($itype[$startIndx] === "chart") {
			$elevWidth = $widthAtMax[$startIndx];
			$rowHtml = $rowHtml . '<img class="chart" width="' . $elevWidth . 
				'" height="' . $maxRowHt . '" src="../images/' . $elevChart .
				'" alt="Elevation Chart" />';
			$startIndx += 1;
		} else {
			$othrWidth[$othrIndx] = $widthAtMax[$startIndx];
			$othrHeight[$othrIndx] = $maxRowHt;
			$rowHtml = $rowHtml . '<img width="' . $othrWidth[$othrIndx] . '" height="' .
				$maxRowHt . '" src="../images/' . $addonImg[$othrIndx] .
				'" alt="Additional page image" />';
			$othrIndx += 1;
			$startIndx += 1;
		}
	}
	$rowHtml = $rowHtml . "</div>";
}
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

<div id="postPhoto">
	<?php if($trailTips == 'YES') echo '<div id="trailTips">' . "\n\t\t" .
		'<img id="tipPic" src="../images/tips.png" alt="special notes icon" />' . "\n\t\t" .
		'<p id="tipHdr">TRAIL TIPS!</p>' . "\n\t\t" . '<p id="tipNotes">Put tip info here...</p>' .
		"\n\t" . '</div>';?>

	<p id="hikeInfo">ENTER TRAIL DESCRIPTION AND NOTES HERE...</p>
	
	<fieldset>
	<legend id="fldrefs">References &amp; Links</legend>
	<ul id="refs">
		<li>Book: <em>Day Hikes In The Santa Fe Area [8th Ed.]</em>, by The Northern New
			Mexico Group of the Sierra Club</li>
		<li>App: <a href="http://www.alltrails.com/trail/us/new-mexico/burnt-mesa-trail"
			target="_blank">AllTrails</a></li>
	</ul>
	</fieldset>
	<fieldset>
	<legend id="flddat">GPS Maps &amp; Data</legend>
		<div id="proposed">
			<p id="proptitle">- Proposed Hike Data</p>
			<ul id="plinks">
				<li>Map: <a href="../maps/BistiWestProposed.html"
						target="_blank">Proposed Trail</a></li>
				<li>GPX: <a href="../gpx/BistiWestProposed.gpx"
						target="_blank">Proposed Trail</a></li>
			</ul>
		</div>
		<div id="actual">
			<p id="acttitle">- Actual Hike Data</p>
			<ul id="alinks">
				<li>Map: <a href="../maps/Bisti_geomap.html"
					target="_blank">Actual Hike</a></li>
				<li>GPX: <a href="../gpx/Bisti.GPX" target="_blank">
					Actual GPX</a></li>
			</ul>
		</div>
	</fieldset>
	
	<div id="dbug"></div>
</div>  <!-- end of postPhoto -->

<div class="popupCap"></div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/hikes_build.js"></script>

</body>
</html>