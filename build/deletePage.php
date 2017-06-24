<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Delete A Hike or Index Pg</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit a given hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="tables.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Delete A Hike</p>
   
<div style="padding:16px;text-align:center;">
    <p>To delete a page (<em>either</em> index page or hike page),
        <strong>carefully</strong> select the page and click on the "Web Pg" checkmark
        for that page.
    </p>
    <p style="font-size:20px;color:red;">
        NOTE: Be careful with hikes that are at a Visitor Center, or with Index 
        Pages, as there is an inter-relationship that can be destroyed! Consult 
        with site master! Index numbers will be reordered.
    </p>
</div><br />
<?php 
    require "../php/TblConstructor.php";
?>

<div style="margin-left:8px;">
    <form action="removeEntry.php" method="GET">
    <h3>Select an option below to delete the page</h3>
    <p><em>Site Master:</em> Enter Password to Delete Hike&nbsp;&nbsp;
        <input id="master" type="password" name="mpass" size="12" maxlength="10" 
            title="8-character code required" />&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="submit" name="savePg" value="Site Master" />
    </p>
    <p><em>Registered Users:</em> Select button to submit to Site Master&nbsp;&nbsp;
        <input type="submit" name="savePg" value="Submit for Review" />
    </p>
    <input id="passhike" type="hidden" name="hikeno" value="-1" />
    </form>
</div>	

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="deletePage.js"></script>

</body>
</html>