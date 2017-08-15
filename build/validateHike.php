<?php
    $pstyle = '<p style="margin-left:16px;font-size:18px;">';
    # Process $hikeName to ensure no html special characters will disrupt
    $hike = filter_input(INPUT_POST,'hpgTitle');
    $hikeName = htmlspecialchars($hike);
    $hikeNo = intval(filter_input(INPUT_POST,'hno'));
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
        echo '<p>You may continue on that page, or come back later to work on it</p>';
        echo '<p>Your Hike No is ' . $hikeNo . ', and the hike saved is ' .
            $hikeName . '</p></div>';
        
    } else {
        echo '<h2>STEP 2: VALIDATE DATA AND SELECT IMAGES</h2>';
        echo '<form target="_blank" action="displayHikePg.php" method="POST">';
        # Check to see if pictures will be used for this page
        $nopics = filter_input(INPUT_POST,'nopix');
        if ( !isset($nopics) ) {
            $usetsv = true;
            require "getPicDat.php";
        } else {
            $usetsv = false;
        }
        # Peform any uploads and file validation & summaries
        require "fileUploads.php";
    }
?>  
<p style="display:none" id="tsvStat"><?php if ($usetsv) { echo "YES"; } else { echo "NO"; }?></p>

<?php
/*
 *  hike form entry - both 'save form' and 'validate' need this data
 *  NOTE: $hikeNo already saved previously and should not be over-written
 */
$xml = simplexml_load_file('../data/database.xml');
if ($xml === false) {
    $errmsg = '<p style="margin-left:20px;color:red;font-size:18px;">' .
        'Could not load xml database: contact Site Master</p>';
    die ($errmsg);
}
# from here on out, $hikeNo is decremented as hikes start at 1, not 0:
$hikeNo--;
$xml->row[$hikeNo]->pgTitle = $hikeName;

$xml->row[$hikeNo]->locale = filter_input(INPUT_POST,'locale');
$marker = filter_input(INPUT_POST,'mstyle');
$xml->row[$hikeNo]->marker = $marker;
if ($marker === 'ctrhike') {
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
    $gpxupload = getcwd() . '/' . $uploads . 'gpx/' . $hikeGpx;
    $gpxdat = file_get_contents($gpxUpload);
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
    }  
}
/*
if ($usetsv) {
    # place xml declaration and container 'wrapper' on tsvStr for importing as xml
    $photoXml = "<?xml version='1.0'?>\n<photos>\n" . $xmlTsvStr . "</photos>\n";
    $picXml = simplexml_load_string($photoXml);
    if ($picXml === false) {
        $noxml = '<p style="color:brown;margin-left:8px;font-size:18px;>' .
                'Photo data contained in xml string would not load: Contact ' .
                'Site Master</p>';
        die($noxml);
    }
    $picno = 0;
    $phNames = [];
    $phPics = [];
    $phWds = [];
    $rowHt = 220; 
    foreach ($picXml->picDat as $imgData) {
        $phNames[$picno] = $imgData->title;
        $phPics[$picno] = $imgData->mid;
        $pHeight = $imgData->imgHt;
        $aspect = $rowHt/$pHeight;
        $pWidth = $imgData->imgWd;
        $phWds[$picno] = floor($aspect * $pWidth);
        $picno += 1;
    }
    $mdat = preg_replace('/\n/','', $photoXml);
    $mdat = preg_replace('/\t/','', $mdat);
 * 
 */
}
?>

<?php /*
    if ($tipsTxt !== '') {
        echo '<h2 style="text-align:center;">Hike Tips Text:</h2>';
        echo '<div id="trailTips" style="margin:8px;"><img id="tipPic" .
                src="../images/tips.png" alt="special notes icon" />';
        echo '<p id="tipHdr">TRAIL TIPS!</p><p id="tipNotes">' .
                $tipsTxt . '</p></div>';
    }
 * 
 */
