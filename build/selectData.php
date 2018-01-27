<?php
/**
 * This module retrieves the current assignments of visitor centers
 * and clusters to be presented in a form's select drop-down box.
 * 
 * @package Page_Creation
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/**
 * This file contains the relevant database functions:
 */
require '../mysql/dbFunctions.php';
$link = connectToDb(__FILE__, __LINE__);
$vchikes = [];
$vcnos = [];
$clhikes = [];
$cldat = [];
$hquery = "SELECT indxNo,pgTitle,marker,`collection`,cgroup,cname "
        ."FROM HIKES;";
$specdat = mysqli_query($link, $hquery) or die(
    'enterHike.php: Could not retrieve vc/cluster info: ' .
    mysqli_error($link)
);
while ($select = mysqli_fetch_assoc($specdat)) {
    $indx = $select['indxNo'];
    $title = $select['pgTitle'];
    $marker = $select['marker'];
    $coll = $select['collection'];
    $clusltr = $select['cgroup'];
    $clusnme = $select['cname'];
    if ($marker == 'Visitor Ctr') {
        array_push($vchikes, $title);
        array_push($vcnos, $indx);
    } elseif ($marker == 'Cluster') {
        $dup = false;
        for ($l=0; $l<count($clhikes); $l++) {
            if ($clhikes[$l] == $clusnme) {
                $dup = true;
            }
        }
        if (!$dup) {
            array_push($clhikes, $clusnme);
            // Need to include both Cluster Name and Cluster Letter when posting
            $postCl = $clusltr . ":" . $clusnme;
            array_push($cldat, $postCl);
        }
    }
}
mysqli_free_result($specdat);
$vccnt = count($vchikes);
$clcnt = count($clhikes);
