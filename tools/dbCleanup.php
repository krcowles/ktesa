<?php
/**
 * Remove waypoints from TSV database
 * Reset hike stats in HIKES
 */
require "../php/global_boot.php";
// Waypoint removal
/*
$noWptsReq = "DELETE FROM `TSV` WHERE `mid` IS null;";
$pdo->query($noWptsReq);
echo "Waypoints removed from TSV<br/>";
*/
// Iterate through HIKES to update stats: 
$getHikesDataReq = "SELECT `indxNo` FROM `HIKES` WHERE `feet` <> 0;";
$hikeData = $pdo->query($getHikesDataReq)->fetchAll(PDO::FETCH_ASSOC);
foreach ($hikeData as $hike) {
    if ($hike['indxNo'] >= 97) {
        $gpx_info = getGpxArray($pdo, $hike['indxNo'], 'pub');
        // updating info in the database only for 'main' gpx/track1 if more than 1
        $main_gpx = array_keys($gpx_info['main'])[0];
        $gpxPath = "../gpx/" . $main_gpx;
        $gpx_data = simplexml_load_file($gpxPath);
        $stats = getGpxStats($gpx_data, 0);
        $updateReq = "UPDATE `HIKES` SET `miles`=?,`feet`=? WHERE `indxNo`=?;";
        $update = $pdo->prepare($updateReq);
        $update->execute([$stats[0], $stats[1], $hike['indxNo']]);
    }
}
echo "Miles & Feet fields in database updated<br/>";