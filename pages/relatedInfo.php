<?php
/**
 * This file constructs the html that displays 'Related Hike Information'
 * which includes:
 * 1) References, output by $refHtml;
 * 2) Related Hikes (optional), output by $relHikes;
 * 3) GPS Maps and Data (optional), ouptut by $gpsHtml;
 * 4) and any other links or data desired (to be defined).
 * This file is to be included on the hikePageTemplate.php and
 * expects definition of the following variables:
 *   $hikeIndexNo = the hike number in either EHIKES or HIKES
 *   $rtable = either EREFS or REFS 
 *   $gtable = either GPSDAT or EGPSDAT
 *   $row = the fetched EHIKE row data if in-edit hike
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$noOfRelatedHikes = 0;
$relHikes = '';
$clusterId = false;
/**
 * The page will list 'Related Hikes' if it is either a Cluster Page
 * (has a 'page' entry in CLUSTERS), or is a hike in a cluster group (has an
 * entry in CLUSHIKES).
 */
if ($clusterPage) { // may be published or in-edit
    $clusterId = $cpdata['clusid'];
} else {
    if ($tbl === 'new') { // this is a hike in-edit
        if (!empty($hikeGroup)) { // if it is a cluster hike:
            $clusMemReq = "SELECT `clusid` FROM `CLUSTERS` WHERE `group`=?;";
            $clusMem = $pdo->prepare($clusMemReq);
            $clusMem->execute([$hikeGroup]);
            $clus_id = $clusMem->fetch(PDO::FETCH_ASSOC);
            if ($clus_id !== false) {
                $clusterId = $clus_id['clusid'];
            }
        }
    } else { // this $hikeIndexNo is for a published hike (use pub only)
        $clusMemReq = "SELECT `cluster` FROM `CLUSHIKES` WHERE `pub`='Y' " .
            "AND `indxNo`=?;";
        $clusMem = $pdo->prepare($clusMemReq);
        $clusMem->execute([$hikeIndexNo]);
        $clus_id = $clusMem->fetch(PDO::FETCH_ASSOC);
        if ($clus_id !== false) {
            $clusterId = $clus_id['cluster'];
        }
    }
}
// Get related hikes if one of the above is true
if ($clusterId) {
    $relatedReq = "SELECT `indxNo` FROM `CLUSHIKES` WHERE `pub`='Y' " .
        "AND `cluster`=?;";
    $related = $pdo->prepare($relatedReq);
    $related->execute([$clusterId]);
    $noOfRelatedHikes = $related->rowCount();
    $groupHikes = $related->fetchAll(PDO::FETCH_COLUMN);
    $relHikes = '<ul id="related">' . PHP_EOL;
    foreach ($groupHikes as $hike) {
        if ((int) $hike !== (int) $hikeIndexNo) {
            $hikeReq = "SELECT `pgTitle` FROM `HIKES` WHERE `indxNo`=?;";
            $pageTitle = $pdo->prepare($hikeReq);
            $pageTitle->execute([$hike]);
            $pg = $pageTitle->fetch(PDO::FETCH_ASSOC);
            if (($tbl === 'new' && $hikeTitle !== $pg['pgTitle'])
                || $tbl === 'old'
            ) {
                $relHikes .= '<li><a href="hikePageTemplate.php?hikeIndx=' . $hike .
                    '" target="_blank">' . $pg['pgTitle'] . '</a></li>' . PHP_EOL; 
            } 
        } 
    }
    $relHikes .= '</ul>' . PHP_EOL;
}

// books
$bkReq = 'SELECT title,author FROM BOOKS';
$bkPDO = $pdo->prepare($bkReq);
// references
$refReq = "SELECT rtype,rit1,rit2 FROM {$rtable} WHERE indxNo = :indxNo";
$refPDO = $pdo->prepare($refReq);
// gps data
$gpsReq = "SELECT datType,label,`url`,clickText FROM {$gtable} "
    . "WHERE indxNo = :indxNo";
$gpsPDO = $pdo->prepare($gpsReq);
$pdo->beginTransaction();
$bkPDO->execute();
$refPDO->bindValue(":indxNo", $hikeIndexNo);
$refPDO->execute();
$gpsPDO->bindValue(":indxNo", $hikeIndexNo);
$gpsPDO->execute();
$pdo->commit();

