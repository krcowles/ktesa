<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
$query = "SELECT datType,label,url,clickText FROM {$gtable} WHERE indxNo = '{$hikeIndexNo}';";
$result = mysqli_query($link, $query);
if (!$result) {
    die("get_GPSDAT_row.php: Unable to extract references from GPSDAT: " .
            mysqli_error());
}
$pcnt = 0;
$acnt = 0;
if (mysqli_num_rows($result) !== 0) {
    $propHtml = '<p id="proptitle">- Proposed Hike Data</p> ' . "\n" .
                    '<ul id="plinks">' . "\n";
    $actHtml = '<p id="acttitle">- Actual Hike Data</p>' . "\n" .
                    '<ul id="alinks">' . "\n";
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['datType'] === 'P') {
            $pcnt++;
            $propHtml .= "<li>" . $row['label'] . ' <a href="' . $row['url'] .
                        '" target="_blank">' . $row['clickText'] . "</a></li>\n";
        } elseif ($row['datType'] === 'A') {
            $actHtml .= "<li>" . $row['label'] . ' <a href="' . $row['url'] .
                        '" target="_blank">' . $row['clickText'] . "</a></li>\n";
            $acnt++;
        }
    }
    $propHtml .= "</ul>";
    $actHtml .= "</ul>";
}
