<?php
$_SESSION['usr'] = $uid;
# PHOTO DATA REMOVAL:
$_SESSION['tsv'] = $usetsv;
$_SESSION['hno'] = $hikeNo;
# FILE UPLOAD REMOVAL:
$_SESSION['havgpx'] = $haveGpx;
if ($haveGpx) {
    $_SESSION['gpx'] = $gpxLoc;
    $_SESSION['trk'] = '../json/' . $trkfile;
}
$_SESSION['if1'] = $imageFile1;
if ($imageFile1) {
    $_SESSION['i1loc'] = $img1Loc;
}
$_SESSION['if2'] = $imageFile2;
if ($imageFile2) {
    $_SESSION['i2loc'] = $img2Loc;
}
$_SESSION['pf1'] = $pf1;
if ($pf1) {
    $_SESSION['p1loc'] = $pf1site;
}
$_SESSION['pf2'] = $pf2;
if ($pf2) {
    $_SESSION['p2loc'] = $pf2site;
}
$_SESSION['af1'] = $af1;
if ($af1) {
    $_SESSION['a1loc'] = $af1site;
}
$_SESSION['af2'] = $af2;
if ($af2) {
    $_SESSION['a2loc'] = $af2site;
}
