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
	$elevWidth = floatval(trim($_POST['chrtW']));
	$elevHeight = floatval(trim($_POST['chrtH']));
	$elevAR = $elevWidth/$elevHeight;
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
				echo " scaleFactor is {$scaleFactor}, while scaleWidth is {$scaleWidth}";
				$pwdth = floor($scaleWidth * $picWidth[$thispic]);
				$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img id="pic' . $thispic .
					'" height="' . $newHt . '" width="' . $pwdth . '" src="' . 
					$nsize[$thispic] . '" alt="' . $desc[$thispic] . '" />';
			}
			$rowNo++;
			$curRowWidth = 0;
			$itemNo = -1;
		}
		$itemNo++;
	} 
	# at this point, by definition, the row-in-progress is not overflowing,
	#    hence, define this row as starting with 1 or more photos from above:
	$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow">';
	$npItem = 0;  # index no. of non-photo image (geomap, etc)
	$nonPics[$npItem] = '';  # empty string indicates nothing has been added
	# rowItems holds only pix at this point: add geomap if able
	if ((MaxWidth - $curRowWidth) > Min_Iframe_Size) {
		$curRowWidth += Min_Iframe_Size;
		$nonPics[$npItem] = $gpsvMap; # 0 will always represent the geomap by definition
		$npItem++;
		$scaleFactor = opt_row_ht($curRowWidth);
		$newHt = floor($scaleFactor * RowHt);
		$rowHtml[$rowNo] = '';
		for ($m=0; $m<$itemNo; $m++) {
			$thispic = $rowItems[$m];
			$scaleWidth = $newHt/$picHeight[$thispic];
			$pwdth = floor($scaleWidth * $picWidth[$thispic]);
			$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img id="pic' . $thispic .
				'" height="' . $newHt . '" width="' . $pwdth . '" src="' . 
				$nsize[$thispic] . '" alt="' . $desc[$thispic] . '" />';
		}
		$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<iframe id="theMap" height="' . $newHt .
				'" width="' . $newHt . '" src="../../maps/' . $gpsvMap . '"></iframe>';
		# don't optimize until it is seen if something else fits...
	} else {  # the map won't fit, so goto the next step: elevation chart - does that fit?
		# elevation height starts at RowHt, so
		$curElevWidth = floor(RowHt/$elevHeight * $elevWidth); 
		echo "chart width at 260 is {$curElevWidth}";
		if ((MaxWidth - $curRowWidth) > $curElevWidth) {
			# chart fits - surprising, but ok
			echo "chart fits - wow!";
			exit;
		} else {
			# nothing fits, so end the row (only pix) and start a new row with the geomap:
			$scaleFactor = opt_row_ht($curRowWidth);
			$newHt = floor($scaleFactor * RowHt);
			$rowHtml[$rowNo] = '';
			for ($m=0; $m<$itemNo; $m++) {
				$thispic = $rowItems[$m];
				$scaleWidth = $newHt/$picHeight[$thispic];
				$pwdth = floor($scaleWidth * $picWidth[$thispic]);
				$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<img id="pic' . $thispic .
					'" height="' . $newHt . '" width="' . $pwdth . '" src="' . 
					$nsize[$thispic] . '" alt="' . $desc[$thispic] . '" />';
			}
			$rowNo++;
			$rowDiv[$rowNo] =  '<div id="row' . $rowNo . '" class="ImgRow">';
			# add geomap to row
			$rowHtml[$rowNo] = $rowHtml[$rowNo] . '<iframe id="theMap" height="' . $newHt .
				'" width="' . $newHt . '" src="../../maps/' . $gpsvMap . '"></iframe>';
			}
	}
	?>
<html>
<head>
	<title>Making The Page</title>
	<link href="step3.css" type="text/css" rel="stylesheet" />
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
	echo $rowDiv[0];
	echo $rowHtml[0];
	echo $closingDiv;
	echo $rowDiv[1];
	echo $rowHtml[1];
	echo $closingDiv;
	echo $rowDiv[2];
	echo $rowHtml[2];
	echo $closingDiv;
?>


</body>
</html>