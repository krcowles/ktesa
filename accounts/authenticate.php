<?php
/**
 * This script authenticates the username/password combo entered by the
 * the user when submitting a login request. The information is compared
 * to entries in the USERS table. This script is invoked when a user attempts
 * to login, and is invoked via the validateUser() function. Multiple failed
 * attempts results in login lockout for 1 hour, or via email link available
 * to the user when the lockout occurs. Note that the user's IP address is logged
 * to identify cases where user attempts to use a different browser to bypass
 * the lockout. No session variables are set here, nor cookies set; login is
 * not completed until a security question is correctly answered.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day

$username = filter_input(INPUT_POST, 'usr_name');
$userpass = filter_input(INPUT_POST, 'usr_pass');
$ip = getIpAddress();
$ip = $ip === "::1" ? '127.0.0.1' : $ip; // Chrome localhost ipaddr is different
$fails = 0;

$iptbl = <<<TBL
CREATE TABLE IF NOT EXISTS `LOCKS` (
    `indx`   smallint NOT NULL AUTO_INCREMENT,
    `ipaddr` varchar(15) DEFAULT NULL,
    `fails`  smallint DEFAULT 0,
    `lockout` datetime DEFAULT NULL,
    PRIMARY KEY (`indx`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
TBL;
$pdo->query($iptbl);
// Set '$fails' to the current value in the LOCKS table (or use default 0)
$entryReq = "SELECT `ipaddr`,`fails` FROM `LOCKS`;";
$entries = $pdo->query($entryReq)->fetchAll(PDO::FETCH_KEY_PAIR);
if (count($entries) === 0) {
    $entry = "INSERT INTO `LOCKS` (`ipaddr`,`fails`) VALUES (?, 0);";
    $adduser = $pdo->prepare($entry);
    $adduser->execute([$ip]);
} else {
    if (array_key_exists($ip, $entries)) {
        $fails = $entries[$ip];
    } else {
        $entry = "INSERT INTO `LOCKS` (`ipaddr`,`fails`) VALUES (?, 0);";
        $adduser = $pdo->prepare($entry);
        $adduser->execute([$ip]);
    }
}

// validate user info (prior to security questions)
$user_dat = $pdo->query("SELECT * FROM `USERS`")->fetchAll(PDO::FETCH_ASSOC);
$nomatch = true;
$return_data['status'] = "Not processed";
foreach ($user_dat as $user) {
    if ($username === $user['username'] && password_verify(
        $userpass, $user['passwd']
    )
    ) {  // user data matches
        $nomatch = false;
        $uid = $user['userid'];
        reduceLocks(count($entries), $ip, $pdo);
        $expiration = $user['passwd_expire'];
        if ($expiration) {  // i.e. not null, undefined, or ''
            $american = str_replace("-", "/", $expiration);
            $expdate = strtotime($american);
            if ($expdate <= time()) {
                // remove user from Users table
                $expiredUser = "DELETE FROM `USERS` WHERE `userid`=?;";
                $removeUser = $pdo->prepare($expiredUser);
                $removeUser->execute([$uid]);
                $return_data['status'] = 'EXPIRED';
                echo json_encode($return_data);
                exit;
            } else {
                $days = floor(($expdate - time())/UX_DAY);
                if ($days <= 5) {
                    $return_data['status'] = "RENEW";
                    echo json_encode($return_data);
                    exit;
                } else {
                    $return_data = [
                        'status' => 'LOCATED',
                        'ix' => $uid
                    ]; 
                    break;
                }
            }
        } else {
            $return_data['status'] = "Blank field";
        }
    }
}
if ($nomatch) { // no user or bad password
    updateFailures(++$fails, $ip, $pdo); // see adminFunctions.php
    $return_data['status'] = 'FAIL';
    $return_data['fail_cnt'] = $fails;
}
echo json_encode($return_data);
