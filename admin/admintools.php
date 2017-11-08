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
        <button id="show">Show All Tables</button><br />
        <button id="drall">Drop All Tables</button>&nbsp;&nbsp;(Retains USERS)<br />
        <button id="dret">Drop All E-Tables</button><br />
        Table to Delete:&nbsp;&nbsp;
        <select id="dtbl" name="dropper">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select>&nbsp;&nbsp;
        <button id="drop">Drop Table</button><br />
        Table to Create:&nbsp;&nbsp;
        <select id="ctbl" name="creator">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select>&nbsp;&nbsp;
        <button id="create">Create Table</button><br />
        <button id="ia">Insert Admins into USERS</button>
        &nbsp;(USERS Table must exist)<br />
        <button id="ldh">Load HIKES from XML</button>
        &nbsp;(HIKES Table must exist)<br />
        <button id="ldt">Load TSV from XML</button><br />
        <button id="ldr">Load REFS from XML</button><br />
        <button id="ldg">Load GPSDAT from XML</button><br />
        <button id="lip">Load IPTBLS from XML</button><br />
    </fieldset><br />
    <fieldset>
        <legend>Row Manipulation</legend>
        <select id="rdel" name="creator">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select>&nbsp;&nbsp;
        Row No.&nbsp;&nbsp;<input id="drow" type="text" 
            name="indx" size="4" />&nbsp;&nbsp;
        <button id="rowdel">Delete Row</button>
    </fieldset><br />
    <fieldset>
        <legend>Hike Release/Delete</legend>
        <span style="color:brown;">PUBLISH</span> FROM new/in-edit hikes 
            (Select from list on next page):&nbsp;
            <button id="pub">Show List</button>&nbsp;<span style="color:brown;">
                (Not implemented at this time)</span><br />
        <span style="color:brown;">DELETE</span>&nbsp;&nbsp;FROM new/in-edit 
        hikes (Select from list on next page):
            <button id="ehdel">Show List</button> &nbsp;<span style="color:brown;">
                (Not implemented at this time)</span><br />
    </fieldset><br />
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="admintools.js"></script>
</body>
</html>