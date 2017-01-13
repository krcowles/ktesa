<?php session_start(); ?>
<html>
<head>
	<title>Validate &amp; Select Images</title>
	<link href="validateHike.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div class="container_16 clearfix">

<div id="logoBlock">
	<p id="pgLogo"></p>
	<p id="logoLeft">Hike New Mexico</p>
	<p id="logoRight">w/ Tom &amp; Ken</p>
	<p id="page_title" class="grid_16">Add A New Hike!</p>
</div> <!-- end of logoBlock -->

<h2>STEP 2: VALIDATE DATA AND SELECT IMAGES</h2>
<form action="displayHikePg.php" method="POST">
<?php
// This is where the variables are assigned - eventually to be replaced with database
$hikeFile = $_FILES['xlfile']['tmp_name'];
$hfSize = filesize($hikeFile);
$hikeFileName = $_FILES['xlfile']['name'];
if ($hikeFileName == "") {
	// hike form entry
	$hikeName = trim($_REQUEST['hpgTitle']);
	$hikeLocale = trim($_REQUEST['locale']);
	$hikeType = trim($_REQUEST['htype']);
	$hikeLgth = trim($_REQUEST['dist']);
	$hikeElev = trim($_REQUEST['elev']);
	$hikeDiff = trim($_REQUEST['diff']);
	$hikeFac = trim($_REQUEST['fac']);
	$hikeWow = trim($_REQUEST['wow_factor']);
	$hikeSeasons = trim($_REQUEST['seas']);
	$hikeExp = trim($_REQUEST['expos']);
	$hikeGmap = $_FILES['gpsvMap']['name'];
	$hikeEChart = $_FILES['chart']['name'];
	$hikeGpx = $_FILES['gpxname']['name'];
	$hikeJSON = $_FILES['track']['name'];
	$hikeLat = trim($_REQUEST['lat']);
	$hikeLong = trim($_REQUEST['lon']);
	$hikeOthrImage1 = $_FILES['othr1']['name'];
	$hikeOthrImage2 = $_FILES['othr2']['name'];
	$hikeMarker = trim($_REQUEST['mstyle']);
	$hikePurl1 = trim($_REQUEST['photo1']);
	$hikePurl2 = trim($_REQUEST['photo2']);
	$hikeDir = trim($_REQUEST['dirs']);
	$hikePage = "pages/" . trim($_REQUEST['hikepg']);
	// Process the uploaded tsv file:
	$tsvFile = $_FILES['csvfile']['tmp_name'];
	$tsvSize = filesize($tsvFile);
	$tsvType = $_FILES['csvfile']['type'];
	$fname = $_FILES['csvfile']['name'];
	if($fname == "") {
		die( "No tsv file specified..." );
	}
	$name2pass = '../gpsv/' . $fname;
	$rawfile = fopen($tsvFile, "r");
	$fdat = fread($rawfile,$tsvSize);
} else {
	// hike datafile entry
	$datfile = fopen($hikeFile,"r");
	$hdat = fread($datfile,$hfSize);
	$harray = str_getcsv($hdat,"\n");  // array of rows
	$lineCnt = count($harray) - 1;  // row number of latest entry
	// always read in the last row for the latest hike info:
	$hikeDataArray = str_getcsv($harray[$lineCnt],",");
	// VARIABLE ASSIGNMENTS USING NewHikeData.CSV FILE:
	$hikeName = trim($hikeDataArray[0]);
	$hikeLocale = trim($hikeDataArray[1]);
	$hikeType = trim($hikeDataArray[2]);
	$hikeLgth = trim($hikeDataArray[3]);
	$hikeElev = trim($hikeDataArray[4]);
	$hikeDiff = trim($hikeDataArray[5]);
	$hikeFac = trim($hikeDataArray[6]);
	$hikeWow = trim($hikeDataArray[7]);
	$hikeSeasons = trim($hikeDataArray[8]);
	$hikeExp = trim($hikeDataArray[9]);
	$tsvFile = trim($hikeDataArray[10]);
	$name2pass = '../gpsv/' . $tsvFile;
	$hikeGmap = trim($hikeDataArray[11]);
	$hikeEChart = trim($hikeDataArray[12]);
	$hikeGpx = trim($hikeDataArray[13]);
	$hikeJSON = trim($hikeDataArray[14]);
	$hikeLat = trim($hikeDataArray[15]);
	$hikeLong = trim($hikeDataArray[16]);
	$hikeOthrImage1 = trim($hikeDataArray[17]);
	$hikeOthrImage2 = trim($hikeDataArray[18]);
	$hikeMarker = trim($hikeDataArray[19]);
	$hikePurl1 = trim($hikeDataArray[20]);
	$hikePurl2 = trim($hikeDataArray[21]);
	$hikeDir = trim($hikeDataArray[22]);
	$hikePage = "pages/" . trim($hikeDataArray[23]);
	// Get the specified tsv for processing...
	$tsvSize = filesize($name2pass);
	$rawfile = fopen($name2pass,"r") or 
		die ("Could not open TSV FILE in gpsv directory");
	$fdat = fread($rawfile,$tsvSize);
}
// CHECK ON PATHS LATER
$chartLoc = "../images/" . $hikeEChart;
$EChartSize = getimagesize($chartLoc);
$elevWidth = $EChartSize[0];
$elevHeight = $EChartSize[1];
$farray = str_getcsv($fdat,"\n");
$icount = count($farray) - 1;  // no. of rows in csv file
/*
	MARKER-DEPENDENT PAGE ELEMENTS
*/
$database = '../data/TblDB.csv';
# Index page ref -> ctrhike
if ($hikeMarker === 'ctrhike') {
	$dbFile = fopen($database, "r");
	$VClist = array();
	if ($dbFile !== false) {
		$srchCnt = 0;
		while ( ($hike = fgets($dbFile)) !== false ) {
			$srchArray = str_getcsv($hike,",");
			if ( preg_match("/Visitor/i", $srchArray[3]) ==1 ) {
				$VCList[$srchCnt] = $srchArray[0] . ": " . $srchArray[1];
				$srchCnt++;
			}
		}
	} else {
		echo "Could not open database file ../data/TblDB.csv";
	}
	echo '<div id="findvc"><p>This hike was identified as starting at, or in close proximity to,' .
	' a Visitor Center.<br /><em id="vcnote">NOTE: if a page for this Visitor Center does not yet exist, please ' .
	'go back and create it before continuing with this hike.</em></p>' .
	'<p><label style="color:DarkBlue;">Select the Visitor Center Page for this hike: </label><select name="vcList">';
	for ($k=0; $k<$srchCnt; $k++) {
		$namePos = strpos($VCList[$k],":") + 2;
		$namelgth = strlen($VCList[$k]) - $namePos;
		$vcName = substr($VCList[$k],$namePos,$namelgth);
		$vcIndxLgth = $namePos -2;
		$vcIndx = substr($VCList[$k],0,$vcIndxLgth);
		echo '<option value="' . $vcIndx . '">' . $vcName . '</option>';
		# the hike id for the affected visitor center will be passed and processed
	}
	echo "</select></p></div>";
# cluster hike:
} elseif ($hikeMarker === 'cluster') {
	$dbFile = fopen($database,"r");
	$clusterList = array();
	if ($dbFile !== false) {
		$srchCnt = 0;
		while ( ($hike = fgets($dbFile)) !== false ) {
			$srchArray = str_getcsv($hike,",");
			if ( preg_match("/cluster/i",$srchArray[3]) == 1) {
				if ($srchArray[28] !== '') {
					$clusterList[$srchCnt] = $srchArray[5] . "$" . $srchArray[28];
					$srchCnt++;
				}
			}
		}
		# Now eliminate duplicates...
		$result = array_unique($clusterList);
	} else {
		echo "Could not open database file ..data/TblDB.csv";
	}
	$passGroup = implode(";",$result);
	$_SESSION['allTips'] = $passGroup;
	echo '<div id="clus_sel"><p>This hike was identified as belonging to a group of hikes ' .
	'in close proximity with other hikes.<br /><label style="color:DarkBlue;">' .
	'Select the Group to which this hike belongs: </label><select name="tipLtr">';
	foreach ($result as $group) {
		$groupNamePos = strpos($group,"$") + 1;
		$groupNameLgth = strlen($group) - $groupNamePos;
		$groupName = substr($group,$groupNamePos,$groupNameLgth);
		$groupName = trim($groupName);
		$clusLtrLgth = $groupNamePos - 1;
		$clusLtr = substr($group,0,$clusLtrLgth);
		#debug:
		array_push($vals,$clusLtr);
		echo '<option value="' . $clusLtr . '">' . $groupName . '</option>';
	}
	echo "</select></p></div>";
} 
/*
	END OF MARKER=DEPENDENT PAGE CONSTRUCTION
*/
?>
<h2>The Data As It Will Appear In The Index Table (w/Map)</h2>
<div id="tbl1">
	<table id="indxtbl">
		<colgroup>	
			<col style="width:120px">
			<col style="width:140px">
			<col style="width: 95px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:85px">
			<col style="width:100px">
			<col style="width:70px">
			<col style="width:70px">
			<col style="width:74px">
		</colgroup>
		<thead>
		<tr>
			<th class="hdr_row">Locale</th>
			<th class="hdr_row">Hike/Trail Name</th>
			<th class="hdr_row">WOW Factor</th>
			<th class="hdr_row">Web Pg</th>
			<th class="hdr_row">Length</th>
			<th class="hdr_row">Elev Chg</th>
			<th class="hdr_row">Difficulty</th>
			<th class="hdr_row">Exposure</th>
			<th class="hdr_row">By Car</th>
			<th class="hdr_row">Photos</th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $hikeLocale;?></td>
				<td><?php echo $hikeName;?></td>
				<td><?php echo $hikeWow;?></td>
				<td><a href="<?php echo $hikePage;?>" target="_blank">
					<img class="webShift" src="../images/<?php  
					if($hikeMarker === 'center') {
						$pgLnk = 'indxCheck.png';
					} else {
						$pgLnk = 'greencheck.jpg';
					}
					echo $pgLnk;?>" alt="hikepg link" /></td>
				<td><?php echo $hikeLgth;?> miles</td>
				<td><?php echo $hikeElev;?> ft</td>
				<td><?php echo $hikeDiff;?> </td>
				<td><img class="expShift" src="../images/<?php 
					if($hikeExp === 'sun')
						$eimg = 'sun.jpg';
					else if($hikeExp === 'shade')
						$eimg = 'greenshade.jpg';
					else
						$eimg = 'shady.png';
					echo $eimg;?>" alt="exposure icon" /></td>
				<td><a href="<?php echo $hikeDir?>" target="_blank">
					<img style="position:relative;left:17px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
				<td><a href="<?php echo $hikePurl1?>" target="_blank">
					<img class="flckrShift" src="../images/album_lnk.png" alt="Flickr symbol" /></a></td>
			</tr>	
		</tbody>
	</table>
