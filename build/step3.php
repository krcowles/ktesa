<?php
#	All this data from step 2: (eventually in database, not passed via form!)
	$tsvFile = $_POST['whose'];
	$hikeName = $_POST['hname'];
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
	} else if ($exp = "shade") {
		$exposure = "Good shade";
	} else {
		$exposure = "Mixed sun/shade";
	}
	$gpsvMap = $_POST['geomp'];
	$elevChart = $_POST['chart'];
	$elevWidth = $_POST['chrtW'];
	$elevHeight = $_POST['chrtH'];
	$gpxFname = $_POST['gpx'];
	$trackFname = $_POST['json'];
	$addonImg1 = $_POST['img1'];
	$addonImg2 = $_POST['img2'];
	$marker = $_POST['mrkr'];
	$purl1 = $_POST['phot1'];
	$purl2 = $_POST['phot2'];
	$googledirs = $_POST['gdirs'];
	$picarray = $_POST['pix'];
	$noOfPix = count($picarray);
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
		$picSize = getimagesize($nsize[$x]);
		$picWidth[$i] = $picSize[0];
		$picHeight[$i] = $picSize[1];
		$name[$i] = $picName[$x];
		$desc[$i] = $picDesc[$x];
		$album[$i] = $picAlbm[$x];
		$photolink[$i] = $nsize[$x]; 
	}
	
	# function will be used to optimize row height - always starts at "$rowHt"
	function opt_row_ht($fillSpace) {
		$rowMargin = MaxWidth - $fillSpace;
		$scale = 1.000;
		while ($rowMargin > TooMuchMargin) {
			$scale *= 1.1;
			if ($scale > 1.50) { 
				$scale /= 1.1;
				break;
			}
			$newSpace = $scale * $fillSpace;
			$rowMargin = MaxWidth - $newSpace;
		}
		return $scale;	
	}

	# Everything is now in place to make rows
	$rowNo = 0;
	$itemNo = 0;
	$curRowWidth = 0;
	# for each of the user-selected pix, calculate a row and scale it
	for ($i=0; $i<$noOfPix; $i++) {
		$aspect = $picWidth[$i]/$picHeight[$i];
		$curPicWidth = RowHt * $aspect;
		$curRowWidth += $curPicWidth + Spacing;
		$rowItems[$itemNo] = $i;
		if ($curRowWidth >= MaxWidth) {
			$curRowWidth -= ($curPicWidth + Spacing);
			$rowItems[$itemNo] = 0;
			$i--;
			$scaleFactor = opt_row_ht($curRowWidth);
			$newHt = floor($scaleFactor * RowHt);
			# create the row html
			$rowDiv[$rowNo] = '<div id="row' . $rowNo . '" class="ImgRow">';
			$rowHtml[$rowNo] = '';
			for ($m=0; $m<$itemNo; $m++) {
				$thispic = $rowItems[$m];
				$scaleWidth = $newHt/$picHeight[$thispic];
				$pwdth = floor($scaleWidth * $picWidth[$thispic]);
				$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img id="pic' . $thispic .
					'" height="' . $newHt . '" width="' . $pwdth . '" src="' . 
					$photolink[$thispic] . '" alt="' . $desc[$thispic] . '" />';
			}
			$rowNo++;
			$curRowWidth = 0;
			$itemNo = -1;
		}
		$itemNo++;
	} 
	# ALL PHOTOS HAVE BEEN PROCESSED
	$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow">';
	# at this point, by definition, the row-in-progress is not overflowing,
	#    hence, the current row has  1 or more photos from above:
	# most important 1st decision: is there room on this row for the map?
	if ((MaxWidth - $curRowWidth) > Min_Iframe_Size) { # YES, map room...
		$curRowWidth += Min_Iframe_Size; # add map size to width; now:
		$addchart = false;
		if ((MaxWidth - $curRowWidth) > $startChartWidth) {
			# room for chart, too:
			$curRowWidth += $startChartWidth;
			$addchart = true;
		}
		$scaleFactor = opt_row_ht($curRowWidth);
		$newHt = floor($scaleFactor * RowHt);
		$rowHtml[$rowNo] = '';
		for ($m=0; $m<$itemNo; $m++) {
			$thispic = $rowItems[$m];
			$scaleWidth = $newHt/$picHeight[$thispic];
			$pwdth = floor($scaleWidth * $picWidth[$thispic]);
			$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img id="pic' . $thispic .
				'" height="' . $newHt . '" width="' . $pwdth . '" src="' . 
				$photolink[$thispic] . '" alt="' . $desc[$thispic] . '" />';
		}
		$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<iframe id="theMap" height="' . $newHt .
			'" width="' . $newHt . '" src="../../maps/' . $gpsvMap . '"></iframe>';
		if ($addchart === true) { # add chart to this row
			$newChartWidth = floor($scaleFactor * $startChartWidth);
			$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img class="chart" height="' . $newHt .
					'" width="' . $newChartWidth . '" src="../images/' . $elevChart .
					'" alt="elevation graph" />';
		} else { # or else add new row for chart
			$rowNo++;
			$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow Solo">';
			$curRowWidth = $startChartWidth;
			$scaleFactor = opt_row_ht($curRowWidth);
			$newHt = floor($scaleFactor * RowHt);
			$newChartWidth = floor($scaleFactor * $startChartWidth);
			$rowHtml[$rowNo] = '<img class="chart" height="' . $newHt .
					'" width="' . $newChartWidth . '" src="../images/' . $elevChart .
					'" alt="elevation graph" />';
		}
	} else { # NO, no room for map...
		# optimize current row...
		$scaleFactor = opt_row_ht($curRowWidth);
		$newHt = floor($scaleFactor * RowHt);
		$rowHtml[$rowNo] = '';
		for ($m=0; $m<$itemNo; $m++) {
			$thispic = $rowItems[$m];
			$scaleWidth = $newHt/$picHeight[$thispic];
			$pwdth = floor($scaleWidth * $picWidth[$thispic]);
			$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img id="pic' . $thispic .
				'" height="' . $newHt . '" width="' . $pwdth . '" src="' . 
				$photolink[$thispic] . '" alt="' . $desc[$thispic] . '" />';
		}
		# and start a new one
		$rowNo++;
		$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow Solo">';
		$curRowWidth = Min_Iframe_Size;
		$addchart = false;
		if ((MaxWidth - $curRowWidth) > $startChartWidth) { #room for the chart
			$addchart = true;
			$curRowWidth += $startChartWidth;
			$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow">';
		}
		$scaleFactor = opt_row_ht($curRowWidth);
		$newHt = floor($scaleFactor * RowHt);
		$rowHtml[$rowNo] = '<iframe id="theMap" height="' . $newHt .
			'" width="' . $newHt . '" src="../../maps/' . $gpsvMap . '"></iframe>';
		if ($addchart === true) {
			$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow">';
			$newChartWidth = floor($scaleFactor * $startChartWidth);
			$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img class="chart" height="' . $newHt .
				'" width="' . $newChartWidth . '" src="../images/' . $elevChart .
				'" alt="elevation graph" />';
		} 
	}
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
<html>
<head>
	<title>Making The Page</title>
	<link href="../styles/wpages.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="logoBlock">
	<p id="pgLogo"></p>
	<p id="logoLeft">Hike New Mexico</p>
	<p id="logoRight">w/ Tom &amp; Ken</p>
	<p id="page_title">Add A New Hike!</p>
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
				<th>Photos</th>
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
				<td><a href="<?php echo $purl1;?>" target="_blank">
					<img style="margin-bottom:0px;border-style:none;" src="../images/album_lnk.png" alt="photo album link icon" /></a></td>
				<td><a href="<?php echo $googledirs;?>" target="_blank">
				<img style="margin-bottom:0px;padding-bottom:0px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
			</tr>
		</tbody>
	</table>
</div>
<?php 
	for ($i=0; $i<4; $i++) {
		echo $rowDiv[$i];
		echo $rowHtml[$i];
		echo $closingDiv;
	}
	echo $captionHtml;
	echo $albumHtml;
?>
	<div id="albumlinks"><em>-- To see more photos:</em> click on
		<a href="<?php echo $purl2;?>"
			target="_blank">Tom's Flickr Album</a> or
		<a href="<?php echo $purl1;?>"
			target="_blank">Ken's Flickr Album</a>.
	</div>
	<p id="hikeInfo">Burnt Mesa, a finger of land, (part of the Pajarito Plateau 
	</p>
	</p>
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
	</fieldset>

	<div class="popupCap"></div>
	<p id="dbug"></p>
	<script src="../scripts/jquery-1.12.1.js"></script>
	<script src="../scripts/wpages.js"></script>
</body>
</html>