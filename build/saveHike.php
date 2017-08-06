<?php 
session_start();
 ?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Save New Hike</title>
    <meta charset="utf-8" />
    <meta name="description" content="Write hike data to database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
	<img id="hikers" src="../images/hikers.png" alt="hikers icon" />
	<p id="logo_left">Hike New Mexico</p>
	<img id="tmap" src="../images/trail.png" alt="trail map icon" />
	<p id="logo_right">w/Tom &amp; Ken</p>
</div>

<?php
# Styling for output (error) messages:
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
/* Retrieve the last used index number and increment for new hike */
$db = simplexml_load_file('../data/database.xml');
$err = libxml_get_errors();
if ($db === false) {
    $nodb = '<p style="color:red;left-margin:16px;font-size:18px;">Could not '
            . 'load database as xml file: contact site master;<br />' .
            count($err) . ' error flags set</p>';
    die ($nodb);
}
foreach($db->row as $hikeRow) {
    $lastIndxNo = $hikeRow->indxNo;
}
$nxtNo = $lastIndxNo + 1;
# some info is needed in more than one place, so variables assigned:
$pageTitle = htmlspecialchars(filter_input(INPUT_POST,'hname'));
$hikeMiles = filter_input(INPUT_POST,'hmiles');
$hikeElev = filter_input(INPUT_POST,'hfeet');
$hikeExposure = filter_input(INPUT_POST,'hexp');
$hikeMainAlbumLink = filter_input(INPUT_POST,'hphoto1');
$hikeGpx = filter_input(INPUT_POST,'hgpx');
$hikeTrk =filter_input(INPUT_POST,'htrk');
/*
 *  ------- BEGIN XML ROW CREATIONS -------
 */

$xmlout = "\t<indxNo>" . $nxtNo . "</indxNo>\n";
$xmlout .= "\t<rlock></rlock>\n";
$xmlout .= "\t<pgTitle>" . $pageTitle . "</pgTitle>\n";
$xmlout .= "\t<locale>" . filter_input(INPUT_POST,'hlocale') . "</locale>\n";

# Process marker to determine output remarks...
$marker = filter_input(INPUT_POST,'hmarker');
if ($marker == 'center') {
    $marker = 'Visitor Ctr';
    $msg = "New Yellow Marker will be added on the map page for this Visitor "
        . "Center Index; the page will begin with no hikes listed in its table";
} elseif ($marker == 'ctrhike') {
    $marker = "At VC";
    $msg = "No Marker will be added to the map page for this hike, as it will be " .
        "listed in the Visitor Center Index Page, and appear in the info window for " .
        "the Center's yellow marker. The hike will also initially appear at the " .
        "bottom of the Index Table of Hikes as a separate hike";
} elseif ($marker == 'cluster') {
    $marker = 'Cluster';
    $msg = "This hike will be added to the others in the group: " . 
        filter_input(INPUT_POST,'htool') .
        ", which is currently already indicated by a Blue Marker";
} else {
    $marker = 'Normal';
    $msg = "A Red Marker will be added to the map page for this hike. The hike will " .
        "initially appear at the bottom of the Index Table of Hikes";
}
$xmlout .= "\t<marker>" . $marker . "</marker>\n";

# if this is a hike at a Visitor Center, that Index Page needs to be updated
$ctrHikeLoc = filter_input(INPUT_POST,'hindx');

