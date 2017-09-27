<?php
require "local_mysql_connect.php";

$req = mysqli_query( $link, "SELECT * FROM " . $table . " WHERE indxNo = " . $hikeIndexNo );
if (!$req) {
    die ("Could not SELECT data from " . $table . ":  " . mysqli_err());
}
$row = mysqli_fetch_row($req); #just one row

$hikeTitle = $row[1];
$hikeLocale = $row[2];
# Don't need 'marker', 'collection', 'cgroup', or 'cname'
$hikeType = $row[7];
$hikeLength = $row[8] . " miles";
$hikeElevation = $row[9] . " ft";
$hikeDifficulty = $row[10];
$hikeFacilities = $row[11];
$hikeWow = $row[12];
$hikeSeasons = $row[13];
$hikeExposure = $row[14];
$gpxfile = $row[15];
$jsonFile = $row[16];
# Don't need lat/lng
if ($row[19] == '') {
    $hikeAddonImg1 = '';
} else {
    $hikeAddonImg1 = unserialize($row[19]);
}
if ($row[20] == '') {
    $hikeAddonImg2 = '';
} else {
    $hikeAddonImg2 = unserialize($row[20]);
}
$hikePhotoLink1 = $row[21];
$hikePhotoLink2 = $row[22];
$hikeDirections = $row[23];
$hikeTips = $row[24];
$hikeTips = preg_replace("/\s/"," ",$hikeTips);
$hikeInfo = $row[25];
if ($row[26] == '') {
    $hikeRefs = '';
} else {
    $hikeRefs = unserialize($row[26]);
}
if ($row[27] == '') {
    $hikeProposedData = '';
} else {
    $hikeProposedData = unserialize($row[27]);
}
if ($row[28] == '') {
    $hikeActualData = '';
} else {
    $hikeActualData = unserialize($row[28]);
}
if ($row[29] == '') {
    $hikeImages = '';
} else {
    $hikeImages = unserialize($row[29]);
}
mysqli_close($link);
?>

