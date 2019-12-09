<?php
/**
 * This module provides an alphabetized list of hikes from the HIKES table
 * PHP Version 7.1
 * 
 * @package Home
 * @author  Tom Sandberg and Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
$allHikes = $pdo->query("SELECT pgTitle FROM HIKES")->fetchAll(PDO::FETCH_COLUMN);
// convert to javascript array for autocomplete widget
if (!sort($allHikes)) {
    throw new Exception("Could not sort list of hikes");
}
$datalist = '<datalist id="hikelist">';
foreach ($allHikes as $hike) {
    $datalist .= '<option value="' . $hike . '">';
}
$datalist .= '</datalist>';
// create select box options
/*
$AtoC = '';
$DtoJ = '';
$KtoQ = '';
$RtoZ = '';
for ($i=0; $i<count($allHikes); $i++) {
    $opt = '<option value="' . $allHikes[$i] . '">' . $allHikes[$i] .
                '</option>' . PHP_EOL;
    if (!ctype_alpha($allHikes[$i][0])) {
        $AtoC .= $opt;
    } else {
        if ($allHikes[$i][0] >= 'R') {   
            $RtoZ .= $opt;
        } elseif ($allHikes[$i][0] >= 'K') {
            $KtoQ .= $opt;
        } elseif ($allHikes[$i][0] >= 'D') {
            $DtoJ .= $opt;
        } else {
            $AtoC .= $opt;
        }
    }
}
*/
