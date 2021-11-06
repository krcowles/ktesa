<?php
/**
 * This script allows the admin to selectively delete records from the
 * GPX database.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');
$dbtype = filter_input(INPUT_POST, 'dbtype');
$item_list = filter_input(INPUT_POST, 'items');

if ($dbtype === 'gpx') {
    $mtable = 'META';
    $gtable = 'GPX';
} else {
    $mtable = 'EMETA';
    $gtable = 'EGPX';
}
// what is current max fileno?
$maxno = $gdb->query("SELECT MAX(`fileno`) FROM {$mtable};")->fetch(PDO::FETCH_NUM);
$maxfileno = $maxno[0];
// create list of filenos to be deleted
$filenos = [];
$noWhiteSpace = preg_replace('/\s+/', '', $item_list);
$items = explode(",", $noWhiteSpace);
foreach ($items as $item) {
    if (strpos($item, "-") !== false) {
        $range = explode("-", $item);
        $start = array_shift($range);
        $end = array_shift($range);
        if (!is_numeric($start) || !is_numeric($end)) {
            echo "Bad range, non-numeric element: " . $start . "-" . $end;
            exit;
        }
        if ($start >= $end) {
            echo "Range limits are incorrect: " . $start . "-" . $end;
            exit;
        }
        for ($j=$start; $j<=$end; $j++) {
            if ($j > $maxfileno) {
                echo "Range exceeded the max fileno in files: " .
                    $start . "-" . $end . " > " . $maxfileno;
                exit;
            }
            array_push($filenos, $j);
        }
    } else {
        if (!is_numeric($item)) {
            echo "Found non-number item in range: " . $item;
            exit;
        }
        if ($item > $maxfileno) {
            echo "Fileno exceeded the current max fileno: " . $item . " > " . 
                $maxfileno;
            exit;
        }
        array_push($filenos, intval($item));
    }
}

// delete the list
foreach ($filenos as $fileno) {
    $metaDelete = "DELETE FROM {$mtable} WHERE `fileno`=?;";
    $gpxDelete  = "DELETE FROM {$gtable} WHERE `fileno`=?;";
    $metadel = $gdb->prepare($metaDelete);
    $gpxdel  = $gdb->prepare($gpxDelete);
    $metadel->execute([$fileno]);
    $gpxdel->execute([$fileno]);
}
echo "OK";
