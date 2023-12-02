<?php
/**
 * This routine is invoked via ajax from the linkValidate.js script.
 * Incoming links are deleted from the REFS table. Only the links need
 * be processed here, as when a link is bad with one hike, it is bad
 * with another...
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');

$badlnks = filter_input(INPUT_POST, 'links');
$bad_lnks = json_decode($badlnks);

foreach ($bad_lnks as $lnk) {
    $deletionReq = "DELETE FROM `REFS` WHERE `rit1`=?;";
    $deleteREF   = $pdo->prepare($deletionReq);
    try {
        $deleteREF->execute([$lnk]);
    } catch (PDOException $pdoe) {
        echo $lnk . " Could not be deleted";
    }
}
echo "ok";
