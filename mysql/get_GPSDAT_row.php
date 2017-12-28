<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$query = "SELECT datType,label,url,clickText FROM {$gtable} " .
    "WHERE indxNo = '{$hikeIndexNo}';";
$result = mysqli_query($link, $query);
if (!$result) {
    die(
        "get_GPSDAT_row.php: Unable to extract references from GPSDAT: " .
        mysqli_error()
    );
}
$pcnt = 0;
$acnt = 0;
if (mysqli_num_rows($result) !== 0) {
    $propHtml = '<p id="proptitle">' .
        '- Proposed Hike Data</p> ' . '<ul id="plinks">' . PHP_EOL;
    $actHtml = '<p id="acttitle">' .
        '- Actual Hike Data</p>' . '<ul id="alinks">' . PHP_EOL;
    $pgpxHtml = '';
    $pothrHtml = '';
    $agpxHtml = '';
    $aothrHtml = '';
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['datType'] === 'P') {
            $pcnt++;
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
                $pgpxHtml .= '<li class="gpslnks">' . $row['label'] .
                    '&nbsp;' . $row['clickText'] . '&nbsp;&nbsp;' . ' <a href="' .
                    $url . '" download>Download</a>&nbsp;&nbsp;' .'<a href="' .
                    $url . '" target="_blank">View as File</a>&nbsp;&nbsp;' .
                    '<a href="' . $mapLink . '" target="_blank">View as Map</a></li>' .
                    PHP_EOL;
            } else {
                $pothrHtml .= '<li>' . $row['label'] . '<a href="' . $url .'" target="_blank">' .
                $row['clickText'] . '</a></li>' . PHP_EOL;
            }
        } elseif ($row['datType'] === 'A') {
            $acnt++;
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
                $agpxHtml .= '<li class="gpslnks">' . $row['label'] . '&nbsp;' . $row['clickText'] .
                '&nbsp;&nbsp;' . ' <a href="' . $url . '" download>Download</a>&nbsp;&nbsp;' .
                '<a href="' . $url . '" target="_blank">View as File</a>&nbsp;&nbsp;' .
                '<a href="' . $mapLink . '" target="_blank">View as Map</a></li>' .
                PHP_EOL;
            } else {
                $aothrHtml .= '<li>' . $row['label'] . '<a href="' . $url .'" target="_blank">' .
                $row['clickText'] . '</a></li>' . PHP_EOL;
            }
        }
    }
    $propHtml .= $pgpxHtml . $pothrHtml . "</ul>" . PHP_EOL;
    $actHtml .= $agpxHtml . $aothrHtml . "</ul>" . PHP_EOL;
}
