<?php
/**
 * Upload the preview and thumb images created by the user in editor tab2
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$previewImg  = $_FILES['prev']['tmp_name'];
$thumbImg    = $_FILES['thmb']['tmp_name'];
$prefix = filter_input(INPUT_POST, 'prefix');
$indxNo = filter_input(INPUT_POST, 'indxNo');

$suffix = bin2hex(random_bytes(3));
$fname = $prefix . $suffix . '.jpg';
if (($prev = file_get_contents($previewImg)) === false) {
    throw new Exception(
        "Uploaded server data could not be retrieved for {$fanme}\n"
    );
}
if (($thmb = file_get_contents($thumbImg)) === false) {
    throw new Exception(
        "Uploaded server data could not be retrieved for {$fanme}\n"
    );
}
$pictures_directory = getPicturesDirectory();
//$s = $pictures_directory . $fname;
//file_put_contents($s, $prev);
$prevs  = str_replace('zsize', 'previews', $pictures_directory);
$thumbs = str_replace('zsize', 'thumbs', $pictures_directory);
$prevPic = $prevs . $fname;
$thmbPic = $thumbs . $fname;
if (file_put_contents($prevPic, $prev) === false) {
    throw new Exception("Could not store preview image data from upload");
}
if (file_put_contents($thmbPic, $thmb) === false) {
    throw new Exception("Could not store thumb image data from upload");
}
// place reference in HIKES table
$prevReq = "UPDATE `EHIKES` SET `preview` = ? WHERE `indxNo` = ?;";
$savePreview = $pdo->prepare($prevReq);
$savePreview->execute([$fname, $indxNo]);
echo "OK";
