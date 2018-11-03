<?php
/**
 * Switch between production and development modes for the entire site.
 * This involves setting the $appMode variable as shown below.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$newMode = filter_input(INPUT_GET, 'mode');
$settingsFile = "../../settings.php";
$settings = file($settingsFile);
if ($settings === false) {
    echo "Could not read settings!";
    exit;
}
foreach ($settings as &$line) {
    if (strpos($line, "appMode") !== false) {
        if ($newMode === 'development') {
            $line = str_replace("production", "development", $line);
        } else {
            $line = str_replace("development", "production", $line);
        }
        break;
    }
}
if (file_put_contents($settingsFile, $settings) === false) {
    echo "Could not write settings!";
} else {
    echo $newMode;
}
