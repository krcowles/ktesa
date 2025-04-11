<?php
/**
 * This script checks for any incoming site cookies. If cookies are
 * present, and the user has not already logged in, login credentials
 * are set up for this member. If the user is already logged in,
 * or a session is still in effect, essentially nothing else occurs.
 * session_start() must reside on the page from which this routine is
 * being invoked. The ktesaPanel/Navbar calls getLogin.php.
 * Note: having a separate admin ('master') cookie allows admins
 * to have both an admin account and a user account to verify user
 * functionality.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day

$master = isset($_COOKIE['nmh_mstr']) ? true : false;
$regusr = isset($_COOKIE['nmh_id'])   ? true : false;
$cookie_state = "NOLOGIN";  // default
$admin = false;
$memid = '0';

// Some use cases have 'partial logins':
if (!isset($_SESSION['username']) || !isset($_SESSION['userid']) 
    || !isset($_SESSION['cookie_state']) || !isset($_SESSION['club_member'])
) {
     unset($_SESSION['username']);
     unset($_SESSION['userid']);
     unset($_SESSION['cookie_state']);
     unset($_SESSION['club_member']);
}

if (!isset($_SESSION['username'])) { // No login yet...
    if ($regusr) { // is there a site cookie present?
        $username = $_COOKIE['nmh_id'];
        // check password expiration
        $userDataReq = "SELECT `userid`,`passwd_expire`,`club_member` FROM " .
            "`USERS` WHERE username = ?;";
        $userData = $pdo->prepare($userDataReq);
        $userData->execute([$username]);
        $rowcnt = $userData->rowCount();
        if ($rowcnt === 0) {
            $cookie_state = 'NONE'; // no user matching this cookie
        } elseif ($rowcnt === 1) {
            $cookie_state = "OK";
            $user_info = $userData->fetch(PDO::FETCH_ASSOC);
            $userid  = $user_info['userid'];
            $expDate = $user_info['passwd_expire'];
            $club    = $user_info['club_member'];
            $memid   = $user_info['userid'];
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
            // Note: user not fully logged in if RENEW/EXPIRED
            if ($cookie_state === 'OK') {
                // protected login credentials:
                $_SESSION['username']     = $username;
                $_SESSION['userid']       = $userid;
                $_SESSION['club_member']  = $club;
            }
        } else {
            $cookie_state = "MULTIPLE"; // for testing only; no longer possible
        }
    } elseif ($master) { // Currently 3 masters...
        $cookie_state = "OK";
        $_SESSION['club_member'] = "Y";
        if ($_COOKIE['nmh_mstr'] === 'mstr') {
            $_SESSION['userid'] = '1';
            $_SESSION['username'] = 'tom';
        } elseif ($_COOKIE['nmh_mstr'] === 'Rockcogar') {
            $_SESSION['userid'] = '14';
            $_SESSION['username'] = 'Rockcogar';
        } else {
            $_SESSION['userid'] = '2';
            $_SESSION['username'] = 'kc';
        }
        $admin = true;
    }
    $_SESSION['cookie_state'] = $cookie_state;
} else {
    // LOGGED IN: (User data is in $_SESSION vars);
    if ($_SESSION['userid'] == '1'  || $_SESSION['userid'] == '2'
        || $_SESSION['userid'] == '14'
    ) {
        $admin = true;
        $cookie_state = "OK";
    } 
}
if (!$admin) {
    /**
     * Capture visitor tracking info; Functions are contained in editFunctions.php
     * and in adminFunctions.php. For browser type identification:
     * https://stackoverflow.com/questions/2199793/php-get-the-browser-name
     * For page url identification: [PSR syntax corrections made]
     * http://geeklabel.com/tutorial/track-visitors-php-tutorial/ 
     * Using country identification method outline in 
     * http://www.phptutorial.info/iptocountry/the_script.html#example1
     * which is a free downloaded library not requiring http:// lookups.
     */
    $user_ip = getIpAddress(); // can be null!
    $user_ip = $user_ip ?? 'no ipaddr';
    if ($user_ip === '91.240.118.252') { // Chang Way Enterprise
        die("Access not permitted");
    }
    if ($user_ip !== 'no ipaddr' && $user_ip !== '127.0.0.1' && $user_ip !== '::1') {
        // New method: former ipinfo site limits no of requests
        $country = '';
        $numbers = preg_split("/\./", $user_ip);   
        include "../ip_files/" . $numbers[0] . ".php";
        $code = ($numbers[0] * 16777216) + ($numbers[1] * 65536) +
            ($numbers[2] * 256) + ($numbers[3]);   
        foreach ($ranges as $key => $value) {
            if ($key <= $code) {
                if ($ranges[$key][0] >= $code) {
                    $country = $ranges[$key][1];
                    break;
                }
            }
        }
        if ($country !== 'US') {
            die("Access not permitted");
        }
        // Bad robot...
        if (strpos($user_ip, '52.167.144') !== false
            || strpos($user_ip, '40.77.167') !== false
            || strpos($user_ip, '20.26.44') !== false
        ) {
            die("Access not permitted");
        }
    }
    $browser = getBrowserType(); // can be null!
    if (!isset($browser)) {
        $browser['name'] = "no name";
        $browser['patform'] = "no platform";
    }
    date_default_timezone_set('America/Denver');
    $visit_time = date('Y-m-d h:i:s');
    $vpage = selfURL(); // can be null
    $vpage = $vpage ?? "no page";
    $visitor_data_req = "INSERT INTO `VISITORS` (`vip`,`memid`,`vbrowser`," .
        "`vplatform`,`vdatetime`,`vpage`) VALUES (?,?,?,?,?,?);";
    $visitor_data = $pdo->prepare($visitor_data_req);
    $visitor_data->execute(
        [
            $user_ip,
            $memid,
            $browser['name'],
            $browser['platform'],
            $visit_time,
            $vpage
        ]
    );
}
