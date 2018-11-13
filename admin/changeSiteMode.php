<?php
/**
 * This script will change the mode of the site between production and
 * development, and redirect back to the admintoos page.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$current = filter_input(INPUT_GET, 'mode');
$settings_file = "../../settings.php";
$settings = file($settings_file);
if ($settings === false) {
    die("Could not load the settings file when Switching modes: "
        . "admin/changeSiteMode.php " . __LINE__);
}
foreach ($settings as &$line) {
    if (strpos($line, "appMode =") !== false) {
        if ($current === "development") {
            $line = str_replace("development", "production", $line);
            break;
        } elseif ($current === "production")  {
            $line = str_replace("production", "development", $line);
            break;
        } else {
            die("Unrecognized mode: " . $current);
        }
    }
}
$bytes = file_put_contents($settings_file, $settings);
if ($bytes === false) {
    die("Could not write the settings file when Switching modes: "
        . "admin/changeSiteMode.php " . __LINE__);
}
header("Location: admintools.php");

