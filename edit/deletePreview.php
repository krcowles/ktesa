<?php
/**
 * Delete the current preview and thumb images, and clear the 'preview' 
 * field in EHIKES
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$indxNo  = filter_input(INPUT_POST, 'indxNo');
$prevloc = filter_input(INPUT_POST, 'img');
$thmbloc = str_replace("previews", "thumbs", $prevloc);
if (!unlink($prevloc)) {
    throw New Exception("Could not delete preview image");
}
if (!unlink($thmbloc)) {
    throw New Excepption("Could not delete thumb image");
}
$clearPreviewReq = "UPDATE `EHIKES` SET `preview` = null WHERE `indxNo` = ?;";
$clearPreview = $pdo->prepare($clearPreviewReq);
$clearPreview->execute([$indxNo]);
echo "OK";
