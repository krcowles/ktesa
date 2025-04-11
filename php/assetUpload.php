<?php
/**
 * This script will use the submitted form data to populate
 * the database with the user information, and will upload
 * the file to the club_assets directory.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$_SESSION['upload_msg'] = '';
$redirect = "../pages/clubAssets.php";
$file_err = $_FILES['asset']['error'];
if ($file_err !== UPLOAD_ERR_OK) {
    $SESSION['upload_msg'] = "File upload error; try a different file";
    header("Location: {$redirect}");
    exit;
}
$upload   = $_FILES['asset']['name'];
$file_loc = $_FILES['asset']['tmp_name'];
$description = filter_input(INPUT_POST, 'label');
$location = $_POST['nm_location'];
move_uploaded_file($file_loc, "../club_assets/{$upload}");
$newReq = "INSERT INTO `CLUB_ASSETS` (`item`,`region`,`description`) " .
    "VALUES(?,?,?);";
$new_item = $pdo->prepare($newReq);
$new_item->execute([$upload, $location, $description]);
$_SESSION['upload_msg'] = "OK";
header("Location: {$redirect}");