// Create arrays correlating books and authors:
$books = [];
$auths = [];
$bookData = $bkPDO->fetchAll(PDO::FETCH_ASSOC);
foreach ($bookData as $bkitem) {
    array_push($books, $bkitem['title']);
    array_push($auths, $bkitem['author']);
}
// Now process the remaining 'Related Info' data:
$referenceData = $refPDO->fetchAll(PDO::FETCH_ASSOC);
$noOfRefs = 0;
$refHtml = '<ul id="refs" style="position:relative;top:-10px;">';
foreach ($referenceData as $row) {
    $rtype = trim($row['rtype']);
    if ($rtype === 'Text:') {
        $refHtml .= "<li>" . $row['rit1'] . "</li>" . PHP_EOL;
    } elseif ($rtype === 'Book:' || $rtype === 'Photo Essay:') {
        $indx = $row['rit1'] -1;
        $refHtml .= "<li>" . $rtype . " <em>" . $books[$indx] .
                "</em>, by " . $auths[$indx] . "</li>" . PHP_EOL;
    } else {
        $refHtml .= "<li>" . $rtype . ' <a href="' . $row['rit1'] .
                '" target="_blank">' . $row['rit2'] . '</a></li>' . PHP_EOL;
    }
    $noOfRefs += 1;
}
if ($noOfRefs === 0) {
    $refHtml .= "<li>No References</li>" . PHP_EOL;
}
$refHtml .= "</ul>". PHP_EOL;

// GPS Data Section
$noOfGps = 0;
$gpsHtml = '<ul id="gps">' . PHP_EOL;
$gpsData = $gpsPDO->fetchAll(PDO::FETCH_ASSOC);
foreach ($gpsData as $row) {
    // Originally P => Proposed; A => Actual:  no longer used
    if ($row['datType'] === 'P' || $row['datType'] === 'A') {
        $url = $row['url'];
        $extpos = strrpos($url, ".") + 1;
        $ext = strtolower(substr($url, $extpos, 3));
        if ($ext === 'gpx') {
            if (substr($gtable, 0, 1) === 'E') {
                $age = 'new';
            } else {
                $age = 'old';
            }
            $mapLink = "../maps/fullPgMapLink.php?maptype=extra&" .
                "hno={$hikeIndexNo}&hike={$hikeTitle}&gpx={$url}&tbl={$age}";
            $gpsHtml .= '<li class="gpslnks">' . $row['clickText'] .
                '&nbsp;&nbsp;' . ' <a href="' .
                $url . '" download>Download</a>&nbsp;&nbsp;<a class="gpxview" ' .
                'href="' .$url . '" target="_blank">View as File</a>&nbsp;&nbsp;' .
                '<a class="gpxmap" href="' . $mapLink . 
                '" target="_blank">View as Map</a></li>' . PHP_EOL;
        } else {
            $gpsHtml .= '<li>' . $row['label'] . '&nbsp;<a class="mapfile" href="' .
                $url . '" target="_blank">' . $row['clickText'] . '</a></li>' .
                PHP_EOL;
        }
    } 
    $noOfGps += 1;
}
$gpsHtml .= "</ul>";

/**
 *  Present 'bottom-of-page' information, including:
 *  References,
 *  Related hikes
 *  GPS Maps and Data
 *  Other miscellaneous info
 */
$bop = '<fieldset>'. PHP_EOL .
    '<legend id="fldrefs"><em>Related Hike Information</em></legend>' . PHP_EOL .
    '<span class="boptag">REFERENCES:</span>' . 
    ' <a href="../pages/booklist.php" target="_blank">List of All Site Book
    References</a>' . PHP_EOL . $refHtml . PHP_EOL;
if (!$clusterPage && $noOfRelatedHikes > 0) {
    $bop .= '<span class="boptag">RELATED HIKES</span>' . PHP_EOL .
        $relHikes . PHP_EOL;
}
if ($noOfGps > 0) {
    $bop .= '<span class="boptag" style="margin-bottom:0px;">GPS DATA: ' .
        '</span>' . PHP_EOL . $gpsHtml . PHP_EOL;
}
$bop .= '</fieldset>' . PHP_EOL;
