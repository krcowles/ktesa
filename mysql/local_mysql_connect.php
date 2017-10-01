<?php
DEFINE("KTESA_DBUG", false,true);
DEFINE("HOSTNAME", "127.0.0.1", true);
DEFINE("USERNAME", "root", true);
DEFINE("PASSWORD", "root", true);
DEFINE("DATABASE", "mysql", true);
$link = mysqli_connect(HostName, UserName, PASSWORD, Database);
if (!$link) {
    $ecode = mysqli_connect_errno();
    if (Ktesa_Dbug) {
        dbug_print("Could not connect to database - error number: " . $ecode);
    } else {
        user_error_msg($rel_addr,0,$ecode);
    }
}
function dbug_print($msg) {
    if (Ktesa_Dbug) {
        echo $msg;
    }
}
function user_error_msg($rel,$errnum,$errcode) {
    header("Location: "  . $rel . "mysql_error_page.php?eno=" . $errnum .
            "&ecd=" . $errcode);
    exit();
}