<?php
if ( !mysqli_query($link,"DROP PROCEDURE IF EXISTS getTitles") ) {
    if (Ktesa_Dbug) {
        dbug_print('newHike.php: Failed to DROP PROCEDURE ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$titles = <<<Proc
CREATE PROCEDURE getTitles(INOUT title_list VARCHAR(10000))
BEGIN
    DECLARE eoloop INTEGER DEFAULT 0;
    DECLARE pt VARCHAR(30) DEFAULT "";
    DECLARE pt_cursor CURSOR FOR 
        SELECT pgTitle FROM HIKES;
    DECLARE CONTINUE HANDLER
        FOR NOT FOUND SET eoloop = 1;
    OPEN pt_cursor;
    get_pgtitles: LOOP
        FETCH pt_cursor INTO pt;
        IF eoloop = 1 THEN
            CLOSE pt_cursor;
            LEAVE get_pgtitles;
        END IF;
        SET title_list = CONCAT(title_list,"^",pt);
    END LOOP get_pgtitles;
END;
Proc;
$loop_proc = mysqli_query($link,$titles);
if (!$loop_proc) {
   if (Ktesa_Dbug) {
        dbug_print('newHike.php: Failed to CREATE PROCEDURE; ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
if ( !mysqli_query($link,"SET @title_list = '';") ) {
    if (Ktesa_Dbug) {
        dbug_print('newHike.php: Failed to SET variable title_list; ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$query = "CALL getTitles(@title_list);";
$proc_call = mysqli_query($link,$query);
if (!$proc_call) {
   if (Ktesa_Dbug) {
        dbug_print('newHike.php: Failed to invoke PROCEDURE ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$result = mysqli_query($link,"SELECT @title_list;");
if (!$result) {
    if (Ktesa_Dbug) {
        dbug_print('newHike.php: Failed to retrieve list of titles: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$row = mysqli_fetch_row($result);
$tlist = explode("^",$row[0]);
# NOTE: initializing title_list to '' is REQUIRED, but seems to create an empty name
array_shift($tlist);
# convert to javascript array:
$hnames = json_encode($tlist);
mysqli_free_result($result);
