<?php
/**
 * This is the main routine utilized to display any given hike page.
 * 
 * Depending on the query string ('age'), either the in-edit hikes will be
 * accessed, or the already published hikes. All MySQL tables for
 * in-edit hikes begin with the letter "E". If a page is being created for
 * the first time, the variable $building will be true and the hike index
 * number will be extracted differently.
 * 
 * @package Page_Display
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../php
 */
/**
 * The variable $hikeIndexNo is established below and is used throughout
 * to locate data corresponding to this unique hike identifier.
 */
$tbl = filter_input(INPUT_GET, 'age');
if (isset($tbl) && $tbl === 'new') {
    $hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx');
    $ehikes = true;
} elseif (isset($building) && $building === true) {
    $hikeIndexNo = $hikeRow;
    $ehikes = true;
} else {
    $hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx');
    $ehikes = false;
}
/**
 * The following variables are used to define the tables to be used
 * in the MySQL queries, based on whether or not in-edit hike data
 * is required.
 */
if ($ehikes) {
    $htable = 'EHIKES';
    $rtable = 'EREFS';
    $gtable = 'EGPSDAT';
    $ttable = 'ETSV';
    $tbl = 'new';
} else {
    $htable = 'HIKES';
    $rtable = 'REFS';
    $gtable = 'GPSDAT';
    $ttable = 'TSV';
    $tbl = 'old';
}
/**
 * The get_HIKES_row.php utility extracts data from the $htable
 * based on the $hikeIndexNo established above.
 */
require "../mysql/get_HIKES_row.php";
if ($gpxfile == '') {
    $newstyle = false;
    $gpxPath = '';
} else {
    $newstyle = true;
    $gpxPath = '../gpx/' . $gpxfile;
}
/**
 * The get_TSV_row.php utility extracts data about the photos to
 * be displayed on the page.
 */
require "../mysql/get_TSV_row.php";
$capCnt = count($descs);
if (is_array($hikeAddonImg1)) {
    $aoimg1 = '../images/' . $hikeAddonImg1[0];
    array_push($descs, $hikeAddonImg1);
    array_push($alblnks, '');
    array_push($piclnks, $aoimg1);
    array_push($captions, '');
    $ht = $hikeAddonImg1[1];
    $wd = $hikeAddonImg1[2];
    array_push($widths, $wd);
    $imgRatio = $wd/$ht;
    array_push($aspects, $imgRatio);
}
if (is_array($hikeAddonImg2)) {
    $aoimg2 = '../images/' . $hikeAddonImg2[0];
    array_push($descs, $hikeAddonImg2[0]);
    array_push($alblnks, '');
    array_push($piclnks, $aoimg2);
    array_push($captions, '');
    $ht = $hikeAddonImg2[1];
    $wd = $hikeAddonImg2[2];
    array_push($widths, $wd);
    $imgRatio = $wd/$ht;
    array_push($aspects, $imgRatio);
}
/**
 * There are two possible types of hike page displays. If the hike page
 * has a map and elevation chart to display, the variable $newstyle is
 * true, and these items are displayed.  Otherwise, a page with a hike
 * summary table is presented with photos and information, but no map or
 * elevation chart ($newstyle is false).
 */
if ($newstyle) {
    /**
     * In the case of hike map and elevation chart, in order for the map to be
     * displayed in an iframe, a file is created and stored in the maps/tmp
     * sub-directory. The file is deleted upon exiting the page.
     */
    $extLoc = strrpos($gpxfile, '.');
    $gpsvMap = substr($gpxfile, 0, $extLoc); // strip file extension
    $tmpMap = "../maps/tmp/{$gpsvMap}.html";
    if (($mapHandle = fopen($tmpMap, "w")) === false) {
        $mapmsg = "Contact Site Master: could not open tmp map file: " .
            $tmpMap . ", for writing";
        die($mapmsg);
    }
    $fpLnk = "../maps/fullPgMapLink.php?maptype=page&hike={$hikeTitle}" .
        "&gpx={$gpxPath}&hno={$hikeIndexNo}&tbl={$tbl}";
    $map_opts = [
        'show_geoloc' => 'false',
        'zoom' => 'auto',
        'map_type' => 'ARCGIS_TOPO_WORLD',
        'street_view'=> 'false',
        'zoom_control' => 'large',
        'map_type_control' => 'menu',
        'center_coordinates' => 'true',
        'measurement_tools' => 'false',
        'utilities_menu' => "{ 'maptype':true, 'opacity':true, " .
            "'measure':true, 'export':true }",
        'tracklist_options' => 'false',
        'marker_list_options' => 'false',
        'show_markers' => 'true',
        'dynamicMarker' => 'true'  
    ];
    include "../php/makeGpsv.php";
    fputs($mapHandle, $maphtml);
    fclose($mapHandle);
}
?>
<!DOCTYPE html>
<html lang="en-us">
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
    <?php
    if ($newstyle) {
        echo '<script type="text/javascript">var iframeWindow;</script>';
        echo '<script src="../scripts/canvas.js"></script>';
    }
    ?>
