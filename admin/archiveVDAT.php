<?php
/**
 * This script will return VISITORS data for the specified year
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$arch_year = filter_input(INPUT_GET, 'yr');

// mysqli prep:
$link =  mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);
if (!$link) {
    throw new Exception(
        "Could not connect to the database using mysqli: File " .
        __FILE__ . "at line " . __LINE__
    );
}
if (!mysqli_set_charset($link, "utf8")) {
    throw new Exception(
        "Function mysqli_set_charset failed when called from file " .
        __FILE__ . " line " . mysqli_error($link)
    );
}
// get the data (Whoops thinks the query has a problem, but it works as is)
$getVDATReq = "SELECT * FROM `VISITORS` WHERE YEAR(`vdatetime`)={$arch_year};";
$VDAT = $pdo->query($getVDATReq);
$cols = $VDAT->columnCount();
$rows = $VDAT->rowCount();
$visitor_data = $VDAT->fetchAll(PDO::FETCH_NUM);
$content = '';
$lines = 0;
foreach ($visitor_data as $row) {
    if ($lines%100 == 0 || $lines == 0) {
        $content .= "\nINSERT INTO `VISITORS` VALUES";
        $content .= "\n(";
    }
    for ($j=0; $j<$cols; $j++) {
        if ($row[$j] === null) {
            $content .= "NULL";
        } else {
            $row[$j] = $link->real_escape_string($row[$j]);
            if (isset($row[$j])) {
                $content .= "'" . $row[$j] . "'" ;
            }
        }
        if ($j<$cols-1) {
            $content.= ',';
        }
    }
    $content .=")";
    if (($lines+1)%100 == 0 && $lines != 0 || $lines+1==$rows
    ) {
        $content .= ";";
    } else {
        $content .= ",";
    }
    $lines++;
}
echo $content;
