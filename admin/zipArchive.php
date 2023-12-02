<?php
/**
 * This script iterates through the project directory, looking
 * for files that have changed since the last time stamp (when
 * dummy.txt was uploaded). Those files are then added to a zip
 * archive and downloaded.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
ignore_user_abort(true);

/**
 * The settings file contains a list of 'defines' at the end of the
 * file. These will conflict with this script, so a tmp_settings is
 * created that eliminates the defines.
 */
$settings_file = $_SERVER['DOCUMENT_ROOT'] . "/../settings.php";
$settings = file($settings_file);
$tmp_settings = [];
foreach ($settings as $line) {
    if (strpos($line, "define") !== false) {
        break;
    } else {
        array_push($tmp_settings, $line);
    }
}
file_put_contents("tmp_settings.php", $tmp_settings);
// mode_settings must be read before invoking 'tmp_settings'
require "../admin/mode_settings.php";
require "tmp_settings.php";
unlink("tmp_settings.php");

// zip file storage location depends on site:
$db_save = '../data/' . $DATABASE . '.sql';
if ($devhost) {
    // currently in admin directory
    exec('mkdir ../tmp');
    $tmpFilename = "../tmp/changes.zip";
} else {
    $tmpFilename = sys_get_temp_dir() . '/changes.zip';
}
if (file_exists($tmpFilename)) {
    unlink($tmpFilename);
}

// make the zip file
$zip = new ZipArchive();
$ziparch = $zip->open($tmpFilename, ZipArchive::CREATE);

// get new file list:
$dir_iterator = new RecursiveDirectoryIterator(
    "../", RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    $dir_iterator, RecursiveIteratorIterator::SELF_FIRST
);
// Upload time plus 20 seconds for unzip
$uploadDate = filemtime("./dummy.txt") + 20;
$udate = date(DATE_RFC2822, $uploadDate);
$chgList = array();
foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() > $uploadDate) {
            if (substr($iterator->getSubPathName(), 0, 4) !== '.git') {
                $zip->addFile($file);
                array_push($chgList, $file);
            }
        }
    }
}
// db was saved in exportDatabase() [$loc] prior to invoking this script
$zip->addFile($loc, $db_save);
$zip->close();
unlink($loc);

echo "Zip archive has been saved to " . $tmpFilename;

/* Apparently too big to download...
// Download the zip file
header("Content-Type: application/x-gzip");
header("Content-Transfer-Encoding: binary");
header("Content-Disposition: attachment; filename=" . basename($tmpFilename));
header("Content-Length: " . filesize($tmpFilename));    
@readfile($tmpFilename);
*/