</div>

<h2>The Data As It Will Appear On The Hike Page</h2>			
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
				<td><?php echo $hikeDiff;?></td>
				<td><?php echo $hikeLgth;?> miles</td>
				<td><?php
					if($hikeType === 'loop')
						echo 'Loop';
					else if ($hikeType === 'outandback')
						echo 'Out-and-Back';
					else
						echo 'Two-car';?></td>
				<td><?php echo $hikeElev;?> ft</td>
				<td><?php
					if($hikeExp === 'sun')
						echo 'Full sun';
					else if ($hikeExp === 'shade')
						echo 'Good shade';
					else
						echo "Mixed sun/shade";?></td>
				<td><?php echo $hikeWow;?></td>
				<td><?php echo $hikeFac;?></td>
				<td><?php echo $hikeSeasons;?></td>
				<td><a href="<?php $hikePurl1;?>" target="_blank">
					<img style="margin-bottom:0px;border-style:none;" src="../images/album_lnk.png" alt="photo album link icon" /></a></td>
				<td><a href="<?php echo $hikeDir;?>" target="_blank">
				<img style="margin-bottom:0px;padding-bottom:0px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
			</tr>
		</tbody>
	</table>
</div>
<h2>Data for Google Maps API</h2>
<ul>
	<li>Marker Latitude: <?php echo $hikeLat;?></li>
	<li>Marker Longitude: <?php echo $hikeLong;?></li>
	<li>Marker Style: <?php
		if ($hikeMarker === "center")
			echo "Visitor Center";
		else if ($hikeMarker === "ctrhike")
			echo "Visitor Center Hike Start";
		else if ($hikeMarker === "cluster")
			echo "Overlapping Trailhead";
		else
			echo "'Normal' Hike"; ?></li>
	<li>Track File: <?php echo $hikeJSON;?></li>
