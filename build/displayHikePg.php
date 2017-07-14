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
/* HTML form elements used by validateHike.js that are not used here:
 *  'tsvow', 'mapow', 'gpxow' ,'trkow', 'jsonow'
 * 
 * FIRST: Uploaded filenames (tsv may be empty)
 */
$tsvname = filter_input(INPUT_POST,'tsv');
$tsvFile = $buildFiles . 'gpsv/' . $tsvname;
$gpx = filter_input(INPUT_POST,'gpx');
if ($gpx !== '') {
    $gpxPath = $buildFiles . 'gpx/' . $gpx;
} else {
    die ("GPX FILE REQUIRED: Go back and upload");
 }
$trkfile = filter_input(INPUT_POST,'json');
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
/* An 'array string' is passed to 'saveHike.php' consisting of 10 pairs of values;
 * each value in the pair is either "YES" or "NO": First val: has a duplicate file name
 * been detected? Second val: if so, should it be replaced with the newly uploaded file?
 * Order of array: tsv file, geomap, gpx file, track file, image1, image2, prop dat1,
 * prop dat2, act dat1, act dat2 
 */
$saveFileInfo = [];
array_push($saveFileInfo,filter_input(INPUT_POST,'tsvf'));
array_push($saveFileInfo,filter_input(INPUT_POST,'owt'));
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
# ----- end of file uploads ------



