<?php
require "000mysql_connect.php";

$req = mysqli_query( $link, "SELECT * FROM " . $table . " WHERE indxNo = " . $hikeIndexNo );
if (!$req) {
    die ("Could not SELECT data from " . $table . ":  " . mysqli_err());
}
$row = mysqli_fetch_row($req);

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
# Don't need lat/lng, add-on-imgs
$hikePhotoLink1 = $row[21];
$hikePhotoLink2 = $row[22];
$hikeDirections = $row[23];
$hikeTips = $row[24];
$hikeTips = preg_replace("/\s/"," ",$hikeTips);
$hikeInfo = $row[25];
$hikeReferences = unserialize($row[26]);
$hikeProposedData = unserialize($row[27]);
$hikeActualData = unserialize($row[28]);
$hikeImages = unserialize($row[29]);
mysqli_close($link);
die ("OK");
?>

