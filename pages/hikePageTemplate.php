<!DOCTYPE html>
<html lang="en-us">

<?php
define('Simple','0');
define('References','1');
define('Proposed','2');
define('Actual','3');
define('fullMapOpts','&show_markers_url=true&street_view_url=true&map_type_url=GV_HYBRID&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=true&tracklist_options_enabled=true&dynamicMarker_url=false');
define('iframeMapOpts','&show_markers_url=true&street_view_url=false&map_type_url=ARCGIS_TOPO_WORLD&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=false&tracklist_options_enabled=false&dynamicMarker_url=true"');
define('gpsvTemplate','../maps/gpsvMapTemplate.php?map_name=');

/* 
 * The following function is used to create the html code for the items in a string,
 *  which were retrieved from the database in the form of 'string arrays'
 */
function makeHtmlList($type,$str) {
    $list = explode("^",$str);
    $noOfItems = intval($list[0]);
    array_shift($list);
    if ($type === Simple) {
        $htmlout = '<ol>';
        for ($j=0; $j<$noOfItems; $j++) {
            $htmlout = $htmlout . '<li>' . $list[$j] . '</li>';
        }
        $htmlout = $htmlout . '</ol>';
    } elseif ($type === References) {
        $nxt = 0;
        $htmlout = '<ul id="refs">';
        for ($k=0; $k<$noOfItems; $k++) {
            $tagType = $list[$nxt];
            if ($tagType === 'b') { 
                $htmlout .= '<li>Book: <em>' . $list[$nxt+1] . '</em>' . $list[$nxt+2] . '</li>';
                $nxt += 3;
            } elseif ($tagType === 'p') {
                $htmlout .= '<li>Photo Essay: <em>' . $list[$nxt+1] . '</em>' . $list[$nxt+2] . '</li>';
                $nxt += 3;
            } elseif ($tagType === 'n') {
                $htmlout .= '<li>' . $list[$nxt+1] . '</li>';
                $nxt += 2;
            } else {
                if ($tagType === 'w') {
                    $tag = '<li>Website: ';
                } elseif ($tagType === 'a') {
                    $tag = '<li>App: ';
                } elseif ($tagType === 'd') {
                    $tag = '<li>Downloadable Doc: ';
                } elseif ($tagType === 'h') {
                    $tag = '<li>';
                } elseif ($tagType === 'l') {
                    $tag = '<li>Blog: ';
                } elseif ($tagType === 'r') {
                    $tag = '<li>Related Site: ';
                } elseif ($tagType === 'o') {
                    $tag = '<li>Map: ';
                } elseif ($tagType === 'm') {
                    $tag = '<li>Magazine: ';
                } elseif ($tagType === 's') {
                    $tag = '<li>News article: ';
                } elseif ($tagType === 'g') {
                    $tag = '<li>Meetup Group: ';
                } else {
                    $tag = '<li>CHECK DATABASE: ';
                }
                $htmlout .= $tag . '<a href="' . $list[$nxt+1] . '" target="_blank">' .
                    $list[$nxt+2] . '</a></li>';
                $nxt += 3;
            }
        } // end of for loop in references
        $htmlout .= '</ul>';
    } elseif ($type === Proposed || $type === Actual) {
        $nxt = 0;
        if ($type === Proposed) {
            $htmlout = '<p id="proptitle">- Proposed Hike Data</p><ul id="plinks">';
        } else {
            $htmlout = '<p id="acttitle">- Actual Hike Data</p><ul id="alinks">';
        }
        for ($n=0; $n<$noOfItems; $n++) {
            $htmlout .= '<li>' . $list[$nxt] . ' <a href="' . $list[$nxt+1] .
                    '" target="_blank">' . $list[$nxt+2] . '</a></li>';
            $nxt += 3;
        }
        $htmlout .= '</ul>';
    } else {
        echo "Unknown argument in makeHtmlList, Hike " . $hikeIndexNo . ': ' . $tagType;
    }  // end of if tagtype ifs
    return $htmlout;
} // FUNCTION END....

