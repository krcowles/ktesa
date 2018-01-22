<?php
session_start();
?>
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
        <legend>Session Settings</legend>
        <button id="mode">Show/Set SQL Modes</button>
        <div id="modeopt">
        <?php
        echo '<form action="modify_modes.php" method="POST">';
        if (isset($_SESSION['sqlmode']) && $_SESSION['sqlmode'] === 'active') {
            echo '<p id="dstat" style="display:none">Open</p>';
            $_SESSION['sqlmode'] = 'inactive';
        } else {
            echo '<p id="dstat" style="display:none">Closed</p>';
        }
        $modes = file('sql_modes.ini', FILE_IGNORE_NEW_LINES);
        $cbStates = '[';
        for ($i=0; $i<count($modes); $i++) {
            $opt = $modes[$i];
            $val = substr($opt, 2, strlen($opt)-2);
            echo '<input class="cb" type="checkbox" name="ons[]" ' .
                    'value="' . $val .  '" />';
            echo '&nbsp;&nbsp;' . $val . '<br />' . PHP_EOL;
            if (substr($opt, 0, 1) === 'Y') {
                $cbStates .= '"Y",';
            } else {
                $cbStates .= '"N",';
            }
        }
        $cbStates = substr($cbStates, 0, strlen($cbStates)-1);
        $cbStates .= ']';
        echo '<br /><input type="submit" value="Apply" />';
        echo '</form>';
        ?>
        </div>
    </fieldset>
    <script type="text/javascript">
        var cbs = <?php echo $cbStates;?>;
    </script>
    <fieldset>
        <legend>Create/Delete</legend>
        <button id="show">Show All Tables</button><br />
        <button id="drall">Drop All Tables</button><br />
        <button id="ldall">Load All Tables</button>
        (NOTE: Tables should be dropped and not created)<br />
        <button id="exall">Export All Tables</button>
        (NOTE: Will download a file which should be renamed appropriately)<br />
        <button id="dret">Drop All E-Tables</button><br />
        <button id="ldet">Load All E-Tables</button>
        <span style="color:brown;">(Not implemented at this time)</span>
        <p></p>
        <span class="ind">Table to Delete:</span>
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
        </select>
        <button id="drop">Drop Table</button><br />
        <span class="ind">Table to Create:</span>
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
        </select>
        <button id="create">Create Table</button><br />
        <span class="ind">Table to Load:</span>
        <select id="ltbl" name="sgl_ld">
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
        </select>
        <button id="sgls">Load Table</button>
        <span id="ni">(Not implented at this time)</span><br />
    </fieldset><br />
    <fieldset>
        <legend>Hike Release/Delete</legend>
        Actions available for all EHIKES (Affects all E-Tables):<br />
        &nbsp;&nbsp;&nbsp;<button id="pub">Publish Hike</button><br/>
        &nbsp;&nbsp;&nbsp;<button id="ehdel">Remove Hike</button>
            <span style="color:brown;">(Not implemented at this time)</span><br />
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
        <button id="rowdel">Delete Row</button><br /><br />
        NOTE: Deleting a row in a table may cause issues if companion tables 
        are not also updated.
    </fieldset><br />
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="admintools.js"></script>
</body>
</html>
