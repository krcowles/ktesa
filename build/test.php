<?php
/**
 * This script is called via ajax from the ktesaUploader.js module.
 * One or more files will be POSTed from the form. This data will be used
 * to construct the images required for storage on the site and
 * corresponding data will be entered into the database. The files will
 * be checked for exif metadata.
 * PHP Version 7.1
 * 
 * @package Uploading
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../vendor/autoload.php";
/** 
 * THE FOLLOWING REQUIRES WILL BE REPLACED WITH A SINGLE LINE WHEN
 * 'PDO_complete' is merged:
 * require "../php/global_boot.php";
 */
require "../php/global_boot.php";

// POSTED DATA
$indxNo = filter_input(INPUT_POST, 'indx');
$picname = filter_input(INPUT_POST, 'pnme');
$picdesc = filter_input(INPUT_POST, 'pdes');
$image = $_FILES['img'];
sleep(2);
$msg = "Yap";
echo json_encode($msg);
