<?php
require "000mysql_connect.php";

$req = "SELECT * FROM " . $table . " WHERE indxNo = " . $hikeIndexNo;
$result = mysqli_query($link,$req);
if (!$result) {
    die ("Could not SELECT data from " . $table . ":  " . mysqli_err());
}
$row = mysqli_fetch_assoc($result);

# Assign variables for hikePageTemplate.php
$hikeTitle = $row['pgTitle'];
$hikeLocale = $row['locale'];
# Don't need 'marker', 'collection', 'cgroup', or 'cname'
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
# Don't need lat/lng
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
if ($row['refs'] == '') {
    $hikeRefs = '';
} else {
    $hikeRefs = unserialize($row['refs']);
}
if ($row['props'] == '') {
    $hikeProposedData = '';
} else {
    $hikeProposedData = unserialize($row['props']);
}
if ($row['acts'] == '') {
    $hikeActualData = '';
} else {
    $hikeActualData = unserialize($row['acts']);
}
if ($row['tsv'] == '') {
    $hikeImages = '';
} else {
    $hikeImages = unserialize($row['tsv']);
}

mysqli_free_result($row);
mysqli_close($link);
?>