# UNTESTED SECTION:
if ($ctrHikeLoc !== '') {
    /*  
     * $ctrHikeLoc holds the index number of the Visitor Center associated with
     * this hike. So, if not not empty, find the Index Page for the assoc. 
     * hike and update it in two places: the Visitor Center's 'clusterStr' 
     * and the Visitor Center's table of hikes.
    */
    $sunIcon = '../images/sun.jpg';
    $partialIcon = '../images/greenshade.jpg';
    $shadeIcon = '../images/shady.png';
    #xml database object already exists
    foreach ($db->row as $rowXml) {
        if ($rowXml->indxNo == $ctrHikeLoc) {
            # new clusterStr
            $oldClusStr = $rowXml->clusterStr;
            if (strlen($oldClusStr) === 0) {
                $newStr = $nxtNo;
            } else {
                $newStr = $oldClusStr . "." . $nxtNo;
            }
            # new table entry
            $tblxml .= "\t\t\t<compl>Y</compl>\n\t\t\t<tdname>" . $pageTitle .
                "</tdname>\n";
            $tblxml .= "\t\t\t<tdpg>" . $nxtNo . "</tdpg>\n";
            $tblxml .= "\t\t\t<tdmiles>" . $hikeMiles . "</tdmiles>\n";
            $tblxml .= "\t\t\t<tdft>" . $hikeElev . "</tdft>\n";
            if ($hikeExposure === 'Full sun') {
                $expIcon = $sunIcon;
            } elseif ($hikeExposure === 'Good shade') {
                $expIcon = $shadeIcon;
            } else {
                $expIcon = $partialIcon;
            }
            $tblxml .= "\t\t\t<tdexp>" . $expIcon . "</tdexp>";
            $tblxml .= "\t\t\t<tdalb>" . $hikeMainAlbumLink . "</tdalb>\n";
            $rowXml->content->addChild('tblRow',$tblxml);
        }
    }
    $xmlout .= "\t<clusterStr>" . $newStr . "</clusterStr>\n";
    
} else {
    $xmlout .= "\t<clusterStr></clusterStr>\n";
}
# END UNTESTED

# Continue spec'ing xmlout:
$xmlout .= "\t<clusGrp>" . filter_input(INPUT_POST,'hclus') . "</clusGrp>\n";
$xmlout .= "\t<logistics>" . filter_input(INPUT_POST,'htype') . "</logistics>\n";
$xmlout .= "\t<miles>" . $hikeMiles . "</miles>\n";
$xmlout .= "\t<feet>" . $hikeElev . "</feet>\n";
$xmlout .= "\t<difficulty>" . filter_input(INPUT_POST,'hdiff') . "</difficulty>\n";
$xmlout .= "\t<facilities>" . htmlspecialchars(filter_input(INPUT_POST,'hfac'))
        . "</facilities>\n";
$xmlout .= "\t<wow>" . htmlspecialchars(filter_input(INPUT_POST,'hwow')) .
        "</wow>\n";
$xmlout .= "\t<seasons>" . htmlspecialchars(filter_input(INPUT_POST,'hseas'))
        . "</seasons>\n";
$xmlout .= "\t<expo>" . $hikeExposure . "</expo>\n";
$xmlout .= "\t<tsv>\n" . $_SESSION['tsvdata'] . "\t</tsv>\n";
$xmlout .= "\t<gpxfile>" . $hikeGpx . "</gpxfile>\n";
$xmlout .= "\t<trkfile>" . $hikeTrk . "</trkfile>\n";
$xmlout .= "\t<lat>" . filter_input(INPUT_POST,'hlat') . "</lat>\n";
$xmlout .= "\t<lng>" . filter_input(INPUT_POST,'hlon') . "</lng>\n";
$xmlout .= "\t<mpUrl>" . $hikeMainAlbumLink . "</mpUrl>\n";
$xmlout .= "\t<spUrl>" . filter_input(INPUT_POST,'hphoto2') . "</spUrl>\n";
$xmlout .= "\t<dirs>" . filter_input(INPUT_POST,'hdir') . "</dirs>\n";
$xmlout .= "\t<cgName>" . htmlspecialchars(filter_input(INPUT_POST,'htool')) . "</cgName>\n";

$pix = filter_input(INPUT_POST,'usepix');
if ($pix === 'YES') {
    $xmlout .= "\t<content>\n" . $_SESSION['picrows'] . "\t</content>\n";
    # convert album links to xml:
    $alnks = filter_input(INPUT_POST,'hplnks');
    $phlinks = explode("^",$alnks);
    array_shift($phlinks);
    $albxml = "\t<albLinks>\n";
    foreach ($phlinks as $albLink) {
        $albxml .= "\t\t<alb>" . $albLink . "</alb>\n";
    }
    $albxml .= "\t</albLinks>\n";
    $xmlout .= $albxml;
} else {
    $xmlout .= "\t<content>\n\t</content>\n";
}