?>
<!-- <h2 style="text-align:center;">Hike Information:</h2> -->
<?php 
    #echo '<p id="hikeInfo" style="text-indent:8px;">' . $hikeDetails . '</p>' . "\n";
?>
<!-- <h2>Hike References:</h2> -->
<?php 
    /* There SHOULD always be at least one reference, however, if there is not,
       a message will appear in this section: No References Found */
/*
    echo '<fieldset>' . "\n" . '<legend id="fldrefs">References &amp; Links</legend>' . "\n";
    echo "\t" . '<ul id="refs">' . "\n";
    $completeRef = "<?xml version='1.0'?>\n" . $refXmlStr . "\n";
    $xmlRef = simplexml_load_string($completeRef);
    if ($xmlRef === false) {
        $norefs = $msgStyle . 'Could not load $xmlRef: contact Site Master</p>';
        die ($norefs);
    }
    foreach ($xmlRef->ref as $refItem) {
        if ($refItem->rtype == 'b' || $refItem->rtype == 'p') {
            if ($refItem->rtype == 'b') {
                $refLbl = 'Book';
            } else {
                $refLbl = 'Photo Essay';
            }
            echo "\t\t<li>" . $refLbl . ": <em>" . $refItem->rit1 . "</em>" .
                $refItem->rit2 . "</li>\n";
        } elseif ($refItem->rtype == 'n') {
            echo "\t\t<li>" . $refItem->rit1 . "</li>\n";
        } else {
            switch ($refItem->rtype) {
                case 'a':
                    $refLbl = 'App';
                    break;
                case 'd':
                    $refLbl = 'Downloadable Doc';
                    break;
                case 'g':
                    $refLbl = 'Meetup Group';
                    break;
                case 'l':
                    $refLbl = 'Blog' ;
                    break;
                case 'm':
                    $refLbl = 'Magazine';
                    break;
                case 'o':
                    $refLbl = 'On-line Map';
                    break;
                case 'r':
                    $refLbl = 'Related Link';
                    break;
                case 's':
                    $refLbl = 'News Article';
                    break;
                case 'w':
                    $refLbl = 'Website';
                    break;
            }
            echo "\t\t<li>" . $refLbl . ': <a href="' . $refItem->rit1 .
                '" target="_blank"> ' . $refItem->rit2 . "</a></li>\n";
        }
    }
    echo "\t</ul>\n</fieldset>\n";
    
 * 
 */
    /* OPTIONAL INFO: GPS Maps & Data fieldset
     * No h2 header is used here: just display if present
     */
