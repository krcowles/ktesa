<?php
/**
 * This script is used by the ktesaPanel to determine whether or not
 * a user is logged in, and if so, to get the user id. The user id is
 * utilized to enable menu settings (via javascript/getLogins.js), and
 * to direct the user to the correct pages for his/her hikes for editing.
 * If there are cookies on the client's browser for this site (ktesa),
 * then they are used. If there are no cookies for this site, or if the
 * client has cookies turned off, the script will look for login via a
 * php session ($_SESSION). If not logged in via the session, the $uname
 * is set to 'none' to advise the javascript.
 * PHP Version 7.1
 * 
 * @package Display
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day

$master = isset($_COOKIE['nmh_mstr']) ? true : false;
$regusr = isset($_COOKIE['nmh_id'])   ? true : false;
$uname = 'none';
$uid = '0';
$cstat = 'OK'; // changed below based on user cookie expiration data
if ($regusr) {
    $uname = $_COOKIE['nmh_id'];
    $expirationReq = "SELECT `userid`,`passwd_expire` FROM `USERS` WHERE username = ?;";
    $userExpire = $pdo->prepare($expirationReq);
    $userExpire->execute([$uname]);
    $rowcnt = $userExpire->rowCount();
    if ($rowcnt === 0) {
        $cstat = 'NONE';
    } elseif ($rowcnt === 1) {
        $fetched = $userExpire->fetch(PDO::FETCH_ASSOC);
        $uid = $fetched['userid'];
        $expDate = $fetched['passwd_expire'];
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
        $cstat = 'MULTIPLE';
    }
} elseif ($master) {
    $uname = 'mstr';
    $uid = '1';
}
