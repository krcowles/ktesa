<?php
/** 
 * This file is intended to reside in this directory only during the transition
 * to a more secure location in a new file, 'settings.php'. It will be a virtual
 * copy of that file until the transition is completed.
 */
require "../admin/mode_settings.php"; // these can change, so no 'require_once'
$PORT = "3306";
$CHARSET = "UTF8";
$devhost = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($devhost) { // LOCAL MACHINE
    $HOSTNAME = "127.0.0.1";
    $USERNAME = "root";
    $PASSWORD = "root";
    if ($dbState === 'main') {  // main db
        $DATABASE = "id140870_hikemaster";
    } else {  // test db
        $DATABASE = "id140870_nmhikestest";
    }
} else { // WEB SERVER
    $HOSTNAME = "localhost";
    $PASSWORD = "xxxxx"; // to be manually edited on each machine
    if ($dbState === 'main') {  // main db
        $USERNAME = "nmhikesc_dbUser";
        $DATABASE = "nmhikesc_main";
    } else {  // test db
        $USERNAME = "nmhikesc_dbUser";
        $DATABASE = "nmhikesc_test";
    }
}
