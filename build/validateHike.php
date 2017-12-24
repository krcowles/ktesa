<?php
session_start();
/* The one case on this site where session data is a great solution: 'Unvalidate'
 * Unvalidate can only happen from this page while live, and to pass
 * the required data to unvalidate.php would be a messy ordeal without 
 * session memory...
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
# Process $hikeName to ensure no html special characters will disrupt
$hike = filter_input(INPUT_POST, 'hpgTitle');
$hikeNo = intval(filter_input(INPUT_POST, 'hno'));
$uid = filter_input(INPUT_POST, 'usr');
$uid = mysqli_real_escape_string($link, $uid);
$status = filter_input(INPUT_POST, 'state');
/* If hikeNo = 0, then this is a brand new page, and both the hike and hikeNo
 * have not yet been saved. Since the user is now saving data, it is time to 
 * establish these parameters in the database...
 */
if ($status === 'new') {
#   Add new record
#    $newRow = insertDbRow($link,'EHIKES',__File__,__LINE__); 
    $newHike = mysqli_real_escape_string($link, $hike);
    $query = "INSERT INTO EHIKES (pgTitle,usrid) VALUES ('{$newHike}','{$uid}');";
    doQuery($link, $query, __File__, __LINE__);
#
#   Update fields one by one so that NULLs can be checked
    $hikeNo = getDbRowNum($link, 'EHIKES', __File__, __LINE__);
    updateDbRow($link, 'EHIKES', $hikeNo, 'stat', 'indxNo', 'new', __File__, __LINE__);
}
/*
 * Note: the next four variables are initialized false as 'save' type cannot
 * save file uploads. If 'validate' type, then fileUploads.php may set them.
 */
