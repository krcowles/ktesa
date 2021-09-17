<?php
/**
 * This simple script verifies the existence of the `Checksums` table
 * for the purpose of properly executing the 'Reload Database' action via
 * admintools.js. The script is ajaxed from the latter.
 * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');

$chksumTblReq = "SELECT `indx` FROM `Checksums`;";
$result = 'yes';
try {
    $pdo->query($chksumTblReq);
} catch (PDOException $pdoe) {
    $result = 'no';
}
echo $result;
