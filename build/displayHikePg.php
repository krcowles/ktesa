<?php
session_start();
# map option definitions
define('fullMapOpts','&show_markers_url=true&street_view_url=true&map_type_url=GV_HYBRID&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=true&tracklist_options_enabled=true&dynamicMarker_url=false');
define('iframeMapOpts','&show_markers_url=true&street_view_url=false&map_type_url=ARCGIS_TOPO_WORLD&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=false&tracklist_options_enabled=false&dynamicMarker_url=true');
define('gpsvTemplate','../maps/gpsvMapTemplate.php?map_name=');
# image processing definitions
define("SPACING", 14, true);
define("MAXWIDTH", 960, true);
define("ROWHT", 260, true);
define("TOOMUCHMARGIN", 80, true);
define("MIN_IFRAME_SIZE", 270, true);
# Location of user-stored new files
$buildFiles = 'tmp/';

# output msg styling:
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
$xmlmsg = $pstyle . 'Could not open new xml string for ';

# FILE INPUTS:
$gpx = filter_input(INPUT_POST,'gpx');
if ($gpx !== '') {
    $gpxPath = $buildFiles . 'gpx/' . $gpx;
} else {
    $nogpx = $pstyle . 'GPX File name is currently empty: conact Site Master</p>';
    die ($nogpx);
 }
$trkfile = filter_input(INPUT_POST,'json');
if ($trkfile === '') {
    $notrk = $pstyle . "Track file, if created, is no longer present: contact"
            . "Site Master</p>";
    die ($notrk);
}
$addonImg[0] = filter_input(INPUT_POST,'img1');
$imgIndx = 0;
if ($addonImg[0] === '') {
    $noOfOthr = 0;
} else {
    $noOfOthr = 1;
    $firstimg = getimagesize("../images/" . $addonImg[0]);
    $othrWidth[$imgIndx] = $firstimg[0];
    $othrHeight[$imgIndx] = $firstimg[1];
    $imgIndx += 1;
    $img1File = $buildFiles . 'images/' . $addonImg[0];
}
$addonImg[1] = filter_input(INPUT_POST,'img2');
if ($addonImg[1] !== '') {
    $noOfOthr += 1;
    $secondimg = getimagesize("../images/" . $addonImg[1]);
    $othrWidth[$imgIndx] = $secondimg[0];
    $othrHeight[$imgIndx] = $secondimg[1];
    $img2File = $buildFiles . 'images/' . $addonImg[1];
}
# All files associated with proposed & actual data sections:
$propact = filter_input(INPUT_POST,'dfiles');
/* An 'array string' is passed to 'saveHike.php' consisting of 8 pairs of values;
 * each value in the pair is either "YES" or "NO": First val: has a duplicate file name
 * been detected? Second val: if so, should it be replaced with the newly uploaded file?
 * Order of array: gpx file, track file, image1, image2, prop dat1,
 * prop dat2, act dat1, act dat2 
 */
$saveFileInfo = [];
array_push($saveFileInfo,filter_input(INPUT_POST,'gpxf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owg'));
array_push($saveFileInfo,filter_input(INPUT_POST,'jsonf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owj'));
array_push($saveFileInfo,filter_input(INPUT_POST,'img1f'));
array_push($saveFileInfo,filter_input(INPUT_POST,'ow1'));
array_push($saveFileInfo,filter_input(INPUT_POST,'img2f'));
array_push($saveFileInfo,filter_input(INPUT_POST,'ow2'));
array_push($saveFileInfo,filter_input(INPUT_POST,'pmapf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owpm'));
array_push($saveFileInfo,filter_input(INPUT_POST,'pgpxf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owpg'));
array_push($saveFileInfo,filter_input(INPUT_POST,'amapf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owam'));
array_push($saveFileInfo,filter_input(INPUT_POST,'agpxf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owag'));

$fileSaveData = implode("^",$saveFileInfo);
$_SESSION['filesaves'] = $fileSaveData;
# ----- end of file data ------

/* NEXT: IMPORTED VARIABLE DATA (i.e. HIKE DATA) */
$pgTitle = filter_input(INPUT_POST,'hTitle');
$locale = filter_input(INPUT_POST,'area');
$hikeType = filter_input(INPUT_POST,'htype');
if ($hikeType === "outandback") {
    $htype = "Out-and-back";
} else if ($hikeType === "loop") {
    $htype = "Loop";
} else {
    $htype = "Two-Cars";
}
# from select drop-down for hike at Visitor Center: passed to saveHike.php as is;
$ctrHikeLoc = filter_input(INPUT_POST,'vcList');