/*
 * -------------------------  MAIN ROUTINE ------------------------
 */
$hikeIndexNo = filter_input(INPUT_GET,'hikeIndx');
$datatable = '../data/database.xml';
$tabledat = simplexml_load_file($datatable);
if ($tabledat === false) {
    die ("Could not load database.xml as simplexml");
}
foreach ($tabledat->row as $page) {
    if ($page->indxNo == $hikeIndexNo) {
        $newstyle = true;  // change later if no gpx file
        $hikeTitle = $page->pgTitle;
        $hikeLocale = $page->locale;
        $hikeDifficulty = $page->difficulty;
        $hikeLength = $page->miles . " miles";
        $hikeType = $page->logistics;
        $hikeElevation = $page->feet . " ft";
        $hikeExposure = $page->expo;
        $gpsvFile = $page->tsv;
        $gpxfile = $page->gpxfile;
        if (strlen($gpxfile) === 0) {
            $newstyle = false;
        } else {
            $gpxPath = '../gpx/' . $gpxfile;
        }
        $jsonFile = $page->trkfile;
        $hikeWow = $page->wow;
        $hikeFacilities = $page->facilities;
        $hikeSeasons = $page->seasons;
        $hikePhotoLink1 = $page->mpUrl;
        $hikePhotoLink2 = $page->spUrl;
        $hikeDirections = $page->dirs;
        /* 
         * ----- create the html that will display the image rows
         */
        $rows = array();
        $rowNo = 0;
        $picNo = 0;
        foreach ($page->content->picRow as $thisrow) {
            $rowdat = explode("^",$thisrow);
            $leftmost = true;
            $els = intval($rowdat[0]);
            $rowht = $rowdat[1];
            $elType = $rowdat[2]; // can be either 'p' (w/caption) or 'n' (no caption)
            $nxtel = 2;
            $rowhtml = '<div id="row' . $rowNo . '" class="ImgRow">';
            for ($k=0; $k<$els; $k++) {
                if ($leftmost) {
                    $style = '';
                    $leftmost = false;
                } else {
                    $style = 'margin-left:1px;';
                }
                $width = $rowdat[$nxtel+1];
                $src = $rowdat[$nxtel+2];
                if ($elType === 'p') { // captioned image
                    $cap = $rowdat[$nxtel+3];
                    $rowhtml = $rowhtml . '<img id="pic' . $picNo . '" style="' .
                            $style . '" width="' . $width . '" height="' . $rowht .
                            '" src="' . $src . '" alt="' . $cap . '" />';
                    $picNo++;
                    $nxtel += 4;
                } elseif ($elType === 'n') { // non-captioned image
                    $rowhtml = $rowhtml . '<img style="' . $style .
                            '" width="' . $width . '" height="' . $rowht .
                            '" src="' . $src . '" alt="no caption" />';
                    $nxtel +=3;
                }
                $elType = $rowdat[$nxtel];
            }  # end of for imgs in row processing
            $rowhtml = $rowhtml . '</div>';
            array_push($rows,$rowhtml);
            $rowNo++;
        }  # end of foreach picRow in xml file
        /* 
        * Extract remaining database elements:
        */
        $picLinks = $page->albLinks;
        $picLinks = makeHtmlList(Simple,$picLinks);
        $hikeTips = $page->tipsTxt;
        $hikeTips = preg_replace("/\s/"," ",$hikeTips);
        $hikeInfo = '<p id="hikeInfo">' . $page->hikeInfo . '</p>';
        # there should always be something to report in 'references'
        $hikeReferences = $page->refs;
        # there may or may not be any proposed data or actual data to present
        $hikeReferences = makeHtmlList(References,$hikeReferences);
        $hikeProposedData = $page->dataProp;
        $hikeActualData = $page->dataAct;
        if ($hikeProposedData !== '' || ($hikeActualData !== '' && $hikeActualData !== "\n")) {
                $fieldsets = true;
                $datasect = '<fieldset><legend id="flddat">GPS Maps &amp; Data</legend>';
                if ($hikeProposedData !== '') {
                        $datasect .= makeHtmlList(Proposed,$hikeProposedData);
                }
                if ($hikeActualData !== '') {
                        $datasect .= makeHtmlList(Actual,$hikeActualData);
                }
                $datasect .= '</fieldset>';
        }
        break;
    }  # end if the correct hike = indx no
}  # end of foreach $page
?>
<head>
    <title><?php echo $hikeTitle;?></title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Details about the {$hikeTitle} hike" />
    <meta name="author"
        content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
        content="nofollow" />
    <link href="../styles/logo.css"
        type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css"
        type="text/css" rel="stylesheet" />
    <script type="text/javascript"> var iframeWindow; </script>
    <script type="text/javascript" src="../scripts/canvas.js"> </script>
