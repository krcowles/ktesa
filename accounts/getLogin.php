<?php
/**
 * This script checks for any incoming cookies. Presence of a
 * cookie indicates prior opt-in for cookie use. If no cookie
 * is detected, the cookie banner will display and the user
 * may opt-in or opt-out. Opting out prevents cookies from 
 * being sent to the visitor's browser. Note: having a separate
 * admin ('master') cookie allows admins to have both an admin
 * account and a user account (to verify user functionality).
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day

$master = isset($_COOKIE['nmh_mstr']) ? true : false;
$regusr = isset($_COOKIE['nmh_id'])   ? true : false;
$banner = ($master || $regusr) ? "no" : "yes";
$cookie_state = "OK";  // may change depending on user verification
$admin = false;

// Some test cases have resulted in 'partial logins':
if (!isset($_SESSION['username']) || !isset($_SESSION['userid']) 
    || !isset($_SESSION['expire'])
) {
     unset($_SESSION['username']);
     unset($_SESSION['userid']);
     unset($_SESSION['expire']);   
}

if ($banner === 'no' && !isset($_SESSION['username'])) {  // user previously opted in
    if ($regusr) {
        $username = $_COOKIE['nmh_id'];
        $expirationReq = "SELECT `userid`,`passwd_expire` FROM " .
            "`USERS` WHERE username = ?;";
        $userExpire = $pdo->prepare($expirationReq);
        $userExpire->execute([$username]);
        $rowcnt = $userExpire->rowCount();
        if ($rowcnt === 0) {
            $cookie_state = 'NONE'; // no user matching this cookie
        } elseif ($rowcnt === 1) {
            $expire_data = $userExpire->fetch(PDO::FETCH_ASSOC);
            $userid = $expire_data['userid'];
            $expDate = $expire_data['passwd_expire'];
            // protected login credentials:
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $userid;
            $_SESSION['expire'] = $expDate;
            $american = str_replace("-", "/", $expDate);
            $orgDate = strtotime($american);
            if ($orgDate <= time()) {
                $cstat = 'EXPIRED';
            } else {
                $days = floor(($orgDate - time())/UX_DAY);
                if ($days <= 5) {
                    $cstat = 'RENEW';
                }
            }
        } else {
            $cstat = "MULTIPLE"; // for testing only; no longer possible
        }
    } elseif ($master) {
        $_SESSION['username'] = 'mstr';
        if ($_COOKIE['nmh_mstr'] === 'mstr2') {
            $_SESSION['userid'] = '2';
        } else {
            $_SESSION['userid'] = '1';
        }
        $_SESSION['expire'] = "2050-12-31";
        $admin = true;
    }
} elseif ($_SESSION['username'] === 'mstr') {
    $admin = true;
}
