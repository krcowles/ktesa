<?php
/**
 * Look for json files where name = ""
 */
require "../php/global_boot.php";
$json_files = scandir("../json");
foreach ($json_files as $track) {
    if ($track !== '.' && $track !== '..'
        && $track !== '.DS_Store' && $track !== 'areas.json'
    ) {
        $data = file_get_contents("../json/" . $track);
        $trk_pos = strpos($data, '"trk"');
        $name_val = substr($data, 8, 10);
        if ($name_val[1] === '"') {
            echo $track . "<br />";
        }
    }
}
echo "DONE";
