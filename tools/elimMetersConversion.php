<?php
/**
 * Redo all JSON files to correct gross errors induced by virtue
 * of the fact that some gpx files can occasionally list an
 * elevation <ele> of ~0 feet. The entry for 'feet' in the 
 * database is only used on the home page to provide elevation
 * change data for the hike infoWin popups.
 * 
 * To run this script it is necessary to move the directory 
 * 'OldGPX' from ~/src to the ktesa/tools directory. OldGPX
 * holds all the original gpx files used before the site was
 * modified to eliminate them, and it is kept up to date in
 * case the files are needed again for scripts like this one.
 * The directory 'json_check' was created by moving the previous
 * (incorrect) json files to a separate location so that the
 * newly formed json files could be compared to them for unintended
 * errors. Note that no gpx category contains more than 1 gpx file;
 * some gpx files may generate multiple json files.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

/**
 * This function creates the actual json file and places it in the
 * json directory
 * 
 * @param PDO    $pdo  The database object
 * @param string $indx The database indxNo for the hike
 * @param string $type The json label type (e.g. main, add1, ...)
 * @param string $file The gpx file to be processed and converted
 * @param number $ext  The file extension to start with
 * 
 * @return number $next Which extension to use next if repeat hikeNo
 */
function makeJSON($pdo, $indx, $type, $file, $ext)
{
    // get file as simple xml
    $file_addr = "./OldGPX/" . $file;
    $gpxdat = simplexml_load_file($file_addr);
    if ($gpxdat === false) {
        throw new Exception(
            __FILE__ . "Line " . __LINE__ . "Could not load {$file} as " .
            "simplexml"
        );
    }
    // If file happens to contain routes instead of tracks, convert:
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }
    $track_names = [];
    $trkcnt = $gpxdat->trk->count();
    for ($j=0; $j<$trkcnt; $j++) {
        $trkname= $gpxdat->trk[$j]->name->__toString();
        $track_name = empty($trkname) ? "No Track Name" : $trkname;
        array_push($track_names, $track_name);
    }
    // $track_files: gpxLatLng returns arrays: $gpxlats, $gpxlngs, $gpxelev
    $track_files = gpxLatLng($gpxdat, $trkcnt);
    $newJson = [];
    for ($k=0; $k<$trkcnt; $k++) {
        $echgs = [];
        $json_array = $track_files[$k]; // $k is the kth track
        $no_of_entries = count($json_array[0]); // lats, lngs, eles have same cnt
        $jdat = '{"name":"' . $track_names[$k] . '","trk":[';   // array of objects
        for ($n=0; $n<$no_of_entries; $n++) {
            $elev = $json_array[2][$n];
            $jdat .= '{"lat":' . $json_array[0][$n] . ',"lng":' .
                $json_array[1][$n] . ',"ele":' . $elev . '},';
            // some gpx files record an element with ele = 0
            if ($elev > 0.1 && $elev !== null && $elev !== 'undefined') {
                if ($indx == "20") {
                    $x = 1;
                }
                array_push($echgs, $elev);
            }
            
        }
        if ($type === 'pmn' && $ext === 1) {
            $eReq = "UPDATE `HIKES` SET `feet`=? WHERE `indxNo`=?;";
            $eData = $pdo->prepare($eReq);
            $chg = round(max($echgs) - min($echgs), 0);
            $eData->execute([$chg, $indx]);
        }
        $jdat = rtrim($jdat, ","); 
        $jdat .= ']}';

        // now save the json file data for this track
        $json_fname = $type . $indx . "_" . $ext++ . ".json";
        array_push($newJson, $json_fname);
        $baseaddr = "../json/" . $json_fname;
        file_put_contents($baseaddr, $jdat);
    }
    return [$ext, $newJson];
}


$allHikesReq = "SELECT `indxNo` from `HIKES`;";
$allHikes = $pdo->query($allHikesReq)->fetchAll(PDO::FETCH_COLUMN);
$gpxMain = [];
$gpxAdd1 = [];
$gpxAdd2 = [];
$gpxAdd3 = [];
$allGpx  = [];
$allJson = [];
foreach ($allHikes as $hike) {
    $hikeGpx = getGpxArray($pdo, $hike, 'pub');
    if (!empty($hikeGpx['main'])) {
        $file = array_keys($hikeGpx['main'])[0];
        array_push($allGpx, $file);
        $main_id = $hike . "_" . $file;
        array_push($gpxMain, $main_id);
        $allJson = array_merge($allJson, array_values($hikeGpx['main'])[0]);
    }
    if (!empty($hikeGpx['add1'])) {
        $file = array_keys($hikeGpx['add1'])[0];
        array_push($allGpx, $file);
        $add1_id = $hike . "_" . $file;
        array_push($gpxAdd1, $add1_id);
        $allJson = array_merge($allJson, array_values($hikeGpx['add1'])[0]);
    }
    if (!empty($hikeGpx['add2'])) {
        $file = array_keys($hikeGpx['add2'])[0];
        array_push($allGpx, $file);
        $add2_id = $hike . "_" . $file;
        array_push($gpxAdd2, $add2_id);
        $allJson = array_merge($allJson, array_values($hikeGpx['add2'])[0]);
    }
    if (!empty($hikeGpx['add3'])) {
        $file = array_keys($hikeGpx['add3'])[0];
        array_push($allGpx, $file);
        $add3_id = $hike . "_" . $file;
        array_push($gpxAdd3, $add3_id);
        $allJson = array_merge($allJson, array_values($hikeGpx['add3'])[0]);
    }
}
$allGPSReq = "SELECT `indxNo`,`url` FROM `GPSDAT` WHERE `label` LIKE 'GPX%'";
$allGPS = $pdo->query($allGPSReq)->fetchAll(PDO::FETCH_ASSOC);
$GPSarray = [];
foreach ($allGPS as $json) {
    $id = $json['indxNo'];
    $stdClass = json_decode($json['url'], true);
    foreach ($stdClass as $key => $value) {
        array_push($allGpx, $key);
        $gps_id = $id . "_" . $key;
        array_push($GPSarray, $gps_id);
        $allJson = array_merge($allJson, $value);
    }
}
/**
 * Make sure that all 'OldGPX' files are present in the db ($allGpx)
 */
