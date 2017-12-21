<?php
DEFINE("KTESA_DBUG", true, true);
DEFINE("HOSTNAME", "localhost", true);
DEFINE("USERNAME", "id140870_krcowles", true);
DEFINE("PASSWORD", "000ktesa9", true);
DEFINE("DATABASE", "id140870_hikemaster", true);
$link = mysqli_connect(HostName, UserName, PASSWORD, Database);
if (!$link) {
    $ecode = mysqli_connect_errno();
    if (Ktesa_Dbug) {
        dbug_print("connect.php: Could not connect to database - error number: " . $ecode);
    } else {
        user_error_msg($rel_addr, 0, $ecode);
    }
}

require_once "../admin/set_sql_mode.php";

function dbug_print($msg)
{
    if (Ktesa_Dbug) {
        echo $msg;
    }
}
function user_error_msg($rel, $errnum, $errcode)
{
    header("Location: "  . $rel . "mysql_error_page.php?eno=" . $errnum .
            "&ecd=" . $errcode);
    exit();
}
