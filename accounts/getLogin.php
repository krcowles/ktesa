<?php
/**
 * This script checks for any incoming site cookies. If cookies are
 * present, and the user has not already logged in, login credentials
 * are set up if the user has registered. If the user is already logged
 * in, essentially nothing else occurs.
 * Note: having a separate admin ('master') cookie allows admins
 * to have both an admin account and a user account to verify user
 * functionality.
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
$cookie_state = "NOLOGIN";  // default
$admin = false;

// Some test cases have resulted in 'partial logins':
if (!isset($_SESSION['username']) || !isset($_SESSION['userid']) 
    || !isset($_SESSION['expire']) || !isset($_SESSION['cookies'])
    || !isset($_SESSION['cookie_state'])
) {
     unset($_SESSION['username']);
     unset($_SESSION['userid']);
     unset($_SESSION['expire']); 
     unset($_SESSION['cookies']);
     unset($_SESSION['cookie_state']);
}

if (!isset($_SESSION['username'])) { // NO LOGIN YET
    if ($regusr) {
        $username = $_COOKIE['nmh_id'];
        // check cookie choice and password expiration
        $userDataReq = "SELECT `userid`,`passwd_expire`,`facebook_url` FROM " .
            "`USERS` WHERE username = ?;";
        $userData = $pdo->prepare($userDataReq);
        $userData->execute([$username]);
        $rowcnt = $userData->rowCount();
        if ($rowcnt === 0) {
            $cookie_state = 'NONE'; // no user matching this cookie
        } elseif ($rowcnt === 1) {
            $cookie_state = "OK";
            $user_info = $userData->fetch(PDO::FETCH_ASSOC);
            $userid = $user_info['userid'];
            $expDate = $user_info['passwd_expire'];
            $cookies = $user_info['facebook_url'];
            $choice = 'reject';  // default if no user selection 
            if (!empty($cookies)) {
                $choice = $cookies;
            }
            // protected login credentials:
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $userid;
            $_SESSION['expire'] = $expDate;
            $_SESSION['cookies'] = $choice;
            $american = str_replace("-", "/", $expDate);
            $orgDate = strtotime($american);
            if ($orgDate <= time()) {
                $cookie_state = 'EXPIRED';
            } else {
                $days = floor(($orgDate - time())/UX_DAY);
                if ($days <= 5) {
                    $cookie_state = 'RENEW';
                }
            }
        } else {
            $cookie_state = "MULTIPLE"; // for testing only; no longer possible
        }
    } elseif ($master) {
        $cookie_state = "OK";
        $_SESSION['username'] = 'mstr';
        if ($_COOKIE['nmh_mstr'] === 'mstr2') {
            $_SESSION['userid'] = '2';
        } else {
            $_SESSION['userid'] = '1';
        }
        $_SESSION['expire'] = "2050-12-31";
        $_SESSION['cookies'] = "accept";
        $admin = true;
    } else {
        // STILL NOT LOGGED IN: No credentials are set
        $cookie_state = "NOLOGIN";  // repeat as documentation...
    }
    $_SESSION['cookie_state'] = $cookie_state;
} else { // LOGGED IN: (User data in $_SESSION vars): $_SESSION['username'] is true
    if ($_SESSION['username'] === 'mstr') {
        $admin = true;
    }
}
