<?php
/**
 * Convert one or more gpx files to their equivalent track files: specify
 * either an alphabetic range or a hike name in the query string.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$range = isset($_GET['range']) ? filter_input(INPUT_GET, 'range') : false;
$hike  = isset($_GET['hike'])  ? filter_input(INPUT_GET, 'hike')  : false;

if ($hike) {
    $hikeReq = "SELECT `indxNo`,`gpx` FROM `HIKES` WHERE `pgTitle`=?;";
    $c_data   = $pdo->prepare($hikeReq);
    $c_data->execute([$hike]);
    $conv_data = $c_data->fetch(PDO::FETCH_ASSOC);
    $hikeNo = $conv_data['indxNo'];
    $gpxField = $conv_data['gpx'];
    // gpx may be a comma-separated list
    $gpxfiles = explode(",", $gpxField);
    $mainfile = "../gpx/" . $gpxfiles[0];
    $gpxdat = simplexml_load_file($mainfile);
    if ($gpxdat === false) {
        throw new Exception(
            __FILE__ . "Line " . __LINE__ . "Could not load {$gpxfile} as " .
            "simplexml"
        );
    }
    $fileName = $gpxdat->trk->name->__toString();
    $track_files = gpxLatLng($gpxdat, 1); // returns array of arrays
    $json_array = $track_files[0];
    $no_of_entries = count($json_array[0]); // lats, lngs, eles have same cnt
    $jdat = '{"name":"' . $fileName . ',"trk":[';   // array of objects
    for ($n=0; $n<$no_of_entries; $n++) {
        $jdat .= '{"lat":' . $json_array[0][$n] . ',"lng":' .
            $json_array[1][$n] . ',"ele":' . $json_array[2][$n] . '},';
    }
    $jdat = rtrim($jdat, ","); 
    $jdat .= ']}';
    $json_data .= $jdat;
    // now save the json file data for this track
    $basename = 'pmn' . $hikeNo . "_1.json";
    $jname = "../json/" . $basename;
    file_put_contents($jname, $json_data);
} elseif ($range) {
    $list = explode("-", $range);
    $start = $list[0];
    // NOTE: first record will be $start + 1...
    $noOfRecords = $list[1];
    $rangeReq
        = "SELECT `indxNo`,`gpx` FROM `HIKES` LIMIT {$start}, {$noOfRecords};";
    $c_hikes = $pdo->query($rangeReq)->fetchAll(PDO::FETCH_ASSOC);

    // Start with empty array and fill as available
    $gpx_array = ['main'=>[], 'add1'=>[], 'add2'=>[],'add3'=>[]];
    foreach ($c_hikes as $hike) {
        // Start with empty array and fill as available
        $gpx_array = ['main'=>[], 'add1'=>[], 'add2'=>[],'add3'=>[]];
        if (!empty($hike['gpx'])) {
            $hikeNo = $hike['indxNo'];
            $oldgpx = $hike['gpx']; // original list of files for hike
            // gpx may be a comma-separated list
            $gpxfiles = explode(",", $oldgpx);
            $noOfGpx = count($gpxfiles);
            // there can only be a MAX of: 'main' + 3 'additionl' = 4 files
            for ($k=0; $k<4; $k++) {
                /**
                 * In the case of multiple gpx files (not multiple tracks)
                 * each $gpx_array key is assigned via the switch
                 * statement. Any gpx $label can contain multiple tracks.
                 */
                switch ($k) {
                case 0:
                    $key = 'main';
                    $base  = 'pmn';
                    break;
                case 1:
                    $key = 'add1';
                    $base  = 'pa1';
                    break;
                case 2:
                    $key = 'add2';
                    $base  = 'pa2';
                    break;
                case 3:
                    $key = 'add3';
                    $base  = 'pa3';
                }
                $json_value_array = [];
                if (($k+1) <= $noOfGpx) {
                    $fileName = $gpxfiles[$k]; // array of indiv. gpx files in `gpx`
                    $file = "../gpx/" . $fileName;
                    $gpxdat = simplexml_load_file($file);
                    if ($gpxdat === false) {
                        throw new Exception(
                            __FILE__ . "Line " . __LINE__ .
                            "Could not load {$gpxfiles[$k]} as simplexml."
                        );
                    }
                    // any given gpx may have multiple tracks:
                    $noOfTracks = $gpxdat->trk->count();
                    $trackFileExt = 1; // increments for each track written
                    for ($j=0; $j<$noOfTracks; $j++) {
                        $trk_name = $gpxdat->trk[$j]->name;
                        // $track_files has an array for each track,
                        // containing arrays of lats, lngs, eles
                        $track_files = gpxLatLng($gpxdat, $noOfTracks);
                        $json_array = $track_files[$j]; // this track's set of arrays
                        $no_of_entries = count($json_array[0]); // cnt lats/lngs/eles
                        $jdat = '{"name":"' . $trk_name . '","trk":['; // fill w/objs
                        for ($n=0; $n<$no_of_entries; $n++) {
                            $jdat .= '{"lat":' . $json_array[0][$n] . ',"lng":' .
                                $json_array[1][$n] . ',"ele":' . $json_array[2][$n]
                                . '},';
                        }
                        $jdat = rtrim($jdat, ","); 
                        $jdat .= ']}';
                        $json_name = $base . $hikeNo . '_' . $trackFileExt++ .
                            '.json';
                        $trackfile = '../json/' . $json_name;
                        file_put_contents($trackfile, $jdat);
                        // collect array of associated json file names
                        array_push($json_value_array, $json_name);
                    }
                    // updates default empty array
                    $gpx_array[$key] = [$fileName => $json_value_array];
                }
            }
        }
        $newgpx = json_encode($gpx_array);
        $updateReq = "UPDATE `HIKES` SET `gpx`=? WHERE `indxNo`=?;";
        $updateGpx = $pdo->prepare($updateReq);
        $updateGpx->execute([$newgpx, $hikeNo]);
    }
}
// THe numbering is very unreliable!!!!!
$first = $start - 4;
$last  = $first + $noOfRecords;
echo "DONE: Records {$first} thru {$last} ? May be unreliable<br />";
