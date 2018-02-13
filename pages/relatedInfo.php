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
 *   $hikeGroup set by get_HIKES_row.php and holds the value in 'cgroup'
 *   $rtable either EREFS or REFS 
 *   $gtable either GPSDAT or EGPSDAT
 * 
 * @package Display_Page
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../pages
 */
/**
 * All connections to MySQL are handled by this function
 * 
 * @param string __FILE__ a constant holding file name
 * @param string __LINE__ a constand holding line number in file
 */
$link = connectToDb(__FILE__, __LINE__);
// --- 1) References:
$query = "SELECT rtype,rit1,rit2 FROM {$rtable} WHERE " .
    "indxNo = '{$hikeIndexNo}';";
$result = mysqli_query($link, $query);
if (!$result) {
    die(
        "get_REFS_row.php: Unable to extract references from REFS: " .
        mysqli_error()
    );
}
$noOfRefs = mysqli_num_rows($result);
$refHtml = '<ul id="refs" style="position:relative;top:-10px;">';
if ($noOfRefs > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rtype = trim($row['rtype']);
        if ($rtype === 'Text:') {
            $refHtml .= "<li>" . $row['rit1'] . "</li>" . PHP_EOL;
        } elseif ($rtype === 'Book:' || $rtype === 'Photo Essay:') {
            $refHtml .= "<li>" . $rtype . " <em>" . $row['rit1'] .
                    "</em>, by " . $row['rit2'] . "</li>" . PHP_EOL;
        } else {
            $refHtml .= "<li>" . $rtype . ' <a href="' . $row['rit1'] .
                    '" target="_blank">' . $row['rit2'] . '</a></li>' . PHP_EOL;
        }
    }
    $refHtml .= "</ul>". PHP_EOL;
} else {
    $refHtml .= "<li>No References</li>" . PHP_EOL;
    $refHtml .= "<ul>" . PHP_EOL;
}
// exit here if this is for an Index Page:
if (isset($pageType) && $pageType === 'Index') {
    return;
}
// ---- 2) Related Hikes
/**
 * If this hike belongs to a cluster, establish 'Related Hikes' by identifying the
 * other hikes in this cluster:
 */
$noOfClus = 0;
if (isset($hikeGroup)  && $hikeGroup !== '') {
    $related = trim($hikeGroup);
    $query = "SELECT indxNo,pgTitle FROM HIKES WHERE cgroup = '{$related}';";
    $relquery = mysqli_query($link, $query);
    if (!$relquery) {
        die(
            "hikePageTemplate.php: No extaction of cluster group hikes" .
            mysqli_error($link)
        );
    }
    $noOfClus = mysqli_num_rows($relquery);
    if ($noOfClus > 0) {
        $relHikes = '<ul id="related">' . PHP_EOL;
        for ($i=0; $i<$noOfClus; $i++) {
            $rHike = mysqli_fetch_assoc($relquery);
            if ($rHike['pgTitle'] !== $hikeTitle) {
                $relHikes .= '<li><a href="hikePageTemplate.php?hikeIndx=' .
                $rHike['indxNo'] . '" target="_blank">' . $rHike['pgTitle'] .
                '</a></li>' . PHP_EOL;
            }
        }
        $relHikes .= '</ul>' . PHP_EOL;
    }
    mysqli_free_result($relquery);
}
// --- 3) GPS Maps and Data
/**
 * Get any GPS Maps & Data to be displayed on the page. The data type
 * identifier 'P' (proposed data) and 'A' (actual data) are no longer 
 * required by the curent scheme and are ignored.
 */
$query = "SELECT datType,label,`url`,clickText FROM {$gtable} " .
    "WHERE indxNo = '{$hikeIndexNo}';";
$result = mysqli_query($link, $query);
if (!$result) {
    die(
        "get_GPSDAT_row.php: Unable to extract references from GPSDAT: " .
        mysqli_error()
    );
}
$noOfGps = mysqli_num_rows($result);
if ($noOfGps > 0) {
    $gpsHtml = '<ul id="gps">' . PHP_EOL;
    while ($row = mysqli_fetch_assoc($result)) {
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
    }
    $gpsHtml .= "</ul>";
}
mysqli_free_result($result);
/**
 * New style for presenting 'bottom-of-page' information, including:
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
if ($noOfClus > 0) {
    $bop .= '<span class="boptag">RELATED HIKES</span>' . PHP_EOL .
        $relHikes . PHP_EOL;
}
if ($noOfGps > 0) {
    $bop .= '<span class="boptag" style="margin-bottom:0px;">GPS DATA: ' .
        '</span>' . PHP_EOL . $gpsHtml . PHP_EOL;
}
if ($noOfRefs > 0 || $noOfClus > 0 || $noOfGps > 0) {
    $bop .= '</fieldset>' . PHP_EOL;
}
?>