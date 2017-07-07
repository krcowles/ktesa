<?php 
	session_start();
        #ini_set("auto_detect_line_endings", true); // if trouble on Mac w/newlines
	$sunIcon = '../images/sun.jpg';
	$partialIcon = '../images/greenshade.jpg';
	$shadeIcon = '../images/shady.png';
 ?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Save New Hike</title>
    <meta charset="utf-8" />
    <meta name="description" content="Write hike data to TblDB.csv" />
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
    /* get last used hike No.. */
    $database = '../data/database.csv';
    $handle = fopen($database, "r");
    if ($handle !== false) {
        while ( ($hikeLine = fgetcsv($handle)) !== false ) {
            $lastIndx = $hikeLine[0];
        }
    } else {
        echo "<p>Could not open database file</p>";
    }
    # NEW HIKE INDX STARTS AT LAST INDX + 1:
    $newHike[0] = intval($lastIndx) + 1;
    $newHike[1] = filter_input(INPUT_POST,'hname');
?>
    <p id="trail"><?php echo $newHike[1];?></p>
    
<div style="margin-left:12px;padding:8px;">
<?php
    $newHike[2] = filter_input(INPUT_POST,'hlocale');
    $newHike[3] = filter_input(INPUT_POST,'hmarker');
    # define text for marker type
    if ($newHike[3] == 'center') {
        $newHike[3] = 'Visitor Ctr';
        $msg = "New Yellow Marker will be added on the map page for this Visitor Center Index;" .
            " the page will begin with no hikes listed in its table";
    } elseif ($newHike[3] == 'ctrhike') {
        $newHike[3] = "At VC";
        $msg = "No Marker will be added to the map page for this hike, as it will be " .
            "listed in the Visitor Center Index Page, and appear in the info window for " .
            "the Center's yellow marker. The hike will also initially appear at the " .
            "bottom of the Index Table of Hikes as a separate hike";
    } elseif ($newHike[3] == 'cluster') {
        $newHike[3] = 'Cluster';
        $msg = "This hike will be added to the others in the group: " . 
            filter_input(INPUT_POST,'htool') .
            ", which is currently already indicated by a Blue Marker";
    } else {
        $newHike[3] = 'Normal';
        $msg = "A Red Marker will be added to the map page for this hike. The hike will " .
            "initially appear at the bottom of the Index Table of Hikes";
    }
    $newHike[4] = filter_input(INPUT_POST,'hindx');
    $newHike[5] = filter_input(INPUT_POST,'hclus');
    $newHike[6] = filter_input(INPUT_POST,'htype');
    $newHike[7] = filter_input(INPUT_POST,'hmiles');
    $newHike[8] = filter_input(INPUT_POST,'hfeet');
    $newHike[9] = filter_input(INPUT_POST,'hdiff');
    $newHike[10] = filter_input(INPUT_POST,'hfac');
    $newHike[11] = filter_input(INPUT_POST,'hwow');
    $newHike[12] = filter_input(INPUT_POST,'hseas');
    $newHike[13] = filter_input(INPUT_POST,'hexp');
    $newHike[14] = filter_input(INPUT_POST,'htsv');
    $newHike[15] = filter_input(INPUT_POST,'hmap');
    $newHike[16] = filter_input(INPUT_POST,'hchart');
    $newHike[17] = filter_input(INPUT_POST,'hgpx');
    $newHike[18] = filter_input(INPUT_POST,'htrk');
    $newHike[19] = filter_input(INPUT_POST,'hlat');
    $newHike[20] = filter_input(INPUT_POST,'hlon');
    $newHike[21] = filter_input(INPUT_POST,'hadd1');
    $newHike[22] = filter_input(INPUT_POST,'hadd2');
    $newHike[23] = filter_input(INPUT_POST,'hphoto1');
    $newHike[24] = filter_input(INPUT_POST,'hphoto2');
    $newHike[25] = filter_input(INPUT_POST,'hdir');
    /* Tips [Y/N] & HikePg.html OBSOLETE: [26], [27] */
    $newHike[26] = '';
    $newHike[27] = '';
    $newHike[28] = filter_input(INPUT_POST,'htool');
    $newHike[29] = $_SESSION['row0'];
    $newHike[30] = $_SESSION['row1'];
    $newHike[31] = $_SESSION['row2'];
    $newHike[32] = $_SESSION['row3'];
    $newHike[33] = $_SESSION['row4'];
    $newHike[34] = $_SESSION['row5'];
    $newHike[35] = filter_input(INPUT_POST,'hcaps');
    $newHike[36] = filter_input(INPUT_POST,'hplnks');
    $newHike[37] = $_SESSION['hikeTips'];
    $newHike[38] = $_SESSION['hikeDetails'];
    $newHike[39] = filter_input(INPUT_POST,'href');
    $newHike[40] = filter_input(INPUT_POST,'hpdat');
    $newHike[41] = filter_input(INPUT_POST,'hadat');
    ksort($newHike, SORT_NUMERIC);
    $supptFiles = filter_input(INPUT_POST,'hdatf');
    $newDatFiles = explode("^",$supptFiles);
    /* Updates involving Index Page, if the hike is associated with a 
     * Visitor Center. This updates the Index Page's 'Cluster String' and
     * Table of Hikes
     */
    $updateIndx = false;
    if ($newHike[4] !== '') {  # then process the index page for updates
    	# the value of $newHike[4] is the 'hike indx no' for the Visitor Center/Index Page
    	$othrhikes = true;
        $newstr = $_SESSION['indxCluster'];
        if ($newstr === '') {
            $othrhikes = false;
        }
    	if ($othrhikes) {
            $newstr .= '.' . $newHike[0];
    	} else {
            $newstr = $newHike[0];
    	}
        rewind($handle);
        while ( ($indxLine = fgetcsv($handle)) !== false ) {
            if ($indxLine[0] == $newHike[4]) {
                $prevTbl = $indxLine[29];
                $updateIndx = true;
                break;
            }
        }
        if (!$updateIndx) {
            die ("Visitor Center Page for this hike not found - contact site master");
        }
    	# create the table entry
        if ($othrhikes) {
            $row0 = '|n^';
        } else {
            $row0 = 'n^';
        }
    	$row0 .= $newHike[1] . '^hikePageTemplate.php?hikeIndx=' . $newHike[0] .
            '^' . $newHike[7] . ' miles^' . $newHike[8] . ' ft.^';
    	if ($newHike[13] === 'Full sun') {
            $expIcon = $sunIcon;
    	} elseif ($newHike[13] === 'Good shade') {
            $expIcon = $shadeIcon;
    	} else {
            $expIcon = $partialIcon;
    	}
    	$row0 .= $expIcon . '^' . $newHike[23];
        $indxLine[4] = $newstr;
        
    	$indxLine[29] = $prevTbl . $row0;
    	
    }
    fclose($handle);
    # Array to determine which files to overwrite, if any
    $saveRules = $_SESSION['filesaves'];
    $rules = explode("^",$saveRules);
    # Estabish directory locations
    $cwd = getcwd();
    $basedirlgth = strpos($cwd,"/build");
    $basedir = substr($cwd,0,$basedirlgth);
    $uploads = '/tmp/';
    /* DATA WILL BE SAVED TO DIFFERENT PLACES DEPENDING ON SUBMITTER
     * Site Masters: Hike Data will be saved to the standard site usign the
     *   database.csv; this means files will be moved from build/tmp to their
     *   correct respective locations;
     * Registered Users: Data will be saved to the reviewdat.csv database (also
     *   in the data directory), and the tmp files will not be moved.
     */
    $user = true;
    if (filter_input(INPUT_POST,'savePg') === 'Site Master') {
        $passwd = filter_input(INPUT_POST,'mpass');
        if ($passwd !== '000ktesa') {
            die('<p style="color:brown;">Incorrect Password - save not executed</p>');
        }
        $user = false;
        $delmsg1 = '<p style="color:brown;">';
        $delmsg2 = " is being deleted because it was detected as a duplicate " .
            "file, and was not designated to overwrite the existing file.</p>";
        # There is always a tsv file...
        $oldLoc = $cwd . $uploads . 'gpsv/' . $newHike[14];
        if ( ($rules[0] === 'YES' && $rules[1] === 'YES') || $rules[0] === 'NO' ) {
            $newLoc = $basedir . '/gpsv/' . $newHike[14];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE TSV FILE</p>');
            } else {
                echo "<p>Successfully moved tsv file</p>";
            }
        } else {
            echo $delmsg1 . $newHike[14] . $delmsg2;
            unlink($oldLoc);
        }
        # remaining files should be tested for existence:
        # GPX FILE
        $oldLoc = $cwd . $uploads . 'gpx/' . $newHike[17];
        if ( $newHike[17] !== '' &&
                (($rules[2] === 'YES' && $rules[3] === 'YES') || $rules[2] === 'NO')) {
            $newLoc = $basedir . '/gpx/' . $newHike[17];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE GPX FILE</p>');
            } else {
                echo "<p>Successfully moved gpx file</p>";
            }
        } elseif ($newHike[17] !== '') {
            echo $delmsg1 . $newHike[17] . $delmsg2;
            unlink($oldLoc);
        }
        # JSON FILE:
        $oldLoc = $cwd . $uploads . 'json/' . $newHike[18];
        if ( $newHike[18] !== '' &&
                (($rules[4] === 'YES' && $rules[5] === 'YES') || $rules[4] === 'NO')) {
            $newLoc = $basedir . '/json/' . $newHike[18];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE JSON FILE</p>');
            } else {
                echo "<p>Successfully moved json file</p>";
            }
        } elseif ($newHike[18] !== '') {
            echo $delmsg1 . $newHike[18] . $delmsg2;
            unlink($oldLoc);
        }
        # IMAGE1 FILE:
        $oldLoc = $cwd . $uploads . 'images/' . $newHike[21];
        if ( $newHike[21] !== '' &&
                (($rules[6] === 'YES' && $rules[7] === 'YES') || $rules[6] === 'NO')) {
            $newLoc = $basedir . '/images/' . $newHike[21];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE 1st IMAGE FILE</p>');
            } else {
                echo "<p>Successfully moved 1st image file</p>";
            } 
        } elseif ($newHike[21] !== '') {
            echo $delmsg1 . $newHike[21] . $delmsg2;
            unlink($oldLoc);
        }
        # IMAGE2 FILE:
        $oldLoc = $cwd . $uploads . 'images/' . $newHike[22];
        if ( $newHike[22] !== '' &&
                (($rules[8] === 'YES' && $rules[9] === 'YES') || $rules[8] === 'NO')) {
            $newLoc = $basedir . '/images/' . $newHike[22];
            if (!rename($oldLoc,$newLoc)) {
                die('<p style="color:brown;">COULD NOT MOVE 2nd IMAGE FILE</p>');
            } else {
                echo "<p>Successfully moved 2nd image file</p>";
            } 
        } elseif ($newHike[22] !== '') {
            echo $delmsg1 . $newHike[22] . $delmsg2;
            unlink($oldLoc);
        }
        # PROP DAT FILE1
        # Check to see if a file is specified, and is not already uploaded:
        if ($newDatFiles[0] !== 'x' && $newDatFiles[2] == 0) {
            $oldLoc = $cwd . $uploads . $newDatFiles[1] . '/' . $newDatFiles[0];
            # $newDatFiles[1] holds either the 'gpx' or 'maps' dir value
            if (($rules[10] === 'YES' && $rules[11] === 'YES') || $rules[10] === 'NO') {
                $newLoc = $basedir . '/' . $newDatFiles[1] . '/' . $newDatFiles[0];
                if (!rename($oldLoc,$newLoc)) {
                    die('<p style="color:brown;">COULD NOT MOVE 1st PROP DAT FILE</p>');
                } else {
                    echo "<p>Successfully moved 1st prop dat file</p>";
                } 
            } else {
                echo $delmsg1 . $newDatFiles[0] . $delmsg2;
                unlink($oldLoc);
            }
        } elseif ($newDatFiles[2] === 1) {  # "1" -> already uploaded
            echo '<p>' . $newDatFiles[0] . ' had already been uploaded - no activity</p>';
        }
        # PROP DAT FILE2  
        # Check to see if a file is specified, and is not already uploaded:
        if ( $newDatFiles[3] !== 'x' && $newDatFiles[5] == 0) {
            $oldLoc = $cwd . $uploads . $newDatFiles[4] . '/' . $newDatFiles[3];
            # $newDatFiles[4] holds either the 'gpx' or 'maps' dir value
            if (($rules[12] === 'YES' && $rules[13] === 'YES') || $rules[12] === 'NO') {	
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
        } elseif ($newDatFiles[5] === 1) {
            echo '<p>' . $newDatFiles[3] . ' had already been uploaded - no activity</p>';
        }
        # ACT DAT FILE1:
        # Check to see if a file is specified, and is not already uploaded:
        if ( $newDatFiles[6] !== 'x' && $newDatFiles[8] == 0) {
            $oldLoc = $cwd . $uploads . $newDatFiles[7] . '/' . $newDatFiles[6];
            
            if (($rules[14] === 'YES' && $rules[15] === 'YES') || $rules[14] === 'NO') {
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
        } elseif ($newDatFiles[8] === 1) {
            echo '<p>' . $newDatFiles[6] . ' had already been uploaded - no activity</p>';
        }
        # ACT DAT FILE2:
        # Check to see if a file is specified, and is not already uploaded:
        if ( $newDatFiles[9] !== 'x' && $newDatFiles[11] == 0) {
            $oldLoc = $cwd . $uploads . $newDatFiles[10] . '/' . $newDatFiles[9];
            # $newDatFiles[10] holds either the 'gpx' or 'maps' dir value
            if (($rules[16] === 'YES' && $rules[17] === 'YES') || $rules[16] === 'NO') {
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
        } elseif ($newDatFiles === 1) {
            echo '<p>' . $newDatFiles[9] . ' had already been uploaded - no activity</p>';
        }
        # if any, need to add index page changes...
        if ($updateIndx) {
        	$outdat = fopen($database,"r");
        	$ptr = 0;
        	while ( ($db = fgetcsv($outdat)) !== false ) {
                    $wholeDB[$ptr] = $db;
                    $ptr++;
        	}
        	fclose($outdat);
        	$outdat = fopen($database,"w");
        	for ($j=0; $j<count($wholeDB); $j++) {
                    if ($wholeDB[$j][0] == $newHike[4]) {
                        fputcsv($outdat,$indxLine);
                    } else {
                        fputcsv($outdat,$wholeDB[$j]);
                    }
        	}
        	fputcsv($outdat,$newHike);
        } else {
        	$outdat = fopen($database,"a");
        	fputcsv($outdat,$newHike);
                #fputs($outdat,"\n");
        }
        fclose($outdat);
        echo "<h2>" . $msg . "</h2>";
    } else if (filter_input(INPUT_POST,'savePg') === 'Submit for Review') {
        # NOT UPDATED FOR VISITOR CENTER HIKES - need process
        $usrdb = '../data/reviewdat.csv';
        $usrHandle = fopen($usrdb,"a");
        if(!fputcsv($usrHandle,$newHike)) {
            die('<p style="color:brown;">Hike Data could not be saved: contact Site Master<p>');
        }
        if ($updateInx) {
        }
        echo '<h2>Hike Data saved for review - you will be notified when ' .  
            'the data has been accepted and posted.</h2>';
        fclose($usrHandle);
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