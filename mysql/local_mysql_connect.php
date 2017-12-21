<?php
DEFINE("KTESA_DBUG", true, true);
DEFINE("HOSTNAME", "127.0.0.1", true);
DEFINE("USERNAME", "root", true);
DEFINE("PASSWORD", "root", true);
DEFINE("DATABASE", "nmhikes", true);
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