$clusGrp = filter_input(INPUT_POST,'clusgrp');
$clusTip = '';  // default: may change below
/*
    With clusGrp, find the associated tooltip
*/
if ($clusGrp !== '') {
    $str2find = $clusGrp . "$";
    $lgthOfGrp = strlen($str2find);
    $clusString = $_SESSION['allTips'];
    $strLoc = strpos($clusString,$str2find);
    $tipStrt = $strLoc + $lgthOfGrp;
    $strEnd = strlen($clusString) - $tipStrt;
    $firstHalf = substr($clusString,$tipStrt,$strEnd);
    $grpEndPos = strpos($firstHalf,";");
    $clusTip = substr($firstHalf,0,$grpEndPos);
}
/* 
    End of cluster tooltip processing
*/
$distance = filter_input(INPUT_POST,'lgth');
$elevation = filter_input(INPUT_POST,'elev');
$difficulty = filter_input(INPUT_POST,'diffi');
$lat = filter_input(INPUT_POST,'lati');
$lon = filter_input(INPUT_POST,'long');
$facilities = filter_input(INPUT_POST,'facil');
$wowFactor = filter_input(INPUT_POST,'wow');
$seasons = filter_input(INPUT_POST,'seasn');
$exp = filter_input(INPUT_POST,'expo');
if ($exp === "sun") {
    $exposure = "Full sun";
} else if ($exp === "shade") {
    $exposure = "Good shade";
} else {
    $exposure = "Mixed sun/shade";
}
$marker = filter_input(INPUT_POST,'mrkr');
$purl1 = filter_input(INPUT_POST,'phot1');
$purl2 = filter_input(INPUT_POST,'phot2');
if ($purl2 == '' ) {
    $twoLinks = false;
} else {
    $twoLinks = true;
}
$googledirs = filter_input(INPUT_POST,'gdirs');
$tips = $_SESSION['hikeTips'];
# the passed tips may be an empty string
$info = $_SESSION['hikeDetails'];
$usePix = filter_input(INPUT_POST,'usepics');

if ($usePix == 'YES') {
    # establish the $photos for this hike as a complete xml string for loading
    $sessXml = $_SESSION['tsvdata'];
    $completeXml = "<?xml version='1.0'?>\n<photos>\n" . $sessXml . "</photos>\n";
    $photos = simplexml_load_string($completeXml);
    if ($photos === false) {
        $nophxml = $xmlmsg . '$completeXml; contact Site Master</p>'; 
    }
    # retrieve array of photos checked for inclusion on hike page:
    $picarray = $_POST['pix'];
    $noOfPix = count($picarray); # NOTE: the array doesn't include unchecked items...
    if ($noOfPix === 0) {
        $nopix = $pstyle . 'No pictures were selected for inclusion on the ' .
                'hike page: if this is correct, continue; else go back and ' .
                'select the desired items</p>';
        echo $nopix;
    } else {
        for ($z=0; $z<$noOfPix; $z++) {
            # change the 'N' to a 'Y' in the $photos xml object
            foreach ($photos->picDat as $picel) {
                if ($picel->title == $picarray[$z]) {
                    $picel->hpg = 'Y';
                    break;
                }
            }
        }
    }
    # retrieve array of photos checked for inclusion on map:
    $maparray = $_POST['mapit'];
    $noOfMapPix = count($maparray);
    if ($noOfMapPix === 0) {
        $nomappix = $pstyle . 'No pictures were selected for inclusion on the '
                . 'hike map: if this is correct, continue; else go back and'
                . ' select desired items</p>';
        echo $nomappix;
    } else {
        for ($q=0; $q<$noOfMapPix; $q++) {
            # change the 'N' to a 'Y' in the $photos xml object
            foreach ($photos->picDat as $picel) {
                if ($picel->title == $maparray[$q]) {
                    $picel->mpg = 'Y';
                    break;
                }
            }
        } 
    }
}
/*
    ------------------------------ END OF IMPORTING DATA -------------------------
*/
?>

<!DOCTYPE html>
<html lang="en-us">

<head>
    <title><?php echo $pgTitle;?></title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Details about the <?php echo $pgTitle;?> hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript"> var iframeWindow; </script>
    <script type="text/javascript" src="../scripts/canvas.js"></script>
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $pgTitle;?></p>

