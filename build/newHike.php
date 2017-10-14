<?php
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../php/';
    require_once "../php/000mysql_connect.php";
}
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
# NOTE: initializing title_list to '' (REQUIRED) seems to create an empty name
array_shift($tlist);
# convert to javascript array:
$hnames = json_encode($tlist);
?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Start A New Hike Page</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
        <link href="newHike.css" type="text/css" rel="stylesheet" />
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail">Assign New Hike</p>

        <form id="newbie" target="_blank" action="newSave.php" method="GET">
        <div id="newrow" style="margin-left:16px;font-size:18px;">
            <p>Begin by Assigning a Hike Name: &nbsp;
                <input id="newname" type="text" name="new" size="40" required />
            </p>
        </div>
        <div style="margin-left:16px;">
            To reserve this hike: &nbsp;&nbsp;
            <input id="saveit" type="submit" name="resrv" value="Reserve This Hike" /><br /><br />
            <span style="color:brown">You will be able to continue to add hike data to this hike,
                or proceed at a later date.</span>
        </div>
        </form>
        <script type="text/javascript">
            var hnames = <?php echo $hnames;?>;
        </script>
        <script src="../scripts/jquery-1.12.1.js"></script>
        <script src="newHike.js"></script>
    </body>
</html>