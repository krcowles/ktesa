<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Site Administration Tools</p>

<div style="margin-left:24px;" id="tools">
    <fieldset>
        <legend>Create/Delete</legend>
        Table to Delete:&nbsp;&nbsp;
        <select id="dtbl" name="dropper">
            <option>EHIKES</option>
            <option>USERS</option>
            <option>HIKES</option>
        </select>&nbsp;&nbsp;
        <button id="drop">Drop Table</button><br />
        Table to Create:&nbsp;&nbsp;
        <select id="ctbl" name="creator">
            <option>EHIKES</option>
            <option>USERS</option>
            <option>HIKES</option>
        </select>&nbsp;&nbsp;
        <button id="create">Create Table</button><br />
        <button id="ia">Insert Admins into USERS</button>
        &nbsp;(USERS Table must exist)<br />
        <button id="ldh">Load HIKES from XML</button>
        &nbsp;(HIKES Table must exist)<br />
    </fieldset><br />
    <fieldset>
        <legend>Row Manipulation</legend>
        <select id="rdel" name="creator">
            <option>EHIKES</option>
            <option>USERS</option>
            <option>HIKES</option>
        </select>&nbsp;&nbsp;
        Row No.&nbsp;&nbsp;<input id="drow" type="text" 
            name="indx" size="4" />&nbsp;&nbsp;
        <button id="rowdel">Delete Row</button>
    </fieldset><br />
    <fieldset>
        <legend>Page Release</legend>
        <button id="hrel">Release Page to HIKES</button>
    </fieldset>
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="admintools.js"></script>
</body>
</html>