$haveGpx = false;
$imageFile1 = false;
$imageFile2 = false;
$propFiles = false;
$actFiles = false;
#
$saveType = filter_input(INPUT_POST, 'saveit');
$valType = filter_input(INPUT_POST, 'valdat');
if (isset($saveType)) {
    $tabTitle = 'Save Form Data';
    $logo = 'Save ' . $hikeName;
    $type = 'Save';
    $status = "new";
}
if (isset($valType)) {
    $tabTitle = 'Validate &amp; Select Images';
    $logo = 'Validate This Hike!';
    $type = 'Validate';
    $status = "upl";
    $nopics = filter_input(INPUT_POST, 'nopix');
    if (!isset($nopics)) {
        $usetsv = true;
        require "getPicDat.php"; # fill ETSV table

    } else {
        $usetsv = false;  # no TSV table entries
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
	<title><?php echo $tabTitle;?></title>
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
<p id="trail"><?php echo $logo;?></p>
<p id="ptype" style="display:none">Validate</p>

<?php
if ($type === 'Save') {
    echo '<div style="margin-left:24px;font-size:18px;">';
    echo '<h2>You have saved the current data on the form</h2>';
    echo '<p >You may continue by clicking the link below, or come back ' .
        'later to work on it by going back to the main page and selecting ' .
        '"Edit Hikes" (New/Active Edits)</p>';
    echo '<a href="enterHike.php?hno=' . $hikeNo . '&usr=' . $uid .
            '">Continue Data Entry</a>';
    echo "<p>Your saved Hike is '" . $hike . "'</p></div>";
        
} else {
    echo '<form id="valsub" target="_blank" action="displayHikePg.php" method="POST">' . "\n";
    echo "<h2>STEP 2: VALIDATE DATA AND SELECT IMAGES</h2>\n";
    echo '<div style="margin-left:24px;font-size:18px;">';
    echo "<h3 style='color:darkBlue;'>Please Note!</h3>\n" . '<p>You have '
        . 'saved new data. Do not go back to the previous page and repeat '
        . 'this step or duplicate data will be created!<br />'
        . 'If it is necessary to "back up" due to an error or omission, '
        . 'please use the "Un-Validate" button below. This will open the '
        . 'enterHike form and delete data from uploaded files which would '
        . 'otherwise be duplicated.<br /><br />';
    echo '<button id="unval">Un-Validate</button><br /><br />'
        . 'OTHERWISE: If you wish to stop here and return later to '
        . 'finish the page, please return to the main page and select '
        . '"Edit Hikes" (New/Active)<br /><br /><br />';
    require "fileUploads.php";
    require "unvalidateData.php";
    echo '<p id="gfile" style="display:none;">' . $gpxLoc . '</p>';
    echo '<p id="tfile" style="display:none;">' . $trkLoc . '</p>';
    echo '<p id="i1file" style="display:none;">' . $img1Loc . '</p>';
    echo '<p id="i2file" style="display:none;">' . $img2Loc . '</p>';
}
?>  
<p style="display:none" id="tsvStat"><?php if ($usetsv) {
    echo "YES";

} else {
    echo "NO";
}?></p>

<?php
/*
 *  hike form entry - both 'save form' and 'validate' need this data
 *  Prep data with mysqli_real_escape_string()
 */
$hname = mysqli_real_escape_string($link, $hike);
$locale = filter_input(INPUT_POST, 'locale');
$loc = mysqli_real_escape_string($link, $locale);
$marker = filter_input(INPUT_POST, 'mstyle');
# default values, altered depending on marker type
$coll = '';
$cg = '';
$cn = '';
if ($marker === 'ctrhike') {
    $mrkr = "At VC";
    $coll = filter_input(INPUT_POST, 'vchike'); # only a number, no escape needed
} elseif ($marker === 'cluster') {
    $mrkr = 'Cluster';
    $cinfo = filter_input(INPUT_POST, 'clusgrp');
    $colon = strpos($cinfo, ":");
    $cg = substr($cinfo, 0, $colon); # 1 or more letters, no escape needed
    $nmlgth = strlen($cinfo) - ($colon + 1);
    $cname = substr($cinfo, $colon+1, $nmlgth);
    $cn = mysqli_real_escape_string($link, $cname);
} elseif ($marker === 'other') {
    $mrkr = 'Normal';
}
$style = filter_input(INPUT_POST, 'htype');
$logistics = mysqli_real_escape_string($link, $style);
$distance = filter_input(INPUT_POST, 'dist');
$dist = mysqli_real_escape_string($link, $distance);
$elevation = filter_input(INPUT_POST, 'elev');
$elev = mysqli_real_escape_string($link, $elevation);
$difficulty = filter_input(INPUT_POST, 'diff');
$diff = mysqli_real_escape_string($link, $difficulty);
$facilities = filter_input(INPUT_POST, 'fac');
$fac = mysqli_real_escape_string($link, $facilities);
$wows = filter_input(INPUT_POST, 'wow_factor');
$wow = mysqli_real_escape_string($link, $wows);
$seasons = filter_input(INPUT_POST, 'seas');
$seasn = mysqli_real_escape_string($link, $seasons);
$exposure = filter_input(INPUT_POST, 'expos');
$expo = mysqli_real_escape_string($link, $exposure);

if ($haveGpx) { # fileUploads will set true, if 'validate' & gpx file present
    # Extract trailhead lat & lng from gpx file
    $cwd = getcwd();
    $bloc = strpos($cwd, "build");
    $basedir = substr($cwd, 0, $bloc);
    $gpxupload = $basedir . 'gpx/' . $hikeGpx;
    $gpxdat = file_get_contents($gpxupload);
    if ($gpxdat === false) {
        $nord = $pstyle . 'Could not read ' . $hikeGpx .
            ': contact Site Master</p>';
        die($nord);
    }
    $trksegloc = strpos($gpxdat, "<trkpt lat=");
    $trksubstr = substr($gpxdat, $trksegloc, 100);
    $latloc = strpos($trksubstr, "lat=") + 5;
    $latend = strpos($trksubstr, '" lon=');
    $latlgth = $latend - $latloc;
    $lat = substr($trksubstr, $latloc, $latlgth);
    $lonloc = strpos($trksubstr, "lon=") + 5;
    $lonend = strpos($trksubstr, ">") - 1;
    $lonlgth = $lonend - $lonloc;
    $lng = substr($trksubstr, $lonloc, $lonlgth);
}
if ($imageFile1) { # fileUploads.php may set true if 'validate' and 1 or 2 present
    $aoimg1 = $hikeOthrImage1;
}
if ($imageFile2) {
    $aoimg2 = $hikeOthrImage2;
}
$url1 = filter_input(INPUT_POST, 'photo1');
$purl1 = mysqli_real_escape_string($link, $url1);
$url2 = filter_input(INPUT_POST, 'photo2');
$purl2 = mysqli_real_escape_string($link, $url2);
$gdirs = filter_input(INPUT_POST, 'dirs');
$dirs = mysqli_real_escape_string($link, $gdirs);
$rawtips = filter_input(INPUT_POST, 'tipstxt');
if (substr($rawtips, 0, 10) !== '[OPTIONAL]') {
    $tips = mysqli_real_escape_string($link, $rawtips);
} else {
    $tips = '';
}
$hikeDetails = filter_input(INPUT_POST, 'hiketxt');
$info = mysqli_real_escape_string($link, $hikeDetails);
# Now the updates (if any) can be stored in the EHIKES database
$query = "UPDATE IGNORE EHIKES SET pgTitle = '{$hname}',stat = '{$status}'," .
        "locale = '{$loc}',logistics = '{$logistics}',marker = '{$mrkr}'," .
        "collection = '{$coll}',cgroup = '{$cg}',cname = '{$cn}'," .
        "diff = '{$diff}',expo = '{$expo}',miles = '{$dist}',feet = '{$elev}'," .
        "fac = '{$fac}',wow = '{$wow}', seasons = '{$seasn}'";
if ($type === 'Validate') {
    if ($haveGpx) {
        $query .= ",gpx = '{$hikeGpx}',trk = '{$trkfile}'";
    }
    if ($imageFile1) {
        $query .= ",aoimg1 = '{$hikeOthrImage1}'";
    }
    if ($imageFile2) {
        $query .= ",aoimg2 = '{$hikeOthrImage2}'";
    }
}
if ($haveGpx) {
    $query .= ",lat = '{$lat}',lng = '{$lng}'";
}
$query .= ",purl1 = '{$url1}',purl2 = '{$url2}',dirs = '{$dirs}',tips = '{$tips}'," .
    "info = '{$info}' WHERE indxNo = '{$hikeNo}';";
doQuery($link, $query, __File__, __LINE__);

/*
 * Next update the EREFS table (a bit lengthy, but necessary to account 
 * for all cases as expressed by the 'if/elseif/else' statements)
 */
$RTypes = $_POST['rtype'];
$RItems1 = $_POST['rit1'];
$RItems2 = $_POST['rit2'];
$RefTypes = [];
$RefItems1 = [];
$RefItems2 = [];
/* get a count of items actually specified: */
$noOfRefs = 0;
for ($w=0; $w<count($RTypes); $w++) { # this includes all, even empty...
    /* Posted data references may not have content other than default label,
     * so test with RItems1, not RTypes. Also, there may be non-sequential data
     * entered in form by user, so form arrays that ARE sequential
     */
    if ($RItems1[$w] !== '') {
        $RefTypes[$noOfRefs] = $RTypes[$w];
        $RefItems1[$noOfRefs] = $RItems1[$w];
        $RefItems2[$noOfRefs] = $RItems2[$w];
        $noOfRefs++;
    }
}
/* also get a count of existing entries in the EREFS table for this hike */
$query = "SELECT refId FROM EREFS WHERE indxNo = '{$hikeNo}';";
$exrows = getDbRowCount($link, $query, __File__, __LINE__);
if ($exrows === 0) {
    /* There are no existing EREFS for this hike; no UPDATES need be performed
     * All posted items, if any, will be INSERTED;
     * If $noOfRefs = 0, nothing will happen.
     */
    for ($r=0; $r<$noOfRefs; $r++) { # covers the case for noOfRefs = 0
        $a = mysqli_real_escape_string($link, $RefTypes[$r]);
        $b = mysqli_real_escape_string($link, $RefItems1[$r]);
        $c = mysqli_real_escape_string($link, $RefItems2[$r]);
        $query = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (" .
            "'{$hikeNo}','{$a}','{$b}','{$c}');";
        doQuery($link, $query, __File__, __LINE__);
    }
} else {
    /* Some records already exist in the EREFS table for this hike;
     * Form an array of refIds for this set of existing records
     * (they may not be sequentially numbered).
     * 
     * NOTE: In the case of DELETE, since id's are renumbered automatically,
     * perform updates PRiOR to DELETE!
     */
    $exids = [];  # the array of refId's for existing records in EREFS
    while ($exdat = mysqli_fetch_row($ecntq)) {
        array_push($exids, $exdat[0]);
    }
    $deletes = false;
    $ups = []; # refIds to be updated
    # one of three situations can exist, as expressed by the following 'ifs'
    if ($noOfRefs > $exrows) {
        # all existing refs will be updated (later), and new ones inserted here:
        for ($a=0; $a<$noOfRefs; $a++) {
            if ($a < $exrows) {
                array_push($ups, $exids[$a]);
            } else {
                $x = mysqli_real_escape_string($link, $RefTypes[$a]);
                $y = mysqli_real_escape_string($link, $RefItems1[$a]);
                $z = mysqli_real_escape_string($link, $RefItems2[$a]);
                $query = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) " .
                    "VALUES ('{$hikeNo}','{$x}','{$y}','{$z}');";
                doQuery($link, $query, __File__, __LINE__);
            }
        }
    } elseif ($noOfRefs < $exrows) {
        # excess rows need to be removed
        for ($b=0; $b<$exrows; $b++) {
            $deletes = true;
            $dels = [];
            if ($b < $noOfRefs) {
                array_push($ups, $exids[$b]);
            } else {
                array_push($dels, $exids[$b]);
            }
        }
    } else {  # both are equal, all existing get updated
        for ($c=0; $c<$noOfRefs; $c++) {
            array_push($ups, $exids[$c]);
        }
    }
    # Now perform the updates: (indxNo is already good for these)
    for ($d=0; $d<count($ups); $d++) {
        $i = mysqli_real_escape_string($link, $RefTypes[$d]);
        $j = mysqli_real_escape_string($link, $RefItems1[$d]);
        $k = mysqli_real_escape_string($link, $RefItems2[$d]);
        $query = "UPDATE EREFS SET rtype = '{$i}',rit1 = '{$j}'," .
            "rit2 = '{$k}' WHERE refId = {$ups[$d]};";
        doQuery($link, $query, __File__, __LINE__);
    }
    if ($deletes) {
        for ($k=0; $k<count($dels); $k++) {
            $query = "DELETE FROM EREFS WHERE refId = {$dels[$k]};";
            doQuery($link, $query, __File__, __LINE__);
        }
    }
}

/*
 * Next update the EGPSDAT table - similar construction to EREFS
 * Start with Proposed Data:
 */
$P_lbls = $_POST['plbl'];
$P_urls = $_POST['purl'];
$P_cots = $_POST['pctxt'];
$Plbls = [];
$Purls = [];
$Pcots = [];
/* get a count of items actually specified: */
$noOfProps = 0;
for ($w=0; $w<count($P_urls); $w++) { # this includes all, even empty...
    /* There may be non-sequential data entered in form by user,
     * so form arrays that ARE sequential
     */
    if ($P_urls[$w] !== '') {
        $Plbls[$noOfProps] = $P_lbls[$w];
        $Purls[$noOfProps] = $P_urls[$w];
        $Pcots[$noOfProps] = $P_cots[$w];
        $noOfProps++;
    }
}
/* also get a count of existing entries in the EGPSDAT table for this hike */
$query = "SELECT datId FROM EGPSDAT WHERE indxNo = '{$hikeNo}' AND " .
        "datType = 'P';";
$prows = getDbRowCount($link, $query, __File__, __LINE__);
if ($prows === 0) {
    /* There is no existing Proposed Data in EGPSDAT for this hike;
     * no UPDATES to be performed. All posted items, if any, will be INSERTED;
     * If $noOfProps = 0, nothing will happen.
     */
    for ($r=0; $r<$noOfProps; $r++) { # covers the case for $noOfProps = 0
        $a = mysqli_real_escape_string($link, $Plbls[$r]);
        $b = mysqli_real_escape_string($link, $Purls[$r]);
        $c = mysqli_real_escape_string($link, $Pcots[$r]);
        $query = "INSERT INTO EGPSDAT (indxNo,datType,label,url," .
            "clickText) VALUES ('{$hikeNo}','P','{$a}','{$b}','{$c}');";
        doQuery($link, $query, __File__, __LINE__);
    }
} else {
    /* Some records already exist in the EGPSDAT table for this hike;
     * Form an array of datId's for this set of existing records
     * (they may not be sequentially numbered).
     * 
     * NOTE: In the case of DELETE, since id's are renumbered automatically,
     * perform updates PRiOR to DELETE!
     */
    $expids = [];  # the array of datId's for existing records in EGPSDAT
    while ($expdat = mysqli_fetch_row($pidq)) {
        array_push($expids, $expdat[0]);
    }
    $deletes = false;
    $ups = []; # refIds to be updated
    # one of three situations can exist, as expressed by the following 'ifs'
    if ($noOfProps > $prows) {
        # all existing 'props' will be updated (later), and new ones inserted here:
        for ($a=0; $a<$noOfProps; $a++) {
            if ($a < $prows) {
                array_push($ups, $expids[$a]);
            } else {
                $x = mysqli_real_escape_string($link, $Plbls[$a]);
                $y = mysqli_real_escape_string($link, $Purls[$a]);
                $z = mysqli_real_escape_string($link, $Pcots[$a]);
                $query = "INSERT INTO EGPSDAT (indxNo,datType,label," .
                    "url,clickText) VALUES ('{$hikeNo}','P','{$x}','{$y}','{$z}');";
                doQuery($link, $query, __File__, __LINE__);
            }
        }
    } elseif ($noOfProps < $prows) {
        # excess rows need to be removed
        for ($b=0; $b<$prows; $b++) {
            $deletes = true;
            $dels = [];
            if ($b < $noOfProps) {
                array_push($ups, $expids[$b]);
            } else {
                array_push($dels, $expids[$b]);
            }
        }
    } else {  # both are equal, all existing get updated
        for ($c=0; $c<$noOfProps; $c++) {
            array_push($ups, $expids[$c]);
        }
    }
    # Now perform the updates: (indxNo & datType are already good for these)
    for ($d=0; $d<count($ups); $d++) {
        $i = mysqli_real_escape_string($link, $Plbls[$d]);
        $j = mysqli_real_escape_string($link, $Purls[$d]);
        $k = mysqli_real_escape_string($link, $Pcots[$d]);
        $query = "UPDATE EGPSDAT SET label = '{$i}',url = '{$j}'," .
            "clickText = '{$k}' WHERE datId = {$ups[$d]};";
        doQuery($link, $query, __File__, __LINE__);
    }
    if ($deletes) {
        for ($k=0; $k<count($dels); $k++) {
            $query = "DELETE FROM EGPSDAT WHERE datId = {$dels[$k]};";
            doQuery($link, $query, __File__, __LINE__);
        }
    }
}
/*
 * Finish with Actual Data - modified version of Proposed Data code
 */
$A_lbls = $_POST['albl'];
$A_urls = $_POST['aurl'];
$A_cots = $_POST['actxt'];
$Albls = [];
$Aurls = [];
$Acots = [];
/* get a count of items actually specified: */
$noOfActs = 0;
for ($w=0; $w<count($A_urls); $w++) { # this includes all, even empty...
    /* There may be non-sequential data entered in form by user,
     * so form arrays that ARE sequential
     */
    if ($A_urls[$w] !== '') {
        $Albls[$noOfActs] = $A_lbls[$w];
        $Aurls[$noOfActs] = $A_urls[$w];
        $Acots[$noOfActs] = $A_cots[$w];
        $noOfActs++;
    }
}
/* also get a count of existing entries in the EGPSDAT table for this hike */
$query = "SELECT datId FROM EGPSDAT WHERE indxNo = '{$hikeNo}' AND " .
        "datType = 'A';";
$arows = getDbRowCount($link, $query, __File__, __LINE__);
if ($arows === 0) {
    /* There is no existing Proposed Data in EGPSDAT for this hike;
     * no UPDATES to be performed. All posted items, if any, will be INSERTED;
     * If $noOfProps = 0, nothing will happen.
     */
    for ($r=0; $r<$noOfActs; $r++) { # covers the case for $noOfActs = 0
        $a = mysqli_real_escape_string($link, $Albls[$r]);
        $b = mysqli_real_escape_string($link, $Aurls[$r]);
        $c = mysqli_real_escape_string($link, $Acots[$r]);
        $query = "INSERT INTO EGPSDAT (indxNo,datType,label,url," .
            "clickText) VALUES ('{$hikeNo}','A','{$a}','{$b}','{$c}');";
        doQuery($link, $query, __File__, __LINE__);
    }
} else {
    /* Some records already exist in the EGPSDAT table for this hike;
     * Form an array of datId's for this set of existing records
     * (they may not be sequentially numbered).
     * 
     * NOTE: In the case of DELETE, since id's are renumbered automatically,
     * perform updates PRiOR to DELETE!
     */
    $exaids = [];  # the array of datId's for existing records in EGPSDAT
    while ($exadat = mysqli_fetch_row($aidq)) {
        array_push($exaids, $exadat[0]);
    }
    $deletes = false;
    $ups = []; # refIds to be updated
    # one of three situations can exist, as expressed by the following 'ifs'
    if ($noOfActs > $arows) {
        # all existing 'acts' will be updated (later), and new ones inserted here:
        for ($a=0; $a<$noOfActs; $a++) {
            if ($a < $arows) {
                array_push($ups, $exaids[$a]);
            } else {
                $x = mysqli_real_escape_string($link, $Albls[$a]);
                $y = mysqli_real_escape_string($link, $Aurls[$a]);
                $z = mysqli_real_escape_string($link, $Acots[$a]);
                $query = "INSERT INTO EGPSDAT (indxNo,datType,label," .
                    "url,clickText) VALUES ('{$hikeNo}','A','{$x}','{$y}','{$z}');";
                doQuery($link, $query, __File__, __LINE__);
            }
        }
    } elseif ($noOfActs < $arows) {
        # excess rows need to be removed
        for ($b=0; $b<$arows; $b++) {
            $deletes = true;
            $dels = [];
            if ($b < $noOfActs) {
                array_push($ups, $exaids[$b]);
            } else {
                array_push($dels, $exaids[$b]);
            }
        }
    } else {  # both are equal, all existing get updated
        for ($c=0; $c<$noOfActs; $c++) {
            array_push($ups, $exaids[$c]);
        }
    }
    # Now perform the updates: (indxNo & datType are already good for these)
    for ($d=0; $d<count($ups); $d++) {
        $i = mysqli_real_escape_string($link, $Albls[$d]);
        $j = mysqli_real_escape_string($link, $Aurls[$d]);
        $k = mysqli_real_escape_string($link, $Acots[$d]);
        $query = "UPDATE EGPSDAT SET label = '{$i}',url = '{$j}'," .
            "clickText = '{$k}' WHERE datId = {$ups[$d]};";
        doQuery($link, $query, __File__, __LINE__);
    }
    if ($deletes) {
        for ($k=0; $k<count($dels); $k++) {
            $query = "DELETE FROM EGPSDAT WHERE datId = {$dels[$k]};";
            doQuery($link, $query, __File__, __LINE__);
        }
    }
}
/*
 * If 'Save', there is no tsv data; nor are there any file uploads.
 * The remainder of this script displays photos for selection and inclusion
 * on both the hike page and on the GPSV map, and can be skipped in the case
 * of a 'Save'.
 */
if ($type === 'Validate') {
    if ($usetsv) {
        $pgType = 'Validate';
        $ptable = 'ETSV';
        require "photoSelect.php";
    }
    if ($usetsv) {
        $passtsv = "YES";
    } else {
        $passtsv = "NO";
    }
    echo '<input type="hidden" name="usepics" value="' . $passtsv . '" />' . "\n";
    echo '<input type="hidden" name="hikeno" value="' . $hikeNo . '" />' . "\n";
    echo "</form>\n";
}
?>

<script src="validateHike.js"></script>
</body>

</html>