$xmlout .= "\t<tipsTxt>" . htmlspecialchars($_SESSION['hikeTips']) . "</tipsTxt>\n";
$xmlout .= "\t<hikeInfo>" . htmlspecialchars($_SESSION['hikeDetails']) . "</hikeInfo>\n";
$xmlout .= $_SESSION['hikerefs'];
$xmlout .= $_SESSION['propdata'];
$xmlout .= $_SESSION['actdata'];

?>
    <p id="trail"><?php echo $pageTitle;?></p>
    
<div style="margin-left:12px;padding:8px;">
    
<?php
# Array to determine which files to overwrite, if any
$saveRules = $_SESSION['filesaves'];
$rules = explode("^",$saveRules);
# Estabish directory locations
$cwd = getcwd();
$basedirlgth = strpos($cwd,"/build");
$basedir = substr($cwd,0,$basedirlgth);
$uploads = '/tmp/';
/* DATA WILL BE SAVED TO DIFFERENT PLACES DEPENDING ON SUBMITTER
 * Site Masters: Hike Data will be saved to the standard site using 
 *   database.xml; this means files will be moved from build/tmp to their
 *   correct respective locations on the site;
 * Registered Users: Data will be saved to the reviewdat.xml database (also
 *   in the data directory), and the tmp files will not be moved.
 */
