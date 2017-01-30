<!-- REFERENCE TABLE OF HIKES -->
<table id="sortable">
	<colgroup>	
		<col style="width:120px">
		<col style="width:190px">
		<col style="width: 140px">
		<col style="width:80px">
		<col style="width:70px">
		<col style="width:95px">
		<col style="width:100px">
		<col style="width:70px">
		<col style="width:70px">
		<col style="width:74px">
	</colgroup>
	<tr>
		<th class="hdr_row" data-sort="std">Locale</th>
		<th class="hdr_row" data-sort="std">Hike/Trail Name</th>
		<th class="hdr_row" data-sort="std">WOW Factor</th>
		<th class="hdr_row">Web Pg</th>
		<th class="hdr_row" data-sort="lan">Length</th>
		<th class="hdr_row" data-sort="lan">Elev Chg</th>
		<th class="hdr_row" data-sort="std">Difficulty</th>
		<th class="hdr_row">Exposure</th>
		<th class="hdr_row">By Car</th>
		<th class="hdr_row">Photos</th>
	</tr>
	<tbody>
	<!-- ADD HIKE ROWS VIA PHP HERE: -->
	<?php
	$dataTable = '../data/test.csv';
	$handle = fopen($dataTable,'r');
	if ($handle !== false) {
		$lineno = 0;
		/* some image definitions for icons that will appear as hyperlinks in the table */
		$indxIcon = '<img class="webShift" src="../images/indxCheck.png" alt="index checkbox" />';
		$webIcon = '<img class="webShift" src="../images/greencheck.jpg" alt="checkbox" />';
		$dirIcon = '<img src="../images/dirs.png" alt="google driving directions" />';
		$picIcon = '<img class="flckrShift" src="../images/album_lnk.png" alt="Flickr symbol" />';
		$sunIcon = '<img class="expShift" src="../images/sun.jpg" alt="Sunny icon" />';
		$partialIcon = '<img class="expShift" src="../images/greenshade.jpg" alt="Partial shade icon" />';
		$shadeIcon = '<img class="expShift" src="../images/shady.png" alt="Partial sun/shade icon" />';
		// loop through each entry in the csv file
		while ( ($line = fgets($handle)) !== false ) {
			if ($lineno > 0) {
				$hikeArray = str_getcsv($line,",");
				
				/* The next 4 variables are hidden in the html text using data- attributes */
				$hikeIndx = $hikeArray[0];
				$hikeLat = $hikeArray[19];
				$hikeLon = $hikeArray[20];
				$hikeTrk = $hikeArray[18];
				$hikeHiddenDat = 'data-indx="' . $hikeIndx . '" data-lat="' . $hikeLat . '" data-lon="' . $hikeLon .
					'" data-track="' . $hikeTrk . '"';
				/* the following variables are assigned depending on marker types: the 
				   $hikeArray supplies defaults which are over-ruled if an index page */
				$hikeWow = $hikeArray[11];
				$hikeLgth = $hikeArray[7];
				$hikeElev = $hikeArray[8];
				$hikeDiff = $hikeArray[9];
				$hikeExposure = $hikeArray[13];
				if ($hikeExposure === 'Full sun') {
					$hikeExpIcon = '<td>' . $sunIcon . '</td>';
				} elseif ($hikeExposure === 'Mixed sun/shade') {
					$hikeExpIcon = '<td>' . $partialIcon . '</td>';
				} else {
					$hikeExpIcon = '<td>' . $shadeIcon . '</td>';
				}
				$hikeMainURL = rawurldecode($hikeArray[23]);
				$hikePhotoLink = '<td><a href="' . $hikeMainURL . '" target="_blank">' . $picIcon . '</a></td>';
				$hikeLinkIcon = $webIcon;
				/* There are four types of markers to consider requiring different treatment: */
				$hikeMarker = $hikeArray[3];
				if ($hikeMarker === 'Visitor Ctr') {
					echo '<tr class="indxd" ' . $hikeHiddenDat . ' data-org-hikes="' .
						$hikeArray[4] . '">';  // Visitor centers id any subhikes
					$hikeLinkIcon = $indxIcon;
					$hikeWow = "See Indx";
					$hikeLgth = "0*";
					$hikeElev = "0*";
					$hikeDiff = "See Indx";
					$hikeExpIcon = '<td>See Indx</td>';
					$hikePhotoLink = '<td>See Indx</td>';
				} elseif ($hikeMarker === 'Cluster') {
					echo '<tr class="clustered" data-cluster=" ' . $hikeArray[5] . '" ' .
						$hikeHiddenDat . ' data-tool="' . $hikeArray[28] . '">';
				} elseif ($hikeMarker === 'At VC') {
					# At VC hikes will be ignored when time to create markers
					echo '<tr class="vchike"  ' . $hikeHiddenDat . '>';
				} else {  // "Normal"
					echo '<tr class="normal" ' . $hikeHiddenDat . '>';
				}
				$hikePage = $hikeArray[27];
				if ($hikePage === '') {
					if ($hikeMarker === 'Visitor Ctr') {
						$hikePage = 'indexPageTemplate.php?hikeIndx=' . $hikeIndx;
					} else {
						$hikePage = 'hikePageTemplate.php?hikeIndx=' . $hikeIndx;
					}
				}
				$hikeName = $hikeArray[1];
				$hikeLocale = $hikeArray[2];
				$hikeDirections = rawurldecode($hikeArray[25]);
				/* There may be either one or two photo links... if only one, then
				   post the icon for photos on the hike page summary table; regardless,
				   post the "main" link here in the data table */
				
				//print out a row:
				echo '<td>' . $hikeLocale . '</td>';
				echo '<td>' . $hikeName . '</td>';
				echo '<td>' . $hikeWow . '</td>';
				echo '<td><a href="' . $hikePage . '" target="_blank">' . $hikeLinkIcon . '</a></td>';
				echo '<td>' . $hikeLgth . ' miles</td>';
				echo '<td>' . $hikeElev . ' ft</td>';
				echo '<td>' . $hikeDiff . '</td>';
				echo $hikeExpIcon;
				echo '<td style="text-align:center"><a href="' . $hikeDirections . '" target="_blank">' .
						$dirIcon . '</a></td>';
				echo $hikePhotoLink;
				echo '</tr>';
			}
			$lineno++;
		}
		$lineno -= 1;
	} else {
		echo "<p>Could not open {$fname}</p>";
	} 
	?>
	</tbody>
</table>