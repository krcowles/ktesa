<?php
    DEFINE("KTESA_DBUG",true,true);
    DEFINE("HOSTNAME","localhost",true);
    DEFINE("USERNAME","id140870_krcowles",true);
    DEFINE("PASSWORD","000ktesa9",true);
    DEFINE("DATABASE","id140870_hikemaster",true);
    $link = mysqli_connect(HostName, UserName, PASSWORD, Database);
    if (!$link) {
        $ecode = mysqli_connect_errno();
        if (Ktesa_Dbug) {
            dbug_print("Could not connect to database - error number: " . $ecode);
        } else {
            header("Location: mysql_error_page.php?errno=0&errcd=" . $ecode);
            exit();
        }
    } 
    function dbug_print($msg) {
        if (Ktesa_Dbug) {
            echo $msg;
        }
    }