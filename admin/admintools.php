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
if (isset($_SESSION['usr_alert'])) {
    $admin_alert = $_SESSION['usr_alert'];
    unset($_SESSION['usr_alert']);
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
        <button id="commit">Display Commit</button>&nbsp;&nbsp;[for this site]<br />
        <button id="cleanPix">Cleanup Pictures</button>
            &nbsp;&nbsp;[removes photos not related to hikes]<br />
        <button id="pinfo">Php Info</button><br />
        <button id="addbk">Add Book</button><br />
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
        <form action="reverseGpx.php" method="POST" enctype="multipart/form-data"
            onsubmit="return checkSize(20*1024*1024)">
            <input type="file" id="gpx2edit" name="gpx2edit" /><br />
            <input class="ged" type="submit" name="gpxall"
                value="Reverse All Tracks" /><br />
            <input class="ged" type="submit" name="gpxlst"
                value="Reverse Track No(s):" />
            (Single trk#, comma-list, or hyphen-range):&nbsp;
            <input type="text" id="revlst" name="revlst" size="20" />
        </form>
    </fieldset><br/>
    <p id="admin_alert" style="display:none;"><?=$admin_alert;?></p>
</div>
<script type="text/javascript">
function checkSize(max_size)
{
    var input = document.getElementById("gpx2edit");
    // check for browser support (may need to be modified)
    if(input.files && input.files.length == 1)
    {           
        if (input.files[0].size > max_size) 
        {
            alert("Error: The file is too large.\n" +
                "The size must be less than " + (max_size/1024/1024) + " MB.");
            return false;
        }
    }

    return true;
}
</script>
<script src="../scripts/menus.js" type="text/javascript"></script>
<script src="admintools.js" type="text/javascript"></script>
</body>
</html>