$retrieved = scandir("./OldGPX");
$gpx_files = array_diff($retrieved, [".", "..", "filler.gpx", "Picacho.kml"]);
foreach ($gpx_files as $file) {
    if (!in_array($file, $allGpx)) {
        echo "OldGPX file " . $file . " not located in database;<br />";
    }
}
/**
 * Make sure all db files are present in OldGPX
 */
foreach ($allGpx as $dbfile) {
    if (!in_array($dbfile, $gpx_files)) {
        "Database file " . $dbfile . " not found in OldGPX;<br>";
    }
}
/**
 * Generate new json files for all the gpx files identified in the db
 * NOTE: Minor differences in Canada del Ojo, Red Mesa West, and
 * additional file for Ball Ranch (2017) - all ignored
 */
$created = [];
foreach ($gpxMain as $main) {
    $file_loc = strpos($main, "_");
    $hikeNo = substr($main, 0, $file_loc);
    $filename = substr($main, $file_loc+1);
    $json_data = makeJSON($pdo, $hikeNo, 'pmn', $filename, 1);
    $created = array_merge($created, $json_data[1]);
}
foreach ($gpxAdd1 as $add1) {
    $file_loc = strpos($add1, "_");
    $hikeNo = substr($add1, 0, $file_loc);
    $filename = substr($add1, $file_loc+1);
    $json_data = makeJSON($pdo, $hikeNo, 'pa1', $filename, 1);
    $created = array_merge($created, $json_data[1]);
}
foreach ($gpxAdd2 as $add2) {
    $file_loc = strpos($add2, "_");
    $hikeNo = substr($add2, 0, $file_loc);
    $filename = substr($add2, $file_loc+1);
    $json_data = makeJSON($pdo, $hikeNo, 'pa2', $filename, 1);
    $created = array_merge($created, $json_data[1]);
}
foreach ($gpxAdd3 as $add3) {
    $file_loc = strpos($add3, "_");
    $hikeNo = substr($add3, 0, $file_loc);
    $filename = substr($add3, $file_loc+1);
    $json_data = makeJSON($pdo, $hikeNo, 'pa3', $filename, 1);
    $created = array_merge($created, $json_data[1]);
}
$gpx_extensions = [];
foreach ($GPSarray as $gps) {
    $file_loc = strpos($gps, "_");
    $hikeNo = substr($gps, 0, $file_loc);
    $filename = substr($gps, $file_loc+1);
    $next_ext = 1;
    $gps_keys = array_keys($gpx_extensions);
    if (!in_array($hikeNo, $gps_keys)) {
        $gpx_extensions[$hikeNo] = $next_ext;
    } else {
        $next_ext = $gpx_extensions[$hikeNo];  
    }
    $new_next = makeJSON($pdo, $hikeNo, 'pgp', $filename, $next_ext);
    $gpx_extensions[$hikeNo] = $new_next[0];
    $created = array_merge($created, $new_next[1]);
}
/**
 * Compare the list of new json files created to those that were
 * defined previously (arbitrarily held in ktesa/json_check)
 */
$previous = scandir("../json_check");
$prev = array_diff(
    $previous, [".", "..", ".DS_Store", "areas.json", "filler.json", 
        "package.json", "package-lock.json"]
);

foreach ($created as $new) {
    if (!in_array($new, $prev)) {
        echo "New file [" . $new . "] was not previously created;<br>";
    }
}
/**
 * Have any of the previous files not been re-created?
 */
foreach ($prev as $old) {
    if (!in_array($old, $created)) {
        echo "Previous file [" . $old . "] was not re-created<br>";
    }
}
/**
 * Did this actually solve the problem??
 */
foreach ($created as $new) {
    $old = array_search($new, $prev);
    $newly_created = file_get_contents("../json/" . $new);
    $prev_created  = file_get_contents("../json_check/" . $prev[$old]);
    $result = strncmp($newly_created, $prev_created, 200);
    if ($result !== 0) {
        echo "Difference found: " .  $new . " vs " . $prev[$old] . "<br>";
    }
}
echo "JSON file creation DONE!<br>";
/**
 * Re-calculate max-min for each hike (main file only) and update db
 */
