<?php
$newHike = filter_input(INPUT_GET,'new');
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../php/';
    require_once "../php/000mysql_connect.php";
}
$query = "INSERT INTO HIKES (pgTitle) VALUES ('{$newHike}');";
$result = mysqli_query($link,$query);
if (!$result) {
    if (Ktesa_Dbug) {
        dbug_print('newSave.php: Could not add new hike to database: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$lastid = "SELECT indxNo FROM HIKES ORDER BY indxNo DESC LIMIT 1";
$getid = mysqli_query($link,$lastid);
if (!$getid) {
    if (Ktesa_Dbug) {
        dbug_print('newSave.php: Could not retrieve highest indxNo: ' . 
                mysqli_error($link));
    } else {
        user_error_msg($rel_addr,5,0);
    }
}
$lastindx = mysqli_fetch_row($getid);
die ("OK");
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
        <div style="margin-left:24px">
        <?php
        echo '<h2 style="color:brown">You have successfully created a new '
            . 'hike for ' . $newHike . " as Hike No. " . $lastindx . "</h2>\n"
            . "<p>You may edit this hike at any time by returning to the "
            . "home page and selecting 'Edit Hike'</p>\n NOTE: For now, " .
            "please enter the following into the browser url bar (or click): ";
        echo '<a style="color:darkBlue;text-indent:24px;font-size:18px;" ' .
                'href="enterHike.php?hikeNo=' . $lastindx .
                '" target="_blank">' . 'localhost/build/enterHike.php?hikeNo=' . 
                $lastindx . "</a>";
        ?>
        </div>
        <p id="trail"><?php echo $newHike;?></p>
    </body>
</html>