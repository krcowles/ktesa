<?php
/**
 * Administration tools for the site masters are included here. These
 * comprise buttons to carry out certain admin tasks, and are grouped
 * and ordered based on current usage.
 * PHP Version 7.4
 * 
 * @package Ktesa
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
$server_loc = strlen($thisSiteRoot) > strlen($documentRoot) ?
    'test' : 'main';
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
        var hostIs = "<?=$_SERVER['SERVER_NAME'];?>";
        var server_loc = "<?=$server_loc;?>";
        var auth;
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
        <span id="sitemode">The site is currently in
            <span id="currstate"><?= $appMode;?></span> mode:</span>&nbsp;&nbsp;
            <button id="switchstate" class="adminbuttons">Switch Site Mode
            </button><br />

        <span id="cdb">
            <?php if ($dbState === 'test') : ?>
                <span id="test" style="color:red;">Test DB</span>
            <?php else : ?>
                <span id="real" style="color:blue;">Main DB</span>
            <?php endif; ?>
            &nbsp;is currently in use:&nbsp;&nbsp;
            <button id="swdb" class="adminbuttons">Switch DB's</button>
        </span><br />

        <!-- The following uploads or installs a test site -->
        <span class="cats">Upload Test Site:</span><br />
        <button id="upld" class="adminbuttons">Upload</button>&nbsp;&nbsp;
            [From Localhost]<br />
        <div id="testsite">
            <span>If not current master, specify git branch here:</span>
            <input id="ubranch" class="installdat" type="text" 
                placeholder="master" />&nbsp;&nbsp;
            Commit# for upload: <input id="ucomm" class="installdat" type="text" />
        </div>

        <span class="cats">Install Test Site to Main</span><br />
        <button id="install" class="adminbuttons">Install main</button>&nbsp;&nbsp;
            [From server]<br />
        <div style="margin-left:24px;">
            <span>Delete the following <strong>test site</strong>
            directories (comma-separated list)<br />[NOTE:]
            the <em>install</em> directory is always saved</span>
            <br /><textarea id="sites" cols="80"></textarea>
            <br /><span>Install from:</span>
            <input id="copyloc" class="installdat" type="text"
                placeholder="Test Site" /><br />
        </div><br />

        <span class="cats">Downloads:</span><br />
        <button id="chgs" class="adminbuttons">Changes Only</button>
            &nbsp;[Downloads zip file]<br />
        <button id="npix" class="adminbuttons">New Pictures</button>
            &nbsp;[Downloads new pictures since last Site upload]<br />
        <button id="rel2pic" class="adminbuttons">Pictures newer than:
            </button>&nbsp;&nbsp;
            <span id="psel">Select a file from the 'pictures' directory</span>
                &nbsp;&nbsp;<input id="cmppic" type="file" /><br />
            <span id="dsel">OR specify calendar date&nbsp;&nbsp;
            <input style="font-size:12px;width:90px;"
                id="datepicker" type="text" name="datepicker" /></span><br />
        <span class="cats">Listings:</span><br />
        <button id="lst" class="adminbuttons">List New Files</button>
            &nbsp;&nbsp;[Since last upload]<br />
        <span id="mgmt" class="cats">Database Management Tools:</span><br />
        <button id="reload" class="adminbuttons">Reload Database</button>&nbsp;
            [Drops All Tables and Loads All Tables]<br />
        <button id="drall" class="adminbuttons">Drop All Tables</button><br />
        <button id="ldall" class="adminbuttons">Load All Tables</button>
            &nbsp;&nbsp;[NOTE: Tables must not exist]<br />
        <button id="exall" class="adminbuttons">Export All Tables</button>
            &nbsp;&nbsp;[NOTE: Creates .sql file]<br />
        <button id="dbchanges" class="adminbuttons">
            Check for DB Changes</button><br />
        <button id="gensums" class="adminbuttons">Generate Checksums</button>
            &nbsp;&nbsp;[NOTE: New checksums will be placed in Checksums table]<br />
        <button id="show" class="adminbuttons">Show All Tables</button><br />
        <span class="cats">Miscellaneous Tools:</span><br />
        <?php
        if ($editing === 'yes') {
            $allow = "Editing Allowed";
        } else {
            $allow = "No Editing Mode";
        }
        ?>
        <button id="editmode" class="adminbuttons">Change Edit Mode</button>
            &nbsp;&nbsp;<span id="emode" style="color:blue;"><?=$allow;?>
            </span><br />
        <button id="commit" class="adminbuttons">Display Commit</button>
            &nbsp;&nbsp;[for this site]<br />
        <button id="cleanPix" class="adminbuttons">Cleanup Pictures</button>
            &nbsp;&nbsp;[removes photos not related to hikes]<br />
        <button id="pinfo" class="adminbuttons">Php Info</button><br />
        <button id="addbk" class="adminbuttons">Add Book</button><br />
    </fieldset><br />
    <fieldset>
        <legend>Hike Management</legend>
        <button id="pub" class="adminbuttons">Publish Page</button>
            (Move from EHIKES to HIKES)<br/>
        <button id="ehdel" class="adminbuttons">Remove Page</button>
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
            <button id="revall" class="adminbuttons">Reverse All
                    Tracks</button><br />
            <button id="revsgl" class="adminbuttons">Reverse Only Track(s)</button>
            <input type="hidden" name="revtype" value="" /> 
            (Single trk#, comma-list, or hyphen-range):&nbsp;
            <input id="revlist" type="text" name="revlist" size="20" />
        </form>
    </fieldset><br/>
    <p id="admin_alert" style="display:none;"><?=$admin_alert;?></p>
</div>
<div id="loading">
    <img src="../images/loader-64x/Preloader_3.gif" alt="image while loading" />
</div>

<script src="../scripts/menus.js" type="text/javascript"></script>
<script src="admintools.js" type="text/javascript"></script>
</body>
</html>
