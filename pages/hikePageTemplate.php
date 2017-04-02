<!DOCTYPE html>
<html>

<?php
define('Simple','0');
define('References','1');
define('Proposed','2');
define('Actual','3');
	
function makeHtmlList($type,$str) {
	$list = explode("^",$str);
	$noOfItems = intval($list[0]);
	array_shift($list);
	if ($type === Simple) {
		$htmlout = '<ol>';
		for ($j=0; $j<$noOfItems; $j++) {
			$htmlout = $htmlout . '<li>' . $list[$j] . '</li>';
		}
		$htmlout = $htmlout . '</ol>';
	} elseif ($type === References) {
		$nxt = 0;
		$htmlout = '<ul id="refs">';
		for ($k=0; $k<$noOfItems; $k++) {
			$tagType = $list[$nxt];
			if ($tagType === 'b') { 
				$htmlout .= '<li>Book: <em>' . $list[$nxt+1] . '</em>' . $list[$nxt+2] . '</li>';
				$nxt += 3;
			} elseif ($tagType === 'p') {
				$htmlout .= '<li>Photo Essay: <em>' . $list[$nxt+1] . '</em>' . $list[$nxt+2] . '</li>';
				$nxt += 3;
			} elseif ($tagType === 'n') {
				$htmlout .= '<li>' . $list[$nxt+1] . '</li>';
				$nxt += 2;
			} else {
				if ($tagType === 'w') {
					$tag = '<li>Website: ';
				} elseif ($tagType === 'a') {
					$tag = '<li>App: ';
				} elseif ($tagType === 'd') {
					$tag = '<li>Downloadable Doc: ';
				} elseif ($tagType === 'h') {
					$tag = '<li>';
				} elseif ($tagType === 'l') {
					$tag = '<li>Blog: ';
				} elseif ($tagType === 'r') {
					$tag = '<li>Related Site: ';
				} elseif ($tagType === 'o') {
					$tag = '<li>Map: ';
				} elseif ($tagType === 'm') {
					$tag = '<li>Magazine: ';
				} elseif ($tagType === 's') {
					$tag = '<li>News article: ';
				} elseif ($tagType === 'g') {
					$tag = '<li>Meetup Group: ';
				} else {
					$tag = '<li>CHECK DATABASE: ';
				}
				$htmlout .= $tag . '<a href="' . $list[$nxt+1] . '" target="_blank">' .
					$list[$nxt+2] . '</a></li>';
				$nxt += 3;
			}
		} // end of for loop in references
		$htmlout .= '</ul>';
	} elseif ($type === Proposed || $type === Actual) {
		$nxt = 0;
		if ($type === Proposed) {
			$htmlout = '<p id="proptitle">- Proposed Hike Data</p><ul id="plinks">';
		} else {
			$htmlout = '<p id="acttitle">- Actual Hike Data</p><ul id="alinks">';
		}
		for ($n=0; $n<$noOfItems; $n++) {
			$htmlout .= '<li>' . $list[$nxt] . '<a href="' . $list[$nxt+1] .
				'" target="_blank">' . $list[$nxt+2] . '</a></li>';
			$nxt += 3;
		}
		$htmlout .= '</ul>';
	} else {
			echo "Unknown argument in makeHtmlList, Hike " . $hikeIndexNo . ': ' . $tagType;
	}  // end of if tagtype ifs
	return $htmlout;
} // FUNCTION END....
	
	$hikeIndexNo = $_GET['hikeIndx'];
	/* NOTE: The database file is only read in here, no writing to it occurs */
	$dataTable = '../data/database.csv';
	$handle = fopen($dataTable,'r');
	if ($handle !== false) {
		$lineno = 0;
		while ( ($hikeArray = fgetcsv($handle)) !== false ) {
			if ($lineno > 0) {  // skip the header row
				if ($hikeIndexNo == $hikeArray[0]) {  // find the target hike
					$hikeTitle = $hikeArray[1];
					$hikeDifficulty = $hikeArray[9];
					$hikeLength = $hikeArray[7] . " miles";
					$hikeType = $hikeArray[6];
					$hikeElevation = $hikeArray[8] . " ft";
					$hikeExposure = $hikeArray[13];
					$hikeWow = $hikeArray[11];
					$hikeFacilities = $hikeArray[10];
					$hikeSeasons = $hikeArray[12];
					$hikePhotoLink1 = $hikeArray[23];
					$hikePhotoLink2 = $hikeArray[24];
					$hikeDirections = $hikeArray[25];
					$rows = array();
					$picNo = 0;
					$frameFlag = false;  # true identifies next row needs space for link under map
					for ($j=0; $j<6; $j++) {
						$thisrow = $hikeArray[$j+29];
						if ($thisrow == '') {
							$rowCount = $j;
							break;
						} else {
							$rowdat = explode("^",$thisrow);
							$leftmost = true;
							$els = intval($rowdat[0]);
							$rowht = $rowdat[1];
							$elType = $rowdat[2]; // can be either 'p' 'n' or 'f'
							$nxtel = 2;
							if ($frameFlag) {
								$rowhtml = '<div id="row' . $j . '" class="ImgRow Solo">';
								# class Solo is a misnomer, but allows the needed space for the map link
								$frameFlag = false;
							} else {
								$rowhtml = '<div id="row' . $j . '" class="ImgRow">';
							}
							for ($k=0; $k<$els; $k++) {
								if ($leftmost) {
									$style = '';
									$leftmost = false;
								} else {
									$style = 'margin-left:1px;';
								}
								$width = $rowdat[$nxtel+1];
								$src = $rowdat[$nxtel+2];
								if ($elType === 'p') { // captioned image
									$cap = $rowdat[$nxtel+3];
									$rowhtml = $rowhtml . '<img id="pic' . $picNo . '" style="' .
										$style . '" width="' . $width . '" height="' . $rowht .
										'" src="' . $src . '" alt="' . $cap . '" />';
									$picNo++;
									$nxtel += 4;
								} elseif ($elType === 'n') { // non-captioned image
									$rowhtml = $rowhtml . '<img style="' . $style .
										'" width="' . $width . '" height="' . $rowht .
										'" src="' . $src . '" alt="no caption" />';
									$nxtel +=3;
								} else {  // iframe
									$rowhtml = $rowhtml . '<iframe id="theMap" style="' . $style .
										'" width="' . $rowht . '" height="' . $rowht . '" src="' .
										$src . '"></iframe>';
									$frameFlag = true;
									$nxtel += 3;
								}
								$elType = $rowdat[$nxtel];
							}
							$rowhtml = $rowhtml . '</div>';
							array_push($rows,$rowhtml);
							if ($j === 5) {
								$rowCount = 6;
							}
						} // end of if row not empty
					}  // end of row loop
					$picCaptions = $hikeArray[35];
					$picCaptions = makeHtmlList(Simple,$picCaptions);
					$picLinks = $hikeArray[36];
					$picLinks = makeHtmlList(Simple,$picLinks);
					$hikeTips = $hikeArray[37];
					$hikeTips = preg_replace("/\s/"," ",$hikeTips);
					$hikeInfo = '<p id="hikeInfo" style="clear:both;">' . $hikeArray[38] . '</p>';
					$hikeReferences = $hikeArray[39];
					$hikeReferences = makeHtmlList(References,$hikeReferences);
					$hikeProposedData = $hikeArray[40];
					$hikeActualData = $hikeArray[41];
					/* No fieldset when there is neither prop or act data */
					if ($hikeProposedData !== '' || $hikeActualData !== '') {
						$fieldsets = true;
						$datasect = '<fieldset><legend id="flddat">GPS Maps &amp; Data</legend>';
						if ($hikeProposedData !== '') {
							$datasect .= makeHtmlList(Proposed,$hikeProposedData);
						}
						if ($hikeActualData !== '') {
							$datasect .= makeHtmlList(Actual,$hikeActualData);
						}
						$datasect .= '</fieldset>';
					}
				}  // end of if finding the hike to display
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
					<td><?php echo $hikeExposure;?></td>
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
		echo '</div>'; # end of container_16 forced width
		for ($k=0; $k<$rowCount; $k++) {
			echo $rows[$k];
		}
		echo '<div class="captionList">' . $picCaptions . '</div>';
		echo '<div class="lnkList">' . $picLinks . '</div>';
	?>
	<div id="postPhoto">
		<?php
			if ($hikeTips !== '') {
				echo '<div id="trailTips" style="clear:both;"><img id="tipPic" src="../images/tips.png" alt="special notes icon" />' .
					'<p id="tipHdr">TRAIL TIPS!</p><p id="tipNotes">' . 
					htmlspecialchars_decode($hikeTips,ENT_COMPAT) . '</p></div>';
			}
			echo $hikeInfo;
			if ($hikeReferences !== '') {
				echo '<fieldset>'."\n";
				echo '<legend id="fldrefs">References &amp; Links</legend>'."\n";
				echo htmlspecialchars_decode($hikeReferences,ENT_COMPAT) . "\n";
				echo '</fieldset>';
			}
			if ($fieldsets) {
				echo $datasect;
			}
		?>
		
		<div id="dbug"></div>

	</div>  <!-- end of postPhoto section -->

<div class="popupCap"></div>
	
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/hikes.js"></script> 

</body>

</html>
