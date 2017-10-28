<?php
require '../mysql/setenv.php';
$newHike = filter_input(INPUT_GET,'new');
$usr = filter_input(INPUT_GET,'usr');
$query = "INSERT INTO EHIKES (pgTitle, usrid, stat) VALUES " .
        "('{$newHike}','{$usr}','new');";
$result = mysqli_query($link,$query);
if (!$result) {
    if (Ktesa_Dbug) {
        dbug_print('newSave.php: Could not add new hike to database: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
mysqli_free_result($result);
$lastid = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1";
$getid = mysqli_query($link,$lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('newSave.php: Could not retrieve highest indxNo: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$lastitem = mysqli_fetch_row($getid);
$lastindx = $lastitem[0];
mysqli_free_result($getid);
?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Hike Reserved</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
        <style type="text/css">
            body { background-color: #dfdfdf; }
        </style>
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail"><?php echo $newHike;?></p>
        <div style="margin-left:24px">
        <?php
        echo '<h2 style="color:brown">You have successfully created a new '
            . 'hike for ' . $newHike . " as Editable Hike No. " . $lastindx . "</h2>\n"
            . "<p>You may edit this hike at any time by returning to the "
            . "home page and selecting 'Edit New/Unpublished Hikes'<br />";
        ?>
        </div>
    </body>
</html>