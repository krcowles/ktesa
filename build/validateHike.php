<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en-us">
<head>
	<title>Validate &amp; Select Images</title>
	<link href="validateHike.css" type="text/css" rel="stylesheet" />
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>
<body>

 <div id="logo">
	<img id="hikers" src="../images/hikers.png" alt="hikers icon" />
	<p id="logo_left">Hike New Mexico</p>
	
	<img id="tmap" src="../images/trail.png" alt="trail map icon" />
	<p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Validate This Hike!</p>

<h2>VALIDATE DATA AND SELECT IMAGES</h2>

<form target="_blank" action="displayHikePg.php" method="POST">

<?php
/*  Default values for identifying previously saved files 
 *  and whether or not to overwite them when saving the page
 */
$dupTsv = 'NO';
$owTsv = 'NO';
$dupMap = 'NO';
$owMap = 'NO';
$dupGpx = 'NO';
$owGpx = 'NO';
$dupJSON = 'NO';
$owJSON = 'NO';
/* Message text for upload data section */
$fexists1 = '<p style="margin-left:8px;margin-top:-12px;"><em>NOTE: ';
$fexists2 = ' has been previously saved on the server; ' .
            'Check here to overwrite: ';
$fexists3 = '</em></p>' . "\n";
/* Uploaded file data looks for presence / absence of files and responds
 * accordingly. The data type for each file is also checked for correctness.
 * If a filename is found corresponding to an existing host file, the user
 * is alerted and provided the opportunity to overwrite the host file later.
 * All uploaded files are saved in the 'tmp' directory according to type.
 */

# TSV FILE OPS:
echo '<h3 style="text-indent:8px">Uploaded TSV File Info:</h3>' . "\n";
$tsvFile = $_FILES['csvfile']['tmp_name'];
$tsvSize = filesize($tsvFile);
$tsvType = $_FILES['csvfile']['type'];
$tsvFname = $_FILES['csvfile']['name'];
# NOTE: Cannot proceed without the tsv file!
$nofile = '</form>' . "\n" .
    '<p><strong>--- No tsv file specified...</strong></p>' . "\n" .
        '</body>' . "\n" .
        '</html>';
if($tsvFname == "") { die( $nofile ); }
if ( preg_match("/tab-separated-values/",$tsvType) === 0 ) {
    $msgout = '<p style="margin-left:20px;color:red"><strong>Incorrect file type for ' .
            $tsvFname . ': must be "tab-separated-variables"</strong></p>';
    die ($msgout);
}
$tmpTsv = '../tmp/gpsv/' . $tsvFname;
$tsvLoc = '../gpsv/' . $tsvFname;
if ( file_exists($tsvLoc) ) {
    echo $fexists1 . $tsvFname . $fexists2. 
        '<input id="tsvnew" type="checkbox" name="tsvow" />' . $fexists3;
    $dupTsv = 'YES';
}
$saveTsv = fopen($tmpTsv,"w");
fwrite($saveTsv,$tsvFile);
fclose($saveTsv);
echo '<ul style="margin-top:-10px;">' . "\n";
echo '<li>Uploaded tsv file: ' .  $tsvFname . '</li>' . "\n";
echo '<li>File size: ' . $tsvSize . ' bytes</li>' . "\n";
echo '<li>File type: ' . $tsvType . '</li>' . "\n";
echo '</ul>' . "\n";

