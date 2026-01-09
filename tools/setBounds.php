<?php
/**
 * Calculate the compass boundaries for each hike and store in the db
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$hikes = $pdo->query("SELECT `indxNo`,`gpx` FROM `HIKES`;")
    ->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($hikes as $hikeNo => $gpx) {
    $gpx_array = getGpxArray($pdo, $hikeNo, 'pub');
    $no_of_files = count($gpx_array['main']);
    if ($no_of_files === 1) {
        $json_array = $gpx_array['main'];
        // There is only 1 'main' gpx file, but it may have several json files
        $files = array_values($json_array)[0]; // there is only [0]
        $nmax = [];
        $smin = [];
        $emax = [];
        $wmin = [];
        for ($i=0; $i<count($files); $i++) {
            $maxlat = 0;
            $minlat = 100;
            $maxlng = -110;
            $minlng = -102;
            // calculate bounds for all json files associated w/'main'
            $contents = file_get_contents('../json/' . $files[$i]);
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
                if ($point['lng'] < $minlng) {
                    $minlng = $lng;
                }
            }
            array_push($nmax, $maxlat);
            array_push($smin, $minlat);
            array_push($emax, $maxlng);
            array_push($wmin, $minlng);
        }
        $bnds[0] = max($nmax);
        $bnds[1] = min($smin);
        $bnds[2] = max($emax);
        $bnds[3] = min($wmin);
        $bounds = implode(",", $bnds);
        $updateBoundsReq = "UPDATE `HIKES` SET `bounds`='{$bounds}' WHERE " .
            "`indxNo`={$hikeNo};";
        $pdo->query($updateBoundsReq);
    }
}
echo "DONE";
