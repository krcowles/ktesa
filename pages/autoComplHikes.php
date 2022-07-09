<?php
/**
 * For jQueryUI autocomplete, each 'item' is a js object with 'value' and 'label'
 * keys. For each hike name containing an accented letter (letter w/diacritical
 * mark), the 'value' will be placed into an undisplayed <ul id="specchars"> on the
 * home page where its accented letters can be properly rendered by HTML. The
 * javascript (see sideTables.ts) will then extract the correctly rendered text and
 * place it into the search element, once the displayed item 'label' is selected by
 * the user. The 'label' will not contain accented letters so that the user can
 * locate the hike with a standard keyboard.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$allHikes = $pdo->query("SELECT pgTitle FROM HIKES")->fetchAll(PDO::FETCH_COLUMN);
if (!sort($allHikes)) {
    throw new Exception("Could not sort list of hikes");
}
$items   = [];
$charcnt = 0;
$charli  = '';
foreach ($allHikes as $hike) {
    $hikeType = htmlEntityId($hike, $entitiesISO8859);
    if ($hikeType[1]) { // this hike contains a special char
        $hstring = $hikeType[0]; // hike w/spec char entity no.
        foreach ($hikeType[2] as $coded) {
            $nextCode = strpos($hstring, '&#');
            $codeno = substr($hstring, $nextCode+2, 3);
            $srchval = '&#' . $codeno . ';';
            $hstring = str_replace($srchval, $coded, $hstring);
        }
        $label = $hstring;
        $hikeObj = '{value:"' . $charcnt . '",label:"' . $label . '"}';
        $charli .= "<li>$hikeType[0]</li>";
        $charcnt++;
    } else { // no special char
        $hikeObj = '{value:"' . $hikeType[0] . '",label:"' . $hikeType[0] . '"}';
    } 
    array_push($items, $hikeObj);
}
$jsItems  = '[' . implode(",", $items) . ']';
