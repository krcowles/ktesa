<?php
/**
 * This simple ajaxed script deletes the current 'photoStat.txt' created by
 * usrPhotos.php then read by ktesaUploader.js. After reading post upload, it
 * is no longer required.
 * PHP Version 7.1
 * 
 * @package Uploading
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$status = 'photoStat.txt';
if (file_exists($status)) {
    unlink($status);
}
echo "Done";
