<?php 
	session_start();
 ?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Write Hike File</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Write hike data to TblDB.csv" />
    <meta name="author"
        content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
        content="nofollow" />
    <link href="../styles/hikes.css"
        type="text/css" rel="stylesheet" />
</head>

<body>
<div style="margin-left:12px;padding:8px;">
<?php
    /* get last used hike No.. */
    $database = '../data/database.csv';
    $handle = fopen($database, "c+");
    if ($handle !== false) {
        while ( ($hikeLine = fgetcsv($handle)) !== false ) {
            $lastIndx = $hikeLine[0];
        }
    } else {
        echo "<p>Could not open database file</p>";
    }
    echo " ...Start import...";
    # NEW HIKE INDX STARTS AT LAST INDX + 1:
    $newHike[0] = intval($lastIndx) + 1;
    $newHike[1] = filter_input(INPUT_POST,'hname');
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
    $newHike[4] = '';  // NOTE: Index page cluster string updated in previous displayHikePg.php
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
    # Array to determine which files to overwrite, if any
    $saveRules = $_SESSION['filesaves'];
    $rules = explode("^",$saveRules);
    # Estabish directory locations
    $cwd = getcwd();
    $basedirpos = strpos($cwd."/build");
    $basedir = $cwd.substring(0,$basedirpos);
    echo "BASEDIR IS: " . $basedir;
    $uploads = '/tmp/';
    /* DATA WILL BE SAVED TO DIFFERENT PLACES DEPENDING ON SUBMITTER
     * Site Masters: Hike Data will be saved to the standard site usign the
     *   database.csv; this means files will be moved from build/tmp to their
     *   correct respective locations;
     * Registered Users: Data will be saved to the pending.csv database (also
     *   in the data directory), and the tmp files will not be moved.
     */
    $passwd = filter_input(INPUT_POST,'mpass');
    if (filter_input(INPUT_POST,'savePg') === 'Site Master') {
        # There is always a tsv file...
        if ( ($rules[0] === 'YES' && $rules[1] === 'YES') || $rules[0] === 'NO' ) {
            $oldLoc = $cwd . $uploads . 'gpsv/' . $newHike[14];
            $newLoc = $basedir . '/gpsv/' . $newHike[14];
            if (!rename($oldLoc,$newLoc)) {
                die("COULD NOT MOVE TSV FILE");
            } else {
                echo "Successfully moved tsv file";
            }
        }
        #fputcsv($handle,$newHike);
    } else if (filter_input(INPUT_POST,'savePg') === 'Submit for Review') {
        echo "Registered user: files not moved";
    } else {
        die("Contact Site Master: Submission not recognized");
    }
    
    
    echo "<h1>HIKE SUCCESSFULLY SAVED!</h1>";
    echo "<h2>" . $msg . "</h2>";
    # DEBUG OUTPUT ---
    /*
    $listOut = array("Hike Index No.","Hike Name","Locale","Marker","Indx. Cluster String","Cluster Letter",
            "Hike Type","Length","Elevation Change","Difficulty","Facilities","Wow Factor",
            "Seasons","Exposure","tsv File","Geomap","Elevation Chart","Geomap GPX",
            "Track File","Latitude","Longitude","Additonal Image1","Additional Image2",
            "Ken's Photo Album","Tom's Photo Album","Google Directions","OBS: Trail Tips?","OBS: Page.html",
            "Cluster Group Label","Row0 HTML","Row1 HTML","Row2 HTML","Row 3HTML","Row4 HTML",
            "Row5 HTML","Captions","Photo Links","Tips Text","Hike Info","References","Proposed Data",
            "Actual Data");
    echo "<br />NEW: ";
    for ($i=0; $i<42; $i++) {
            if ($i === 29 || $i === 30 || $i === 31 || $i === 32 || $i === 33 || $i === 34) {
                    echo "Not outputting row" . ($i - 29) . " ;";
            } else {
                    echo $listOut[$i] . "-> " . $newHike[$i] . "<br />";
            }
    }
    */
?>
</div>
<div data-ptype="hike" data-indxno="<?php echo $newHike[0];?>" style="padding:16px;" id="more">
    <button style="font-size:16px;color:DarkBlue;" id="same">Edit this hike</button><br />
    <button style="font-size:16px;color:DarkBlue;" id="diff">Edit a different hike</button>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="saveHike.js"></script>
<script src="postEdit.js"></script>
</body>
</html>