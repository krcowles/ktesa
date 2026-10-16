<?php
/**
 * Update the user version displayed on the site or the app;
 * This file is located in the php directory so that the ktesa_app
 * tool can access it without special admin authorization. The
 * ktesa_app will generate a CORS request, so appropriate headers
 * are set at the beginning of the script. For website access,
 * the 'about' page is auto-updated with the new version no.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$allowed = [
    'http://localhost:8888',   // MAMP port
    'http://localhost',
    'capacitor://localhost',   // iOS Capacitor
    'https://localhost',       // Android Capacitor
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {   // preflight, if ever triggered
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
    exit;
}
header('Content-Type: application/json');

session_start();
require "../php/global_boot.php";

$preview  = filter_input(INPUT_POST, 'preview');
$platform = filter_input(INPUT_POST, 'platform'); // site or app
$type     = filter_input(INPUT_POST, 'type'); // major or minor
$commit   = filter_input(INPUT_POST, 'commit'); // new commit #

$version_query = <<<QUERY
SELECT version
FROM VERSIONS
WHERE platform = ?
ORDER BY
  CAST(SUBSTRING_INDEX(version, '.', 1)  AS UNSIGNED) DESC,
  CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC
LIMIT 1
QUERY;
// 1st CAST => major version
// 2nd CAST => minor version
try {
    $ver_request = $pdo->prepare($version_query);
    $ver_request->execute([$platform]);
    $current_ver = $ver_request->fetchColumn();
} catch (PDOException $pdoe) {
    echo json_encode(
        ['error' => "Failed to retrieve version: " . $pdoe->getMessage()]
    );
    exit;
}

$dot = strpos($current_ver, ".");
$curr_major = intval(substr($current_ver, 0, $dot));
$curr_minor = intval(substr($current_ver, $dot+1));
if ($type === 'major') {
    $curr_major++;
    $new_version = "{$curr_major}.0";
} elseif ($type === 'minor') {
    $curr_minor++;
    $new_version = "{$curr_major}.{$curr_minor}";
}

$result = [
    'old' => $current_ver,
    'new' => $new_version
];
$json_result = json_encode($result);
if ($preview === 'yes') {
    echo $json_result;
    exit;
}

try {
    $version_upateReq = "INSERT INTO `VERSIONS` (`platform`,`version`,`commit`) VALUES (?,?,?);";
    $version_update = $pdo->prepare($version_upateReq);
    $version_update->execute([$platform, $new_version, $commit]);
} catch (PDOException $pdoe) {
    echo json_encode(
        ['error' => "Failed to update version: " . $pdoe->getMessage()]
    );
    exit;
}
if ($platform === 'site') {
    $filename = "../pages/about.php";
    $replacement = "<p>Version " . $new_version . "</p>\n";
    $about_file = file($filename);
    foreach ($about_file as $key => $line) {
        if (strpos($line, "<p>Version") !== false) {
            $about_file[$key] = $replacement;
            break;
        }
    }
    file_put_contents($filename, implode('', $about_file));
}
echo $json_result;
