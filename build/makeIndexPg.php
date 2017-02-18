<!DOCTYPE html>
<html>

<?php 
$hikeFile = $_FILES['xlfile']['tmp_name'];
$hfSize = filesize($hikeFile);
$hikeFileName = $_FILES['xlfile']['name'];
if ($hikeFileName == "") {
	// hike form entry
	$indexPage = trim($_REQUEST['hpgTitle']);
	$hikeLocale = trim($_REQUEST['locale']);
	# $hikeType = trim($_REQUEST['htype']);  // always center
	$hikeLat = trim($_REQUEST['lat']);
	$hikeLong = trim($_REQUEST['lon']);
	$indexImage = $_FILES['othr1']['name'];
	$parkDir = trim($_REQUEST['dirs']);
} else {  // OBSOLETED
/*
	// hike datafile entry
	$datfile = fopen($hikeFile,"r");
	$hdat = fread($datfile,$hfSize);
	$harray = str_getcsv($hdat,"\n");  // array of rows
	$lineCnt = count($harray) - 1;  // row number of latest entry
	// always read in the last row for the latest new page info
	$hikeDataArray = str_getcsv($harray[$lineCnt],",");
	// VARIABLE ASSIGNMENTS USING CSV FILE:
	
	$indexPage = trim($hikeDataArray[0]);
	$hikeLocale = trim($hikeDataArray[1]);
	$hikeLat = trim($hikeDataArray[15]);
	$hikeLong = trim($hikeDataArray[16]);
	$indexImage = trim($hikeDataArray[17]);
	$parkDir = trim($hikeDataArray[22]);
	*/
}
$passMap = $indexImage;
$indexImage = '../images/' . $indexImage;
$database = '../data/test.csv';
$handle = fopen($database,"r");
# determine number of hikes for drop-down list
$noOfHikes = 0;
$choices = array();
$indices = array();
while ( ($hikeArray = fgetcsv($handle)) !== false ) {
	if ($hikeArray[3] === 'Cluster' || $hikeArray[3] === 'Normal') {
		$choices[$noOfHikes] = $hikeArray[1];
		$indices[$noOfHikes] = $hikeArray[0];
		$noOfHikes++;
	}
}
fclose($handle);
?>

<head>
	<title><?php echo $indexPage;?></title>
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
		<p id="indxTitle" class="grid_16"><?php echo $indexPage;?></p>
	</div> <!-- end of logoBlock -->
	
	<img class="mainPic" src="<?php echo $indexImage;?>" alt="Park Map" /><br />
	
	<form action="saveIndex.php" method="POST">
	<p id="dirs"><a href="<?php echo $parkDir?>" target="_blank">Directions to the Visitor Center</a></p>
	<input type="hidden" name="pgName" value="<?php echo $indexPage;?>" />
	<input type="hidden" name="hLocale" value="<?php echo $hikeLocale;?>" />
	<input type="hidden" name="imap" value="<?php echo $passMap;?>" />
	<input type="hidden" name="hlat" value="<?php echo $hikeLat;?>" />
	<input type="hidden" name="hlon" value="<?php echo $hikeLong;?>" />
	<input type="hidden" name="gdirs" value="<?php echo $parkDir;?>" />
	
	<p id="indxContent"><textarea name="parkInfo" cols="116" rows="12">Enter Park Description Here...</textarea></p>
	<?php 
		echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
		echo '<ul id="refs">';
		echo '<ul>';
		echo '<li>Book: <input name="bk[]" type="text" size="40" placeholder="Title of book reference"><br>' .
				'<input name="auth[]" type="text" size="40" placeholder="Name of author(s)">';
		echo '<li>Book: <input name="bk[]" type="text" size="40" placeholder="Title of book reference"><br>' .
				'<input name="auth[]" type="text" size="40"placeholder="Name of author(s)">';
		echo '<li>Book: <input name="bk[]" type="text" size="40" placeholder="Title of book reference"><br>' .
				'<input name="auth[]" type="text" size="40" placeholder="Name of author(s)">';
		echo '<li>Website: <input name="web[]" type="text" size="100" placeholder="Paste link to app here"><br>' .
				'<input name="webtxt[]" type="text" size="20" placeholder="Text to click on">';
		echo '<li>Website: <input name="web[]" type="text" size="100" placeholder="Paste link to app here"><br>' .
				'<input name="webtxt[]" type="text" size="20" placeholder="Text to click on">';
		echo '<li>Website: <input name="web[]" type="text" size="100" placeholder="Paste link to app here"><br>' .
				'<input name="webtxt[]" type="text" size="20" placeholder="Text to click on">';
		echo '</ul></fieldset>';
	?>
	
	<div id="hdrContainer">
		<p id="tblHdr">Park Hiking & Walking Opportunities:</p>
		<p>List any hikes associated with this park by selecting one or more hikes
		from the drop-down list below. They will then appear in a summary table for this park.
		If there are no hikes in the database yet, simply do not select any below. You 
		will be able to add hikes later.</p><br />
		<?php 
			for ($j=0; $j<$noOfHikes; $j++) {
				echo '<label style="text-align:left;" class="grid_4"><input type="checkbox" name="tblList[]"' .
					'value="' . $indices[$j] . '" />' . $choices[$j] . '</label>';
			}
		?>
	</div>
	<div>
	<input type="submit" value="Save Index Page" />
	</div>
	</form>
		
</div>  <!-- END OF CONTAINER 16 -->

</body>
</html>