</head>

<body>
    
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $hikeTitle;?></p>
<p id="gpx" style="display:none"><?php echo $gpxPath;?></p>
<?php
 /* There are two page styles from which to choose:
  *  if there is either no map OR no chart, the "original" style is presented;
  *  if there is both a map AND a chart, the "new" style is presented
  */
if (!$newstyle) {
    /* ---------------------------- OLD STYLE -------------------------- */
    echo '<div id="hikeSummary">' . PHP_EOL .
        '<table id="topper">' . PHP_EOL .
            '<thead>' .  PHP_EOL .
                '<tr>' .  PHP_EOL .
                    '<th>Difficulty</th>' .  PHP_EOL .
                    '<th>Round-trip</th>' .  PHP_EOL .
                    '<th>Type</th>' .  PHP_EOL .
                    '<th>Elev. Chg.</th>' .  PHP_EOL .
                    '<th>Exposure</th>' .  PHP_EOL .
                    '<th>Wow Factor</th>' .  PHP_EOL .
                    '<th>Facilities</th>' .  PHP_EOL .
                    '<th>Seasons</th>' . PHP_EOL;
    if ($hikePhotoLink2 == '') {
        echo "<th>Photos</th>";
    }
    echo '<th>By Car</th>' .  PHP_EOL .
        '</tr>' .  PHP_EOL .
        '</thead>' .  PHP_EOL .
        '<tbody>' .  PHP_EOL .
        '<tr>' .  PHP_EOL .
        '<td>' . $hikeDifficulty . '</td>' .  PHP_EOL .
        '<td>' . $hikeLength . '</td>' .  PHP_EOL .
        '<td>' . $hikeType . '</td>' .  PHP_EOL .
        '<td>' . $hikeElevation . '</td>' .  PHP_EOL .
        '<td>' . $hikeExposure . '</td>' .  PHP_EOL .
        '<td>' . $hikeWow . '</td>' .  PHP_EOL .
        '<td>' . $hikeFacilities . '</td>' .  PHP_EOL .
        '<td>' . $hikeSeasons . '</td>' .  PHP_EOL;
    if ($hikePhotoLink2 == '') {
        echo '<td><a href="' . $hikePhotoLink1 . '" target="_blank">' .
            '<img style="margin-bottom:0px;border-style:none;"' .
            ' src="../images/album_lnk.png"' .
            ' alt="photo album link icon" /></a></td>';
    }
    echo '<td><a href="' . $hikeDirections . '" target="_blank">' .
        '<img style="margin-bottom:0px;padding-bottom:0px;"' .
        ' src="../images/dirs.png" alt="google driving directions" />' .
        '</a></td>' .  PHP_EOL .
        '</tr>' .  PHP_EOL .
        '</tbody>' .  PHP_EOL .
        '</table>' .  PHP_EOL .
        '</div>' .   PHP_EOL ;
} else { // newstyle has the side panel with map & chart on right
    /* ---------------------------- NEW STYLE -------------------------- */
    // SIDE PANEL:
    echo '<div id="sidePanel">' . "\n" .
        '<p id="stats"><strong>Hike Statistics</strong></p>' . "\n";
        echo '<p id="summary">' . "\n" .
            'Nearby City / Locale: <span class=sumClr>' . $hikeLocale .
            "</span><br />\n" . 'Hike Difficulty: <span class=sumClr>' .
            $hikeDifficulty . "</span><br />\n" .
            'Total Length of Hike: <span class=sumClr>' . $hikeLength .
            "</span><br />\n" . 'Max to Min Elevation: <span class=sumClr>' .
            $hikeElevation . "</span><br />\n" . 'Logistics: <span class=sumClr>' .
            $hikeType . "</span><br />\n" .
            'Exposure Type: <span class=sumClr>' . $hikeExposure .
            "</span><br />\n" . 'Seasons : <span class=sumClr>' .
            $hikeSeasons . "</span><br />\n" .
            '"Wow" Factor: <span class=sumClr>' . $hikeWow . "</span></p>\n";
        
        
        echo '<p id="addtl"><strong>More!</strong></p>' . "\n";
        echo '<p id="mlnk">View <a href="' . $fpLnk . '" target="_blank">' .
            'Full Page Map</a><br />';
        echo '<span class="track">View <a id="view" href="' .
            $gpxPath . '" target="_blank">GPX File</a></span><br />';
        echo '<span class="track">Download <a id="dwn" href="' . $gpxPath .
                '" download>GPX File</a></span></p>';
        echo '<p id="albums">For improved photo viewing,<br />check out ' .
            'the following album(s):</p>' .
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
        echo '<p id="scrollmsg">Scroll down to see images, hike description, ' .
            'reference sources and additonal information as applicable</p>' . "\n";
        echo '<p id="closer">If you are having problems with this page, please: ' .
            '<a href="mailto:krcowles29@gmail.com">send us a note!</a></p>' ."\n";
    echo '</div>';
    
    // MAP AND CHART ON RIGHT:
    // map:
    //echo '<iframe id="mapline" src="../maps/gpsvMapTemplate.php?map_name=' .
    echo '<iframe id="mapline" src="' .
                $tmpMap . '"></iframe>' . "\n";
    // elevation chart:
    /*
    echo '<script>' . "\n" .
            'var alts = ' . $jsElevation . ';' . "\n" . '</script>' . "\n";
     */
    echo '<div data-gpx="' . $gpxPath .
        '" id="chartline"><canvas id="grph"></canvas></div>' . "\n";
}
/* BOTH PAGE STYLES */
// clear floats when no pics:
echo '<div style="clear:both;"><br />' . PHP_EOL;
if ($hikeTips !== '') {
    echo '<div id="trailTips"><img id="tipPic" src="../images/tips.png" ' .
        'alt="special notes icon" /><p id="tipHdr">TRAIL TIPS!</p>' .
        '<p id="tipNotes">' .
        htmlspecialchars_decode($hikeTips, ENT_COMPAT) . '</p></div>' . "\n";
}
echo '<div id="hikeInfo">' . $hikeInfo . "</div></div><br />" . PHP_EOL;
require 'relatedInfo.php';
if ($bop !== '') {
    echo $bop;
}
?>
<div id="imgArea"></div>
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
    echo 'var photocnt = ' . $capCnt . PHP_EOL;
    echo 'var d = "' . implode("|", $descs) . '";' . PHP_EOL;
    echo 'var al = "' . implode("|", $alblnks) . '";' . PHP_EOL;
    echo 'var p = "' . implode("|", $piclnks) . '";' . PHP_EOL;
    echo 'var c = "' . implode("|", $captions) . '";' . PHP_EOL;
    echo 'var as = "' . implode("|", $aspects) . '";' . PHP_EOL;
    echo 'var w = "' . implode("|", $widths) . '";' . PHP_EOL;
    ?>
</script>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/picRowFormation.js"></script>
<script src="../scripts/captions.js"></script>
<script src="../scripts/rowManagement.js"></script>
<?php if ($newstyle) {
    echo '<script src="../scripts/dynamicChart.js"></script>' . PHP_EOL;
} ?>
<?php if ($newstyle) : ?>
<script type="text/javascript">
    window.onbeforeunload = deleteTmpMap;
    function deleteTmpMap() {
        $.ajax({
            url: '../php/tmpMapDelete.php',
            data: {'file' : "<?php echo $tmpMap;?>" },
            success: function (response) {
               var msg = "Map deleted: " + "<?php echo $tmpMap?>";
            },
            error: function () {
               var msg = "Map NOT deleted: " + "<?php echo $tmpMap?>";
            }
        });
    }
</script>
<?php endif; ?>
</body>
</html>
