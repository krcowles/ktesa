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
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day
$data = $_REQUEST;
$usrname = filter_input(INPUT_POST, 'usr_name');
$usrpass = filter_input(INPUT_POST, 'usr_pass');
$admin = ($usrname === 'tom' || $usrname === 'kc') ? true : false;
$usr_req = "SELECT username,passwd,passwd_expire FROM USERS WHERE username = :usr;";
$auth = $pdo->prepare($usr_req);
$auth->bindValue(":usr", $usrname);
$auth->execute();
$rowcnt = $auth->rowCount();
if ($rowcnt === 1) {  // located single instance of user
    $user_dat = $auth->fetch(PDO::FETCH_ASSOC);
    if (password_verify($usrpass, $user_dat['passwd'])) {  // user data correct
        if ($admin) {
            $adminExpire = time() + 10 * UX_DAY * 365;  // 10 yrs
            setcookie('nmh_mstr', 'mstr', $adminExpire, "/");
        } else {
            $expiration = $user_dat['passwd_expire'];
            $american = str_replace("-", "/", $expiration);
            $expdate = strtotime($american);
            if ($expdate <= time()) {
                echo "EXPIRED";
                exit;
            } else {
                $days = floor(($expdate - time())/UX_DAY);
                if ($days <= 5) {
                    // set current cookie pending renewal
                    setcookie('nmh_id', $usrname, $expdate, "/");
                    echo "RENEW";
                    exit;
                }
            }
            setcookie('nmh_id', $usrname, $expdate, "/");
        }
        echo "LOCATED";
    } else {  // user exists, but password doesn't match:
        echo "BADPASSWD" . $usrpass . ";" . $user_dat['passwd'];
    }
} else {  // not in USER table (or multiple entries for same user)
    echo "FAIL";
}
