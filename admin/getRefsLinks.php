<?php
/**
 * Retrieve all links present in the REFS table. If being
 * called via ajax, return the count of all links; if
 * being included in the getBadLinks.php script, simply
 * establish the required arrays to parse in that routine.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
if (!isset($caller)) {
    session_start();
    include "../php/global_boot.php";
    $caller = filter_input(INPUT_GET, 'caller');
}
$refsLinksReq = "SELECT `rit1`,`indxNo` FROM `REFS` WHERE " .
    "`rtype`<>'Book:' AND `rtype`<>'Photo Essay';";
$allRefs = $pdo->query($refsLinksReq);
$allLinks = $allRefs->fetchAll(PDO::FETCH_KEY_PAIR);
if ($caller === 'ajax') {
    echo count($allLinks);
    exit;
} else {
    $rit1      = array_keys($allLinks);
    $links     = [];
    $hikenos   = [];
    $bad_lnks  = [];
    $hike_nos  = [];
    for ($k=0; $k<count($rit1); $k++) {
        $url = $rit1[$k];
        if (strpos($url, "http") !== false) {
            array_push($links, $url);
            array_push($hikenos, $allLinks[$url]);
        }
    }
}
