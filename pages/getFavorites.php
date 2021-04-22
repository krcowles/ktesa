<?php
/**
 * This script retrieves any user favorites (hike nos). It is 
 * computed prior to loading sideTables.js, as previously, when
 * ajaxed by that script, timing issues arose.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
if (isset($_SESSION['userid'])) {
    $favreq = "SELECT `hikeNo` FROM `FAVORITES` WHERE `userid` = :userid;";
    $usrfavs = $pdo->prepare($favreq);
    $usrfavs->execute(["userid" => $_SESSION['userid']]);
    $favs = $usrfavs->fetchAll(PDO::FETCH_COLUMN);
    $favlist = json_encode($favs);
} else {
    $favlist = '[]';
}
