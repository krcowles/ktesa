<?php
	$hikeIndexNo = $_GET['hikeIndx'];
	/* Use the common database (excel csv file) to extract info */
	$dataTable = '../data/test.csv';
	$handle = fopen($dataTable,'r');
	if ($handle !== false) {
		$lineno = 0;
		while ( ($line = fgets($handle)) !== false ) {
			if ($lineno > 0) {
				$indxArray = str_getcsv($line,",");
				if ($hikeIndexNo == $indxArray[0]) {  // find the target hike
					$indxTitle = $indxArray[1];
					$lnkText = str_replace('Index','',$indxTitle);
					$parkMap = $indxArray[21];
					$parkDirs = $indxArray[25];
					$parkInfo = $indxArray[38];
					$refStr = $indxArray[39];
					/* Convert string array into real references */
					$list = explode("^",$refStr);
					$noOfItems = intval($list[0]);
					array_shift($list);
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
					$indxTbl = rawurldecode($indxArray[29]);
					break;
				}  // end of: if this is the hike
			}
			$lineno++;
		}
	} else {
		echo "<p>Could not open {$dataTable}</p>";
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $indxTitle;?></title>
	<meta charset="utf-8" />
	<meta name="language"
			content="EN" />
	<meta name="description"
		content="Details about the {$hikeTitle} hike" />
	<meta name="author"
		content="Tom Sandberg and Ken Cowles" />
	<meta name="robots"
		content="nofollow" />
	<link href="../styles/960_16_col.css" type="text/css" rel="stylesheet" />
	<link href="../styles/subindx.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div class="container_16 clearfix">
	<div id="logoBlock">
		<p id="pgLogo"></p>
		<p id="logoLeft">Hike New Mexico</p>
		<p id="logoRight">w/ Tom &amp; Ken</p>
		<p id="indxTitle" class="grid_16"><?php echo $indxTitle;?></p>
	</div> <!-- end of logoBlock -->
	
	<img class="mainPic" src="<?php echo '../images/' . $parkMap;?>" alt="Park Service Map" />
	<p id="dirs"><a href="<?php echo $parkDirs;?>" target="_blank">
	Directions to the <?php echo $lnkText;?></a></p>
    <?php
        echo '<p id="indxContent">' . $parkInfo . '</p>';
        echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
        echo $htmlout . '</fieldset>';
    ?>
    <div id="hdrContainer">
		<p id="tblHdr">Hiking & Walking Opportunities at <?php echo $lnkText;?>:</p>
	</div>
	<div>
	<?php 
		if ($indxTbl !== '') {
			echo $indxTbl;
		} else {
			echo "No hikes yet associated with this park";
		}
	?>
	</div>

</div>  <!-- end of container 16 -->
	
<script src="../scripts/jquery-1.12.1.js"></script>

</body>

</html>
