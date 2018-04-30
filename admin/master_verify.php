<?php
/**
 * This script is a deterrent for users to gain access to the admin page.
 * This is the script invoked from index.html, and unless the password 
 * entered on the main page matches an entry for either of the current 
 * site masters, the script will go no further. Hence, the actual url for
 * the admin page remains unseen.
 * PHP Version 7.0
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once '../mysql/dbFunctions.php';
$link = connectToDb(__FILE__, __LINE__);
$master_pass = filter_input(INPUT_POST, 'madmin');
$masters = "SELECT username,passwd,passwd_expire,twitter_handle "
    . "FROM USERS WHERE username = 'tom' OR username = 'kc';";
$verify = mysqli_query($link, $masters) or die(
    "Failed to extract critical data from needed table: " . mysqli_error($link)
);
$mstr_cnt = mysqli_num_rows($verify);
if ($mstr_cnt !== 2) {
    die("Incorrect number of master entries in USERS");
}
$match = false;
while ($check = mysqli_fetch_assoc($verify)) {
    if (password_verify($usrpass, $check['passwd'])) {
        //if ($check['passwd_expire'] == formDate()) {
            $match = true;
            $file = $check['twitter_handle'];
        //}
    }
}
if ($match) {
    redirect("Location: {$file}");
} else {
    echo "SORRY - Password not found";
}
