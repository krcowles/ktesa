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
 *   $hikeIndexNo is the hike number in either EHIKES or HIKES
 *   $hikeCluster defined in hikePageData.php (for hikePageTemplate.php)
 *   $rtable = either EREFS or REFS 
 *   $gtable either GPSDAT or EGPSDAT
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$rhikes = (isset($hikeCluster)  && $hikeCluster !== '') ? true : false;
$noOfRelatedHikes = 0;
// Transactions:
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
if ($rhikes) {
    $relatedhikes = "SELECT indxNo,pgTitle FROM HIKES WHERE cname = :relhike";
    $hikesPDO = $pdo->prepare($relatedhikes);
}
$pdo->beginTransaction();
$bkPDO->execute();
$refPDO->bindValue(":indxNo", $hikeIndexNo);
$refPDO->execute();
$gpsPDO->bindValue(":indxNo", $hikeIndexNo);
$gpsPDO->execute();
if ($rhikes) {
    $hikesPDO->bindValue(":relhike", $hikeCluster);
    $hikesPDO->execute();
}
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

// exit here if this is for an Index Page:
if (isset($pageType) && $pageType === 'Index') {
    return;
}

if ($rhikes) {   
    $relHikes = '<ul id="related">' . PHP_EOL;
    while (($rHike = $hikesPDO->fetch(PDO::FETCH_ASSOC)) !== false) {
        if ($rHike['pgTitle'] !== $hikeTitle) {
            $relHikes .= '<li><a href="hikePageTemplate.php?hikeIndx=' .
                $rHike['indxNo'] . '" target="_blank">' . $rHike['pgTitle'] .
                '</a></li>' . PHP_EOL;
            $noOfRelatedHikes += 1;
        }
    }
    $relHikes .= '</ul>' . PHP_EOL;
}

$noOfGps = 0;
$gpsHtml = '<ul id="gps">' . PHP_EOL;
$gpsData = $gpsPDO->fetchAll(PDO::FETCH_ASSOC);
foreach ($gpsData as $row) {
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
                $url . '" download>Download</a>&nbsp;&nbsp;' .'<a href="' .
                $url . '" target="_blank">View as File</a>&nbsp;&nbsp;' .
                '<a href="' . $mapLink . 
                '" target="_blank">View as Map</a></li>' . PHP_EOL;
        } else {
            $gpsHtml .= '<li>' . $row['label'] . '<a href="' . $url .
                '" target="_blank">' . $row['clickText'] . '</a></li>' . PHP_EOL;
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
$bop = '';
$bop .= '<fieldset>'. PHP_EOL .
    '<legend id="fldrefs"><em>Related Hike Information</em></legend>' . PHP_EOL .
    '<span class="boptag">REFERENCES:</span>' . PHP_EOL .
$refHtml . PHP_EOL;
if ($noOfRelatedHikes > 0) {
    $bop .= '<span class="boptag">RELATED HIKES</span>' . PHP_EOL .
        $relHikes . PHP_EOL;
}
if ($noOfGps > 0) {
    $bop .= '<span class="boptag" style="margin-bottom:0px;">GPS DATA: ' .
        '</span>' . PHP_EOL . $gpsHtml . PHP_EOL;
}
$bop .= '</fieldset>' . PHP_EOL;
