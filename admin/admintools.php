<?php
/**
 * Administration tools for the site masters are included here. These
 * comprise buttons to carry out certain admin tasks, and are grouped
 * and ordered based on current usage.
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    .,/docs/
 */
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
        <legend>Rebase DB</legend>
        <button id="reload">Reload Database</button>&nbsp;(Drops All Tables and Loads All)<br />
        <button id="drall">Drop All Tables</button><br />
        <button id="ldall">Load All Tables</button>
        (NOTE: Tables must not exist)<br />
        <button id="exall">Export All Tables</button>
        (NOTE: Will download a file)<br />
    </fieldset><br />
    <fieldset>
        <legend>Hike Management</legend>
        <button id="pub">Publish Hike</button> (Move from EHIKES to HIKES)<br/>
        <button id="lst">List New Files</button><br />
        <button id="ehdel">Remove Hike</button>
            <span style="color:brown;">(Not implemented at this time)</span><br />
    </fieldset><br />
    <fieldset>
        <legend>GPX File Edits</legend>
        <button id="gpxed">Reverse entire track file</button><br />
        <button id="sgltrk">Reverse Track No.</button>&nbsp;
        <select>
            <option value="1">Trk 1</option>
            <option value="2">Trk 2</option>
            <option value="3">Trk 3</option>
            <option value="4">Trk 4</option>
            <option value="5">Trk 5</option>
        </select>
    </fieldset><br/>
    <fieldset>
        <legend>Misc Tools</legend>
        <button id="show">Show All Tables</button><br />
        <button id="drop">Drop Table</button>&nbsp;
        <select id="dtbl" name="dropper">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>BOOKS</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select><br />
        <button id="create">Create Table</button>
        <select id="ctbl" name="creator">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>BOOKS</options>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select><br />
        <button id="sgls">Load Table</button>&nbsp;
        <select id="ltbl" name="sgl_ld">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>BOOKS</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select>
        <span id="ni">&nbsp;(Not implented at this time)</span><br /><br />
        <button id="mode">Show/Set SQL Modes</button><br />
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
        <script type="text/javascript">
            var cbs = <?php echo $cbStates;?>;
        </script>
        <br /><button id="addbk">Add Book</button><br /><br />
        <select id="rdel" name="creator">
            <option>USERS</option>
            <option>HIKES</option>
            <option>TSV</option>
            <option>BOOKS</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select>&nbsp;&nbsp;
        Row No.&nbsp;&nbsp;<input id="drow" type="text" 
            name="indx" size="4" />
        <button id="rowdel">Delete Row</button><br />
        NOTE: Deleting a row in a table may cause issues if linked tables 
        are not also updated.
    </fieldset><br />
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="admintools.js"></script>
</body>
</html>
