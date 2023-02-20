<?php
/**
 * Allows user to edit a validated gpx file. When done, the user
 * can download the file containing the edits as 'editedGpx.gpx'.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$trackno = filter_input(INPUT_POST, 'trackno', FILTER_VALIDATE_INT) - 1;
$backuri  = urldecode(filter_input(INPUT_POST, 'backurl'));
$_SESSION['user_alert'] = '';
$file_data = validateUpload('file2edit', false);
if ($file_data['type'] !== 'gpx' && empty($_SESSION['user_alert'])) {
    $_SESSION['user_alert'] = "Incorrect file type [Not GPX]";
}
if (empty($_SESSION['user_alert'])) {
    $gpxfile = $file_data['file'];
    $tmploc  = $file_data['loc'];
    // formulate json data for google maps polyline path
    $gpxdat = simplexml_load_file($tmploc);
    if ($gpxdat === false) {
        throw new Exception(
            __FILE__ . "Line " . __LINE__ . "Could not load {$gpxfile} as " .
            "simplexml"
        );
    }
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }
    file_put_contents('usergpx.gpx', $gpxdat->asXML());
    $gpxlats = [];
    $gpxlons = [];
    $plat = 0;
    $plng = 0;
    /**
     * Here, data from multiple <trkseg>'s within a <trk> are simply
     * consecutively appended to the arrays; When the gpx files is saved
     * however, trksegs are independently updated.
     */
    foreach ($gpxdat->trk[$trackno]->trkseg as $trackdat) {
        foreach ($trackdat->trkpt as $datum) {
            if (!( $datum['lat'] === $plat && $datum['lon'] === $plng )) {
                $plat = $datum['lat'];
                $plng = $datum['lon'];
                array_push($gpxlats, (float)$plat);
                array_push($gpxlons, (float)$plng);
            }
        }
    }
    $path_literals = '[';
    $items = count($gpxlats);
    for ($k=0; $k<$items; $k++) {
        $path_literals .= '{"lat":' . $gpxlats[$k] . ',"lng":' . $gpxlons[$k] . '}';
        if ($k !== $items -1) {
            $path_literals .= ',';
        }
    }
    $path_literals .= ']';
} else {
    echo "<div style='color:brown;margin-left:24px;font-size:18px;".
        "font-weight:bold;'>" .
        "<p>There was problem with the specified GPX file:</p>" .
        $_SESSION['user_alert'] . "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="eng-us">
    <head>
        <title>GPX Editor</title>
        <link href="./gpxEditor.css" rel="stylesheet" />
        <script src="../scripts/jquery.js"></script>
        <script type="text/javascript">
            var trk_json = <?=$path_literals;?>;
            var trackno  = <?=$trackno;?>;
        </script>
    </head>
    
    <body>
        <div id="editbar"> 
            <a class="link-button" href="usergpx.gpx"
                download="editedGpx.gpx">Save Edited File</a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <button id="del" class="regfonts">Delete Pts</button>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <a id="udel" class="link-button udel_off">
                Undo Delete(s)</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span id="pttxt" class="regfonts">Point ID: </span>
            <input type="text" id="ptid" />
        </div>
        <span id="dproc">&nbsp;&nbsp;[Select points in side table]</span>
    
        <div id="map"></div>
        <div id="gpxpts"></div>
        <script src="./loadGPX.js"></script>
        <script src="./gpxEditor.js"></script>
        <script src="<?=GOOGLE_MAP;?>" defer></script>
    </body>
</html>
