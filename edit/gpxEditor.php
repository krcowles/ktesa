<?php
/**
 * Allows user to edit a validated gpx file. When done, the user
 * can download the file containing the edits as 'editedGpx.gpx'.
 * This script is invoked via the gpx menu editor (modal). For 
 * upload issues, a blank page is presented listing the problems
 * encountered.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('post');

$trackno = filter_input(INPUT_POST, 'trackno', FILTER_VALIDATE_INT) - 1;
$backuri  = urldecode(filter_input(INPUT_POST, 'backurl'));
$msg = '';
$noupload = false;
$path_literals = '';

// prevent errors seen in production mode: someone shortcutting the system?
try {
    $ifile = $_FILES['file2edit']['name'];
    if (empty($ifile)) {
        $noupload = true;
    }
} catch (Exception $e) {
    $msg =  "The required input file cannot be located;<br />" .
        "This script must be executed via the GPX Editor on the menu bar<br />";
    $msg .= "If you need help, submit details to: admin@nmhikes.com<br />";
    $noupload = true;
}
if (!$noupload) {
    $file_data = uploadFile(prepareUpload('file2edit'));
    $msg .= $_SESSION['alerts'][0];
    unset($_SESSION['alerts']); // alerts may have been set in uploadFile
    if ($file_data === 'none') {
        $noupload = true;
    }
}
// stil good? $file_data will be defined
if (!$noupload) {
    $gpxfile = pathinfo($file_data, PATHINFO_BASENAME);
    // formulate json data for google maps polyline path
    $gpxdat = simplexml_load_file($file_data);
    if ($gpxdat === false) {
        throw new Exception(
            __FILE__ . "Line " . __LINE__ . "Could not load {$ifile} as " .
            "simplexml"
        );
    }
    unlink($file_data);
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
        $path_literals .= '{"lat":' . $gpxlats[$k] . ',"lng":' .
            $gpxlons[$k] . '}';
        if ($k !== $items -1) {
            $path_literals .= ',';
        }
    }
    $path_literals .= ']';
}
?>
<!DOCTYPE html>
<html lang="eng-us">
    <head>
        <title>GPX Editor</title>
        <link href="../styles/gpxEditor.css" rel="stylesheet" />
        <link href="../styles/bootstrap.min.css" rel="stylesheet" />
        <script src="../scripts/jquery.js"></script>
        <script type="text/javascript">
            var trk_json = <?=$path_literals;?>;
            var trackno  = <?=$trackno;?>;
        </script>
    </head>
    
    <body>
        <!-- bootstrap must be in body, not in head -->
        <script src="../scripts/bootstrap.min.js"></script>
        <p id="return_url" style="display:none;"><?=$backuri;?></p>

        <?php if ($noupload) : ?>
        <div style="margin-left:24px;font-size:20px;">
            <form method="get" action="../pages/home.php">
                <p><em>The following issue has occurred preventing upload
                    of the selected gpx file [<?=$ifile;?>]:</em><br />
                <?=$msg;?></p>
                <button id="restart">Exit Editor</button>
            </form>
        </div>
        <?php else : ?>
        <div id="editbar">
            <a id="start_save" class="link-button">Save Edited File</a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <button id="del" class="regfonts">Delete Pts</button>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <a id="udel" class="link-button udel_off">
                Undo Delete(s)</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span id="pttxt" class="regfonts">Point ID: </span>
            <input type="text" id="ptid" />
            <button id="back">Exit Editor</button> 
        </div>
        <span id="dproc">&nbsp;&nbsp;[Select points in side table]</span>
    
        <div id="map"></div>
        <div id="gpxpts"></div>

        <!-- GPX Edit Save Modal -->
        <div id="ged_save" class="modal fade" tabindex="-1"
            aria-labelledby="GPX File Save" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Save Your Edits</h5>
                        <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Please enter a new name for the edited file; The .gpx
                        file extension will be automatically added.<br/><br/>
                        <input id="file2save" type="text"
                            placeholder="Enter Name" />  
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button id="edits_save" type="button"
                            class="btn btn-success">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="./loadGPX.js"></script>
        <script src="./gpxEditor.js"></script>
        <script src="<?=GOOGLE_MAP;?>" defer></script>
        <?php endif; ?>
    </body>

</html>
