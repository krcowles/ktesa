<html>
<head>
	<title>Upload</title>
	<link href="stepW.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="logoBlock">
	<p id="pgLogo"></p>
	<p id="logoLeft">Hike New Mexico</p>
	<p id="logoRight">w/ Tom &amp; Ken</p>
	<p id="page_title">Add A New Hike!</p>
</div> <!-- end of logoBlock -->

<h2>STEP 2: VALIDATE DATA AND SELECT IMAGES</h2>
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
				<td><?php echo trim($_REQUEST['locale']);?></td>
				<td><?php echo trim($_REQUEST['hike_name']);?></td>
				<td><?php echo trim($_REQUEST['wow_factor']);?></td>
				<td><a href="pages/<?php echo trim($_REQUEST['hikepg']);?>" target="_blank">
					<img class="webShift" src="../images/<?php  
					if($_REQUEST['mstyle'] === 'vc' || $_REQUEST['mstyle'] === 'vch')
						$mimg = 'indxCheck.png';
					else
						$mimg = 'greencheck.jpg';
					echo $mimg;?>" alt="hikepg link" /></td>
				<td><?php echo trim($_REQUEST['dist']);?> miles</td>
				<td><?php echo trim($_REQUEST['elev']);?> ft</td>
				<td><?php echo trim($_REQUEST['diff']);?> </td>
				<td><img class="expShift" src="../images/<?php 
					if($_REQUEST['expos'] === 'sun')
						$eimg = 'sun.jpg';
					else if($_REQUEST['expos'] === 'shade')
						$eimg = 'greenshade.jpg';
					else
						$eimg = 'shady.png';
					echo $eimg;?>" alt="exposure icon" /></td>
				<td><a href="<?php echo $_REQUEST['dirs']?>" target="_blank">
					<img style="position:relative;left:17px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
				<td><a href="<?php echo $_REQUEST['photo1']?>" target="_blank">
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
				<td><?php echo trim($_REQUEST['diff']);?></td>
				<td><?php echo trim($_REQUEST['dist']);?> miles</td>
				<td><?php
					$hikeType = $_REQUEST['htype'];
					if($hikeType === 'loop')
						echo 'Loop';
					else if ($hikeType === 'oab')
						echo 'Out-and-Back';
					else
						echo 'Two-car';?></td>
				<td><?php echo trim($_REQUEST['elev']);?> ft</td>
				<td><?php
					$exposure = $_REQUEST['expos'];
					if($exposure === 'sun')
						echo 'Full sun';
					else if ($exposure === 'shade')
						echo 'Good shade';
					else
						echo "Mixed sun/shade";?></td>
				<td><?php echo trim($_REQUEST['wow_factor']);?></td>
				<td><?php echo trim($_REQUEST['fac']);?></td>
				<td><?php echo trim($_REQUEST['seas']);?></td>
				<td><a href="<?php echo trim($_REQUEST['photo1']);?>" target="_blank">
					<img style="margin-bottom:0px;border-style:none;" src="../images/album_lnk.png" alt="photo album link icon" /></a></td>
				<td><a href="<?php echo trim($_REQUEST['dirs']);?>" target="_blank">
				<img style="margin-bottom:0px;padding-bottom:0px;" src="../images/dirs.png" alt="google driving directions" /></a></td>
			</tr>
		</tbody>
	</table>
</div>
<h2>Data for Google Maps API</h2>
<ul>
	<li>Marker Latitude: <?php echo trim($_REQUEST['lat']);?></li>
	<li>Marker Longitude: <?php echo trim($_REQUEST['lon']);?></li>
	<li>Marker Style: <?php
		if ($_REQUEST['mstyle'] === "center")
			echo "Visitor Center";
		else if ($_REQUEST['mstyle'] === "ctrhike")
			echo "Visitor Center Hike Start";
		else if ($_REQUEST['mstyle'] === "cluster")
			echo "Overlapping Trailhead";
		else
			echo "'Normal' Hike"; ?></li>
	<li>Track File: <?php echo trim($_REQUEST['track']);?></li>
</ul>
<h2>Other data submitted:</h2>
<ul>
	<li>Title to appear on Hike Page: <?php echo trim($_REQUEST['hpgTitle']);?></li>
	<li>GPSVisualizer map: <?php echo trim($_REQUEST['gpsvMap']);?></li>
	<li>Elevation chart: <?php
		echo "{$_REQUEST['chart']}: {$_REQUEST['elevWd']}px x {$_REQUEST['elevHt']}px";?></li>
	<li>GPX File: <?php echo trim($_REQUEST['gpxname']);?></li>
	<li>Added Image 1: <?php echo trim($_REQUEST['othr1']);?></li>
	<li>Added Image 2: <?php echo trim($_REQUEST['othr2']);?></li>
	<li>Photo Link 1: <?php echo trim($_REQUEST['photo1']);?></li>
	<li>Photo Link 2: <?php echo trim($_REQUEST['photo2']);?></li>
	<li>Google Directions Link: <?php echo trim($_REQUEST['dirs']);?></li>