</head>

<body>
    
<div id="logo">
	<img id="hikers" src="../images/hikers.png" alt="hikers icon" />
	<p id="logo_left">Hike New Mexico</p>	
	<img id="tmap" src="../images/trail.png" alt="trail map icon" />
	<p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $hikeTitle;?></p>

<?php
 /* There are two page styles from which to choose: 
  *  if there is either no map OR no chart, the "original" style is presented;
  *  if there is both a map AND a chart, the "new" style is presented
  */
if (!$newstyle) {
    echo '<div id="hikeSummary">' .
        '<table id="topper">' .
            '<thead>' .
                '<tr>' .
                    '<th>Difficulty</th>' .
                    '<th>Round-trip</th>' .
                    '<th>Type</th>' .
                    '<th>Elev. Chg.</th>' .
                    '<th>Exposure</th>' .
                    '<th>Wow Factor</th>' .
                    '<th>Facilities</th>' .
                    '<th>Seasons</th>';
                    if($hikePhotoLink2 == '') {
                                    echo "<th>Photos</th>";
                    }
                    echo '<th>By Car</th>' .
               '</tr>' .
           '</thead>' .
           '<tbody>' .
                '<tr>' .
                    '<td>' . $hikeDifficulty . '</td>' .
                    '<td>' . $hikeLength . '</td>' .
                    '<td>' . $hikeType . '</td>' .
                    '<td>' . $hikeElevation . '</td>' .
                    '<td>' . $hikeExposure . '</td>' .
                    '<td>' . $hikeWow . '</td>' .
                    '<td>' . $hikeFacilities . '</td>' .
                    '<td>' . $hikeSeasons . '</td>';
                    if($hikePhotoLink2 == '') {
                        echo '<td><a href="' . $hikePhotoLink1 . '" target="_blank">' .
                            '<img style="margin-bottom:0px;border-style:none;"' .
                            ' src="../images/album_lnk.png"' .
                            ' alt="photo album link icon" /></a></td>';
                    }
                    echo '<td><a href="' . $hikeDirections . '" target="_blank">' .
                        '<img style="margin-bottom:0px;padding-bottom:0px;"' .
                        ' src="../images/dirs.png" alt="google driving directions" />' .
                        '</a></td>' .
                '</tr>' .
           '</tbody>' .
       '</table>' .
    '</div>';
} else { # newstyle has the side panel with map & chart on right
    # SIDE PANEL:
    # dynamically created map:
    $extLoc = strrpos($gpsvFile,'.');
    $gpsvMap = substr($gpsvFile,0,$extLoc); # strip file extension
    # holding place for page's hike map (deleted when page exited)
    $tmpMap = '../maps/tmp/' . $gpsvMap . '.html';
    if ( ($mapHandle = fopen($tmpMap,"w")) === false) {
        $mapmsg = "Contact Site Master: could not open tmp map file: " . $tmpMap . ", for writing";
        die ($mapmsg);
    }
    include "../php/makeGpsv.php";
    fputs($mapHandle,$html);
    fclose($mapHandle);
    # Full-ppage map link cannot assume existence of tmp file: (Name is bogus 'MapLink')
    $fpLnk = 'MapLink' . fullMapOpts . '&hike=' . $hikeTitle . '&gpsv=' . 
            $gpsvFile . '&gpx=' . $gpxfile;
    echo '<div id="sidePanel">' . "\n" . '<p id="stats"><strong>Hike Statistics</strong></p>' . "\n";
        echo '<p id="summary">' .
                'Nearby City / Locale: <span class=sumClr>' . $hikeLocale . '</span><br />' .
                'Hike Difficulty: <span class=sumClr>' . $hikeDifficulty . '</span><br />' .
                'Total Length of Hike: <span class=sumClr>' . $hikeLength . '</span><br />' .
                'Max to Min Elevation: <span class=sumClr>' . $hikeElevation . '</span><br />' .
                'Logistics: <span class=sumClr>' . $hikeType . '</span><br />' .
                'Exposure Type: <span class=sumClr>' . $hikeExposure . '</span><br />' .
                'Seasons : <span class=sumClr>' . $hikeSeasons . '</span><br />' .
                '"Wow" Factor: <span class=sumClr>' . $hikeWow . '</span></p>' . "\n";
        echo '<p id="addtl"><strong>More!</strong></p>' . "\n";        
        echo '<p id="mlnk"><a href="../maps/gpsvMapTemplate.php?map_name=' . 
                $fpLnk . '" target="_blank">Full Page Map Link</a></p>' ."\n";
        echo '<p id="albums">For improved photo viewing,<br />check out the following album(s):</p>' .
                '<p id="alnks"><a href="' . $hikePhotoLink1 . '" target="_blank">Photo Album Link</a>';
        if ($hikePhotoLink2 !== '') {
                echo '<br /><p>VALUE OBTAINED FROM XML: ' . $hikePhotoLink2 . '</p>';
                echo '<br /><a href="' . $hikePhotoLink2 . '" target="_blank">Additional Album Link</a>';
        }
        echo '</p>' . "\n";
        echo '<p id="directions">The following link provides on-line directions' .
                ' to the trailhead:</p>' . "\n";
        echo '<p id="dlnk"><a href="' . $hikeDirections . '" target="_blank">' .
                'Google Directions</a></p>' . "\n";
        echo '<p id="scrollmsg">Scroll down to see images, hike description, reference sources and ' .
                'additonal information as applicable</p>' . "\n";
        echo '<p id="closer">If you are having problems with this page, please: ' .
            '<a href="mailto:krcowles29@gmail.com">send us a note!</a></p>' ."\n";
    echo '</div>';
    
    # MAP AND CHART ON RIGHT:
    # map:
    echo '<iframe id="mapline" src="../maps/gpsvMapTemplate.php?map_name=' . 
                $tmpMap . iframeMapOpts . '></iframe>' . "\n";
    # elevation chart:
    echo '<script>' . "\n" .
            'var alts = ' . $jsElevation . ';' . "\n" . '</script>' . "\n";
    echo '<div data-gpx="' . $gpxPath . '" id="chartline"><canvas id="grph"></canvas></div>' . "\n";
}
/* BOTH PAGE STYLES */
for ($k=0; $k<count($rows); $k++) {
    echo $rows[$k] . "\n"; 
}
echo '<div class="captionList">' . $picCaptions . '</div>' . "\n";
echo '<div class="lnkList">' . $picLinks . '</div>' . "\n";
if ($hikeTips !== '') {
    echo '<div id="trailTips"><img id="tipPic" src="../images/tips.png" alt="special notes icon" />' .
        '<p id="tipHdr">TRAIL TIPS!</p><p id="tipNotes">' . 
        htmlspecialchars_decode($hikeTips,ENT_COMPAT) . '</p></div>' . "\n";
}
echo "<br />";
echo $hikeInfo;
if ($hikeReferences !== '') {
    echo '<fieldset>'."\n";
    echo '<legend id="fldrefs">References &amp; Links</legend>'."\n";
    echo htmlspecialchars_decode($hikeReferences,ENT_COMPAT) . "\n";
    echo '</fieldset>';
}
#echo '<div id="postPhoto">';
if ($fieldsets) {
    echo $datasect;
}
?>

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
