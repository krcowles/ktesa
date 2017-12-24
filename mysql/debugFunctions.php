<?php
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
