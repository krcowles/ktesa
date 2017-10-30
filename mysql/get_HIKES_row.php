<?php
require_once "../mysql/setenv.php";
$req = "SELECT * FROM {$htable} WHERE indxNo = " . $hikeIndexNo;
$result = mysqli_query($link,$req);
if (!$result) {
    if (Ktesa_Dbug) {
        dbug_print('get_HIKES_row: Failed to execute SELECT in get_HIKES_row: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,1,0);
    }
}
$row = mysqli_fetch_assoc($result);
$hikeTitle = $row['pgTitle'];
$hikeLocale = $row['locale'];
$hikeType = $row['logistics'];
$hikeLength = $row['miles'] . " miles";
$hikeElevation = $row['feet'] . " ft";
$hikeDifficulty = $row['diff'];
$hikeFacilities = $row['fac'];
$hikeWow = $row['wow'];
$hikeSeasons = $row['seasons'];
$hikeExposure = $row['expo'];
$gpxfile = $row['gpx'];
$jsonFile = $row['trk'];
if ($row['aoimg1'] == '') {
    $hikeAddonImg1 = '';
} else {
    $hikeAddonImg1 = unserialize($row['aoimg1']);
}
if ($row['aoimg2'] == '') {
    $hikeAddonImg2 = '';
} else {
    $hikeAddonImg2 = unserialize($row['aoimg2']);
}
$hikePhotoLink1 = $row['purl1'];
$hikePhotoLink2 = $row['purl2'];
$hikeDirections = $row['dirs'];
$hikeTips = $row['tips'];
$hikeTips = preg_replace("/\s/"," ",$hikeTips);
$hikeInfo = $row['info'];
$hikeInfo = preg_replace("/\s/"," ",$hikeInfo);
mysqli_free_result($result);
