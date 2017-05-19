<?php

/* Message text for upload data section */
$fexists1 = '<p style="margin-left:8px;margin-top:-12px;color:brown;"><em>NOTE: ';
$fexists2 = ' has been previously saved on the server; ' .
            'Check here to overwrite: ';
$fexists3 = '</em></p>' . "\n";
$uploads = "tmp/"; 
$datfileArray = [];  # prop & act data file names
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
$tsvFname = basename($_FILES['csvfile']['name']);
$tsvStat = $_FILES['csvfile']['error'];
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
$tsvLoc = '../gpsv/' . $tsvFname;
if ( file_exists($tsvLoc) ) {
    echo $fexists1 . $tsvFname . $fexists2. 
        '<input id="owtsv" type="checkbox" name="tsvow" />' . $fexists3;
    $dupTsv = 'YES';
}
$tsvUpload = $uploads . 'gpsv/' . $tsvFname;
if ($tsvStat === UPLOAD_ERR_OK) {
    if (!move_uploaded_file($tsvFile,$tsvUpload)) {
        die("Could not save tsv file - contact site master...");
    }
}
echo '<ul style="margin-top:-10px;">' . "\n";
echo '<li>Uploaded tsv file: ' .  $tsvFname . '</li>' . "\n";
echo '<li>File size: ' . $tsvSize . ' bytes</li>' . "\n";
echo '<li>File type: ' . $tsvType . '</li>' . "\n";
echo '</ul>' . "\n";

