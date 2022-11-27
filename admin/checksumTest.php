<?php
/**
 * This simple script verifies the existence of the `Checksums` table
 * for the purpose of properly executing the 'Reload Database' action via
 * admintools.js. The script is ajaxed from the latter. Code revised on
 * Nov 25, 2023.
 * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');

$tablesReq = $pdo->query("SHOW TABLES;");
$tables = $tablesReq->fetchAll(PDO::FETCH_COLUMN);
if (in_array('Checksums', $tables)) {
    $result = 'yes';
} else {
    $result = 'no';
}
echo $result;
