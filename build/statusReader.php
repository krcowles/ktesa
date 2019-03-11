<?php
/**
 * A simple script to read the end result for uploading photos
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$statfile = 'photoStat.txt';
$status = file_get_contents($statfile);
if ($status === false) {
    echo "Status 'photoStat.txt' is not available";
} else {
    echo $status;
    unlink($statfile);
}
