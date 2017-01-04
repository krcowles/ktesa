<!DOCTYPE html>

<?php 
$hikeFile = $_FILES['xlfile']['tmp_name'];
$hfSize = filesize($hikeFile);
$hikeFileName = $_FILES['xlfile']['name'];
if ($hikeFileName == "") {
	// hike form entry
	$indexPage = trim($_REQUEST['hpgTitle']);
	$hikeLocale = trim($_REQUEST['locale']);
	$hikeType = trim($_REQUEST['htype']);
	$hikeLat = trim($_REQUEST['lat']);
	$hikeLong = trim($_REQUEST['lon']);
	$indexImage = $_FILES['othr1']['name'];
	$parkDir = trim($_REQUEST['dirs']);
} else {
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
}
$hikeMarker = "VC";
$indexImage = '../images/' . $indexImage;
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
	
	<img class="mainPic" src="<?php echo $indexImage;?>" alt="Park Map" />
	<p id="dirs"><a href="<?php echo $parkDir?>" target="_blank">Directions to the Visitor Center</a></p>
	
	<form action="indexPgCreator.php" method="POST">
	<p id="indxContent"><textarea cols="112" rows="8">Enter Park Description Here...</textarea></p>
	<ul>
		<li>Book: <input name="bk1" type="text" size="40" placeholder="Title of book reference">
				<input name="auth1" type="text" size="40" placeholder="Name of author(s)">
		<li>Book: <input name="bk2" type="text" size="40" placeholder="Title of book reference">
				<input name="auth2" type="text" size="40"placeholder="Name of author(s)">
		<li>Book: <input name="bk3" type="text" size="40"placeholder="Title of book reference">
				<input name="auth3" type="text" size="40" placeholder="Name of author(s)">
		<li>Website: <input name="web1" type="text" size="100" placeholder="Paste link to app here">
				<input name="webtxt1" type="text" size="20" placeholder="Text to click on">
		<li>Website: <input name="web2" type="text" size="100" placeholder="Paste link to app here">
				<input name="webtxt2" type="text" size="20" placeholder="Text to click on">
		<li>Website: <input name="web3" type="text" size="100" placeholder="Paste link to app here">
				<input name="webtxt3" type="text" size="20" placeholder="Text to click on">
	</ul>
	
	<div id="hdrContainer">
		<p id="tblHdr">Park Hiking & Walking Opportunities:</p>
		<p>Enter the following information about all park hikes available: This will be
		tabulated for review on the next page</p>
	</div>

	<label>Enter Hike Name</label><input type="text" name="hk1" size="40" /><br />
	<label>Enter Hike Index Number</label><input type="text" name="lnk1" size="10" /><br />
	<label>Enter Trail Length (miles)</label><input type="text" name="lgth1" size="40" /><br />
	<label>Enter Elevation Change (feet)</label><input type="text" name="elev1" size="40" /><br />
	Enter Exposure Type: 
	<input id="sunny" type="radio" name="exp1" value="sun" /><label for="sunny">Full Sun</label>
		<input id="shady" type="radio" name="exp1" value="shade" /><label for="shady">Good Shade</label>
		<input id="partly" type="radio" name="exp1" value="mixed" /><label for="partly">Mixed Sun &amp; Shade</label><br />
	<label>Enter URL for photo album<input type="text" name="purl1" size="80" />
	<br /><br />
	<label>Enter Hike Name</label><input type="text" name="hk2" size="40" /><br />
	<label>Enter Hike Index Number</label><input type="text" name="lnk2" size="10" /><br />
	<label>Enter Trail Length (miles)</label><input type="text" name="lgth2" size="40" /><br />
	<label>Enter Elevation Change (feet)</label><input type="text" name="elev2" size="40" /><br />
	Enter Exposure Type: 
	<input id="sunny" type="radio" name="exp2" value="sun" /><label for="sunny">Full Sun</label>
		<input id="shady" type="radio" name="exp2" value="shade" /><label for="shady">Good Shade</label>
		<input id="partly" type="radio" name="exp2" value="mixed" /><label for="partly">Mixed Sun &amp; Shade</label><br />
	<label>Enter URL for photo album<input type="text" name="purl2" size="80" />
	
	<br /><br />
	<label>Enter Hike Name</label><input type="text" name="hk3" size="40" /><br />
	<label>Enter Hike Index Number</label><input type="text" name="lnk3" size="10" /><br />
	<label>Enter Trail Length (miles)</label><input type="text" name="lgth3" size="40" /><br />
	<label>Enter Elevation Change (feet)</label><input type="text" name="elev3" size="40" /><br />
	Enter Exposure Type: 
	<input id="sunny" type="radio" name="exp3" value="sun" /><label for="sunny">Full Sun</label>
		<input id="shady" type="radio" name="exp3" value="shade" /><label for="shady">Good Shade</label>
		<input id="partly" type="radio" name="exp3" value="mixed" /><label for="partly">Mixed Sun &amp; Shade</label><br />
	<label>Enter URL for photo album<input type="text" name="purl3" size="80" />
	
	<br /><br />
	<label>Enter Hike Name</label><input type="text" name="hk4" size="40" /><br />
	<label>Enter Hike Index Number</label><input type="text" name="lnk4" size="10" /><br />
	<label>Enter Trail Length (miles)</label><input type="text" name="lgth4" size="40" /><br />
	<label>Enter Elevation Change (feet)</label><input type="text" name="elev4" size="40" /><br />
	Enter Exposure Type: 
	<input id="sunny" type="radio" name="exp4" value="sun" /><label for="sunny">Full Sun</label>
		<input id="shady" type="radio" name="exp4" value="shade" /><label for="shady">Good Shade</label>
		<input id="partly" type="radio" name="exp4" value="mixed" /><label for="partly">Mixed Sun &amp; Shade</label><br />
	<label>Enter URL for photo album<input type="text" name="purl4" size="80" />
	</form>
		
</div>  <!-- END OF CONTAINER 16 -->

</body>
</html>
