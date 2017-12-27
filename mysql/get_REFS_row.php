<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
# get all data from REFS table for this hike ($hikeIndexNo)
$query = "SELECT rtype,rit1,rit2 FROM {$rtable} WHERE indxNo = '{$hikeIndexNo}';";
$result = mysqli_query($link, $query);
if (!$result) {
    die("get_REFS_row.php: Unable to extract references from REFS: " .
            mysqli_error());
}
$refHtml = '<ul id="refs" style="position:relative;top:-10px;">';
while ($row = mysqli_fetch_assoc($result)) {
    $rtype = trim($row['rtype']);
    if ($rtype === 'Text:') {
        $refHtml .= "<li>" . $row['rit1'] . "</li>";
    } elseif ($rtype === 'Book:' || $rtype === 'Photo Essay:') {
        $refHtml .= "<li>" . $rtype . " <em>" . $row['rit1'] .
                "</em>, by " . $row['rit2'] . "</li>";
    } else {
        $refHtml .= "<li>" . $rtype . ' <a href="' . $row['rit1'] .
                '" target="_blank">' . $row['rit2'] . '</a></li>';
    }
}
$refHtml .= "</ul>";
