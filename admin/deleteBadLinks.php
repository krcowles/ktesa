<?php
/**
 * This routine is invoked via ajax from the linkValidate.php module.
 * Incoming links and their associated hike numbers are deleted from
 * the REFS table.
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
