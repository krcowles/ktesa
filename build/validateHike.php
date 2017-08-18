<?php
    $database = '../data/database.xml';
    $pstyle = '<p style="margin-left:16px;font-size:18px;">';
    # Process $hikeName to ensure no html special characters will disrupt
    $hike = filter_input(INPUT_POST,'hpgTitle');
    $hikeName = htmlspecialchars($hike);
    $hikeNo = intval(filter_input(INPUT_POST,'hno'));
    $xml = simplexml_load_file($database);
    if ($xml === false) {
        $errmsg = '<p style="margin-left:20px;color:red;font-size:18px;">' .
            'Could not load xml database: contact Site Master</p>';
        die ($errmsg);
    }
    $saveType = filter_input(INPUT_POST,'saveit');
    $valType = filter_input(INPUT_POST,'valdat');
    if ( isset($saveType)) {
        $tabTitle = 'Save Form Data';
        $logo = 'Save ' . $hikeName;
        $type = 'Save';
    }
    if ( isset($valType) ) {
        $tabTitle = 'Validate &amp; Select Images';
        $logo = 'Validate This Hike!';
        $type = 'Validate';
        $nopics = filter_input(INPUT_POST,'nopix');
        if ( !isset($nopics) ) {
            $usetsv = true;
            require "getPicDat.php";
            
        } else {
            $usetsv = false;
        }
        $imageFiles = false;  # change if files are encountered
        $gpsDatFiles = false;  # ditto
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

<?php
    if ($type === 'Save') {
        echo '<div style="margin-left:24px;font-size:18px;">';
        echo '<h2>You have saved the current data on the form</h2>';
        echo '<p >You may continue on that page, or come back later to work '
            . 'on it:<br />Use: [server]/[project]/build/enterHike.php?hikeNo='
            . $hikeNo . '</p>';
        echo '<p>Your Hike No is ' . $hikeNo . ', and the hike saved is ' .
            $hikeName . '</p></div>';
        
    } else {
        echo '<form target="_blank" action="displayHikePg.php" method="POST">' . "\n";
        echo "<h2>STEP 2: VALIDATE DATA AND SELECT IMAGES</h2>\n";
        echo '<div style="margin-left:24px;font-size:18px;">';
        echo "<h3>Please Note!</h3>\n" . '<p>You have saved new data. '
                . 'Do not go back to the previous page and repeat this step or '
                . 'duplicate data will be created.<br />If you wish to stop '
                . 'here and return later to select photos, please use the'
                . 'following url:<br />[server]/[project]/build/finishPage.php?'
                . 'hikeNo=' . $hikeNo . ' after exiting this page (do not select '
                . 'the "Create Page.." button</p>';
        require "fileUploads.php";
    }
?>  
<p style="display:none" id="tsvStat"><?php if ($usetsv) { echo "YES"; } else { echo "NO"; }?></p>

<?php
/*
 *  hike form entry - both 'save form' and 'validate' need this data
 *  NOTE: $hikeNo already saved previously and should not be over-written
 */
# from here on out, $hikeNo is decremented as hikes start at 1, not 0:
$hikeNo--;
$xml->row[$hikeNo]->pgTitle = $hikeName;

$xml->row[$hikeNo]->locale = filter_input(INPUT_POST,'locale');
$marker = filter_input(INPUT_POST,'mstyle');
$xml->row[$hikeNo]->marker = $marker;
if ($marker === 'ctrhike') {
    echo 'Saw cluster hike';
    $xml->row[$hikeNo]->clusterStr = filter_input(INPUT_POST,'vchike');
} elseif ($marker === 'cluster') {
    $belongsTo = filter_input(INPUT_POST,'clusgrp');
    $xml->row[$hikeNo]->clusGrp = $belongsTo;
    foreach ($xml->row as $row) {
        if ($row->clusGrp == $belongsTo) {
            $cname = $row->cgName->__toString();
            break;
        }
    }
    $xml->row[$hikeNo]->cgName = $cname;
}
$xml->row[$hikeNo]->logistics = filter_input(INPUT_POST,'htype');
$xml->row[$hikeNo]->miles = filter_input(INPUT_POST,'dist');
$xml->row[$hikeNo]->feet = filter_input(INPUT_POST,'elev');
$xml->row[$hikeNo]->difficulty = filter_input(INPUT_POST,'diff');
$xml->row[$hikeNo]->facilities = filter_input(INPUT_POST,'fac');
$xml->row[$hikeNo]->wow = filter_input(INPUT_POST,'wow_factor');
$xml->row[$hikeNo]->seasons = filter_input(INPUT_POST,'seas');
$xml->row[$hikeNo]->expo = filter_input(INPUT_POST,'expos');

if ($haveGpx) {
    $xml->row[$hikeNo]->gpxfile = $hikeGpx;
    $xml->row[$hikeNo]->trkfile = $trkfile;
    # Extract trailhead lat & lng from gpx file
    $cwd = getcwd();
    $bloc = strpos($cwd,"build");
    $basedir = substr($cwd,0,$bloc);
    $gpxupload = $basedir . 'gpx/' . $hikeGpx;
    $gpxdat = file_get_contents($gpxupload);
    if ($gpxdat === false) {
        $nord = $pstyle . 'Could not read ' . $hikeGpx . 
            ': contact Site Master</p>';
        die($nord);
    }
    $trksegloc = strpos($gpxdat,"<trkpt lat=");
    $trksubstr = substr($gpxdat,$trksegloc,100);
    $latloc = strpos($trksubstr,"lat=") + 5;
    $latend = strpos($trksubstr,'" lon=');
    $latlgth = $latend - $latloc;
    $xml->row[$hikeNo]->lat = substr($trksubstr,$latloc,$latlgth);
    $lonloc = strpos($trksubstr,"lon=") + 5;
    $lonend = strpos($trksubstr,">") - 1;
    $lonlgth = $lonend - $lonloc;
    $xml->row[$hikeNo]->lng = substr($trksubstr,$lonloc,$lonlgth);
}
if ($imageFiles) {
    if ($hikeOthrImage1 !== '') {
        $xml->row[$hikeNo]->aoimg1 = $hikeOthrImage1;
    }
    if ($hikeOthrImage2 !== '') {
        $xml->row[$hikeNo]->aoimg2 = $hikeOthrImage2;
    }
}
$xml->row[$hikeNo]->mpUrl = filter_input(INPUT_POST,'photo1');
$xml->row[$hikeNo]->spUrl = filter_input(INPUT_POST,'photo2');
$xml->row[$hikeNo]->dirs = filter_input(INPUT_POST,'dirs');
$rawtips = filter_input(INPUT_POST,'tipstxt');
if (substr($rawtips,0,10) !== '[OPTIONAL]') {
    $xml->row[$hikeNo]->tipsTxt = $rawtips;
    $tipsTxt = $rawtips;
} else {
    $tipsTxt = '';
}
$hikeDetails = filter_input(INPUT_POST,'hiketxt');
$xml->row[$hikeNo]->hikeInfo = $hikeDetails;

# don't know how to filter arrays:
$hikeRefTypes = $_POST['rtype'];
$hikeRefItems1 = $_POST['rit1'];
$hikeRefItems2 = $_POST['rit2'];
/* get a count of items actually specified: */
for ($w=0; $w<count($hikeRefTypes); $w++) {
    # Imported data references may not have content other than default label
    if ($hikeRefItems1[$w] == '') {
        $noOfRefs = $w;
        break;
    }
}
$newrefs = $xml->row[$hikeNo]->refs->addChild('ref');
if ($noOfRefs === 0) {
    $newrefs->addChild('rtype','No References Found');
} else {
    for ($r=0; $r<$noOfRefs; $r++) {
        $newrefs->addChild('rtype',$hikeRefTypes[$r]);
        $newrefs->addChild('rit1',$hikeRefItems1[$r]);
        $newrefs->addChild('rit2',$hikeRefItems2[$r]);
        if ($r < $noOfRefs-1) {
            $newrefs = $xml->row[$hikeNo]->refs->addChild('ref');
        }
    }
}
# Proposed and Actual GPS Maps & Data:
if ($gpsDatFiles) {
    # PROPOSED:
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
    $newPs = $xml->row[$hikeNo]->dataProp->addChild('prop');
    for ($p=0; $p<$noOfPDats; $p++) {
        $newPs->addChild('plbl',$hikePDatLbls[$p]);
        $newPs->addChild('purl',$hikePDatUrls[$p]);
        $newPs->addChild('pcot',$hikePDatCTxts[$p]);
        if ($p < $noOfPDats-1) {
            $newPs = $xml->row[$hikeNo]->dataProp->addChild('prop');
        }
    }
    # ACTUAL:
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
    $newAs = $xml->row[$hikeNo]->dataAct->addChild('act');
    for ($q=0; $q<$noOfADats; $q++) {
        $newAs->addChild('albl',$hikeADatLbls[$q]);
        $newAs->addChild('aurl',$hikeADatUrls[$q]);
        $newAs->addChild('acot',$hikeADatCTxts[$q]);
        if ($q < $noOfADats-1) {
            $newAs = $xml->row[$hikeNo]->dataAct->addChild('act');
        }
    }  
}
# Done saving data for database: if 'Save Data', then end here....
$xml->asXML($database);
/*
 * If 'Save', there is no tsv data; nor are there any file uploads.
 * The remainder of this script displays photos for selection and inclusion
 * on both the hike page and on the GPSV map, and can be skipped in the case
 * of a 'Save'.
 */
if ($type === 'Validate') {
    if ($usetsv) {
        $picno = 0;
        $phNames = [];
        $phPics = [];
        $phWds = [];
        $rowHt = 220; 
        foreach ($xml->row[$hikeNo]->tsv->picDat as $imgData) {
            $phNames[$picno] = $imgData->title;
            $phPics[$picno] = $imgData->mid;
            $pHeight = $imgData->imgHt;
            $aspect = $rowHt/$pHeight;
            $pWidth = $imgData->imgWd;
            $phWds[$picno] = floor($aspect * $pWidth);
            $picno += 1;
        }
        $mdat = $xml->row[$hikeNo]->tsv->asXML();
        $mdat = preg_replace('/\n/','', $mdat);
        $mdat = preg_replace('/\t/','', $mdat);
    }
    echo '<h4 style="text-indent:16px">Please check the boxes corresponding to ' .
        'the pictures you wish to include on the new page:</h4>' . "\n";
    echo '<div style="position:relative;top:-14px;margin-left:16px;">' .
        '<input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;' .
        'Use All Photos on Hike Page<br />' . "\n" .
        '<input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;' .
        'Use All Photos on Map' . "\n";
    echo "</div>\n";
    echo '<div style="margin-left:16px;">' . "\n";

    for ($i=0; $i<$picno; $i++) {
        echo '<div class="selPic" style="width:' . $phWds[$i] . 'px;float:left;'
                . 'margin-left:2px;margin-right:2px;">';
        echo '<input class="hpguse" type="checkbox" name="pix[]" value="' .  $phNames[$i] .
            '" />Display&nbsp;&nbsp;';
        echo '<input class="mpguse" type="checkbox" name="mapit[]" value="' . $phNames[$i] .
             '" />Map<br />' . "\n";
        echo '<img class="allPhotos" height="200px" width="' . $phWds[$i] . 'px" src="' .
                $phPics[$i] . '" alt="' . $phNames[$i] . '" />' . "\n";
        echo "</div>\n";
    }
    echo "</div>\n";



    echo '<div style="width:200px;position:relative;top:90px;left:20px;float:left;">' .
        '<input type="submit" value="Create Page w/This Data" /><br /><br />' . "\n";
    echo "</div>\n";

    echo '<div class="popupCap"></div>' . "\n";
    
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
<script src="../scripts/jquery-1.12.1.js"></script>
<script type="text/javascript">
    var mouseDat = $.parseXML("<?php echo $mdat;?>");
    var phTitles = [];
    var phDescs = [];
</script>
<script src="validateHike.js"></script>
<script src="../scripts/picPops.js"></script>

</body>

</html>