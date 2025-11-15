<?php
/**
 * Calculate the compass boundaries for each hike and store in the db;
 * NOTE: This includes any 'additional' gpx tracks specified in the db.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$hikes = $pdo->query("SELECT `indxNo`,`gpx` FROM `HIKES`;")
    ->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($hikes as $hikeNo => $gpx) {
    if (!empty($gpx)) {
        $file_array = getTrackFileNames($pdo, $hikeNo, 'pub');
        $json_files = [];
        foreach ($file_array[0] as $tracks) {
            array_push($json_files, $tracks);
        }
        $no_of_files = count($json_files);
        if ($no_of_files !== 0) {
            $nmax = [];
            $smin = [];
            $emax = [];
            $wmin = [];
            $maxlat = 0;
            $minlat = 100;
            $maxlng = -110;
            $minlng = -102;
            for ($i=0; $i<$no_of_files; $i++) {
                $contents = file_get_contents('../json/' . $json_files[$i]);
                $contents_as_array = json_decode($contents, true);
                $track_dat = $contents_as_array['trk'];
                foreach ($track_dat as $point) {
                    $lat = round($point['lat'], 5);
                    $lng = round($point['lng'], 5);
                    if ($lat > $maxlat) {
                        $maxlat = $lat;
                    }
                    if ($lat < $minlat) {
                        $minlat = $lat;
                    }
                    if ($lng > $maxlng) {
                        $maxlng = $lng;
                    }
                    if ($lng < $minlng) {
                        $minlng = $lng;
                    }
                }
            }
            array_push($nmax, $maxlat);
            array_push($smin, $minlat);
            array_push($emax, $maxlng);
            array_push($wmin, $minlng);
            $bnds[0] = max($nmax);
            $bnds[1] = min($smin);
            $bnds[2] = max($emax);
            $bnds[3] = min($wmin);
            $bounds = implode(",", $bnds);
            $updateBoundsReq = "UPDATE `HIKES` SET `bounds`='{$bounds}' WHERE " .
                "`indxNo`={$hikeNo};";
            $pdo->query($updateBoundsReq);
            /*
            if ($hikeNo == 38) {
                echo "West: " . $bnds[3] . "; East: " . $bnds[2];
                exit;
            }
            */
        }
    }
}
echo "DONE";
