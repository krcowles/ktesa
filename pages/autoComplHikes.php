<?php
/**
 * For jQueryUI autocomplete, each 'item' is a js object with 'value' and 'label'
 * keys. For each hike name containing an accented letter (letter w/diacritical
 * mark), the 'value' will be the string w/diacritical, and the 'label' will be
 * the text without diacriticals. The set of ui objects is then encoded for the
 * javascript to import. Note: this script does not have to deal with multi-byte
 * strings containing more than 16-bit (2-byte) characters, as those are prevented
 * during hike page creation (see startNewPg.ts/js).
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$sortedHikesReq = "SELECT `indxNo`,`pgTitle`,`gpx` FROM `HIKES` ORDER BY `pgTitle`;";
$allHikes = $pdo->query($sortedHikesReq)->fetchAll(PDO::FETCH_ASSOC);
$groupsReq = "SELECT `indxNo` FROM `CLUSHIKES`;";
$allGroups = $pdo->query($groupsReq)->fetchAll(PDO::FETCH_COLUMN);

define("ASCII_MAX_VAL", 127);
$items  = [];
$notgrp = [];

foreach ($allHikes as $hike) {
    $txtChars = mb_str_split($hike['pgTitle']);
    foreach ($txtChars as &$char) {
        if (mb_ord($char) > ASCII_MAX_VAL) {
            $char = mapChar($char);
        }
    }
    $label = implode("", $txtChars);
    $hikeObj = '{value:"' . $hike['pgTitle'] . '",label:"' . $label . '"}';
    if (!empty($hike['gpx'])) {
        array_push($notgrp, $hikeObj);
    }
    array_push($items, $hikeObj);
}
$jsItems  = '[' . implode(",", $items) . ']';
$jsNoGrps = '[' . implode(",", $notgrp) . ']';