</ul>
<?php
require "../scripts/mysql_config.php";

$tsvFile = $_FILES['csvfile']['tmp_name'];
$tsvSize = filesize($tsvFile);
$tsvType = $_FILES['csvfile']['type'];
$fname = $_FILES['csvfile']['name'];

$kfile = KENS_KTESA . "index.html";
if( file_exists($kfile) )
	$buildLoc = KENS_KTESA . "build/tsvfile";
else
	$buildLoc = TOMS_KTESA . "build/tsvfile";
if($fname !== "") {
	copy($tsvFile, $buildLoc) or
		die( "Could not copy file to ktesa directory!" );
} else {
	die( "No file specified..." );
}
$rawfile = fopen($tsvFile, "r");
$fsize = filesize($tsvFile);
$fdat = fread($rawfile,$fsize);
$farray = str_getcsv($fdat,"\n");
$icount = count($farray) - 1;
?>
<h3 style="text-indent:8px">Uploaded File Info:</h3>
<ul>
	<li>Sent file: <?php echo $fname;?></li>
	<li>File size: <?php echo $tsvSize;?> bytes</li>
	<li>File type: <?php echo $tsvType;?></li>
</ul>

<h4 style="text-indent:8px">Please check the boxes corresponding to the pictures you wish to include on the new page:</h4>
<p style="text-indent:8px;font-size:16px"><em style="position:relative;top:-20px">Note: these names were extracted from the .tsv file</em></p>
<!-- The following will be replaced by database entries eventually -->
<form action="step3.php" method="POST">
	<input type="hidden" name="whose" value="<?php echo $buildLoc;?>" />
	<input type="hidden" name="hname" value="<?php echo $_REQUEST['hike_name'];?>" />
	<input type="hidden" name="hTitle" value="<?php echo $_REQUEST['hpgTitle'];?>" />
	<input type="hidden" name="area"  value="<?php echo $locale;?>" />
	<input type="hidden" name="htype" value="<?php echo $hikeType;?>" />
	<input type="hidden" name="lgth"  value="<?php echo $_REQUEST['dist'];?>" />
	<input type="hidden" name="elev"  value="<?php echo $_REQUEST['elev'];?>" />
	<input type="hidden" name="diffi" value="<?php echo $_REQUEST['diff'];?>" />
	<input type="hidden" name="lati"  value="<?php echo $_REQUEST['lat'];?>" />
	<input type="hidden" name="long"  value="<?php echo $_REQUEST['lon'];?>" /> 
	<input type="hidden" name="facil" value="<?php echo $_REQUEST['fac'];?>" />
	<input type="hidden" name="webpg" value="<?php echo $_REQUEST['hikepg'];?>" />
	<input type="hidden" name="wow"   value="<?php echo $_REQUEST['wow_factor'];?>" />
	<input type="hidden" name="seasn" value="<?php echo $_REQUEST['seas'];?>" />
	<input type="hidden" name="expo"  value="<?php echo $_REQUEST['expos'];?>" />
	<input type="hidden" name="geomp" value="<?php echo $_REQUEST['gpsvMap'];?>" />
	<input type="hidden" name="chart" value="<?php echo $_REQUEST['chart'];?>" />
	<input type="hidden" name="chrtW" value="<?php echo $_REQUEST['elevWd'];?>" />
	<input type="hidden" name="chrtH" value="<?php echo $_REQUEST['elevHt'];?>" />
	<input type="hidden" name="gpx"   value="<?php echo $_REQUEST['gpxname'];?>" />
	<input type="hidden" name="json"  value="<?php echo $_REQUEST['track'];?>" />
	<input type="hidden" name="img1"  value="<?php echo $_REQUEST['othr1'];?>" />
	<input type="hidden" name="img2"  value="<?php echo $_REQUEST['othr2'];?>" />
	<input type="hidden" name="mrkr"  value="<?php echo $_REQUEST['mstyle'];?>" />
	<input type="hidden" name="phot1" value="<?php echo $_REQUEST['photo1'];?>" />
	<input type="hidden" name="phot2" value="<?php echo $_REQUEST['photo2'];?>" />
	<input type="hidden" name="gdirs" value="<?php echo $_REQUEST['dirs'];?>" />
	<?php
		$handle = fopen($tsvFile, "r");
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
			echo '<div style="width:150px;float:left;">';
			echo '<input type="checkbox" name="pix[]" value="' .  $picarray[$nmeno] .
				'" />' . $picarray[$nmeno] . '<br />';
			echo '<img height="150px" width="150px" src="' .$thumb[$nmeno] . '" alt="pic choice" />';
			echo '</div>';
			$nmeno +=1;
		}
		echo '<br />';
		echo '<div style="width:200;position:relative;top:90px;left:20px;float:left;"><input type="submit" value="Use These Pics" /></div>';
		?>	
</form>
</body>

</html>

				
				
				
				
				
				
				
				