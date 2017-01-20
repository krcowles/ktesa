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
					$parkDirs = rawurldecode($indxArray[25]);
					$parkInfo = rawurldecode($indxArray[38]);
					$refs = rawurldecode($indxArray[39]);
					$indxTbl = rawurldecode($indxArray[29]);
				}
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
        echo $refs . '</fieldset>';
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
