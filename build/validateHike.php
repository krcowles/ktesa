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

<h2>STEP 2: VALIDATE DATA AND SELECT IMAGES</h2>

<form target="_blank" action="displayHikePg.php" method="POST">

<?php
    # Process $hikeName to ensure no html special characters will disrupt
    $hike = filter_input(INPUT_POST,'hpgTitle');
    $hikeName = htmlspecialchars($hike);
    # Get the gpx filename and derive the 'baseName' to precede all file extensions
    $gpxInput = basename($_FILES['gpxname']['name']);
    $ext = strrpos($gpxInput,".");
    $baseName = substr($gpxInput,0,$ext);
    # Check to see if pictures will be used for this page
    $nopics = filter_input(INPUT_POST,'nopix');
    if ( !isset($nopics) ) {
        $usetsv = true;
        require "getPicDat.php";
    } else {
        $usetsv = false;
    }
    /*  Default values for identifying previously saved files 
     *  and whether or not to overwite them when saving the page
     */
    $dupGpx = 'NO';
    $owGpx = 'NO';
    $dupJSON = 'NO';
    $owJSON = 'NO';
    $dupImg1 = 'NO';
    $owImg1 = 'NO';
    $dupImg2 = 'NO';
    $owImg2 = 'NO';
    $dupPmap = 'NO';
    $owPmap = 'NO';
    $dupPgpx = 'NO';
    $owPgpx = 'NO';
    $dupAmap = 'NO';
    $owAmap = 'NO';
    $dupAgpx = 'NO';
    $owAgpx = 'NO';
    # Peform any uploads and file validation & summaries
    require "fileUploads.php";
    
?>  
<p style="display:none" id="tsvStat"><?php if ($usetsv) { echo "YES"; } else { echo "NO"; }?></p>
<!-- Hidden Inputs Carrying File Upload Status --> 
<input type="hidden" name="gpxf" value="<?php echo $dupGpx;?>" />
<input id="overGpx" type="hidden" name="owg" value="<?php echo $owGpx;?>" />
<input type="hidden" name="jsonf" value="<?php echo $dupJSON;?>" />
<input id="overJSON" type="hidden" name="owj" value="<?php echo $owJSON;?>" />
<input type="hidden" name="img1f" value="<?php echo $dupImg1;?>" />
<input id="overImg1" type="hidden" name="ow1" value="<?php echo $owImg1;?>" />
<input type="hidden" name="img2f" value="<?php echo $dupImg2;?>" />
<input id="overImg2" type="hidden" name="ow2" value="<?php echo $owImg2;?>" />
<input type="hidden" name="pmapf" value="<?php echo $dupPmap;?>" />
<input id="overPmap" type="hidden" name="owpm" value="<?php echo $owPmap;?>" />
<input type="hidden" name="pgpxf" value="<?php echo $dupPgpx;?>" />
<input id="overPgpx" type="hidden" name="owpg" value="<?php echo $owPgpx;?>" />
<input type="hidden" name="amapf" value="<?php echo $dupAmap;?>" />
<input id="overAmap" type="hidden" name="owam" value="<?php echo $owAmap;?>" />
<input type="hidden" name="agpxf" value="<?php echo $dupAgpx;?>" />
<input id="overAgpx" type="hidden" name="owag" value="<?php echo $owAgpx;?>" />

<?php
// hike form entry
$hikeName = filter_input(INPUT_POST,'hpgTitle');
$hikeLocale = filter_input(INPUT_POST,'locale');
$hikeType = filter_input(INPUT_POST,'htype');
$hikeLgth = filter_input(INPUT_POST,'dist');
$hikeElev = filter_input(INPUT_POST,'elev');
$hikeDiff = filter_input(INPUT_POST,'diff');
$hikeFac = filter_input(INPUT_POST,'fac');
$hikeWow = filter_input(INPUT_POST,'wow_factor');
$hikeSeasons = filter_input(INPUT_POST,'seas');
$hikeExp = filter_input(INPUT_POST,'expos');

# Extract trailhead lat & lng from gpx file
$gpxupload = getcwd() . '/' . $uploads . 'gpx/' . $hikeGpx;
$gpxdat = file_get_contents($gpxUpload);
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

