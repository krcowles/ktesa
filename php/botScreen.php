<?php
/**
 * This file screens incoming ip addresses for bots and other nuisance trolls.
 * These intrusions are entered into the database (BLOCK_LOG and IP_BLOCKLIST)
 * so they can be checked when a site access is made.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
use GeoIp2\Database\Reader;
$user_ip = getIpAddress(); // can be null [see editFunctions.php]
$user_ip ??= 'No ipaddr';
if ($user_ip !== 'No ipaddr') {
    $blockCheck
        = $pdo->prepare("SELECT 1 FROM `IP_BLOCKLIST` WHERE `bip` = ? LIMIT 1");
    $blockCheck->execute([$user_ip]);
    if ($blockCheck->fetchColumn()) {
        header('HTTP/1.1 403 Forbidden');
        die("Access denied.");
    }
}

$userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$badBots = [
    'bot', 'crawl', 'spider', 'python', 'python-requests',
    'headlesschrome',  'selenium', 'puppeteer', 
    'go-http-client', 'libww', 'java/', 'mj12bot', 'ahrefsbot',
    'semrushbot', 'curl', 'wget', 'scrape' 
];
$isBadUA = $userAgent === '';
foreach ($badBots as $bot) {
    if (strpos($userAgent, $bot) !== false) {
        $isBadUA = true;
        break;
    }
}
if ($isBadUA) {
    blockAndStrike($pdo, $user_ip, 'bad-ua');
    header('HTTP/1.1 403 Forbidden');
    die("Bot traffic detected.");
}
// --- Rate limit: no more than 20 requests in 2 minutes from one IP ---
if ($user_ip !== 'No ipaddr') {
    $since = date('Y-m-d H:i:s', time() - 120);
    $rateCheck = $pdo->prepare(
        "SELECT COUNT(*) FROM `VISITORS` WHERE `vip` = ? AND `vdatetime` >= ?"
    );
    $rateCheck->execute([$user_ip, $since]);
    if ((int)$rateCheck->fetchColumn() >= 20) {
        blockAndStrike($pdo, $user_ip, 'rate-limit'); // see adminFunctions.php
        header('HTTP/1.1 429 Too Many Requests');
        die("Rate limit exceeded.");
    }
}
$isLocalOrUnknown = in_array($user_ip, ['127.0.0.1', '::1', 'No ipaddr'], true);
if (!$isLocalOrUnknown) {
    try {
        $reader = new Reader('../GeoLite2-City.mmdb');
        $record = $reader->city($user_ip);
        $country_code = $record->country->isoCode;
        if ($record->country->name !== 'US') {
            die("Access denied");
        }
        $region = $record->mostSpecificSubdivision->name;
        $city   = $record->city->name;
    } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
        die("IP address not found");
    } catch (\Exception $e) {
        die("Could not apply address to Geolite database");
        //echo "Error: " . $e->getMessage();
    }
    date_default_timezone_set('America/Denver');
    $visit_time = date('Y-m-d h:i:s');
    $vpage = selfURL(); // can be null [see adminFunctions.php]
    $vpage = $vpage ?? "No Page";
    $browser = getBrowserType(); // can be null [see adminFunctions.php]
    if (!isset($browser)) {
        $browser['name'] = "No Name";
        $browser['patform'] = "No Platform";
    }
    $visitor_data_req = "INSERT INTO `VISITORS` (`vip`,`memid`,`vbrowser`," .
        "`vplatform`,`vdatetime`,`vpage`,`vcity`,`vregion`,`vcountry`) " .
        "VALUES (?,?,?,?,?,?,?,?,?);";
    $visitor_data = $pdo->prepare($visitor_data_req);
    $visitor_data->execute(
        [
            $user_ip,
            $memid,
            $browser['name'],
            $browser['platform'],
            $visit_time,
            $vpage,
            $city,
            $region,
            $country_code
        ]
    );
}
