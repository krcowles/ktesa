<?php
/**
 * Administration tools for the site masters are included here. These
 * comprise buttons to carry out certain admin tasks, and are grouped
 * and ordered based on current usage.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
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
    <link rel="stylesheet"
        href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.min.js"></script>
    <script type="text/javascript">
        $(function() {
            //$( "#datepicker" ).datepicker();
            $( "#datepicker" ).datepicker({
                dateFormat: "yy-mm-dd"
            });
        });
    </script>
</head>
<body>
<div id="logo"><img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Site Administration Tools</p>

<?php if (isset($_SESSION['nopix']) && !empty($_SESSION['nopix'])) : ?>
    <script type="text/javascript">var nopix = "<?= $_SESSION['nopix'];?>";</script>
    <?php $_SESSION['nopix'] = ''; ?>
<?php endif; ?>

<div style="margin-left:24px;" id="tools">
    <fieldset>
        <legend>Overall Site Management</legend>
        <p id="sitemode">The site is currently in
            <span id="currstate"><?= $appMode;?></span> mode:&nbsp;&nbsp;
        <button id="switchstate">Switch Site Mode</button></p>
        <form action="upldSite.php" method="POST" target="_blank"
            enctype="multipart/form-data">
            Upload:<br />
            <button id="upld">Upload</button>&nbsp;&nbsp;
            <input id="ufile" type="file" name="ufile" />
                &nbsp;[Uploads Zip File and Extracts to 'upload' directory]<br />
        </form>
        Downloads:<br />
        <button id="chgs">Changes Only</button>
            &nbsp;[Downloads zip file]<br />
        <button id="site">Entire Site</button>
            &nbsp;[Downloads compressed archive]<br />
        <button id="npix">New Pictures</button>
            &nbsp;[Downloads new pictures since last Site upload]<br />
        <button id="rel2pic">Pictures newer than: </button>&nbsp;&nbsp;
            <span id="psel">Select a file from the 'pictures' directory</span>
                &nbsp;&nbsp;<input id="cmppic" type="file" /><br />
            <span id="dsel">OR specify calendar date&nbsp;&nbsp;
            <input style="font-size:12px;width:90px;"
                id="datepicker" type="text" name="datepicker" /></span><br />
        <span style="font-size:20px;color:brown;">Listings:</span><br />
        <button id="lst">List New Files</button>&nbsp;&nbsp;[Since last upload]
        <hr />
        <p>Database Management Tools:</p>
        <button id="reload">Reload Database</button>&nbsp;
            [Drops All Tables and Loads All Tables]<br />
        <button id="drall">Drop All Tables</button><br />
        <button id="ldall">Load All Tables</button>
            [NOTE: Tables must not exist]<br />
        <button id="exall">Export All Tables</button>
            [NOTE: Creates .sql file]<br />
        <button id="show">Show All Tables</button><br />
        <button id="swdb">Switch DB's</button>&nbsp;&nbsp;
            Current database in use:
        <?php if ($dbState === 'test') : ?>
            <span id="test" style="color:red;">Test</span>
        <?php else : ?>
            <span id="real" style="color:blue;">Site</span>
        <?php endif; ?>
        <hr />
        <p>Miscellaneous Tools:</p>
        <?php
        if ($editing === 'yes') {
            $allow = "Editing Allowed";
        } else {
            $allow = "No Editing Mode";
        }
        ?>
        <button id="emode"><?= $allow;?></button> [Click to change modes]<br />
        <button id="cleanPix">Cleanup Pictures</button>
            &nbsp;&nbsp;[removes photos not related to hikes]<br />
        <button id="pinfo">Php Info</button><br />
        <button id="mode">Show/Set SQL Modes</button>
        <!-- div w/form related to Show/Set SQL Modes -->
        <div id="modeopt">
        <form action="modify_modes.php" method="POST">
<?php if (isset($_SESSION['sqlmode']) && $_SESSION['sqlmode'] === 'active') : ?>
        <p id="dstat" style="display:none">Open</p>
        <?php
            $_SESSION['sqlmode'] = 'inactive';
        ?>
<?php else : ?>
        <p id="dstat" style="display:none">Closed</p>
<?php endif; ?>
        <?php
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
        ?>
        <script type="text/javascript">
            var cbs = <?php echo $cbStates;?>;
        </script>
        <br /><input type="submit" value="Apply" />
        </form>
        </div>
        <!-- End of Show/Set div w/form -->
    </fieldset><br />
    <fieldset>
        <legend>Hike Management</legend>
        <button id="pub">Publish Hike</button> (Move from EHIKES to HIKES)<br/>
        <button id="ehdel">Remove Hike</button>
            <span style="color:brown;">(Not implemented at this time)</span><br />
    </fieldset><br />
    <fieldset>
        <legend>GPX File Edits</legend>
        NOTE: Will download a file called "reversed.gpx"<br />
        <form action="reverseGpx.php" method="POST" enctype="multipart/form-data" />
            <input type="file" id="gpx2edit" name="gpx2edit" /><br />
            <input class="ged" type="submit" name="gpxall"
                value="Reverse All Tracks" /><br />
            <input class="ged" type="submit" name="gpxlst"
                value="Reverse Track No(s):" />
            (Single trk#, comma-list, or hyphen-range):&nbsp;
            <input type="text" id="revlst" name="revlst" size="20" />
        </form>
    </fieldset><br/>
    <fieldset>
        <legend>Seldom Used Tools</legend>
        <button id="addbk">Add Book</button><br />
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
            <option>BOOKS</option>
            <option>REFS</option>
            <option>GPSDAT</option>
            <option>IPTBLS</option>
            <option>EHIKES</option>
            <option>ETSV</option>
            <option>EREFS</option>
            <option>EGPSDAT</option>
        </select><br />
    </fieldset><br />
</div>
<script src="admintools.js"></script>
</body>
</html>
