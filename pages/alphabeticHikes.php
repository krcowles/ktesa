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