<?php
    # SETUP FOR MAP CONSTRUCTION:
    # include file expects defined variables as input for:
    #   $hikeTitle, $gpxPath ---> $gpxPath is already defined, above,
    #   $usetsv, and $photos (xml object): $usePix tells include if page creation
    $building = true;
    $hikeTitle = $pgTitle;
    $usetsv = false;
    # establish temp. map name:
    $extLoc = strrpos($gpx,'.');
    $gpsvMap = substr($gpx,0,$extLoc);
    $tmpMap = '../maps/tmp/' . $gpsvMap . '.html';
    if ($gpsvMap === '') {
        $tmpMap = '../maps/tmp/noGpxName.html';
    }
    if ( ($mapHandle = fopen($tmpMap,"w")) === false) {
        $mapmsg = $intro . 'Could not open tmp map file - contact Site Master';
        die ($mapmsg);
    }
    include "../php/makeGpsv.php";
    fputs($mapHandle,$html);
    fclose($mapHandle);
?>
<div id="sidePanel">
    <p id="stats">
        <strong>Hike Statistics</strong>
    </p>
    <p id="summary">
        Nearby City / Locale: <span class=sumClr><?php echo $locale;?></span><br />
        Hike Difficulty: <span class=sumClr><?php echo $difficulty;?></span><br />
        Total Length of Hike: <span class=sumClr><?php echo $distance;?></span><br />
        Max to Min Elevation: <span class=sumClr><?php echo $elevation;?></span><br />
        Logistics: <span class=sumClr><?php echo $htype;?></span><br />
        Exposure Type: <span class=sumClr><?php echo $exposure;?></span><br />
        Seasons : <span class=sumClr><?php echo $seasons;?></span><br />
        "Wow" Factor: <span class=sumClr><?php echo $wowFactor;?></span>
    </p>
    <p id="addtl">
        <strong>More!</strong>
    </p>
    <p id="mlnk">
        <a href="../maps/gpsvMapTemplate.php?map_name=MapLink<?php echo 
                fullMapOpts . '&hike=' . $pgTitle . '&tsv=NO&gpx=' .
                $gpxPath;?>" target="_blank">Full Page Map Link</a>
    </p>
    <p id="albums">
        For improved photo viewing,<br />check out the following album(s):
    </p>
    <p id="alnks">
        <a href="<?php echo $purl1;?>" target="_blank">Photo Album Link</a>
        <?php 
        if ($purl2 !== '') {
            echo '<br /><a href="' . $purl2 . '" target="_blank">' .
                'Additional Album Link</a>'; 
        }
        ?>
    </p>
    <p id="directions">The following link provides on-line directions
        to the trailhead:
    </p>
    <p id="dlnk"><a href="<?php echo $googledirs;?>" target="_blank">
        Google Directions</a>
    </p>
    <p id="scrollmsg">Scroll down to see images, hike description, 
        reference sources and additonal information as applicable
    </p>
    <p id="closer">If you are having problems with this page, please:
        <a href="mailto:krcowles29@gmail.com">send us a note!</a>
    </p>
</div> <!-- END OF SIDE PANEL DIV -->
<iframe id="mapline" src="../maps/gpsvMapTemplate.php?map_name=<?php echo $tmpMap . 
    iframeMapOpts;?>"></iframe>
<div data-gpx="<?php echo $gpxPath;?>" id="chartline"><canvas id="grph"></canvas></div>

<?php
/*  
    ---------------------------  BEGIN IMAGE ROW PROCESSING ----------------------
*/
if ($usePix === "YES") {
    # NOTE - the include file will use the already created $photos xml object (makeGpsv.php)
    include 'formPicRows.php';
}
?>
<form target="_blank" action="saveHike.php" method="POST">

