<?php
/**
 * Display the most recent 10 hikes in a modal in response to 
 * the user's menu selection "Latest Additions"
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('post');

$getHikesReq = "SELECT `indxNo`,`pgTitle` FROM `HIKES` ORDER BY `last_hiked` " .
    "DESC LIMIT 10;";
$getHikes = $pdo->query($getHikesReq);
$newHikes = $getHikes->fetchAll(PDO::FETCH_KEY_PAIR);
// create HTML for modal
$latest_hikes_html = "<ul>" . PHP_EOL;
foreach ($newHikes as $index => $hike) {
    $latest_hikes_html .= '<li><a href="../pages/hikePageTemplate.php?hikeIndx=' .
        $index . '">' . $hike . '</a></li>' . PHP_EOL;
}
$latest_hikes_html .= "</ul>" . PHP_EOL;
echo $latest_hikes_html;