$user = true; # default is 'not site master'
$saveTsv = filter_input(INPUT_POST,'savetsv');
$submitted = filter_input(INPUT_POST,'savePg');
if ($submitted === 'Site Master') {
    $passwd = filter_input(INPUT_POST,'mpass');
    if ($passwd !== '000ktesa') {
        die('<p style="color:brown;">Incorrect Password - save not executed</p>');
    }
    $user = false;
    $delmsg1 = '<p style="color:brown;">';
    $delmsg2 = " is being deleted because it was detected as a duplicate " .
        "file, and was not elected to overwrite the existing file.</p>";  
    # GPX FILE [required]
    $oldLoc = $cwd . $uploads . 'gpx/' . $hikeGpx;
    if ( $hikeGpx !== '' &&
            (($rules[0] === 'YES' && $rules[1] === 'YES') || $rules[0] === 'NO')) {
        $newLoc = $basedir . '/gpx/' . $hikeGpx;
        if (!rename($oldLoc,$newLoc)) {
            die('<p style="color:brown;">COULD NOT MOVE GPX FILE</p>');
        } else {
            echo "<p>Successfully moved gpx file</p>";
        }
    } elseif ($hikeGpx !== '') {
        echo $delmsg1 . $hikeGpx . $delmsg2;
        unlink($oldLoc);
    } 
    # JSON FILE: (created from gpx)
    $oldLoc = $cwd . $uploads . 'json/' . $hikeTrk;
    if ( $hikeTrk !== '' &&
            (($rules[2] === 'YES' && $rules[3] === 'YES') || $rules[2] === 'NO')) {
        $newLoc = $basedir . '/json/' . $hikeTrk;
        if (!rename($oldLoc,$newLoc)) {
            die('<p style="color:brown;">COULD NOT MOVE JSON FILE</p>');
        } else {
            echo "<p>Successfully moved json file</p>";
        }
    } elseif ($hikeTrk !== '') {
        echo $delmsg1 . $hikeTrk . $delmsg2;
        unlink($oldLoc);
    }
    # remaining files should be tested for existence:
    # IMAGE1 FILE:
    $addImg1 = filter_input(INPUT_POST,'hadd1');
    $oldLoc = $cwd . $uploads . 'images/' . $newHike[21];
    if ( $addImg1 !== '' &&
            (($rules[4] === 'YES' && $rules[5] === 'YES') || $rules[4] === 'NO')) {
        $newLoc = $basedir . '/images/' . $addImg1;
        if (!rename($oldLoc,$newLoc)) {
            die('<p style="color:brown;">COULD NOT MOVE 1st IMAGE FILE</p>');
        } else {
            echo "<p>Successfully moved 1st image file</p>";
        } 
    } elseif ($addImg1 !== '') {
        echo $delmsg1 . $addImg1 . $delmsg2;
        unlink($oldLoc);
    }
    # IMAGE2 FILE:
    $addImg2 = filter_input(INPUT_POST,'hadd2');
    $oldLoc = $cwd . $uploads . 'images/' . $addImg2;
    if ( $addImg2 !== '' &&
            (($rules[6] === 'YES' && $rules[7] === 'YES') || $rules[6] === 'NO')) {
        $newLoc = $basedir . '/images/' . $addImg2;
        if (!rename($oldLoc,$newLoc)) {
            die('<p style="color:brown;">COULD NOT MOVE 2nd IMAGE FILE</p>');
        } else {
            echo "<p>Successfully moved 2nd image file</p>";
        } 
    } elseif ($addImg2 !== '') {
        echo $delmsg1 . $addImg2 . $delmsg2;
        unlink($oldLoc);
    }
    
    $gpsdat = filter_input(INPUT_POST,'hdatf');
    $newDatFiles = explode("^",$gpsdat);
    /* newDatFiles holds 4 trios of data, 1 trio each for 2 'proposed data' files,
     * and 1 trio each for 2 'actual data' files. The trios are defined as follows:
     *  a) file name
     *  b) file location (either gpx/ or maps/)
     *  c) file existence elsewhere [either i) already uploaded, or 
     *      ii) already on site]
     */
    
    # PROP DAT FILE1
    # Check to see if a file is specified, and is not already uploaded:
    if ($newDatFiles[0] !== 'x' && $newDatFiles[2] == 0) {  #file doesnt exist elsewhere
        $oldLoc = $cwd . $uploads . $newDatFiles[1] . '/' . $newDatFiles[0];
        # $newDatFiles[1] holds either the 'gpx' or 'maps' dir value
        if (($rules[8] === 'YES' && $rules[9] === 'YES') || $rules[8] === 'NO') {
            $newLoc = $basedir . '/' . $newDatFiles[1] . '/' . $newDatFiles[0];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE 1st PROP DAT FILE</p>');
            } else {
                echo "<p>Successfully moved 1st prop dat file</p>";
            } 
        } else { # rules say kill it...
            echo $delmsg1 . $newDatFiles[0] . $delmsg2;
            unlink($oldLoc);
        }
    } elseif ($newDatFiles[0] !== 'x' && $newDatFiles[2] == 1) {  # "1" -> already uploaded
        echo '<p>' . $newDatFiles[0] . ' had already been uploaded - no activity</p>';
    }
    # PROP DAT FILE2  
    # Check to see if a file is specified, and is not already uploaded:
    if ( $newDatFiles[3] !== 'x' && $newDatFiles[5] == 0) {
        $oldLoc = $cwd . $uploads . $newDatFiles[4] . '/' . $newDatFiles[3];
        # $newDatFiles[4] holds either the 'gpx' or 'maps' dir value
        if (($rules[10] === 'YES' && $rules[11] === 'YES') || $rules[10] === 'NO') {	
            $newLoc = $basedir . '/' . $newDatFiles[4] . '/' . $newDatFiles[3];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE 2nd PROP DAT FILE</p>');
            } else {
                echo "<p>Successfully moved 2nd prop dat file</p>";
            } 
        }
        else {
            echo $delmsg1 . $newDatFiles[3] . $delmsg2;
            unlink($oldLoc);
        }
    } elseif ($newDatFiles[3] !== 'x' && $newDatFiles[5] === 1) {
        echo '<p>' . $newDatFiles[3] . ' had already been uploaded - no activity</p>';
    }
    # ACT DAT FILE1:
    # Check to see if a file is specified, and is not already uploaded:
    if ( $newDatFiles[6] !== 'x' && $newDatFiles[8] == 0) {
        $oldLoc = $cwd . $uploads . $newDatFiles[7] . '/' . $newDatFiles[6];
        if (($rules[12] === 'YES' && $rules[13] === 'YES') || $rules[12] === 'NO') {
            $newLoc = $basedir . '/' . $newDatFiles[7] . '/' . $newDatFiles[6];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE 1st ACT DAT FILE</p>');
            } else {
                echo "<p>Successfully moved 1st act dat file</p>";
            } 
        } else {
            echo $delmsg1 . $newDatFiles[6] . $delmsg2;
            unlink($oldLoc);
        }
    } elseif ($newDatFile[6] !== 'x' && $newDatFiles[8] === 1) {
        echo '<p>' . $newDatFiles[6] . ' had already been uploaded - no activity</p>';
    }
    # ACT DAT FILE2:
    # Check to see if a file is specified, and is not already uploaded:
    if ( $newDatFiles[9] !== 'x' && $newDatFiles[11] == 0) {
        $oldLoc = $cwd . $uploads . $newDatFiles[10] . '/' . $newDatFiles[9];
        # $newDatFiles[10] holds either the 'gpx' or 'maps' dir value
        if (($rules[14] === 'YES' && $rules[15] === 'YES') || $rules[14] === 'NO') {
            $newLoc = $basedir . '/' . $newDatFiles[10] . '/' . $newDatFiles[9];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE 2nd ACT DAT FILE</p>');
            } else {
                echo "<p>Successfully moved 2nd act dat file</p>";
            } 
        } else {
            echo $delmsg1 . $newDatFiles[9] . $delmsg2;
            unlink($oldLoc);
        }
    } elseif ($newDatFiles[9] !== 'x' && $newDatFiles === 1) {
        echo '<p>' . $newDatFiles[9] . ' had already been uploaded - no activity</p>';
    }
    /* For reasons beyond me, when I try to use $db->addChild('row',$xmlout),
     * the $xmlout gets converted with something like htmlspecialchars when
     * adding, so that $db->asXML('file.xml') has the last row garbled... 
     * Therefore, the old-fashioned approach:
     */
    $newRowStr = "\n<row>\n" . $xmlout . "</row>\n</rows>";
    $updatedb = fopen("database.xml","r+");  # fseek doesn't work with "a" - append
    if ($updatedb === false) {
       $noupdb = '<p style="color:brown;">Could not open database.xml: contact'
               . ' site master;</p>';
       die ($noupdb);
    }
    fseek($updatedb,-8,SEEK_END);  # backup before [newline]</rows>
    fwrite($updatedb,$newRowStr);
    fclose($updatedb);
    /*
    $newRow = $db->addChild('row',$xmlout);
    $db->asXML('save.xml');
     */
    echo "<h2>" . $msg . "</h2>";  
} else if ($submitted === 'Submit for Review') {
    # NOT UPDATED FOR VISITOR CENTER HIKES - need process
    $usrdb = '../data/reviewdat.xml';
    $udb = simplexml_load_file($usrdb);
    if ($usrdb === false) {
        $udbmsg = '<p style="color:brown;">Unable to append to reviewdat.xml; '
                . 'contact Site Master';
        die ($udbmsg);
    }
    $udb->rows->addChild('row',$xmlout);
    
} else if ($submitted === 'Save for Re-edit') {
    echo '<p style="color:red;">Not Yet Implemented: nothing saved</p>';
} else {
    die('<p style="color:brown;">Contact Site Master: Submission not recognized');
} 
?>
</div>
<div data-ptype="hike" data-indxno="<?php echo $newHike[0];?>" style="padding:16px;" id="more">
<?php
    if ( !user ) {
        echo '<button style="font-size:16px;color:DarkBlue;" id="same">Edit this hike</button><br />';
        echo '<button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different hike</button>';
    }
?>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="saveHike.js"></script>
<script src="postEdit.js"></script>
</body>
</html>