/*
    $combinedStr = $xmlPDat . $xmlADat;
    $completeGPSDat = "<?xml version='1.0'?>\n<gpsdat>\n" . $combinedStr . "</gpsdat>\n";
    $gpsdatSection = simplexml_load_string($completeGPSDat);
    if ($gpsdatSection === false) {
        $nodat = $msgStyle . 'Could not load $gpsdatSection xml: contact Site Master</p>';
        die ($nodat);
    }
    $noOfProps = 0;
    $prop1 = [];
    $prop2 = [];
    $prop3 = [];
    $noOfActs = 0;
    $act1 = [];
    $act2 = [];
    $act3 = [];
    foreach ($gpsdatSection->dataProp as $gpspdat) {
        if (strlen($gpspdat->prop) !== 0) {
            foreach ($gpspdat->prop as $placeProp) {
                $prop1[$noOfProps] = $placeProp->plbl;
                $prop2[$noOfProps] = $placeProp->purl;
                $prop3[$noOfProps] = $placeProp->pcot;
                $noOfProps++;
            }
        }
    }
    foreach ($gpsdatSection->dataAct as $gpsadat) {
        if (strlen($gpsadat->act) !== 0) {
            foreach ($gpsadat->act as $placeAct) {
                $act1[$noOfActs] = $placeAct->albl;
                $act2[$noOfActs] = $placeAct->aurl;
                $act3[$noOfActs] = $placeAct->acot;
                $noOfActs++;
            }
        }
    }
    if ($noOfProps > 0 || $noOfActs > 0) {
        echo '<fieldset>' . "\n" . '<legend id="flddat">GPS Maps &amp; Data</legend>' . "\n";
        if ($noOfProps > 0) {
            echo '<p id="proptitle">- Proposed Hike Data</p><ul id="plinks">' . "\n";
            for ($a=0; $a<$noOfProps; $a++) {
                $tmploc = $prop2[$a];
                if (strpos($tmploc,'../maps/') !== false) {
                    $tmpurl = str_replace('../maps/','tmp/maps/',$tmploc);
                } elseif (strpos($tmploc,'../gpx/') !== false) {
                    $tmpurl = str_replace('../gpx/','tmp/gpx/',$tmploc);
                }
                echo "\t<li>" . $prop1[$a] . ' <a href="' . $tmpurl .
                        '" target="_blank"> ' . $prop3[$a] . '</a></li>' . "\n";
            }
            echo "\t</ul>\n";
        }
        if ($noOfActs > 0) {
            echo '<p id="acttitle">- Actual Hike Data</p><ul id="alinks">' . "\n";
            for ($b=0; $b<$noOfActs; $b++) {
                $tmploc = $act2[$b];
                if (strpos($tmploc,'../maps/') !== false) {
                    $tmpurl = str_replace('../maps/','tmp/maps/',$tmploc);
                } elseif (strpos($tmploc,'../gpx/') !== false) {
                    $tmpurl = str_replace('../gpx/','tmp/gpx/',$tmploc);
                }
                echo "\t<li>" . $act1[$b] . ' <a href="' . $tmpurl .
                        '" target="_blank"> ' . $act3[$b] . '</a></li>' . "\n";
            }
            echo "\t</ul>\n";
        }  
        echo "</fieldset>\n";
    }
 * 
 */
?>	

<div id="showpics">
<h4 style="text-indent:8px">Please check the boxes corresponding to the pictures you wish
	to include on the new page:</h4>
<div style="position:relative;top:-14px;">
    <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
    Use All Photos on Hike Page<br />
    <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
    Use All Photos on Map
</div>
<?php
    for ($i=0; $i<$picno; $i++) {
        echo '<div class="selPic" style="width:' . $phWds[$i] . 'px;float:left;'
                . 'margin-left:2px;margin-right:2px;">';
        echo '<input class="hpguse" type="checkbox" name="pix[]" value="' .  $phNames[$i] .
            '" />Use&nbsp;&nbsp;';
        echo '<input class="mpguse" type="checkbox" name="mapit[]" value="' . $phNames[$i] .
             '" />Map<br />';
        echo '<img class="allPhotos" height="200px" width="' . $phWds[$i] . 'px" src="' .
                $phPics[$i] . '" alt="' . $phNames[$i] . '" />';
        echo '</div>';
    }
    
?>
</div>

<div style="width:200px;position:relative;top:90px;left:20px;float:left;">
    <input type="submit" value="Create Page w/This Data" /><br /><br />
</div>

<div class="popupCap"></div>

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
<input type="hidden" name="gpx" value="<?php echo $hikeGpx;?>" />
<input type="hidden" name="json"  value="<?php echo $hikeJSON;?>" />
<input type="hidden" name="img1"  value="<?php echo $hikeOthrImage1;?>" />
<input type="hidden" name="img2"  value="<?php echo $hikeOthrImage2;?>" />
<input type="hidden" name="dfiles" value="<?php echo $datfiles;?>" />
<input type="hidden" name="mrkr"  value="<?php echo $hikeMarker;?>" />
<input type="hidden" name="phot1" value="<?php echo $hikePurl1;?>" />
<input type="hidden" name="phot2" value="<?php echo $hikePurl2;?>" />
<input type="hidden" name="gdirs" value="<?php echo $hikeDir;?>" />
<input type="hidden" name="usepics" value="<?php if ($usetsv) { echo "YES"; } else { echo "NO"; }?>" />
</form>

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