</ul>
<h2>Other data submitted:</h2>
<ul>
	<li>Title to appear on Hike Page: <?php echo $hikeName;?></li>
	<li>GPSVisualizer map: <?php echo $hikeGmap;?></li>
	<li>Elevation chart: <?php
		echo "{$hikeEChart}: {$elevWidth}px x {$elevHeight}px";?></li>
	<li>GPX File: <?php echo $hikeGpx;?></li>
	<li>Added Image 1: <?php echo $hikeOthrImage1;?></li>
	<li>Added Image 2: <?php echo $hikeOthrImage2;?></li>
	<li>Photo Link 1: <?php echo $hikePurl1;?></li>
	<li>Photo Link 2: <?php echo $hikePurl2;?></li>
	<li>Google Directions Link: <?php echo $hikeDir;?></li>
</ul>
<?php


?>
<h3 style="text-indent:8px">Uploaded File Info:</h3>
<ul>
	<li>Sent file: <?php if ($fname) {echo $fname;} else {echo "Not uploaded";}?></li>
	<li>File size: <?php echo $tsvSize;?> bytes</li>
	<li>File type: <?php if ($tsvType) {echo $fname;} else {echo "Not uploaded";}?></li>
</ul>
<div style="padding-left:8px">
	<h4 style="margin-bottom:4px">Include a 'Trail Tips' section for new page?</h4>
	<input id="addTT" type="radio" name="TT" value="Y" />
		<label style="color:DarkBlue;" for="addTT">Add Trail Tips</label>
	<input id="noTT" type="radio" name="TT" value="N" checked="checked" />
		<label style="color:DarkBlue;" for="noTT">No Trail Tips</label>
