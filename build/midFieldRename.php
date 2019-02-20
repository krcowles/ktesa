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
$picId = 1;
$tsv = "SELECT mid FROM TSV;";
$mids = $pdo->query($tsv)->fetchAll(PDO::FETCH_COLUMN);
for ($i=0; $i<count($mids); $i++) {
    if (is_numeric($mids[$i]) && $mids[$i] >= $picId) {
        $picId = $mids[$i] + 1;
    }
}
$etsv = "SELECT mid FROM ETSV;";
$emids = $pdo->query($etsv)->fetchAll(PDO::FETCH_COLUMN);
for ($j=0; $j<count($emids); $j++) {
    if (is_numeric($emids[$j]) && $emids[$j] >= $picId) {
        $picId = $emids[$j] + 1;
    }
}
// iterate: replace the 'mid' value with unique integer and form the rename commands:
for ($k=0; $k<count($mids); $k++) {
    if (!is_numeric($mids[$k])) {
        $oldN = $picdir . "nsize/" . $mids[$k] . "_n.jpg";
        $newN = $picdir . "nsize/" . $picId . "_n.jpg";
        $oldZ = $picdir . "zsize/" . $mids[$k] . "_z.jpg";
        $newZ = $picdir . "zsize/" . $picId . "_z.jpg";
        $cmd = "rename('" . $oldN . "', '" . $newN . "');\n";
        $cmd .= "rename('" . $oldZ . "', '" . $newZ . "');\n";
        file_put_contents("fileRenameCmds.php", $cmd, FILE_APPEND);
        $newmid = "UPDATE TSV SET mid = ? WHERE mid = ?;";
        $replace = $pdo->prepare($newmid);
        $replace->execute([$picId++, $mids[$k]]);
    }
    // eliminate the following if when running the whole table, or set=count($mids)
    if ($k > 2) {
        echo "Test DONE";
        exit;
    }
}
for ($l=0; $l<count($emids); $l++) {
    if (!is_numeric($emids[$l])) {
        $cmd = "rename(" . $emids[$l] . ", " . $picId . ");";
        file_put_contents("fileRenameCmds.php", $cmd, FILE_APPEND);
        $newmid = "UPDATE ETSV SET mid = ? WHERE mid = ?;";
        $replace = $pdo->prepare($newmid);
        $replace->execute([$picId++, $emids[$l]]);
    }
}
echo "Iteration Complete";