/* NEXT: IMPORTED VARIABLE DATA (HIKE DATA) */
$pgTitle = filter_input(INPUT_POST,'hTitle');
$locale = filter_input(INPUT_POST,'area');
$hikeType = filter_input(INPUT_POST,'htype');
if ($hikeType === "oab") {
    $htype = "Out-and-back";
} else if ($hikeType === "loop") {
    $htype = "Loop";
} else {
    $htype = "Two-Cars";
}
$ctrHikeLoc = filter_input(INPUT_POST,'vcList');  # from select drop-down;
/*
    If $ctrHikeLoc not empty, find the Index Page for the assoc. hike and update it,
    passing the value on to 'saveHike.php'.
*/
if ($ctrHikeLoc !== '') {
    $database = '../data/database.csv';
    $dbHandle = fopen($database,"r");
    /* $ctrHikeLoc holds the index number of the Visitor Center associated with this hike;	
       This new hike will have the next available index no, which number is to be added to
       the Visitor Center's "Cluster Str", array index [4] */
    $wholeDB = array();
    $dbindx = 0;
    while ( ($hikeLine = fgetcsv($dbHandle)) !== false ) {
        $wholeDB[$dbindx] = $hikeLine;
        $dbindx++;
    }
    fclose($dbHandle);
    # find the associated Visitor Center:
    foreach ($wholeDB as &$hikeInfo) {
        if ($hikeInfo[0] == $ctrHikeLoc) {
            $_SESSION['indxCluster'] = $hikeInfo[4];
            break;
        }
    }
}
/*
    End of ctrHikeLoc processing
*/
$clusGrp = filter_input(INPUT_POST,'clusgrp');  # from select drop-down
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
$refs = filter_input(INPUT_POST,'refstr');
$pdat = filter_input(INPUT_POST,'pstr');
$adat = filter_input(INPUT_POST,'astr');
$picarray = $_POST['pix'];  # don't know how to filter passed array...
$noOfPix = count($picarray);
$forceLoad = filter_input(INPUT_POST,'setForce');
$useAllPix = filter_input(INPUT_POST,'allPix');
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
    # MAP CONSTRUCTION:
    $building = true;
    $gpsvFile = $tsvname;  # include file uses gpsvFle var
    $extLoc = strrpos($gpsvFile,'.');
    $gpsvMap = substr($gpsvFile,0,$extLoc);
    # holding place for page's hike map
    $tmpMap = '../maps/tmp/' . $gpsvMap . '.html';
    if ($gpsvMap === '') {
        $tmpMap = '../maps/tmp/' . $pgTitle . '.html';
    }
    if ( ($mapHandle = fopen($tmpMap,"w")) === false) {
        $mapmsg = $intro . 'Could not open tmp map file - contact Site Master';
        die ($mapmsg);
    }
    $hikeTitle = $pgTitle;  # include file uses $hikeTitle var
    # include file also uses $gpxPath var defined earlier
    include "../php/makeGpsv.php";
    #echo "html is " . strlen($html);
    #echo " placing file in " . $tmpMap;
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
        <a href="../maps/gpsvMapTemplate.php?map_name=<?php echo $tmpMap . 
                fullMapOpts;?>" target="_blank">Full Page Map Link</a>
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
$formpics = filter_input(INPUT_POST,'usepics');
if ($formpics === "YES") {
    # predefined var
    $month = array("Jan","Feb","Mar","Apr","May","Jun",
                                    "Jul","Aug","Sep","Oct","Nov","Dec");
    #  Read in the tsv file and extract ALL usable data:
    /* NOTE: For some older files, the fields in the tsv file vary considerably and may
       omit key data that later files contain. Look for these special files when
       executing a page rebuild. The only fields required for row-filling are:
       -- desc, name, date, n-size
    */
    $handle = fopen($tsvFile, "r");
    if ($handle !== false) {
        $lineno = 0;
        $picno = 0;
        while ( ($line = fgets($handle)) !== false ) {
            $tsvArray = str_getcsv($line,"\t");
            if ($lineno !== 0) {
                $picName[$picno] = $tsvArray[1];
                $picDesc[$picno] = $tsvArray[2];
                $picAlbm[$picno] = $tsvArray[6];
                $picDate[$picno] = $tsvArray[7];
                $nsize[$picno] = $tsvArray[8]; 
                $picno++;
            }
            $lineno++;
        }
        $lineno--;
    } else {
        die( "Could not open tsv file for this hike" );
    }
    # Pull out the index numbers of the chosen few: (or maybe all!)
    $k = 0;
    for ($i=0; $i<$noOfPix; $i++) {
        $targ = $picarray[$i];
        for ($j=0; $j<$lineno; $j++) {
            if( $targ === $picName[$j] ) {
                $indx[$k] = $j;
                $k++;
                break;
            }
        }
    }
    # for each of the <user-selected> pix, define needed arrays
    for ($i=0; $i<$noOfPix; $i++) {
        $x = $indx[$i];
        $picYear = substr($picDate[$x],0,4);
        $picMoDigits = substr($picDate[$x],5,2) - 1;
        $picMonth = $month[$picMoDigits];
        $picDay = substr($picDate[$x],8,2);
        if (substr($picDay,0,1) === '0') {
            $picDay = substr($picDay,1,1);
        }
        $caption[$i] = "{$picMonth} {$picDay}, {$picYear}: {$picDesc[$x]}";
        $picSize = getimagesize($nsize[$x]); # PROVIDE THIS IN GPSV FILE??
        $picWidth[$i] = $picSize[0];
        $picHeight[$i] = $picSize[1];
        $name[$i] = $picName[$x];
        $desc[$i] = $picDesc[$x];
        $album[$i] = $picAlbm[$x];
        $photolink[$i] = $nsize[$x];
    }
    $noOfAlbumLinks = count($album);
    $albStr = $noOfAlbumLinks . '^' . implode("^",$album);
    $albStr = preg_replace("/\n\t\r/"," ",$albStr);
    # Preliminary setup complete, begin row-filling algorithm:
    $imgRows = array(6);
    $maxRowHt = 260;	# change as desired
    $rowWidth = 950;	# change as desired, current page width is 960
    # start by calculating the various images' widths when rowht = maxRowHt
    # PHOTOS:
    for ($i=0; $i<$noOfPix; $i++) {
        $widthAtMax[$i] = floor($picWidth[$i] * ($maxRowHt/$picHeight[$i]));
    }
    # OTHER IMAGES: 
    for ($l=0; $l<$noOfOthr; $l++) {
        $indx = $noOfPix + $l;
        $widthAtMax[$indx] = floor($othrWidth[$l] * ($maxRowHt/$othrHeight[$l]));
    }
    $items = $noOfPix + $noOfOthr;
    # initialize starting rowWidth, counters, and starting point for html creation
    $curWidth = 0;	# row Width as it's being built
    $startIndx = 0;	# when creating html, index to set loop start
    $rowHtml = '';
    $rowNo = 0;
    $totalProcessed = 0;
    $othrIndx = 0;	 # counter for number of other images being loaded
    $leftMostImg = true;
    $rowStr = array();
    for ($i=0; $i<$items; $i++) {
        if ($leftMostImg === false) {  # modify width for added pic margins for all but first img
                $curWidth += 1;
        }
        $rowCompleted = false;
        $curWidth += $widthAtMax[$i];
        $leftMostImg = false;
        if ($i < $noOfPix) {
            $itype[$i] = "picture";
        } else {
            $itype[$i] = "image";
        }
        if ($curWidth > $rowWidth) {
            $rowItems = $i - $startIndx + 1;
            $totalProcessed += $rowItems;
            $scaleFactor = $rowWidth/$curWidth;
            $actualHt = floor($scaleFactor * $maxRowHt);
            # ALL rows concatenated in $rowHtml
            $rowHtml = $rowHtml . '<div id="row' . $rowNo . '" class="ImgRow">';
            /* Create a row unconcatenated to be used for $rowHtml, or passed solo via php */
            $thisRow = '';
            $imgCnt = 0;
            $imel = '';
            for ($n=$startIndx; $n<=$i; $n++) {
                if ($n === $startIndx) {
                    $styling = ''; # don't add left-margin to leftmost image
                } else {
                    $styling = 'margin-left:1px;';
                }
                if ($itype[$n] === "picture") {
                    $picWidth[$n] = floor($scaleFactor * $widthAtMax[$n]);
                    $picHeight[$n] = $actualHt;
                    $thisRow = $thisRow . '<img id="pic' .$n . '" style="' . $styling . '" width="' .
                            $picWidth[$n] . '" height="' . $actualHt . '" src="' . $photolink[$n] . 
                            '" alt="' . $caption[$n] . '" />';	
                    $imel .= 'p^' . $picWidth[$n] . '^' . $photolink[$n] . '^' . $caption[$n];             
                } else {  # its an additional non-captioned image
                    $othrWidth[$othrIndx] = floor($scaleFactor * $widthAtMax[$n]);
                    $othrHeight[$othrIndx] = $actualHt;
                    $thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$n] .
                            '" height="' . $actualHt . '" src="../images/' . $addonImg[$othrIndx] .
                            '" alt="Additional non-captioned image" />';
                    $imel .= 'n^' . $othrWidth[$n] . '^' . $addonImg[$othrIndx];
                    $othrIndx += 1;
                }
                $imgCnt++;
                $imel .= '^';
            }  # end of for loop $n
            # thisRow is completed and will be used below in different ways:
            $imel = $imgCnt . '^' . $actualHt . '^' . $imel;
            array_push($rowStr,$imel);
            $rowHtml = $rowHtml . $thisRow . '</div>';
            $rowNo += 1;
            $startIndx += $rowItems;
            $curWidth = 0;
            $rowCompleted = true;
            $leftMostImg = true;
        }  # end of if currentWidth > rowWidth
    } # end of for loop creating initial rows
    # last row may not be filled, and will be at maxRowHt
    # last item index was "startIndx"; coming into last row as $leftMostImg = true
    if ($rowCompleted === false) {
        $itemsLeft = $items - $totalProcessed;
        $leftMostImg = true;
        $thisRow = '<div id="row' . $rowNo . '" class="ImgRow">';
        $imel = '';
        $imgCnt = 0;
            for ($i=0; $i<$itemsLeft; $i++) {
                if ($leftMostImg) {
                    $styling = ''; 
                    $leftMostImg = false;
                } else {
                    $styling = 'margin-left:1px;';
                }
                if ($itype[$startIndx] === "picture") {
                    $picWidth[$startIndx] = $widthAtMax[$startIndx];
                    $picHeight[$startIndx] = $maxRowHt;
                    $thisRow = $thisRow . '<img id="pic' . $startIndx . '" style="' . $styling .
                            '" width="' . $picWidth[$startIndx] . '" height="' . $maxRowHt . '" src="' . 
                            $photolink[$startIndx] . '" alt="' . $caption[$startIndx] . '" />';
                    $imel .= 'p^' . $picWidth[$startIndx] . '^' . $photolink[$startIndx] . 
                            '^' . $caption[$startIndx];
                    $startIndx += 1;
                } else {
                    $othrWidth[$othrIndx] = $widthAtMax[$startIndx];
                    $othrHeight[$othrIndx] = $maxRowHt;
                    $thisRow = $thisRow . '<img style="' . $styling . '" width="' . $othrWidth[$othrIndx] . '" height="' .
                            $maxRowHt . '" src="../images/' . $addonImg[$othrIndx] .
                            '" alt="Additional page image" />';
                    $imel .= 'n^' . $othrWidth[$othrIndx] . '^' . $addonImg[$othrIndx];
                    $othrIndx += 1;
                    $startIndx += 1;
                }
                $imgCnt++;
                $imel .=  '^';
            } // end of for loop processing
            $imel = $imgCnt . '^' . $maxRowHt . '^' . $imel;
            array_push($rowStr,$imel);
            $imgRows[$rowNo] = $thisRow . "</div>";
            $rowHtml = $rowHtml . $thisRow . "</div>";
    } // end of last row conditional
    # all items have been processed and actual width/heights retained
    # Create the list of album links
    $albumHtml = '<div class="lnkList"><ol>';
    for ($k=0; $k<$noOfPix; $k++ ) {
            $albumHtml = $albumHtml . "<li>{$album[$k]}</li>";
    }
    $albumHtml = $albumHtml . "</ol></div>";

    $noOfRows = count($rowStr);
    for ($x=$noOfRows; $x<6; $x++) {
        $rowStr[$x] = '';
    }
    for ($y=0; $y<6; $y++) {
        if ($rowStr[$y] !== '') {
            $rlgth = strlen($rowStr[$y]) - 1;
            $rowStr[$y] = substr($rowStr[$y],0,$rlgth);
        }
    }
    $_SESSION['row0'] = $rowStr[0];
    $_SESSION['row1'] = $rowStr[1];
    $_SESSION['row2'] = $rowStr[2];
    $_SESSION['row3'] = $rowStr[3];
    $_SESSION['row4'] = $rowStr[4];
    $_SESSION['row5'] = $rowStr[5];

    /*  
        ---------------------------  END OF IMAGE ROW PROCESSING ----------------------
    */
    echo $rowHtml;
    echo $albumHtml;
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
    echo '<ul id="refs">' . "\n";
    $dispRefs = explode("^",$refs);
    $noOfRefs = intval($dispRefs[0]);
    array_shift($dispRefs);
    $nxt = 0;
    $listel = '';
    #echo 'Number of references found: ' . $noOfRefs;
    for ($i=0; $i<$noOfRefs; $i++) {
        switch ($dispRefs[$nxt]) {
            case 'b':
                $listel .= '<li>Book: <em>' . $dispRefs[$nxt+1] . '</em> ' . $dispRefs[$nxt+2] . '</li>' . "\n";
                $nxt += 3;
                break;
            case 'p':
                $listel .= '<li>Photo Essay: <em>' . $dispRefs[$nxt+1] . '</em> ' . $dispRefs[$nxt+2] . '</li>' . "\n";
                $nxt += 3;
                break;
            case 'w':
                $lbl ='Website: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'a':
                $lbl = 'App: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'd':
                $lbl = 'Downloadable Doc: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'l':
                $lbl = 'Blog: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'r':
                $lbl = 'Related Link: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'o':
                $lbl = 'On-Line Map: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'm':
                $lbl = 'Magazine: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 's':
                $lbl = 'News Article: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'g':
                $lbl = 'Meetup Group: ';
                $listel .= '<li>' . $lbl . '<a href="' . $dispRefs[$nxt+1] .
                        '" target="_blank">' . $dispRefs[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
                break;
            case 'n':
                $listel .= '<li>' . $dispRefs[$nxt+1] . '</li>' . "\n";
                break;
            default:
                echo "Unrecognized reference type passed";
        }  // end of switch
    } // end of for loop - refs processing
    echo $listel . '</ul>' . "\n" . '</fieldset>' . "\n";
    /* ----- PROPOSED AND/OR ACTUAL DATA PROCESSING ---- */
    if ($pdat !== '' || $adat !== '') {
        echo '<fieldset>' . "\n" . '<legend id="flddat">GPS Maps &amp; Data</legend>' . "\n";
        if ($pdat !== '') {
            $listel = '';
            echo '<p id="proptitle">- Proposed Hike Data</p><ul id="plinks">' . "\n";
            # get no. of pdats:
            $prop = explode("^",$pdat);
            $noOfProps = intval($prop[0]);
            array_shift($prop);
            $nxt = 0;
            for ($i=0; $i<$noOfProps; $i++) {
                $tmploc = $prop[$nxt+1];
                if (strpos($tmploc,'../maps/') !== FALSE) {
                    $tmpurl = str_replace('../maps/','tmp/maps/',$tmploc);
                } else {
                    $tmpurl = str_replace('../gpx/','tmp/gpx/',$tmploc);
                }
                $listel .= '<li>' . $prop[$nxt] . ' <a href="' . $tmpurl .
                        '" target="_blank">' . $prop[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
            }
            echo $listel . '</ul>';
        }
        if ($adat !== '') {
            $listel = '';
            echo '<p id="acttitle">- Actual Hike Data</p><ul id="alinks">' . "\n";
            # get no of adats:
            $act = explode("^",$adat);
            $noOfActs = intval($act[0]);
            array_shift($act);
            $nxt = 0;
            for ($j=0; $j<$noOfActs; $j++) {
                $tmploc = $act[$nxt+1];
                if (strpos($tmploc,'../maps/') !== FALSE) {
                    $tmpurl = str_replace('../maps/','tmp/maps/',$tmploc);
                } else {
                    $tmpurl = str_replace('../gpx/','tmp/gpx/',$tmploc);
                }
                $listel .= '<li>' . $act[$nxt] . ' <a href="' . $tmpurl .
                        '" target="_blank">' . $act[$nxt+2] . '</a></li>' . "\n";
                $nxt += 3;
            }
            echo $listel . '</ul>' . "\n";
        }
        echo '</fieldset>';
    }
?>
</div>  <!-- end of postPhoto -->
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
<input type="hidden" name="htsv" value="<?php echo $tsvname;?>" />
<input type="hidden" name="hmap" value="<?php echo $geomap;?>" />
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
<input type="hidden" name="hcaps" value="<?php echo $capStr;?>" />
<input type="hidden" name="hplnks" value="<?php echo $albStr;?>" />
<input type="hidden" name="href" value="<?php echo $refs;?>" />
<input type="hidden" name="hpdat" value="<?php echo $pdat;?>" />
<input type="hidden" name="hadat" value="<?php echo $adat;?>" />
<input type="hidden" name="rhno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="savetsv" value="<?php echo $formpics;?>" />

<div style="margin-left:8px;">
<h3>Select an option below to save the hike page</h3>
<p><em>Site Master:</em> Enter Password to Save to Site&nbsp;&nbsp;
    <input id="master" type="password" name="mpass" size="12" maxlength="10" 
           title="8-character code required" />&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="submit" name="savePg" value="Site Master" />
</p>
<p><em>Registered Users:</em> Select button to submit for review&nbsp;&nbsp;
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