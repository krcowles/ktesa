<!DOCTYPE html>
<html lang="en-us">

<?php
define('References','1');
define('Proposed','2');
define('Actual','3');
define('fullMapOpts','&show_markers_url=true&street_view_url=true&map_type_url=GV_HYBRID&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=true&tracklist_options_enabled=true&dynamicMarker_url=false');
define('iframeMapOpts','&show_markers_url=true&street_view_url=false&map_type_url=ARCGIS_TOPO_WORLD&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=false&tracklist_options_enabled=false&dynamicMarker_url=true"');
define('gpsvTemplate','../maps/gpsvMapTemplate.php?map_name=');
$months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug",
    "Sep","Oct","Nov","Dec");

/* 
 * The following function is used to create the html code for the items
 *  which were retrieved from the database 'refs' tag (aka $obj)
 */
function makeHtmlList($type,$obj) {
    if ($type === References) {
        $htmlout = '<ul id="refs">';
        foreach ($obj->ref as $item) {
            $tagType = $item->rtype->__toString();
            if ($tagType === 'b') { 
                $htmlout .= '<li>Book: <em>' . $item->rit1 . '</em>' . $item->rit2 . '</li>';
            } elseif ($tagType === 'p') {
                $htmlout .= '<li>Photo Essay: <em>' . $item->rit1 . '</em>' . $item->rit2 . '</li>';
            } elseif ($tagType === 'n') {
                $htmlout .= '<li>' . $item->rit1 . '</li>';
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
                    $tag = '<li>Unrecognized reference type: Contact Site Master';
                }
                $htmlout .= $tag . '<a href="' . $item->rit1 . '" target="_blank">' .
                    $item->rit2 . '</a></li>';
            }
        } // end of foreach loop in references
        $htmlout .= '</ul>';
    } elseif ($type === Proposed) {
        $htmlout = '<p id="proptitle">- Proposed Hike Data</p> ' . "\n" .
                '<ul id="plinks">' . "\n";
        foreach ($obj->prop as $pdat) {
            $htmlout .= '<li>' . $pdat->plbl . ' <a href="' . $pdat->purl .
                    '" target="_blank">' . $pdat->pcot . "</a></li>\n";
        }
        $htmlout .= "</ul>\n";
    } elseif ($type === Actual) {
        $htmlout = '<p id="acttitle">- Actual Hike Data</p>' . "\n" .
                '<ul id="alinks">' . "\n";
        foreach ($obj->act as $adat) {
            $htmlout .= '<li>' . $adat->albl . ' <a href="' . $adat->aurl .
                    '" target="_blank">' . $adat->acot . "</a></li>\n";
        }
        $htmlout .= "</ul>\n";
    } else {
        die ("Unknown argument in makeHtmlList, Hike " . 
                $hikeIndexNo . ': ' . $type);
    }  // end of if tagtype ifs
    return $htmlout;
} 
function clean($tsvdat) {
    $curdat = $tsvdat;
    $tsvlgth = strlen($curdat);
    if (substr($curdat,0,1) === '"') {
        $tsvlgth -= 2;
        $curdat = substr($curdat,1,$tsvlgth);
    }
    if (substr($curdat,$tsvlgth-2,2) === '\n') {
        $curdat = substr($curdat,0,$tsvlgth-2);
    }
    return addslashes($curdat);   
}
# FUNCTION END

/*
 * -------------------------  MAIN ROUTINE ------------------------
 */

# slight difference depending on whether coming from a new page create or not
if (isset($building) && $building === true) {
    $hikeIndexNo = $hikeRow + 1;
} else {
    $hikeIndexNo = filter_input(INPUT_GET,'hikeIndx');
    $database = '../data/database.xml';
    $xml = simplexml_load_file($database);
    if ($xml === false) {
        $noload = '<p style="margin-left:16px;color:red;font-size:18px">' .
                "Could not load database.xml: contact Site Master</p>";
        die ($noload);
    }
}
# end difference