$hikeMarker = filter_input(INPUT_POST,'mstyle');
$hikePurl1 = filter_input(INPUT_POST,'photo1');
$hikePurl2 = filter_input(INPUT_POST,'photo2');
$hikeDir = filter_input(INPUT_POST,'dirs');
$rawtips = filter_input(INPUT_POST,'tipstxt');
if (substr($rawtips,0,10) === '[OPTIONAL]') {
	$tipTxt = '';
} else {
	$tipTxt = $rawtips;
}
$_SESSION['hikeTips'] = $tipTxt;
$rawhike = filter_input(INPUT_POST,'hiketxt');
$_SESSION['hikeDetails'] = $rawhike;
# don't know how to filter arrays:
$hikeRefTypes = $_POST['rtype'];
$hikeRefItems1 = $_POST['rit1'];
$hikeRefItems2 = $_POST['rit2'];
/* get a count of items actually specified: */
$noOfRefs = count($hikeRefTypes);
for ($w=0; $w<$noOfRefs; $w++) {
    # Imported data references may not have content other than default label
    if ($hikeRefItems1[$w] == '') {
        $noOfRefs = $w;
        break;
    }
}
include "xmlRefs.php";

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
include "xmlGpsDat.php";

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
}
/*
 *  MARKER-DEPENDENT PAGE ELEMENTS
*/
# Index page ref -> ctrhike
$xmlDataBase = '../data/database.xml';
if ($hikeMarker === 'ctrhike') {
    $xmlDB = simplexml_load_file($xmlDataBase);
    if ($xmlDB === false) {
        $dbnogo = '<p style="margin-left:8px;color:brown;font-size:18px;">' .
            'Could not load the database to retrieve data for Visitor Center hike.</p>';
        die($dbnogo);
    }
    $VClist = array();
    $srchCnt = 0;
    foreach ( $xmlDB->row as $hikerow ) {
        if ( preg_match("/Visitor/i", $hikerow->marker) == 1 ) {
            $VCList[$srchCnt] = $hikerow->indexNo . ": " . $hikerow->pgTitle;
            $srchCnt++;
        }
    }
    echo '<div style="color:brown;" id="findvc"><p>This hike was identified as '
        . 'starting at, or in close proximity to, a Visitor Center.<br /><em '
        . 'id="vcnote">NOTE: if a page for this Visitor Center does not yet '
        . 'exist, please go back and create it before continuing with this '
        . 'hike.</em></p><p><label style="color:DarkBlue;">Select the Visitor '
        . 'Center Page for this hike: </label><select name="vcList">';
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
    $xmlDB = simplexml_load_file($xmlDataBase);
    if ($xmlDB === false) {
        $dbnogo = '<p style="margin-left:8px;color:brown;font-size:18px;">' .
            'Could not load the database to retrieve data for Cluster hike.</p>';
        die($dbnogo);
    }
    $clusterList = Array();
    $srchCnt = 0;
    foreach ($xmlDB->row as $hikerow) {
        if ( preg_match("/cluster/i",$hikerow->marker) == 1) {
                $clusterList[$srchCnt] = $hikerow->clusGrp . "$" . $hikerow->cgName;
                $srchCnt++;
            }
    }
    # Now eliminate duplicates...
    $result = array_unique($clusterList);
    $passGroup = implode(";",$result) . ";";  // display hike looks for terminal semi-colon
    /* NOTE: even though the array holds empty keys where duplicates were eliminated,
       when imploding, the empty keys are disregarded */
    $_SESSION['allTips'] = $passGroup;
    echo '<div style="color:brown;" id="clus_sel"><p style="font-size:18px;color:Brown;">This hike '
        . 'was identified as belonging to a group of hikes in close proximity '
        . 'with other hikes.<br /><label style="color:DarkBlue;">Select the '
        . 'Group to which this hike belongs: </label><select name="clusgrp">';
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
                    if($hikeExp === 'sun') {
                        $eimg = 'sun.jpg';
                    } elseif($hikeExp === 'shade') {
                        $eimg = 'greenshade.jpg';
                    }  else {
                        $eimg = 'shady.png';
                    }
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
<?php 
    if ($hikeMarker === 'center') {
        $listType = "Visitor Center";
        $listLat = $hikeLat;
        $listLng = $hikeLong;
    } elseif ($hikeMarker === 'cluster') {
        $listType = "Hike Trailhead Common With or in Close Proximity to Others";
        $listLat = "[Using Cluster Group Coordinates]";
        $listLng = "[Using Cluster Group Coordinates]";
    } elseif ($hikeMarker === 'ctrhike') {
        $listType = "Hike Starts At/Near Visitor Center";
        $listLat = "[Using Visitor Center Coordinates]";
        $listLng = "[Using Visitor Center Coordiantes]";
    } else {
        $listType = "'Normal' Hike - Not Overlapping;";
        $listLat = $hikeLat;
        $listLng = $hikeLong;
    }
?>
<ul>
    <li>Marker Style: <?php echo $listType;?></li>
    <li>Marker Latitude: <?php echo $listLat;?></li>
    <li>Marker Longitude: <?php echo $listLng;?></li>
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
    echo '<p id="hikeInfo" style="text-indent:8px;">' . $rawhike . '</p>' . "\n";
?>
<h2>Hike References:</h2>
<?php 
    /* There SHOULD always be at least one reference, however, if there is not,
       a message will appear in this section: No References Found */
    echo '<fieldset>' . "\n" . '<legend id="fldrefs">References &amp; Links</legend>' . "\n";
    echo "\t" . '<ul id="refs">' . "\n";
    $completeRef = "<?xml version='1.0'?>\n" . $refXmlStr . "\n";
    $xmlRef = simplexml_load_string($completeRef);
    if ($xmlRef === false) {
        $norefs = $msgStyle . 'Could not load $xmlRef: contact Site Master</p>';
        die ($norefs);
    }
    foreach ($xmlRef->ref as $refItem) {
        if ($refItem->rtype == 'Book' || $refItem->rtype == 'Photo Essay') {
            echo "\t\t<li><em>" . $refItem->rtype . "</em>: " . $refItem->rit1 .
                $refItem->rit2 . "</li>\n";
        } else {
            echo "\t\t<li>" . $refItem->rtype . ': <a href="' . $refITem->rit1 .
                '" target="_blank"> ' . $refItem->rit2 . "</a></li>\n";
        }
    }
    echo "\t</ul>\n</fieldset>\n";
    
    /* OPTIONAL INFO: GPS Maps & Data fieldset
     * No h2 header is used here: just display if present
     */
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
                echo "\t<li>" . $prop1[$a] . ': <a href="' . $tmpurl .
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
                echo "\t<li>" . $act1[$b] . ': <a href="' . $tmpurl .
                        '" target="_blank"> ' . $act3[$b] . '</a></li>' . "\n";
            }
            echo "\t</ul>\n";
        }  
        echo "</fieldset>\n";
    }
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
