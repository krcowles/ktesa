<?php
/**
 * This script uploads user-selected files and places them in the appGpxFiles
 * directory for access via mobile devices.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$fname   = filter_input(INPUT_POST, 'name');
$content = filter_input(INPUT_POST, 'data');
// save uploaded file:
$appFile = "../appGpxFiles/{$fname}";
file_put_contents($appFile, $content);
// generate the new table listing of files:
$newTable = <<<TABLE
<table id="available_gpx">
    <tbody>
        <tr>
            <th class="fname"
                style="background-color:moccasin;">
                File Name</th>
            <th class="fname"
                style="background-color:moccasin;">
                Remove</th>
        </tr>
TABLE;
$gpxlist = scandir("../appGpxFiles");
array_splice($gpxlist, 0, 2); // eliminate . and ..
$rows = [];
foreach ($gpxlist as $gpx) {
    $row = "<tr><td class='fname'>{$gpx}</td>";
    $row .= "<td class='gcbox'><input type='checkbox' class='delfile'></td></tr>";
    array_push($rows, $row);                           
}
foreach ($rows as $row) {
    $newTable .= $row;
}
$newTable .= "</tbody></table>";

echo $newTable;