foreach ($xml->row as $page) {
    if ($page->indxNo == $hikeIndexNo) {
        # Extract relevant data from database:
        $newstyle = true;  // change later if no gpx file
        $hikeTitle = $page->pgTitle;
        $hikeLocale = $page->locale;
        $hikeDifficulty = $page->difficulty;
        $hikeLength = $page->miles . " miles";
        $hikeType = $page->logistics;
        $hikeElevation = $page->feet . " ft";
        $hikeExposure = $page->expo;
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
         * Retrieve data required to form picture rows:
         */
        $descs = [];
        $alblnks = [];
        $piclnks = [];
        $captions = [];
        $aspects = [];
        $widths = [];
        /* Note - some of the imported tsv files have fields enclosed
         * in double quotes, and include a line feed (\n). 
         */
        foreach ($page->tsv->picDat as $img) {
            if ($img->hpg == 'Y') {
                $filename = clean($img->title);
                array_push($descs,$filename);
                array_push($alblnks,$img->alblnk);
                array_push($piclnks,$img->mid);
                $pDesc = clean($img->desc);
                $dateStr = clean($img->date);
                if ($dateStr == '') {
                    array_push($captions,$pDesc);
                } else {
                    $year = substr($dateStr,0,4);
                    $month = intval(substr($dateStr,5,2));
                    $day = intval(substr($dateStr,8,2));  # intval strips leading 0
                    $date = $months[$month-1] . ' ' . $day . ', ' . $year .
                            ': ' . $pDesc;
                    array_push($captions,$date);
                }
                $ht = intval($img->imgHt);
                $wd = intval($img->imgWd);
                array_push($widths,$wd);
                $picRatio = $wd/$ht;
                array_push($aspects,$picRatio);
            }
        }
        $capCnt = count($descs);
        if (strlen($page->aoimg1->name) !== 0) {
            $aoimg1 = '../images/' . $page->aoimg1->name;
            array_push($descs,$page->aoimg1->name);
            array_push($alblnks,'');
            array_push($piclnks,$aoimg1);
            array_push($captions,'');
            $ht = $page->aoimg1->iht;
            $wd = $page->aoimg1->iwd;
            array_push($widths,$wd);
            $imgRatio = $wd/$ht;
            array_push($aspects,$imgRatio);  
        }
        if (strlen($page->aoimg2->name) !== 0) {
            $aoimg2 = '../images/' . $page->aoimg2->name;
            array_push($descs,$page->aoimg2->name);
            array_push($alblnks,'');
            array_push($piclnks,$aoimg2);
            array_push($captions,'');
            $ht = $page->aoimg2->iht;
            $wd = $page->aoimg2->iwd;
            array_push($widths,$wd);
            $imgRatio = $wd/$ht;
            array_push($aspects,$imgRatio);  
        }
        /*
         *  End picture row data prep
         */
        $hikeTips = $page->tipsTxt;
        $hikeTips = preg_replace("/\s/"," ",$hikeTips);
        $hikeInfo = '<p id="hikeInfo">' . $page->hikeInfo . "</p>\n";
        $hikeRefs = $page->refs;
        # there may or may not be any proposed data or actual data to present
        $hikeReferences = makeHtmlList(References,$hikeRefs);
        $hikeProposedData = $page->dataProp;
        $hikeActualData = $page->dataAct;
        if ( $hikeProposedData->prop->count() !== 0 || $hikeActualData->act->count() !== 0 ) {
            $fieldsets = true;
            $datasect = "<fieldset>\n" . 
                    '<legend id="flddat">GPS Maps &amp; Data</legend>' . "\n";
            if ($hikeProposedData->prop->count() !== 0) {
                    $datasect .= makeHtmlList(Proposed,$hikeProposedData);
            }
            if ($hikeActualData->act->count() !== 0) {
                    $datasect .= makeHtmlList(Actual,$hikeActualData);
            }
            $datasect .= "</fieldset>\n";
        }
        # setup hike page map if newstyle
        if ($newstyle) {
            # dynamically created map:
            $extLoc = strrpos($gpxfile,'.');
            $gpsvMap = substr($gpxfile,0,$extLoc); # strip file extension
            # holding place for page's hike map (deleted when page exited)
            $tmpMap = '../maps/tmp/' . $gpsvMap . '.html';
            if ( ($mapHandle = fopen($tmpMap,"w")) === false) {
                $mapmsg = "Contact Site Master: could not open tmp map file: " . $tmpMap . ", for writing";
                die ($mapmsg);
            }
            $photos = $page->tsv;
            $fpLnk = 'MapLink' . fullMapOpts . '&hike=' . $hikeTitle . 
                '&gpx=' . $gpxPath;
            include "../php/makeGpsv.php";
            fputs($mapHandle,$html);
            fclose($mapHandle);
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
    <script type="text/javascript">var ajaxDone = false;</script>
    <?php if ($newstyle) {
        echo '<script type="text/javascript">var iframeWindow;</script>';
        echo '<script src="../scripts/canvas.js"></script>';
    } ?>
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
    '</div>' . "\n";
} else { # newstyle has the side panel with map & chart on right
    # SIDE PANEL:
    echo '<div id="sidePanel">' . "\n" . '<p id="stats"><strong>Hike Statistics</strong></p>' . "\n";
        echo '<p id="summary">' . "\n" .
            'Nearby City / Locale: <span class=sumClr>' . $hikeLocale . "</span><br />\n" .
            'Hike Difficulty: <span class=sumClr>' . $hikeDifficulty . "</span><br />\n" .
            'Total Length of Hike: <span class=sumClr>' . $hikeLength . "</span><br />\n" .
            'Max to Min Elevation: <span class=sumClr>' . $hikeElevation . "</span><br />\n" .
            'Logistics: <span class=sumClr>' . $hikeType . "</span><br />\n" .
            'Exposure Type: <span class=sumClr>' . $hikeExposure . "</span><br />\n" .
            'Seasons : <span class=sumClr>' . $hikeSeasons . "</span><br />\n" .
            '"Wow" Factor: <span class=sumClr>' . $hikeWow . "</span></p>\n";
        echo '<p id="addtl"><strong>More!</strong></p>' . "\n";        
        echo '<p id="mlnk"><a href="../maps/gpsvMapTemplate.php?map_name=' . 
                $fpLnk . '" target="_blank">Full Page Map Link</a></p>' ."\n";
        echo '<p id="albums">For improved photo viewing,<br />check out the following album(s):</p>' .
                "\n" . '<p id="alnks"><a href="' . $hikePhotoLink1 . 
                '" target="_blank">Photo Album Link</a>' . "\n";
        if (strlen($hikePhotoLink2) !== 0) {
            echo '<br /><a href="' . $hikePhotoLink2 . 
                    '" target="_blank">Additional Album Link</a>' . "\n";
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
?>
<div id="imgArea"></div>
<?php
# clear floats when no pics:
echo '<div style="clear:both;">' . "\n";
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

if ($fieldsets) {
    echo $datasect;
}
echo '</div>';
?>

<p id="ptype" style="display:none">Hike</p>
<div id="dbug"></div>

<div class="popupCap"></div>

<script type="text/javascript">
    <?php
    /* Oddly, using json_encode on each array resulted in different treatment
     * on the first 3 arrays - e.g. [{"0":item0},{"0":item1} etc.] whereas later
     * items were rendered simply [item0,item1,item2, etc]: Hence the use of 
     * implode encapsulated as string.
     */
    echo 'var photocnt = ' . $capCnt . ";\n";
    echo 'var d = "' . implode("|",$descs) . '";' . "\n";
    echo 'var al = "' . implode("|",$alblnks) . '";' . "\n";
    echo 'var p = "' . implode("|",$piclnks) . '";' . "\n";
    echo 'var c = "' . implode("|",$captions) . '";' . "\n";
    echo 'var as = "' . implode("|",$aspects) . '";' . "\n";
    echo 'var w = "' . implode("|",$widths) . '";' . "\n";
    ?>
</script>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/picRowFormation.js"></script>
<script src="../scripts/captions.js"></script>
<script src="../scripts/rowManagement.js"></script>
<?php if ($newstyle) {
    echo '<script src="../scripts/dynamicChart.js"></script> ';
} ?>
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
