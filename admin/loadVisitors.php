<?php
/**
 * This file will load the localhost database with a new copy of the
 * VISITORS table.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$pdo->query("DROP TABLE IF EXISTS `VISITORS`;");

$create = '';
$v_data = file("../data/visitors.sql", FILE_IGNORE_NEW_LINES);
for ($k=0; $k<count($v_data); $k++) {
    if (trim($v_data[$k]) !== '') {
        do {
            $create .= $v_data[$k];
        } while (strpos($v_data[$k++], ";") === false);
        $pdo->exec($create);
        break;
    }
}
for ($j=$k; $j<count($v_data); $j++ ) {
    // continue from new value of $k...
    if (trim($v_data[$j]) !== '') {
        $insert = "";
        do {
            $insert .= $v_data[$j];
        } while (strpos($v_data[$j++], ";") === false);
        $pdo->query($insert);
        if (trim($v_data[$j+1]) === '') {
            break;
        }
        $j--;
    }
}
echo "VISITOR Table Loaded";
