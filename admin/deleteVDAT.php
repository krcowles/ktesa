<?php
/**
 * This script will permanently remove visitor data from the table VISITORS 
 * in the database for the specified year.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');

$arch_year = filter_input(INPUT_GET, 'yr');

$deleteReq = "DELETE FROM `VISITORS` WHERE YEAR(vdatetime) = {$arch_year};";
try {
    $delete  = $pdo->query($deleteReq);
} catch (PDOException $pdoe) {
    echo $pdoe->getMessage();
    exit;
}
echo "ok";