# GEOMAP FILE OPS:
echo '<h3 style="text-indent:8px">Uploaded Geomap File Info:</h3>' . "\n";
$gmapFile = $_FILES['gpsvMap']['tmp_name'];
$hikeMap = basename($_FILES['gpsvMap']['name']);
$mapSize = filesize($gmapFile);
$mapType = $_FILES['gpsvMap']['type'];
$mapStat = $_FILES['gpsvMap']['error'];
$mapLoc = '../maps/' . $hikeMap;
if ( $hikeMap !== '' && file_exists($mapLoc) ) {
    echo $fexists1 . $hikeMap . $fexists2. 
        '<input id="owmap" type="checkbox" name="mapow" />' . $fexists3;
    $dupMap = 'YES';
}
if ( $hikeMap !== '') {
    if ( preg_match("/html/",$mapType) === 0 ) { 
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect '
                . 'file type for ' . $hikeMap . ': must be html</strong></p>';
        die($msgout);
    }
    $mapUpload = $uploads . 'maps/' . $hikeMap;
    if ($mapStat === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($gmapFile,$mapUpload)) {
            die("Could not save map file - contact site master...");
        }
    }
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
$hikeGpx = basename($_FILES['gpxname']['name']);
$gpxSize = filesize($gpxFile);
$gpxType = $_FILES['gpxname']['type'];
$gpxStat = $_FILES['gpxname']['error'];
$gpxLoc = '../gpx/' . $hikeGpx;
if ( $hikeGpx !== '' && file_exists($gpxLoc) ) {
    echo $fexists1 . $hikeGpx . $fexists2 . 
        '<input id="owgpx" type="checkbox" name="gpxow" />' . $fexists3;
    $dupGpx = 'YES';
} 
if ( $hikeGpx !== '') {
    if ( preg_match("/octet-stream/",$gpxType) === 0 ) {
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
                . ' file type for ' . $hikeGpx . ': should be "octet-stream"';
        die($msgout);
    }
    $gpxUpload = $uploads . 'gpx/' . $hikeGpx;
    if ($gpxStat === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($gpxFile,$gpxUpload)) {
            die("Could not save gpx file - contact site master...");
        }
    }
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
$mktrk = filter_input(INPUT_POST,'maketrack');
if ( isset($mktrk) ) {
    $cwd = getcwd();
    $ktesaPos = strpos($cwd,"ktesa") + 6;
    $ktesaDir = substr($cwd,0,$ktesaPos);
    $trkcmd = $ktesaDir . 'tools/mktrk.sh -f ' . $cwd . '/' . $uploads .
            'gpx/' . $hikeGpx . ' -p ' . $cwd . '/' . $uploads . 'json';
    $json = exec($trkcmd);
    if ( preg_match("/DONE/",$json) === 1 ) {
        echo '<p style="margin-left:10px;">Track file created from GPX and saved</p>';
    } else {
        echo '<p style="margin-left:10px;">Track file creation failed: Please ' .
            'return to the hike Editor, un-check the box, and upload a track file' .
            ' or contact site master</p>';
    }
    $jpos = strpos($hikeGpx,".");
    $hikeJSON = substr($hikeGpx,0,$jpos) . ".json";
    $JSONloc = '../json/' . $hikeJSON;
    if ( file_exists($JSONloc) ) {
        echo $fexists1 . $hikeJSON . $fexists2 . 
         '<input id="owtrk" type="checkbox" name="trkow" />' . $fexists3;
        $dupJSON = 'YES';
    }
} else {
    $jsonFile = $_FILES['track']['tmp_name'];
    $hikeJSON = basename($_FILES['track']['name']);
    $jsonSize = filesize($jsonFile);
    $jsonType = $_FILES['track']['type'];
    $jsonStat = $_FILES['track']['error'];
    $jsonLoc = '../json/' . $hikeJSON;
    if ( $hikeJSON !== '' && file_exists($jsonLoc) ) {
        echo $fexists1 . $hikeJSON . $fexists2. 
            '<input id="owjson" type="checkbox" name="jsonow" />' . $fexists3;
        $dupJSON = 'YES';
    }
    if ( $hikeJSON !== '') {
        if ( preg_match("/json/",$jsonType) === 0 ) {
            $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
                . ' file type for ' . $hikeJSON . ': should be "json"</strong</p>';
            die($msgout);
        }
        $jsonUpload = $uploads . 'json/' . $hikeJSON;
        if ($jsonStat === UPLOAD_ERR_OK) {
            if (!move_uploaded_file($jsonFile,$jsonUpload)) {
                die("Could not save json file - contact site master...");
            }
        }
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

# ADDITIONAL IMAGES FILES (IF ANY):
echo '<h3 style="text-indent:8px">Uploaded Image Files (if any):</h3>' . "\n";
$othrImg1 = $_FILES['othr1']['tmp_name'];
$othrImg1Size = filesize($othrImg1);
$hikeOthrImage1 = basename($_FILES['othr1']['name']);
$othrImg1Type = $_FILES['othr1']['type'];
$img1Stat = $_FILES['othr1']['error'];
$othrImg2 = $_FILES['othr2']['tmp_name'];
$othrImg2Size = filesize($othrImg2);
$hikeOthrImage2 = basename($_FILES['othr2']['name']);
$othrImg2Type = $_FILES['othr2']['type'];
$img2Stat = $_FILES['othr2']['error'];
$img1Loc = '../images/' . $hikeOthrImage1;
$img2Loc = '../images/' . $hikeOthrImage2;  
if ( $hikeOthrImage1 !== '' && file_exists($img1Loc) ) {
    echo $fexists1 . $hikeOthrImage1 . $fexists2. 
        '<input id="owim1" type="checkbox" name="im1ow" />' . $fexists3;
    $dupImg1 = 'YES';
}
if ( $hikeOthrImage1 !== '') {
    $img1Upload = $uploads . 'images/' . $hikeOthrImage1;
    if ($img1Stat === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($othrImg1,$img1Upload)) {
            die("Could not save 1st image file - contact site master...");
        }
    }  
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($hikeOthrImage1 !== '') {
    echo '<li>Uploaded Image1: ' .  $hikeOthrImage1 . '</li>' . "\n";
    echo '<li>File size: ' . $othrImg1Size . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $othrImg1Type . '</li>' . "\n";
} else {
    echo '<li>NO ADDITIONAL FIRST IMAGE UPLOADED: If needed, go back and '
    . 'select in hike Editor</li>' . "\n";
}
echo '</ul>' . "\n";
if ( $hikeOthrImage2 !== '' && file_exists($img2Loc) ) {
    echo $fexists1 . $hikeOthrImage2 . $fexists2. 
        '<input id="owim2" type="checkbox" name="im2ow" />' . $fexists3;
    $dupImg2 = 'YES';
}
if ( $hikeOthrImage2 !== '') {
    $img2Upload = $uploads . 'images/' . $hikeOthrImage2;
    if ($img2Stat === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($othrImg2,$img2Upload)) {
            die("Could not save 2nd image file - contact site master...");
        }
    }
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($hikeOthrImage2 !== '') {
    echo '<li>Uploaded Image2: ' .  $hikeOthrImage2 . '</li>' . "\n";
    echo '<li>File size: ' . $othrImg2Size . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $othrImg2Type . '</li>' . "\n";
} else {
    echo '<li>NO ADDITIONAL SECOND IMAGE UPLOADED: If needed, go back and '
    . 'select in hike Editor</li>' . "\n";
}
echo '</ul>' . "\n";

/* GPS MAPS & DATA SECTION: PROPOSED FILE OPS:
 *  There are potentially two sections for user data entry:
 *  PROPOSED: User enters either maps or gpx files as references
 *  ACTUAL: User enters either maps or gpx files as references
 * At this time, only two files of either type are provided for;
 * NOTE: Not testing for duplicate file names: these would over-write any such
 */
$noup = '<p>This file has been previously uploaded; No further action taken on this file</p>';
# PROPOSED DATA FILE1:
echo '<h3 style="text-indent:8px">Uploaded Proposed Data User File1 Info:</h3>' . "\n";
$pdatf1 = $_FILES['propmap']['tmp_name'];
$pfile1 = basename($_FILES['propmap']['name']);
array_push($datfileArray,$pfile1);
$pf1Size = filesize($pfile1);
$pf1Type = $_FILES['propmap']['type'];
$pf1Stat = $_FILES['propmap']['error'];
$pf1loc = filter_input(INPUT_POST,'f1');
array_push($datfileArray,$pf1loc);
$pf1site = '../' . $pf1loc . '/' . $pfile1;
if ($pf1loc === 'maps') {
    $ftype = "/html/";
} else {
    $ftype = "/octet-stream/";
}
$fupload = $uploads . $pf1loc . '/' . $pfile1;
if ( $pfile1 !== '') {
    if ( preg_match($ftype,$pf1Type) === 0 ) {
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
            . ' file type for [' . $pf1loc . '] ' . $pfile1 . 
            ': should be ' . $ftype;
        die($msgout);
    }
    # Check against previously uploaded files
    if ( file_exists($fupload) ) {
    	echo $noup;
    } else {
		# Check agains existing site files
		if ( file_exists($pf1site) ) {
			echo $fexists1 . $pfile1 . $fexists2. 
				'<input id="owpf1" type="checkbox" name="pf1ow" />' . $fexists3;
			$dupPmap = 'YES';
		}
		if ($pf1Stat === UPLOAD_ERR_OK) {
			if (!move_uploaded_file($pdatf1,$fupload)) {
				die("Could not save file - contact site master...");
			}
		}
    }
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($pfile1 !== '') {
    echo '<li>Uploaded Proposed Data User File1: ' .  $pfile1 . '</li>' . "\n";
    echo '<li>File size: ' . $pf1Size . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $pf1Type . '</li>' . "\n";
} else {
    echo '<li>NO PROPOSED DATA USER FILE1 UPLOADED: If needed, go back and select in hike ' .
        'Editor</li>' . "\n";
}
echo '</ul>' . "\n";

# PROPOSED DATA FILE2
echo '<h3 style="text-indent:8px">Uploaded Proposed Data User File2 Info:</h3>' . "\n";
$pdatf2 = $_FILES['propgpx']['tmp_name'];
$pfile2 = basename($_FILES['propgpx']['name']);
array_push($datfileArray,$pfile2);
$pf2Size = filesize($pfile2);
$pf2Type = $_FILES['propgpx']['type'];
$pf2Stat = $_FILES['propgpx']['error'];
$pf2loc = filter_input(INPUT_POST,'f2');
array_push($datfileArray,$pf2loc);
$pf2site = '../' . $pf2loc . '/' . $pfile2;
if ($pf2loc === 'maps') {
    $ftype = "/html/";
} else {
    $ftype = "/octet-stream/";
}
$fupload = $uploads . $pf2loc . '/' . $pfile2;
if ( $pfile2 !== '') {
    if ( preg_match($ftype,$pf2Type) === 0 ) {
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
            . ' file type for [' . $pf2loc . '] ' . $pfile2 . 
            ': should be ' . $ftype;
        die($msgout);
    }
    # Check against previously uploaded files
    if ( file_exists($fupload) ) {
    	echo $noup;
    } else {
		# Check agains existing site files
		if ( file_exists($pf2site) ) {
			echo $fexists1 . $pfile2 . $fexists2. 
				'<input id="owpf2" type="checkbox" name="pf2ow" />' . $fexists3;
			$dupPgpx = 'YES';
		}
		if ($pf2Stat === UPLOAD_ERR_OK) {
			if (!move_uploaded_file($pdatf2,$fupload)) {
				die("Could not save file - contact site master...");
			}
		}
    }
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($pfile2 !== '') {
    echo '<li>Uploaded Proposed Data User File2: ' .  $pfile2 . '</li>' . "\n";
    echo '<li>File size: ' . $pf2Size . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $pf2Type . '</li>' . "\n";
} else {
    echo '<li>NO PROPOSED DATA USER FILE2 UPLOADED: If needed, go back and select in hike ' .
        'Editor</li>' . "\n";
}
echo '</ul>' . "\n";

# ACTUAL DATA FILE1:
echo '<h3 style="text-indent:8px">Uploaded Actual Data User File1 Info:</h3>' . "\n";
$adatf1 = $_FILES['actmap']['tmp_name'];
$afile1 = basename($_FILES['actmap']['name']);
array_push($datfileArray,$afile1);
$af1Size = filesize($afile1);
$af1Type = $_FILES['actmap']['type'];
$af1Stat = $_FILES['actmap']['error'];
$af1loc = filter_input(INPUT_POST,'f3');
array_push($datfileArray,$af1loc);
$af1site = '../' . $af1loc . '/' . $afile1;
if ($af1loc === 'maps') {
    $ftype = "/html/";
} else {
    $ftype = "/octet-stream/";
}
$fupload = $uploads . $af1loc . '/' . $afile1;
if ( $afile1 !== '') {
    if ( preg_match($ftype,$af1Type) === 0 ) {
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
            . ' file type for [' . $af1loc . '] ' . $afile1 . 
            ': should be ' . $ftype;
        die($msgout);
    }   
    # Check against previously uploaded files
    if ( file_exists($fupload) ) {
    	echo $noup;
    } else {
		# Check agains existing site files
		if ( file_exists($af1site) ) {
			echo $fexists1 . $afile1 . $fexists2. 
				'<input id="owaf1" type="checkbox" name="af1ow" />' . $fexists3;
			$dupAmap = 'YES';
		}
		if ($af1Stat === UPLOAD_ERR_OK) {
			if (!move_uploaded_file($adatf1,$fupload)) {
				die("Could not save file - contact site master...");
			}
		}
	}
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($afile1 !== '') {
    echo '<li>Uploaded Actual Data User File1: ' .  $afile1 . '</li>' . "\n";
    echo '<li>File size: ' . $af1Size . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $af1Type . '</li>' . "\n";
} else {
    echo '<li>NO ACTUAL DATA USER FILE1 UPLOADED: If needed, go back and select in hike ' .
        'Editor</li>' . "\n";
}
echo '</ul>' . "\n";

# ACTUAL DATA FILE2
echo '<h3 style="text-indent:8px">Uploaded Actual Data User File2 Info:</h3>' . "\n";
$adatf2 = $_FILES['actgpx']['tmp_name'];
$afile2 = basename($_FILES['actgpx']['name']);
array_push($datfileArray,$afile2);
$af2Size = filesize($pfile1);
$af2Type = $_FILES['actgpx']['type'];
$af2Stat = $_FILES['actgpx']['error'];
$af2loc = filter_input(INPUT_POST,'f4');
array_push($datfileArray,$af2loc);
$af2site = '../' . $af2loc . '/' . $afile2;
if ($af2loc === 'maps') {
    $ftype = "/html/";
} else {
    $ftype = "/octet-stream/";
}
$fupload = $uploads . $af2loc . '/' . $afile2;
if ( $afile2 !== '') {
    if ( preg_match($ftype,$af2Type) === 0 ) {
        $msgout = '<p style="margin-left:20px;color:red;"><strong>Incorrect'
            . ' file type for [' . $af2loc . '] ' . $afile2 . 
            ': should be ' . $ftype;
        die($msgout);
    }
    # Check against previously uploaded files
    if ( file_exists($fupload) ) {
    	echo $noup;
    } else {
		# Check agains existing site files
		if ( file_exists($af2site) ) {
			echo $fexists1 . $afile2 . $fexists2. 
				'<input id="owaf2" type="checkbox" name="af2ow" />' . $fexists3;
			$dupAmap = 'YES';
		}
    if ($af2Stat === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($adatf2,$fupload)) {
            die("Could not save gpx file - contact site master...");
        }
    }
}
echo '<ul style="margin-top:-10px;">' . "\n";
if ($afile2 !== '') {
    echo '<li>Uploaded Proposed Data User File1: ' .  $afile2 . '</li>' . "\n";
    echo '<li>File size: ' . $af2Size . ' bytes</li>' . "\n";
    echo '<li>File type: ' . $af2Type . '</li>' . "\n";
} else {
    echo '<li>NO ACTUAL DATA USER FILE2 UPLOADED: If needed, go back and select in hike ' .
        'Editor</li>' . "\n";
}
echo '</ul>' . "\n";
$datfiles = implode("^",$datfileArray);
?>