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
    if ($page->indxNo === $hikeIndexNo) {
        echo "<p>GOT Indx " . $page->indxNo . "</p>";     
    }
}
/* NOTE: The database file is only read in here, no writing to it occurs */
$dataTable = '../data/database.csv';
$handle = fopen($dataTable,'r');
if ($handle !== false) {
    $lineno = 0;
    while ( ($hikeArray = fgetcsv($handle)) !== false ) {
        if ($lineno > 0) {  // skip the header row
            if ($hikeIndexNo == $hikeArray[0]) {  // find the target hike
                /* 
                 * IMPORT the data from database.csv 
                 */     
                $newstyle = true;  // change later if no geomap
                $hikeTitle = $hikeArray[1];
                $hikeLocale = $hikeArray[2];
                $hikeDifficulty = $hikeArray[9];
                $hikeLength = $hikeArray[7] . " miles";
                $hikeType = $hikeArray[6];
                $hikeElevation = $hikeArray[8] . " ft";
                $hikeExposure = $hikeArray[13];
                /* 
                 * This version eliminates a static imported map,
                 * instead one will be created dynamically by the
                 * included 'makeGpsv.php' file
                 */
                $gpsvFile = $hikeArray[14];
                $gpxfile = $hikeArray[17];
                $jsonFile = $hikeArray[18];
                if ($gpxfile === '') {
                    $newstyle = false;
                }
                $hikeWow = $hikeArray[11];
                $hikeFacilities = $hikeArray[10];
                $hikeSeasons = $hikeArray[12];
                $hikePhotoLink1 = $hikeArray[23];
                $hikePhotoLink2 = $hikeArray[24];
                $hikeDirections = $hikeArray[25];
                /* 
                 * ----- create the html that will display the image rows
                 */
                $rows = array();
                $picNo = 0;
                for ($j=0; $j<6; $j++) {
                    $thisrow = $hikeArray[$j+29];
                    if ($thisrow == '') {
                        $rowCount = $j;
                        break;
                    } else {
                        $rowdat = explode("^",$thisrow);
                        $leftmost = true;
                        $els = intval($rowdat[0]);
                        $rowht = $rowdat[1];
                        $elType = $rowdat[2]; // can be either 'p' 'n' or 'f'
                        $nxtel = 2;
                        $rowhtml = '<div id="row' . $j . '" class="ImgRow">';
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
                            } else {  // iframe - no longer used
                                $nxtel += 3;
                            }
                            $elType = $rowdat[$nxtel];
                        }
                        $rowhtml = $rowhtml . '</div>';
                        array_push($rows,$rowhtml);
                        if ($j === 5) {
                                $rowCount = 6;
                        }
                    } // end of if row not empty
                }  // end of row loop
                /* 
                 * Extract remaining database elements:
                 */
                $picCaptions = $hikeArray[35];
                $picCaptions = makeHtmlList(Simple,$picCaptions);
                $picLinks = $hikeArray[36];
                $picLinks = makeHtmlList(Simple,$picLinks);
                $hikeTips = $hikeArray[37];
                $hikeTips = preg_replace("/\s/"," ",$hikeTips);
                $hikeInfo = '<p id="hikeInfo">' . $hikeArray[38] . '</p>';
                # there should always be something to report in 'references'
                $hikeReferences = $hikeArray[39];
                # there may or may not be any proposed data or actual data to present
                $hikeReferences = makeHtmlList(References,$hikeReferences);
                $hikeProposedData = $hikeArray[40];
                $hikeActualData = $hikeArray[41];
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
            }  // end of if finding the hike to display
        }
        $lineno++;
    }
} else {
    echo "<p>Could not open {$dataTable}</p>";
}
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
    /*
     * trying out srcdoc (not supported in IE
     * echo '<iframe id="mapline" srcdoc="<html><p>Loading map...</p></html>"' .
     *      ' src="../maps/defaultMap.html"></iframe>' ."\n";
     * textarea set up for on-the-fly iframe via js, not currently working, 
     * but leaving here for now in case of future adoption...
     * echo '<textarea style="display:none;" id="mapcode">' . "\n" . $html . 
     *      '</textarea>' . "\n"; # contents will be place in mapline iframe by js
     */
    echo '<iframe id="mapline" src="../maps/gpsvMapTemplate.php?map_name=' . 
                $tmpMap . iframeMapOpts . '></iframe>' . "\n";
    #echo '<iframe id="mapline" src="../maps/gpsvMapTemplate.php?map_name=' . 
    #            $mapsrc . iframeMapOpts . '></iframe>' ."\n";
    # elevation chart:
    echo '<script>' . "\n" .
            'var alts = ' . $jsElevation . ';' . "\n" . '</script>' . "\n";
    echo '<div data-gpx="' . $gpxfile. '" id="chartline"><canvas id="grph"></canvas></div>' . "\n";
}
/* BOTH PAGE STYLES */
for ($k=0; $k<$rowCount; $k++) {
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