</div>
<div style="padding-left:8px;">
	<h4 style="margin-bottom:4px">Checkbox to Force Non-Refresh Page Loading
		<em>(Useful when using back/forward arrows in browser during build process)</em></h4>
	<input id="forceLoad" type="checkbox" name="setForce" value="force" checked="checked" />Force
</div>
<br />
<h4 style="text-indent:8px">Please check the boxes corresponding to the pictures you wish
	to include on the new page:</h4>
<p style="text-indent:8px;font-size:16px"><em style="position:relative;top:-20px">Note:
these names were extracted from the .tsv file</em><br />
<input style="margin-left:8px" id="all" type="checkbox" name="allPix" value="useAll" />Use All Photos</p>
<?php
	$handle = fopen($name2pass, "r");
	if ($handle !== false) {
		$lineno = 0;
		$picno = 0;
		while ( ($line = fgets($handle)) !== false ) {
			$tsvArray = str_getcsv($line,"\t");
			if ($lineno !== 0) {
				$picarray[$picno] = $tsvArray[$indx];
				$thumb[$picno] = $tsvArray[$indx+4];
				$picno += 1;
			} else {
				if (strcmp($tsvArray[0],"folder") == 0) {
					$indx = 1;
					//echo "<p>This tsv file has 'folder' field description</p>";
				} else {
					$indx = 0;
					//echo "<p>Older tsv file - no 'folder' field</p>";
				}
			}
			$lineno += 1;
		}
		$lineno -= 1;
	} else {
		echo "<p>Could not open {$fname}</p>";
	} 
	$nmeno = 0;
	for ($i=0; $i<$icount; $i++) {
		echo '<div class="selPic" style="width:150px;float:left;margin-left:2px;margin-right:2px;">';
		echo '<input type="checkbox" name="pix[]" value="' .  $picarray[$nmeno] .
			'" />' . substr($picarray[$nmeno],0,10) . '...<br />';
		echo '<img height="150px" width="150px" src="' .$thumb[$nmeno] . '" alt="pic choice" />';
		echo '</div>';
		$nmeno +=1;
	}
	echo '<br />';
	echo '<div style="width:200;position:relative;top:90px;left:20px;float:left;"><input type="submit" value="Use Selected Pics" /></div>';
?>	
<input type="hidden" name="tsv" value="<?php echo $name2pass;?>" />
<input type="hidden" name="hTitle" value="<?php echo $hikeName;?>" />
<input type="hidden" name="area"  value="<?php echo $hikeLocale;?>" />
<input type="hidden" name="htype" value="<?php echo $hikeType;?>" />
<input type="hidden" name="lgth"  value="<?php echo $hikeLgth;?>" />
<input type="hidden" name="elev"  value="<?php echo $hikeElev;?>" />
<input type="hidden" name="diffi" value="<?php echo $hikeDiff;?>" />
<input type="hidden" name="lati"  value="<?php echo $hikeLat;?>" />
<input type="hidden" name="long"  value="<?php echo $hikeLong;?>" /> 
<input type="hidden" name="facil" value="<?php echo $hikeFac;?>" />
<input type="hidden" name="webpg" value="<?php echo $hikePage;?>" />
<input type="hidden" name="wow"   value="<?php echo $hikeWow;?>" />
<input type="hidden" name="seasn" value="<?php echo $hikeSeasons;?>" />
<input type="hidden" name="expo"  value="<?php echo $hikeExp;?>" />
<input type="hidden" name="geomp" value="<?php echo $hikeGmap;?>" />
<input type="hidden" name="chart" value="<?php echo $hikeEChart;?>" />
<input type="hidden" name="json"  value="<?php echo $hikeJSON;?>" />
<input type="hidden" name="img1"  value="<?php echo $hikeOthrImage1;?>" />
<input type="hidden" name="img2"  value="<?php echo $hikeOthrImage2;?>" />
<input type="hidden" name="mrkr"  value="<?php echo $hikeMarker;?>" />
<input type="hidden" name="phot1" value="<?php echo $hikePurl1;?>" />
<input type="hidden" name="phot2" value="<?php echo $hikePurl2;?>" />
<input type="hidden" name="gdirs" value="<?php echo $hikeDir;?>" />
<input type="hidden" name="rbld" value="NO" />  <!-- Display needs to know whether or not
	this is a rebuild -->
</form>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="validateHike.js"></script>

</body>

</html>

				
				
				
				
				
				
				
				