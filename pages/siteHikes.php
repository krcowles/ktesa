<?php
/**
 * This script creates a list of hike names and indxNo's so that the
 * searchbar can direct the user to the selected hike's hike page.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$getHikesReq = "SELECT `pgTitle`,`indxNo` FROM `HIKES` ORDER BY `pgTitle`";
$getHikes = $pdo->query($getHikesReq);
$hikes = $getHikes->fetchAll(PDO::FETCH_KEY_PAIR);
$hikeNames = array_keys($hikes);
$datalist = '<datalist id="hikelist">';
foreach ($hikeNames as $hike) {
    $datalist .= '<option value="' . $hike . '">';
}
$datalist .= '</datalist>';
$jsonHikes = json_encode($hikes);
