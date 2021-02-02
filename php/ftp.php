
<?php
/**
 * Archive the incoming branch/commit. Then, create an ftp connection
 * to nmhikes.com for uploading a new test site.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

verifyAccess('ajax');

$branch   = filter_input(INPUT_POST, 'branch');
$commit   = filter_input(INPUT_POST, 'commit');
$testSite = $branch . "_" . $commit;
$remote   = "{$testSite}.zip";
$zipFile  = "../CuArchives/{$remote}";

/**
 * Create archive to upload
 * See tools/makeArchive.sh for mods to make this work
 */
$cmd = $documentRoot . "/tools/makeArchive.sh {$branch} {$commit}";
if (($result = shell_exec($cmd)) === false) {
    echo "Archive command failed";
    exit;
}

/**
 * Upload to main
 */
chdir($documentRoot);
if (($conn = ftp_ssl_connect('nmhikes.com')) === false) {
    echo "Created archive, but could not connect to main";
    exit;
}
// NOTE: successful login places you at docroot...
if (!ftp_login($conn, FTP_USER, FTP_PASS)) {
    echo "Created archive, but can't login to ftp";
    exit;
}
if (!ftp_put($conn, $testSite, $zipFile, FTP_BINARY)) {
    echo "Created archive and logged in, but failed to upload file";
    exit;
}
/*
$mkdir ="mkdir {$testSite}";
if (ftp_exec($conn, $mkdir) === false) {
    echo "Failed to create test site directory";
    exit;
}
$chmod = "chmod 755 {$testSite}";
if (ftp_exec($conn, $chmod) === false) {
    echo "Could not set test site directory permissions";
    exit;
}
/*
$unzip = "unzip -qq {$remote} -d {$testSite}";
if (!ftp_exec($conn, $unzip) === false) {
    echo "Unzip could not be performed";
}
*/
ftp_close($conn);
echo 'Done';
