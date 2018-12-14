<?php
/** 
 * This file is intended to reside in this directory only during the transition
 * to a more secure location in a new file, 'settings.php'. It will be a virtual
 * copy of that file until the transition is completed.
 */
require "../admin/mode_settings.php";
DEFINE("PORT", "3306", true);
DEFINE("CHARSET", "UTF8", true);
$devhost = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($devhost) { // LOCAL MACHINE
    DEFINE("HOSTNAME", "127.0.0.1", true);
    DEFINE("USERNAME", "root", true);
    DEFINE("PASSWORD", "root", true);
    if ($dbState === 'main') {  // main db
        DEFINE("DATABASE", "id140870_hikemaster", true);
    } else {  // test db
        DEFINE("DATABASE", "id140870_nmhikestest", true);
    }
} else { // WEB SERVER
    DEFINE("HOSTNAME", "localhost", true);
    DEFINE("PASSWORD", "000ktesa9", true);
    if ($dbState === 'main') {  // main db
        DEFINE("USERNAME", "id140870_krcowles", true);
        DEFINE("DATABASE", "id140870_hikemaster", true);
    } else {  // test db
        DEFINE("USERNAME", "id140870_krcowlestest", true);
        DEFINE("DATABASE", "id140870_nmhikestest", true);
    }
}
