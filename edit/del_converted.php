<?php
/**
 * If, during the conversion process of heic to jpg, a user decides
 * not to include a converted photo, it can be deleted from the 
 * pictures directory, and from ETSV by clicking on the image's
 * associated link, whereupon this script will be invoked.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');

$deletion = filter_input(INPUT_GET, 'thumb');

$picdir = getPicturesDirectory();
$pdat = $pdo->prepare("SELECT * FROM `ETSV` WHERE `thumb`=?;");
$pdat->execute([$deletion]);
$picdat = $pdat->fetch(PDO::FETCH_ASSOC);
$photo = $picdir . $picdat['mid'] . "_" . $picdat['thumb'] . "_z.jpg";
if (!unlink($photo)) {
    echo "NO";
    exit;
}
$record = $pdo->prepare("DELETE FROM `ETSV` WHERE `thumb`=?;");
$record->execute([$deletion]);
echo "DONE";
