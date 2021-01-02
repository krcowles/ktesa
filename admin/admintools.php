<?php
/**
 * Administration tools for the site masters are included here. These
 * comprise buttons to carry out certain admin tasks, and are grouped
 * and ordered based on current usage.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$admin_alert = '';
if (isset($_SESSION['user_alert'])) {
    $admin_alert = $_SESSION['user_alert'];
    unset($_SESSION['user_alert']);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="../styles/jquery-ui.css">
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
    <script type="text/javascript">
        $(function() {
            $( "#datepicker" ).datepicker({
                dateFormat: "yy-mm-dd"
            });
        });
        var hostIs = "<?= $_SERVER['SERVER_NAME'];?>";
    </script>
</head>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Site Administration Tools</p>
<p id="page_id" style="display:none">Admin</p>

<?php if (isset($_SESSION['nopix']) && !empty($_SESSION['nopix'])) : ?>
    <script type="text/javascript">var nopix = "<?= $_SESSION['nopix'];?>";</script>
    <?php $_SESSION['nopix'] = ''; ?>
<?php endif; ?>

<div style="margin-left:24px;" id="tools">
    <fieldset>
        <legend>Overall Site Management</legend>
        <button id="switchstate">Switch Site Mode</button>&nbsp;&nbsp;
        <span id="sitemode">The site is currently in
            <span id="currstate"><?= $appMode;?></span> mode:</span><br />
        <!-- CURRENTLY NOT USED
        <form action="upldSite.php" method="POST" target="_blank"
            enctype="multipart/form-data">
            Upload:<br />
            <button id="upld">Upload</button>&nbsp;&nbsp;
            <input id="ufile" type="file" name="ufile" />
                &nbsp;[Uploads Zip File and Extracts to 'upload' directory]<br />
        </form>
        -->
        <span class="cats">Downloads:</span><br />
        <button id="chgs">Changes Only</button>
            &nbsp;[Downloads zip file]<br />
        <!-- CURRENTLY NOT USED
        <button id="site">Entire Site</button>
            &nbsp;[Downloads compressed archive]<br />
        -->
        <button id="npix">New Pictures</button>
            &nbsp;[Downloads new pictures since last Site upload]<br />
        <button id="rel2pic">Pictures newer than: </button>&nbsp;&nbsp;
            <span id="psel">Select a file from the 'pictures' directory</span>
                &nbsp;&nbsp;<input id="cmppic" type="file" /><br />
            <span id="dsel">OR specify calendar date&nbsp;&nbsp;
            <input style="font-size:12px;width:90px;"
                id="datepicker" type="text" name="datepicker" /></span><br />
        <span class="cats">Listings:</span><br />
        <button id="lst">List New Files</button>
            &nbsp;&nbsp;[Since last upload]<br />
        <span id="mgmt" class="cats">Database Management Tools:</span><br />
        <button id="reload">Reload Database</button>&nbsp;
            [Drops All Tables and Loads All Tables]<br />
        <button id="drall">Drop All Tables</button><br />
        <button id="ldall">Load All Tables</button>
            [NOTE: Tables must not exist]<br />
        <button id="exall">Export All Tables</button>
            [NOTE: Creates .sql file]<br />
        <button id="show">Show All Tables</button><br />
        <button id="swdb">Switch DB's</button>&nbsp;&nbsp;
            <span id="cdb">Current database in use:
        <?php if ($dbState === 'test') : ?>
            <span id="test" style="color:red;">Test</span>
        <?php else : ?>
            <span id="real" style="color:blue;">Site</span>
        <?php endif; ?>
            </span><br />
        <span class="cats">Miscellaneous Tools:</span><br />
        <?php
        if ($editing === 'yes') {
            $allow = "Editing Allowed";
        } else {
            $allow = "No Editing Mode";
        }
        ?>
        <button id="editmode" >Change Edit Mode</button>&nbsp;&nbsp;
            <span id="emode" style="color:blue;"><?=$allow;?></span><br />
        <button id="commit">Display Commit</button>&nbsp;&nbsp;[for this site]<br />
        <button id="cleanPix">Cleanup Pictures</button>
            &nbsp;&nbsp;[removes photos not related to hikes]<br />
        <button id="pinfo">Php Info</button><br />
        <button id="addbk">Add Book</button><br />
    </fieldset><br />
    <fieldset>
        <legend>Hike Management</legend>
        <button id="pub">Publish Page</button> (Move from EHIKES to HIKES)<br/>
        <button id="ehdel">Remove Page</button>
            <span style="color:brown;">(Not implemented at this time)</span><br />
    </fieldset><br />
    <fieldset>
        <legend>GPX File Edits</legend>
        <form id="revgpx" action="reverseGpx.php" method="POST"
            enctype="multipart/form-data" />
            <span id="revresult">NOTE: This will download a file
                        called "reversed.gpx"</span>
            <div id="filebrowse">
                <label for="gpx2edit">Upload GPX File:</label>
                <input type="file" name="gpx2edit" />
            </div>
            <button id="revall">Reverse All Tracks</button><br />
            <button id="revsgl">Reverse Only Track(s)</button>
            <input type="hidden" name="revtype" value="" /> 
            (Single trk#, comma-list, or hyphen-range):&nbsp;
            <input id="revlist" type="text" name="revlist" size="20" />
        </form>
    </fieldset><br/>
    <p id="admin_alert" style="display:none;"><?=$admin_alert;?></p>
</div>
<script src="../scripts/menus.js" type="text/javascript"></script>
<script src="admintools.js" type="text/javascript"></script>
</body>
</html>
