<!DOCTYPE html>
<html>

<?php
	$hikeIndexNo = $_GET['hikeIndx'];
	/* Use the common database (excel csv file) to extract info */
	$dataTable = '../data/test.csv';
	$handle = fopen($dataTable,'r');
	if ($handle !== false) {
		$lineno = 0;
		while ( ($line = fgets($handle)) !== false ) {
			if ($lineno > 0) {
				$hikeArray = str_getcsv($line,",");
				if ($hikeIndexNo == $hikeArray[0]) {  // find the target hike
					$hikeTitle = $hikeArray[1];
					$hikeDifficulty = $hikeArray[9];
					$hikeLength = $hikeArray[7];
					$hikeType = $hikeArray[6];
					$hikeElevation = $hikeArray[8];
					$hikeExposure = $hikeArray[13];
					$hikeWow = $hikeArray[11];
					$hikeFacilities = $hikeArray[10];
					$hikeSeasons = $hikeArray[12];
					$hikePhotoLink1 = $hikeArray[23];
					$hikePhotoLink2 = $hikeArray[24];
					$hikeDirections = rawurldecode($hikeArray[25]);
					$rows = array();
					for ($j=0; $j<6; $j++) {
						$thisRow = $hikeArray[$j+29];
						if ($thisRow == '') {
							$rowCount = $j;
							break;
						} else {
							$rows[$j] = $thisRow;
						}
					}
					$picCaptions = rawurldecode($hikeArray[35]);
					$picLinks = rawurldecode($hikeArray[36]);
					if ($hikeArray[26] == 'Y') {
						$hikeTipsPresent = true;
						$hikeTips = $hikeArray[37];
					} else {
						$hikeTipsPresent = false;
					}
					$hikeInfo = '<p id="hikeInfo">' . $hikeArray[38] . '</p>';
					$hikeReferences = rawurldecode($hikeArray[39]);
					$hikeProposedData = rawurldecode($hikeArray[40]);
					$hikeActualData = rawurldecode($hikeArray[41]);
				}
			}
			$lineno++;
		}
	} else {
		echo "<p>Could not open {$dataTable}</p>";
	}
?>
<head>
	<title><?php echo $hikeTitle;?></title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Details about the {$hikeTitle} hike" />
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
    		<p id="page_title" class="grid_16"><?php echo $hikeTitle;?></p>
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
                        <?php if($hikePhotoLink2 == '') {
                        	echo "<th>Photos</th>";
                        }?>
                        <th>By Car</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $hikeDifficulty;?></td>
                        <td><?php echo $hikeLength;?></td>
                        <td><?php echo $hikeType;?></td>
                        <td><?php echo $hikeElevation;?></td>
                        <td><?php if($hikeExposure == 'sun') {
                        	echo "Full Sun"; } elseif($hikeExposure == 'partial') {
                        	echo "Some shade"; } else {
                        	echo "Good shade"; }?></td>
                        <td><?php echo $hikeWow;?></td>
                        <td><?php echo $hikeFacilities;?></td>
                        <td><?php echo $hikeSeasons;?></td>
                        <?php if($hikePhotoLink2 == '') {
                        	echo '<td><a href="' . $hikePhotoLink1 . '" target="_blank">' .
                        		'<img style="margin-bottom:0px;border-style:none;" src="../images/album_lnk.png" alt="photo album link icon" /></a></td>';
                        	}?>
                        <td><a href="<?php echo $hikeDirections?>" target="_blank">
                             <img style="margin-bottom:0px;padding-bottom:0px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        	if ($hikePhotoLink2 !== '') {
				echo '<div style="margin-bottom:8px;"><em>-- To see more photos:</em> click on <a href="' .
					$hikePhotoLink2 . '" target="_blank">Tom\'s Photo Album</a>, or <a href="' .
					$hikePhotoLink1 . '" target="_blank">Ken\'s Photo Album</a></div>';
			}
        	for ($k=0; $k<$rowCount; $k++) {
        		echo $rows[$k];
        	}
        	echo $picCaptions;
        	echo $picLinks;
        ?>
		<div id="postPhoto">
			<?php
				if ($hikeTipsPresent) {
					echo '<div id="trailTips"><img id="tipPic" src="../images/tips.png" alt="special notes icon" />' .
						'<p id="tipHdr">TRAIL TIPS!</p><p id="tipNotes">' . $hikeTips . '</div>';
				}
				echo $hikeInfo;
				if ($hikeReferences !== '') {
					echo '<fieldset>'."\n";
					echo '<legend id="fldrefs">References &amp; Links</legend>'."\n";
					echo $hikeReferences . "\n";
					echo '</fieldset>';
				}
				if ($hikeProposedData !== '' || $hikeActualData !== '') {
					echo '<fieldset>'."\n";
					echo '<legend id="flddat">GPS Maps &amp; Data</legend>'."\n";
					if ($hikeProposedData !== '') {
						echo '<p id="proptitle">- Proposed Hike Data</p>'."\n";
						echo $hikeProposedData."\n";
					}
					if ($hikeActualData !== '') {
						echo '<p id="acttitle">- Actual Hike Data</p>'."\n";
						echo $hikeActualData."\n";
					}
					echo '</fieldset>';
				}
			?>
			
			<div id="dbug"></div>
	
		</div>  <!-- end of postPhoto section -->

	</div><!-- end of container 16 -->
	<div class="popupCap"></div>
	
	<script src="../scripts/jquery-1.12.1.js"></script>
	<script src="../scripts/hikes.js"></script>
</body>

</html>
