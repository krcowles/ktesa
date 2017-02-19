<!DOCTYPE html>
<html>

<?php 
$database = '../data/test.csv';
$handle = fopen($database,"r");
while ( ($indxInfo = fgetcsv($handle)) !== false ) {
	$lastNo = $indxInfo[0];
}
$index[0] = intval($lastNo) + 1;
# hike form entry
$index[1] = trim($_POST['hpgTitle']);
$index[2] = trim($_POST['locale']);
$index[3] = 'Visitor Ctr';
# NOTE: No cluster string yet - assigned as hikes are created
$index[10] = trim($_POST['fac']);
$index[11] = trim($_POST['wow_factor']);
$index[19] = trim($_POST['lat']);
$index[20] = trim($_POST['lon']);
$parkMap = $_FILES['othr1']['tmp_name'];
$parkMapSize = filesize($parkMap);
$parkMapType = $_FILES['othr1']['type'];
$parkMapName = $_FILES['othr1']['name'];
if($parkMapName == "") {
	die( "No park map specified..." );
}
$mapPath = '../images/' . $parkMapName;
$index[21] = $mapPath;
$index[25] = $_POST['dirs'];
# No table yet ( $info[29] )

/* ASSEMBLE REFERENCES */
$hikeRefTypes = $_POST['rtype'];
$hikeRefItems1 = $_POST['rit1'];
$hikeRefItems2 = $_POST['rit2'];
/* get a count of items actually specified: */
$noOfRefs = count($hikeRefTypes);
for ($k=0; $k<$noOfRefs; $k++) {
	if ($hikeRefItems1[$k] == '') {
		$noOfRefs = $k;
		break;
	}
}
$refLbls = array();
for ($k=0; $k<$noOfRefs; $k++) {
	switch ($hikeRefTypes[$k]) {
		case 'b':
			array_push($refLbls,'Book: ');
			break;
		case 'p':
			array_push($refLbls,'Photo Essay: ');
			break;
		case 'w':
			array_push($refLbls,'Website: ');
			break;
		case 'a':
			array_push($refLblbs,'App: ');
			break;
		case 'd':
			array_push($refLbls,'Downloadable Doc: ');
			break;
		case 'l':
			array_push($refLbls,'Blog: ');
			break;
		case 'r':
			array_push($refLbls,'Related Link: ');
			break;
		case 'o':
			array_push($refLbls,'On-Line Map: ');
			break;
		case 'm':
			array_push($refLbls,'Magazine: ');
			break;
		case 's':
			array_push($refLbls,'News Article: ');
			break;
		case 'g':
			array_push($refLbls,'Meetup Group: ');
			break;
		case 'n':
			array_push($refLbls,'');
			break;
		default:
			echo "Unrecognized reference type passed";
	}
}
$index[38] = $_POST['hiketxt'];
?>

<head>
	<title><?php echo $index[1];?></title>
	<meta charset="utf-8" />
	<meta name="language"
		content="EN" />
	<meta name="description"
		content="Index Page Creation" />
	<meta name="author"
		content="Tom Sandberg & Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/960_16_col.css"
		type="text/css" rel="stylesheet" />
	<link href="../styles/subindx.css"
		type="text/css" rel="stylesheet" />
</head>

<body>

<div class="container_16 clearfix">
	<div id="logoBlock">
		<p id="pgLogo"></p>
		<p id="logoLeft">Hike New Mexico</p>
		<p id="logoRight">w/ Tom &amp; Ken</p>
		<p id="indxTitle" class="grid_16"><?php echo $index[1];?></p>
	</div> <!-- end of logoBlock -->
	
	<img class="mainPic" src="<?php echo $mapPath;?>" alt="Park Map" /><br />
	<p id="dirs"><a href="<?php echo $index[25]?>" target="_blank">Directions to the Visitor Center</a></p>
	<p id="indxContent"><?php echo $index[38];?></p>
	
	<?php 
	/* There SHOULD always be at least one reference, however, if there is not,
	   a message will appear in this section: No References Found */
	$refhtml = '<fieldset><legend id="fldrefs">References &amp; Links</legend><ul id="refs">';
	if ($noOfRefs === 0) {
		$refStr = '1^n^No References Found';
		$refhtml .= '<li>No References Found</li>';
	} else {
		$refStr = $noOfRefs;
		for ($j=0; $j<$noOfRefs; $j++) {
			$x = $hikeRefTypes[$j];
			$refStr .= '^' . $x;
			if ($x === 'n') {
				# only one item in this list element: the text
				$refhtml .= '<li>' . $hikeRefItems1[$j] . '</li>';
				$refStr .= '^' . $hikeRefItems1[$j];
			} else {
				# all other items have two parts + the id label
				$refStr .= '^' . $hikeRefItems1[$j] . '^' . $hikeRefItems2[$j];
				$refhtml .= '<li>' . $refLbls[$j];
				if ($x === 'b' || $x === 'p') {
					# no links in these
					$refhtml .= '<em>' . $hikeRefItems1[$j] . '</em>' . $hikeRefItems2[$j] . '</li>';
				} else {
					$refhtml .= '<a href="' . $hikeRefItems1[$j] . '" target="_blank">' . 
						$hikeRefItems2[$j] . '</a></li>';
				}
			}
		}  // end of for loop processing
	}  // end of if-else
	$refhtml .= '</ul></fieldset>';
	echo $refhtml;
	$index[39] = $refStr;
?>	

	<div id="hdrContainer">
	<p id="tblHdr">Hiking & Walking Opportunities [EMPTY AT THIS TIME]</p>
	</div>
	<form action="saveIndex.php" method="POST">
	<input type="hidden" name="indx[]" value="<?php echo $index[0];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[1];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[2];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[3];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[10];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[11];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[19];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[20];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $parkMapName;?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[25];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[38];?>" />
	<input type="hidden" name="indx[]" value="<?php echo $index[39];?>" />
	
	<input type="submit" value="Save Index Page" />
	</form>
		
</div>  <!-- END OF CONTAINER 16 -->

</body>
</html>
