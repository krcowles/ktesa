<?php
/**
 * This script iterates through the project directory, looking
 * for files that have changed since the last time stamp (when
 * dummy.txt was uploaded). Those files are then added to a zip
 * archive and downloaded.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
ignore_user_abort(true);
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $mysqlUserName = USERNAME_LOC;
    $mysqlPassword = PASSWORD_LOC;
    $mysqlHostName = HOSTNAME_LOC;
    $DbName = DATABASE_LOC;
} else {
    $mysqlUserName = USERNAME_000;
    $mysqlPassword = PASSWORD_000;
    $mysqlHostName = HOSTNAME_000;
    $DbName = DATABASE_000;
}
$db = sys_get_temp_dir() . '/' . $DbName . '.sql';
$tmpFilename = sys_get_temp_dir() . '/changes.zip';
if (file_exists($tmpFilename)) {
    unlink($tmpFilename);
}
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
$zip->addFile($db, '../data/' . $DbName . '.sql');
$zip->close();
// Download the zip file
header("Content-Type: application/x-gzip");
header("Content-Disposition: attachment; filename=".basename($tmpFilename));
header("Content-Length: " . filesize($tmpFilename));    
header("Content-Transfer-Encoding: binary");
readfile($tmpFilename);
// clean up
unlink($tmpFilename);
unlink($db);
?>
