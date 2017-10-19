<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #eaeaea; }
        legend { color: darkgreen;
                 font-style: italic; }
    </style>
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
        <legend>DROP TABLES</legend>
        <button id="du">Drop the USERS Table</button><br />
        <button id="de">Drop the EHIKES Table</button><br />
        <button id="dh">Drop the HIKES Table</button><br />
    </fieldset><br />
    <fieldset>
        <legend>CREATE and LOAD TABLES</legend>
        <button id="cru">Create the USERS Table</button><br />
        <button id="ia">Insert Admins into USERS</button><br />
        <button id="cre">Create the EHIKES Table</button><br />
        <button id="crh">Create the HIKES Table</button><br />
        <button id="ldh">Load HIKES from XML</button><br />
    </fieldset><br />
    <fieldset>
        <legend>Row deletion</legend>
        <button id="drh">HIKES Table: DeleteRow</button>
        &nbsp;&nbsp;Row No.&nbsp;&nbsp;<input id="drow" type="text" 
            name="indx" size="6" /><br />
        <button id="dre">EHIKES Table: Delete Row:</button>
        &nbsp;&nbsp;Row No.&nbsp;&nbsp;<input id="derow" type="text" 
            name="eindx" size="6" /><br />
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