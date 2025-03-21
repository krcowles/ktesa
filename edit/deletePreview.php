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

$field_only = filter_input(INPUT_POST, 'fonly');
$indxNo  = filter_input(INPUT_POST, 'indxNo');
$image   = filter_input(INPUT_POST, 'img');
if ($field_only == 'n') {  // delete images in pictures directory
    $picdir  = getPicturesDirectory();
    $prevloc = str_replace("zsize", "previews", $picdir) . $image;
    $thmbloc = str_replace("zsize", "thumbs", $picdir) . $image;
    if (!unlink($prevloc)) {
        throw New Exception("Could not delete preview image");
    }
    if (!unlink($thmbloc)) {
        throw New Exception("Could not delete thumb image");
    }
}
// clear the preview field in the EHIKE table
$clearPreviewReq = "UPDATE `EHIKES` SET `preview` = null WHERE `indxNo` = ?;";
$clearPreview = $pdo->prepare($clearPreviewReq);
$clearPreview->execute([$indxNo]);
echo "OK";