# GEOMAP FILE OPS:
echo '<h3 style="text-indent:8px">Uploaded Geomap File Info:</h3>' . "\n";
$gmapFile = $_FILES['gpsvMap']['tmp_name'];
$hikeMap = $_FILES['gpsvMap']['name'];
$mapSize = filesize($gmapFile);
$mapType = $_FILES['gpsvMap']['type'];
$tmpMap = '../tmp/maps/' . $hikeMap;
$mapLoc = '../maps/' . $hikeMap;
if ( $hikeMap !== '' && file_exists($mapLoc) ) {
    echo $fexists1 . $hikeMap . $fexists2. 
        '<input id="mapnew" type="checkbox" name="mapow" />' . $fexists3;
    $dupMap = 'YES';
}
if ( $hikeMap !== '') {
    if ( preg_match("/html/",$mapType) === 0 ) { 
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect '
                . 'file type for ' . $hikeMap . ': must be html</strong></p>';
        die($msgout);
    }
    $saveMap = fopen($tmpMap,"w");
    fwrite($saveMap,$gmapFile);
    fclose($saveMap);
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($hikeMap !== '') {
    echo '<li>Uploaded map file: ' .  $hikeMap . '</li>' . "\n";
    echo '<li>File size: ' . $mapSize . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $mapType . '</li>' . "\n";
} else {
    echo '<li>NO GEOMAP UPLOADED: If needed, go back and select in hike Editor</li>' . "\n";
}
echo '</ul>' . "\n";

# GPX FILE OPS
echo '<h3 style="text-indent:8px">Uploaded GPX File Info:</h3>' . "\n";
$gpxFile = $_FILES['gpxname']['tmp_name'];
$hikeGpx = $_FILES['gpxname']['name'];
$gpxSize = filesize($gpxFile);
$gpxType = $_FILES['gpxname']['type'];
$tmpGpx = '../tmp/gpx/' . $hikeGpx;
$gpxLoc = '../gpx/' . $hikeGpx;
if ( $hikeGpx !== '' && file_exists($gpxLoc) ) {
    echo $fexists1 . $hikeGpx . $fexists2 . 
        '<input id="gpxnew" type="checkbox" name="gpxow" />' . $fexists3;
    $dupGpx = 'YES';
} 
if ( $hikeGpx !== '') {
    if ( preg_match("/octet-stream/",$gpxType) === 0 ) {
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
                . ' file type for ' . $hikeGpx . ': should be "octet-stream"';
        die($msgout);
    }
    $saveGpx = fopen($tmpGpx,"w");
    fwrite($saveGpx,$gpxFile);
    fclose($saveGpx);
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($hikeGpx !== '') {
    echo '<li>Uploaded gpx file: ' .  $hikeGpx . '</li>' . "\n";
    echo '<li>File size: ' . $gpxSize . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $gpxType . '</li>' . "\n";
} else {
    echo '<li>NO GPX FILE UPLOADED: If needed, go back and select in hike ' .
        'Editor</li>' . "\n";
}
echo '</ul>' . "\n";

/* JSON FILE OPS: */
echo '<h3 style="text-indent:8px">Uploaded Track File Info:</h3>' . "\n";
if ( isset($_POST['maketrack']) ) {
    $cwd = getcwd();
    $ktesaPos = strpos($cwd,"ktesa") + 6;
    $ktesaDir = substr($cwd,0,$ktesaPos);
    $trkcmd = $ktesaDir . 'tools/mktrk.sh -f ' . $ktesaDir . 
            'gpx/' . $hikeGpx . ' -p ' . $ktesaDir . 'tmp/json';
    $json = exec($trkcmd);
    if ( preg_match("/DONE/",$json) === 1 ) {
        echo '<p style="margin-left:10px;">Track file created from GPX and saved</p>';
    } else {
        echo '<p style="margin-left:10px;">Track file creation failed: Please '
        . 'return to the hike Editor, un-check the box, and upload a track file</p>';
    }
    $hikeJSON = $hikeGpx . ".json";
} else {
    $jsonFile = $_FILES['track']['tmp_name'];
    $hikeJSON = $_FILES['track']['name'];
    $jsonSize = filesize($jsonFile);
    $jsonType = $_FILES['track']['type'];
    $tmpJSON = '../tmp/json/' . $hikeJSON;
    $jsonLoc = '../json/' . $hikeJSON;
    if ( $hikeJSON !== '' && file_exists($jsonLoc) ) {
        echo $fexists1 . $hikeJSON . $fexists2. 
            '<input id="jsonnew" type="checkbox" name="jsonow" />' . $fexists3;
        $dupJSON = 'YES';
    }
    if ( $hikeJSON !== '') {
        if ( preg_match("/json/",$jsonType) === 0 ) {
            $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
                . ' file type for ' . $hikeJSON . ': should be "json"</strong</p>';
            die($msgout);
        }
        $saveJSON = fopen($tmpJSON,"w");
        fwrite($saveJSON,$jsonFile);
        fclose($saveJSON);
    }
    echo '<ul style="margin-top:-10px;">' . "\n";
    if ($hikeJSON !== '') {
        echo '<li>Uploaded track file: ' .  $hikeJSON . '</li>' . "\n";
        echo '<li>File size: ' . $jsonSize . ' bytes</li>' . "\n";
        echo '<li>File type: ' . $jsonType . '</li>' . "\n";
    } else {
        echo '<li>NO JSON/TRACK FILE UPLOADED: If needed, go back and select in hike Editor</li>' . "\n";
    }
    echo '</ul>' . "\n";
}
?>
<input type="hidden" name="tsv" value="<?php echo $dupTsv;?>" />
<input type="hidden" name="owt" value="<?php echo $owTsv;?>" />
<input type="hidden" name="map" value="<?php echo $dupMap;?>" />
<input type="hidden" name="owm" value="<?php echo $owMap;?>" />
<input type="hidden" name="gpx" value="<?php echo $dupGpx;?>" />
<input type="hidden" name="owg" value="<?php echo $owGpx;?>" />
<input type="hidden" name="json" value="<?php echo $dupJSON;?>" />
<input type="hidden" name="owj" value="<?php echo $owJSON;?>" />

<?php
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



$extractGeos = $_REQUEST['thgeos'];
if ( isset($extractGeos) ) {
	if ($_FILES['gpxname']['error'] == UPLOAD_ERR_OK
      && is_uploaded_file($_FILES['gpxname']['tmp_name'])) { 
  		$gpxdat = file_get_contents($gpxFile); 
		$trksegloc = strpos($gpxdat,"<trkpt lat=");
		$trksubstr = substr($gpxdat,$trksegloc,100);
		$latloc = strpos($trksubstr,"lat=") + 5;
		$latend = strpos($trksubstr,'" lon=');
		$latlgth = $latend - $latloc;
		$hikeLat = substr($trksubstr,$latloc,$latlgth);
		$lonloc = strpos($trksubstr,"lon=") + 5;
		$lonend = strpos($trksubstr,">") - 1;
		$lonlgth = $lonend - $lonloc;
		$hikeLong = substr($trksubstr,$lonloc,$lonlgth);
	} else {
		echo "Failed to extract trailhead coordinates: Go back and re-enter manually";
	}
} else {
	$hikeLat = trim($_REQUEST['lat']);
	$hikeLong = trim($_REQUEST['lon']);
}
$hikeOthrImage1 = $_FILES['othr1']['name'];
$hikeOthrImage2 = $_FILES['othr2']['name'];
$hikeMarker = trim($_REQUEST['mstyle']);
$hikePurl1 = trim($_REQUEST['photo1']);
$hikePurl2 = trim($_REQUEST['photo2']);
$hikeDir = trim($_REQUEST['dirs']);
// Process the uploaded tsv file:

$rawtips = $_POST['tipstxt'];
if (substr($rawtips,0,10) === '[OPTIONAL]') {
	$tipTxt = '';
} else {
	$tipTxt = $rawtips;
}
$_SESSION['hikeTips'] = $tipTxt;
$rawhike = $_POST['hiketxt'];
$_SESSION['hikeDetails'] = $rawhike;
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
$hikePDatLbls = $_POST['plbl'];
$noOfPDats = count($hikePDatLbls);
for ($i=0; $i<$noOfPDats; $i++) {
	if ($hikePDatLbls[$i] == '') {
		$noOfPDats = $i;
		break;
	}
}
$hikePDatUrls = $_POST['purl'];
$hikePDatCTxts = $_POST['pctxt'];
$hikeADatLbls = $_POST['albl'];
$noOfADats = count($hikeADatLbls);
for ($j=0; $j<$noOfADats; $j++) {
	if ($hikeADatLbls[$j] == '') {
		$noOfADats = $j;
		break;
	}
}
$hikeADatUrls = $_POST['aurl'];
$hikeADatCTxts = $_POST['actxt'];
# NOTE: reading tsv file only - no writing
$fdat = file($tsvFile); // simple read - not using fgetcsv as there is no "special" data
$icount = count($fdat) - 1; // image count: do not count the header row
# Form array of pictures to display for selection by the user later on...
$lineno = 0;
$picno = 0;
foreach ($fdat as $rawTsvLine) {
	$tsvArray = str_getcsv($rawTsvLine,"\t");
	if ($lineno !== 0) {
		$picarray[$picno] = $tsvArray[$indx];
		$thumb[$picno] = $tsvArray[$indx+4];
		$picno += 1;
	} else {
		if (strcmp($tsvArray[0],"folder") == 0) {
			$indx = 1;
			# echo "<p>This tsv file has 'folder' field description</p>";
		} else {
			$indx = 0;
			# echo "<p>Older tsv file - no 'folder' field</p>";
		}
	}
	$lineno++;
}
/*
	MARKER-DEPENDENT PAGE ELEMENTS
*/
$database = '../data/database.csv';
# Index page ref -> ctrhike
if ($hikeMarker === 'ctrhike') {
	$dbFile = fopen($database, "r");
	$VClist = array();
	if ($dbFile !== false) {
		$srchCnt = 0;
		while ( ($srchArray = fgetcsv($dbFile)) !== false ) {
			if ( preg_match("/Visitor/i", $srchArray[3]) == 1 ) {
				$VCList[$srchCnt] = $srchArray[0] . ": " . $srchArray[1];
				$srchCnt++;
			}
		}
	} else {
		echo "Could not open database file ../data/database.csv";
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
	fclose($dbFile);
# cluster hike:
} elseif ($hikeMarker === 'cluster') {
	$dbFile = fopen($database,"r");
	$clusterList = array();
	if ($dbFile !== false) {
		$srchCnt = 0;
		while ( ($srchArray = fgetcsv($dbFile)) !== false ) {
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
	/* NOTE: even though the array holds empty keys where duplicates were eliminated,
	   when imploding, the empty keys are disregarded */
	$_SESSION['allTips'] = $passGroup;
	echo '<div id="clus_sel"><p>This hike was identified as belonging to a group of hikes ' .
	'in close proximity with other hikes.<br /><label style="color:DarkBlue;">' .
	'Select the Group to which this hike belongs: </label><select name="clusgrp">';
	foreach ($result as $group) {
		$groupNamePos = strpos($group,"$") + 1;
		$groupNameLgth = strlen($group) - $groupNamePos;
		$groupName = substr($group,$groupNamePos,$groupNameLgth);
		$groupName = trim($groupName);
		$clusGrpLgth = $groupNamePos - 1; # may be larger than 1 char
		$clusGrp = substr($group,0,$clusGrpLgth);
		echo '<option value="' . $clusGrp . '">' . $groupName . '</option>';
	}
	echo "</select></p></div>";
	fclose($dbFile);
} 
/*
	END OF MARKER=DEPENDENT PAGE CONSTRUCTION
*/
?>
<h2>The Data As It Will Appear In The Table of Hikes</h2>
<div id="tbl1">
    <table id="indxtbl">
        <colgroup>	
                <col style="width:120px">
                <col style="width:140px">
                <col style="width:105px">
                <col style="width:80px">
                <col style="width:80px">
                <col style="width:75px">
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
                <td><img class="webShift" src="../images/<?php  
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
                    if($hikeType === 'loop') {
                        echo 'Loop';
                    } else if ($hikeType === 'outandback') {
                        echo 'Out-and-Back';
                    } else {
                        echo 'Two-car';
                    }?></td>
                <td><?php echo $hikeElev;?> ft</td>
                <td><?php
                    if($hikeExp === 'sun') {
                        echo 'Full sun';
                    } else if ($hikeExp === 'shade') {
                        echo 'Good shade';
                    } else {
                        echo "Mixed sun/shade";
                    }?></td>
                <td><?php echo $hikeWow;?></td>
                <td><?php echo $hikeFac;?></td>
                <td><?php echo $hikeSeasons;?></td>
                <td><a href="<?php $hikePurl1;?>" target="_blank">
                    <img style="margin-bottom:0px;border-style:none;" src="../images/album_lnk.png" alt="photo album link icon" /></a></td>
                <td><a href="<?php echo $hikeDir;?>" target="_blank">
                    <img style="margin-bottom:0px;padding-bottom:0px;" 
                    src="../images/dirs.png" alt="google driving directions" /></a></td>
            </tr>
        </tbody>
    </table>
</div>
<h3 style="text-indent:8px">Data for Google Maps API</h3>
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
<h3 style="text-indent:8px">Other data submitted:</h3>
<ul>
	<li>Title to appear on Hike Page: <?php echo $hikeName;?></li>
	<li>Added Image 1: <?php echo $hikeOthrImage1;?></li>
	<li>Added Image 2: <?php echo $hikeOthrImage2;?></li>
	<li>Photo Link 1: <?php echo $hikePurl1;?></li>
	<li>Photo Link 2: <?php echo $hikePurl2;?></li>
	<li>Google Directions Link: <?php echo $hikeDir;?></li>
</ul>

<?php
	if ($tipTxt !== '') {
		echo '<h2 style="text-align:center;">Hike Tips Text:</h2>';
		echo '<div id="trailTips" style="margin:8px;"><img id="tipPic" 
			src="../images/tips.png" alt="special notes icon" />';
		echo '<p id="tipHdr">TRAIL TIPS!</p><p id="tipNotes">';
		echo $tipTxt . '</p></div>';
	}
?>
<h2 style="text-align:center;">Hike Information:</h2>
<?php 
	echo '<p id="hikeInfo" style="text-indent:8px;">';
	echo $rawhike;
	echo '</p>';
?>
<h2>Hike References:</h2>
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
	#echo "Ref string to pass: " . $refStr;
?>	

<?php
	$pStr = '';
	$aStr = '';

	if ($noOfPDats > 0 || $noOfADats > 0) {
		echo '<h2 style="text-align:center">Hike Data: Proposed and/or Actual</h2>';
		echo '<fieldset><legend id="flddat">GPS Maps &amp; Data</legend>';
		if ($noOfPDats > 0) {
			$pStr = $noOfPDats;
			echo '<p id="proptitle">- Proposed Hike Data</p><ul id="plinks">';
			for ($j=0; $j<$noOfPDats; $j++) {
				echo '<li>' . $hikePDatLbls[$j] . '<a href="' . $hikePDatUrls[$j] .
					'" target="_blank">' . $hikePDatCTxts[$j] . '</a></li>';
				$pStr .= '^' . $hikePDatLbls[$j] . '^' . $hikePDatUrls[$j] . '^' . $hikePDatCTxts[$j];	
			}
			echo '</ul>';
		}
		if ($noOfADats > 0) {
			$aStr = $noOfADats;
			echo '<p id="acttitle">- Actual Hike Data</p><ul id="alinks">';
			for ($k=0; $k<$noOfADats; $k++) {
				echo '<li>' . $hikeADatLbls[$k] . '<a href="' . $hikeADatUrls[$k] .
					'" target="_blank">' . $hikeADatCTxts[$k] . '</a></li>';
				$aStr .= '^' . $hikeADatLbls[$k] . '^' . $hikeADatUrls[$k] . '^' . $hikeADatCTxts[$k];
			}
		}
		echo '</fieldset>';
	}
	#echo "P & A Dat strings: PDat - " . $pStr . ";   ADat - " . $aStr;
?>
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
<input type="hidden" name="tsv" value="<?php echo $tsvFname;?>" />
<input type="hidden" name="hTitle" value="<?php echo $hikeName;?>" />
<input type="hidden" name="area"  value="<?php echo $hikeLocale;?>" />
<input type="hidden" name="htype" value="<?php echo $hikeType;?>" />
<input type="hidden" name="lgth"  value="<?php echo $hikeLgth;?>" />
<input type="hidden" name="elev"  value="<?php echo $hikeElev;?>" />
<input type="hidden" name="diffi" value="<?php echo $hikeDiff;?>" />
<input type="hidden" name="lati"  value="<?php echo $hikeLat;?>" />
<input type="hidden" name="long"  value="<?php echo $hikeLong;?>" /> 
<input type="hidden" name="facil" value="<?php echo $hikeFac;?>" />
<input type="hidden" name="wow"   value="<?php echo $hikeWow;?>" />
<input type="hidden" name="seasn" value="<?php echo $hikeSeasons;?>" />
<input type="hidden" name="expo"  value="<?php echo $hikeExp;?>" />
<input type="hidden" name="geomp" value="<?php echo $hikeMap;?>" />
<input type="hidden" name="chart" value="<?php echo $hikeEChart;?>" />
<input type="hidden" name="gpx" value="<?php echo $hikeGpx;?>" />
<input type="hidden" name="json"  value="<?php echo $hikeJSON;?>" />
<input type="hidden" name="img1"  value="<?php echo $hikeOthrImage1;?>" />
<input type="hidden" name="img2"  value="<?php echo $hikeOthrImage2;?>" />
<input type="hidden" name="mrkr"  value="<?php echo $hikeMarker;?>" />
<input type="hidden" name="phot1" value="<?php echo $hikePurl1;?>" />
<input type="hidden" name="phot2" value="<?php echo $hikePurl2;?>" />
<input type="hidden" name="gdirs" value="<?php echo $hikeDir;?>" />
<input type="hidden" name="refstr" value="<?php echo $refStr;?>" />
<input type="hidden" name="pstr" value="<?php echo $pStr;?>" />
<input type="hidden" name="astr" value="<?php echo $aStr;?>" />
</form>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="validateHike.js"></script>

</body>

</html>

				
				
				
				
				
				
				
				