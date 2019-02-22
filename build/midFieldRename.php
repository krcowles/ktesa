<?php
/**
 * This module will parse the TSV and ETSV tables, extracting the 'mid' field
 * for each row. The 'mid' value is saved, then replaced with a unique
 * incrementing integer. The saved value and new integer value will be used
 * to create a rename command for the corresponding nsize and zsize directory.
 * The rename commands will be stored in a separate executable PHP script to
 * do the actual renaming of files.
 * PHP Version 7.1
 * 
 * @package Test
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
 require "../php/global_boot.php";   // need PDO object, error reporting
 $picdir = "../pictures/";
/**
 * Since a test will be done on a few files to verify the process, there 
 * may already be an integer assigned, so utilize that; else start with 1.
 */
$tsv = "SELECT thumb,mid FROM TSV;";
$mids = $pdo->query($tsv)->fetchAll(PDO::FETCH_KEY_PAIR);

$testLim = 0;
// iterate: update the 'mid' value with 'thumb' and form the rename commands:
foreach ($mids as $key => $mid) {
    $oldN = $picdir . "nsize/" . $mid . "_n.jpg";
    $newN = $picdir . "nsize/" . $mid . "_" . $key . "_n.jpg";
    $oldZ = $picdir . "zsize/" . $mid . "_z.jpg";
    $newZ = $picdir . "zsize/" . $mid . "_" . $key . "_z.jpg";
    $cmd = "rename('" . $oldN . "', '" . $newN . "');\n";
    $cmd .= "rename('" . $oldZ . "', '" . $newZ . "');\n";
    file_put_contents("fileRenameCmds.php", $cmd, FILE_APPEND);
    $testLim++;
    if ($testLim === 3) {
        echo "DONE";
        exit;
    }
}

$etsv = "SELECT thumb,mid FROM ETSV;";
$emids = $pdo->query($etsv)->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($emids as $key => $emid) {
        $oldN = $picdir . "nsize/" . $emid . "_n.jpg";
        $newN = $picdir . "nsize/" . $emid . "_" . $key . "_n.jpg";
        $oldZ = $picdir . "zsize/" . $emid . "_z.jpg";
        $newZ = $picdir . "zsize/" . $emid . "_"  . $key . "_z.jpg";
        $cmd = "rename('" . $oldN . "', '" . $newN . "');\n";
        $cmd .= "rename('" . $oldZ . "', '" . $newZ . "');\n";
        file_put_contents("fileRenameCmds.php", $cmd, FILE_APPEND);
}
echo "Iteration Complete";
