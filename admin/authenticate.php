<?php
/**
 * This script authenticate the username/password combo on the main
 * site by comparing it to the USERS table.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$usrname = trim($_REQUEST['nmhid']);
$usrpass = trim($_REQUEST['nmpass']);
$usr_req = "SELECT username,passwd FROM USERS WHERE username = :usr;";
$auth = $pdo->prepare($usr_req);
$auth->bindValue(":usr", $usrname);
$auth->execute();
$rowcnt = $auth->rowCount();
if ($rowcnt === 1) {  // located user
    $user_dat = $auth->fetch(PDO::FETCH_ASSOC);
    if (password_verify($usrpass, $user_dat['passwd'])) {  // user data correct
        echo "LOCATED";
    } else {  // user exists, but password doesn't match:
        echo "BADPASSWD" . $usrpass . ";" . $user_dat['passwd'];
    }
} else {  // not in USER table (or multiple entries for same user)
    echo "FAIL";
}
