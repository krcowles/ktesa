<?php
/**
 * Delete the photo & TSV data for selected photo.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$iname = filter_input(INPUT_POST, 'iname');
$tsvname = basename($iname);

/**
 * Find the path to the pictures directory
 */ 
$picdir = "";
$current = getcwd();
$prev = $current;
while (!in_array('pictures', scandir($current))) {
    $picdir .= "../";
    chdir('..');
    $current = getcwd();
}
chdir($prev);
$picdir .= 'pictures/zsize/';
$delete = $picdir . $tsvname;

if (unlink($delete) === false) {
    throw new Exception("Could not delete {$tsvname}");
}

$zpos = strrpos($tsvname, '_z.');
$zstr = substr($tsvname, 0, $zpos);
$midpos = strrpos($zstr, "_");
$thumb = substr($zstr, $midpos+1);
$base = substr($zstr, 0, $midpos);

// in case of multiple entries for image name, thumb will be different
$tsv_delete_req = "DELETE FROM `ETSV` WHERE `title` = ? AND `thumb` = ?;";
$tsv_delete = $pdo->prepare($tsv_delete_req);
$tsv_delete->execute([$base, $thumb]);