<div id="postPhoto" style="clear:both;">
<?php 
    /* ------ TIPS TEXT PROCESSING ----- */
    if($tips !== '') {
        echo '<div id="trailTips">' . "\n\t\t" .
            '<img id="tipPic" src="../images/tips.png" alt="special notes icon" />' . "\n\t\t" .
            '<p id="tipHdr">TRAIL TIPS!</p>' . "\n\t\t" . '<p id="tipNotes">' . 
            $tips . '</p></div>' . "\n";
    }
    /* ----- HIKE INfORMATION PROCESSING ---- */
    echo  '<p id="hikeInfo">' . $info . '</p>' . "\n";
    
    /* ----- REFERENCES PROCESSING ----- */
    echo '<fieldset>' . "\n" . '<legend id="fldrefs">References &amp; Links</legend>' . "\n";
    echo "\t" . '<ul id="refs">' . "\n";
    # form loadable xml string:
    $importRefs = $_SESSION['hikerefs'];
    $completeXmlRefs = "<?xml version='1.0'?>\n" . $importRefs . "\n";
    $xmlRef = simplexml_load_string($completeXmlRefs);
    if ($xmlRef === false) {
        $norefs = $xmlmsg . '$completeXmlRefs: contact Site Master</p>';
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

    /* ----- PROPOSED AND/OR ACTUAL DATA PROCESSING ---- */
    # form loadable xml strings:
    $importPdat = $_SESSION['propdata'];
    $importAdat = $_SESSION['actdata'];
    $wholeStr = $importPdat . $importAdat;
    $completeGPSDat = "<?xml version='1.0'?>\n<gpsdat>\n" . $wholeStr . "</gpsdat>\n";
    $mapsndata = simplexml_load_string($completeGPSDat);
    if ($mapsndata === false) {
        $nomd = $xmlmsg . '$completeGPSDat; contact Site Master</p>';
        die ($nomd);
    }
    $noOfProps = 0;
    $prop1 = [];
    $prop2 = [];
    $prop3 = [];
    $noOfActs = 0;
    $act1 = [];
    $act2 = [];
    $act3 = [];
    foreach ($mapsndata->dataProp as $gpspdat) {
        if (strlen($gpspdat->prop) !== 0) {
            foreach ($gpspdat->prop as $placeProp) {
                $prop1[$noOfProps] = $placeProp->plbl;
                $prop2[$noOfProps] = $placeProp->purl;
                $prop3[$noOfProps] = $placeProp->pcot;
                $noOfProps++;
            }
        }
    }
    foreach ($mapsndata->dataAct as $gpsadat) {
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
?>
</div>
<!-- Hidden Data Passed to saveHike.php -->
<input type="hidden" name="hname" value="<?php echo $pgTitle;?>" />
<input type="hidden" name="hlocale" value="<?php echo $locale;?>" />
<input type="hidden" name="hmarker" value="<?php echo $marker;?>" />
<input type="hidden" name="hindx" value="<?php echo $ctrHikeLoc;?>" />
<input type="hidden" name="hclus" value="<?php echo $clusGrp;?>" />
<input type="hidden" name="htype" value="<?php echo $htype;?>" />
<input type="hidden" name="hmiles" value="<?php echo $distance;?>" />
<input type="hidden" name="hfeet" value="<?php echo $elevation;?>" />
<input type="hidden" name="hdiff" value="<?php echo $difficulty;?>" />
<input type="hidden" name="hfac" value="<?php echo $facilities;?>" />
<input type="hidden" name="hwow" value="<?php echo $wowFactor;?>" />
<input type="hidden" name="hseas" value="<?php echo $seasons;?>" />
<input type="hidden" name="hexp" value="<?php echo $exposure;?>" />
<input type="hidden" name="hgpx" value="<?php echo $gpx;?>" />
<input type="hidden" name="htrk" value="<?php echo $trkfile;?>" />
<input type="hidden" name="hlat" value="<?php echo $lat;?>" />
<input type="hidden" name="hlon" value="<?php echo $lon;?>" />
<input type="hidden" name="hadd1" value="<?php echo $addonImg[0];?>" />
<input type="hidden" name="hadd2" value="<?php echo $addonImg[1];?>" />
<input type="hidden" name="hdatf" value="<?php echo $propact;?>" />
<input type="hidden" name="hphoto1" value="<?php echo $purl1;?>" />
<input type="hidden" name="hphoto2" value="<?php echo $purl2;?>" />
<input type="hidden" name="hdir" value="<?php echo $googledirs;?>" />
<input type="hidden" name="htool" value="<?php echo $clusTip;?>" />
<input type="hidden" name="hplnks" value="<?php echo $albStr;?>" />
<input type="hidden" name="usepix" value="<?php echo $usePix;?>" />

<div style="margin-left:8px;">
<h3>Select an option below to save the hike page</h3>
<p><em style="color:brown;">All Users:</em>Save Data and Re-edit Later&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Save for Re-edit" />
</p>
<p><em style="color:brown;">Site Master:</em> Enter Password to Save to Site&nbsp;&nbsp;
    <input id="master" type="password" name="mpass" size="12" maxlength="10" 
           title="8-character code required" />&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Site Master" />
</p>
<p><em style="color:brown;">Registered Users:</em> Select button to submit for review&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Submit for Review" />
</p>
</div>	
</form>
	
<div id="dbug"></div>


<div class="popupCap"></div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/hikes.js"></script>
<script src="../scripts/dynamicChart.js"></script>
<script type="text/javascript">
    window.onbeforeunload = deleteTmpMap;
    function deleteTmpMap() {
        $.ajax({
            url: '../php/tmpMapDelete.php',
            data: {'file' : "<?php echo $tmpMap;?>" },
            success: function (response) {
               var msg = "Map deleted: " + "<?php echo $tmpMap?>";
               //window.alert(msg);  debug msg
            },
            error: function () {
               var msg = "Map NOT deleted: " + "<?php echo $tmpMap?>";
               //window.alert(msg);  debug msg
            }
        });
    }
</script>


</body>
</html>