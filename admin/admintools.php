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
date_default_timezone_set('America/Denver');

/**
 * Visitor data settings
 */
$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July',
    'August', 'September', 'October', 'November', 'December');
$opts = '';
for ($i=0; $i<12; $i++) {
    $moval = $i < 9 ? "0" . ($i+1) : strval($i+1);
    $opts .= "<option value='{$moval}'>{$months[$i]}</option>" . PHP_EOL;
}

// Archival of Visitor Data:
$rangeRequest = "SELECT MIN(vdatetime) AS MinDate, MAX(vdatetime) AS MaxDate " .
    "FROM `VISITORS`;";
$getRange = $pdo->query($rangeRequest)->fetch(PDO::FETCH_ASSOC);
$mindate = $getRange['MinDate'];
$maxdate = $getRange['MaxDate'];
$minyr = intval(substr($mindate, 0, 4));
$maxyr = intval(substr($maxdate, 0, 4));
$archyears = [];
for ($k=$minyr; $k<=$maxyr; $k++) {
    array_push($archyears, $k);
}
$archopts = '';
foreach ($archyears as $yr) {
    $archopts .= "<option value='{$yr}'>{$yr}</option>" . PHP_EOL;
}

// if any alerts were encountered via admin page accesses:
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/admintools.css" type="text/css" rel="stylesheet" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
    <script type="text/javascript">
        $(function() {
            $( ".datepicker" ).datepicker({
                dateFormat: "yy-mm-dd"
            });
        });
        var hostIs = "<?=$_SERVER['SERVER_NAME'];?>";
        var server_loc = "<?=$server_loc;?>";
        var auth;
    </script>
</head>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js">
</script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Site Administration Tools</p>
<p id="active" style="display:none">Admin</p>

<?php if (isset($_SESSION['nopix']) && !empty($_SESSION['nopix'])) : ?>
    <script type="text/javascript">var nopix = "<?= $_SESSION['nopix'];?>";</script>
    <?php $_SESSION['nopix'] = ''; ?>
<?php endif; ?>

<div style="margin-left:24px;" id="tools">
    <fieldset class="afs">
        <legend class="afs">Overall Site Management</legend>
        <button id="switchstate" type="button" class="btn 
                btn-danger">Switch Site Mode</button>&nbsp;&nbsp;
        <span id="sitemode">The site is currently in
            <span id="currstate"><?= $appMode;?></span> mode:</span>
        <br />
        <span id="cdb">
            <button id="swdb" type="button" class="btn
            btn-danger">Switch DB's</button></span>&nbsp;&nbsp;
            <?php if ($dbState === 'test') : ?>
                <span id="test" style="color:red;">Test DB</span>
            <?php else : ?>
                <span id="real" style="color:blue;">Main DB</span>
            <?php endif; ?>
            &nbsp;is currently in use:
        <br />

        <!-- The following uploads or installs a test site -->
        <span class="cats">Upload Test Site:</span><br />
        <button id="upld" type="button" class="btn btn-secondary">Upload</button>
        &nbsp;&nbsp;[From Localhost]<br />
        <div id="testsite">
            <span>If not current master, specify git branch here:</span>
            <input id="ubranch" class="installdat" type="text" 
                placeholder="master" />&nbsp;&nbsp;
            Commit# for upload: <input id="ucomm" class="installdat" type="text" />
        </div>

        <span class="cats">Install Test Site to Main</span><br />
        <button id="install" type="button" class="btn
            btn-danger">Install main</button>&nbsp;&nbsp;[From server]<br />
        <div style="margin-left:24px;">
            <span>Delete the following <strong>test site</strong>
            directories (comma-separated list)<br />[NOTE:]
            the <em>install</em> directory is always saved</span>
            <br /><textarea id="sites" cols="80"></textarea>
            <br /><span>Install from:</span>
            <input id="copyloc" class="installdat" type="text"
                placeholder="Test Site" /><br />
        </div><br />

        <span class="cats">Test Hike Page Links</span><br />
        <button id="lnk_test" type="button" class="btn btn-secondary">
            Test Links Exist</button>&nbsp;&nbsp;NOTE: This routine can
            take awhile!<br /><br />

        <span class="cats">Downloads:</span><br />
        <button id="chgs" type="button" class="btn 
            btn-secondary">Changes Only</button>&nbsp;[Downloads zip file /
            NOTE: buildPhar & zipArchive not currently working]<br />
        <button id="npix" type="button" class="btn 
            btn-secondary">New Pictures</button>
            &nbsp;[Downloads new pictures since last Site upload: Max 20MB]<br />
        <button id="rel2pic" type="button" class="btn 
            btn-secondary">Pictures newer than:</button>&nbsp;&nbsp;
            <span id="psel">Select a file from the 'pictures' directory</span>
                &nbsp;&nbsp;<input id="cmppic" type="file" /><br />
            <span id="dsel">OR specify calendar date&nbsp;&nbsp;
            <input id="pic_sel"
                class="datepicker" type="text" name="datepicker" /></span><br />
        <span class="cats">Listings:</span><br />
        <button id="lst" type="button" class="btn btn-secondary">List New Files
        </button>&nbsp;&nbsp;<span id="lister">[Specify Test Sites to Skip]</span>
            &nbsp;&nbsp;<textarea id="skipsites"
                placeholder="Comma separated list"></textarea><br />

        <span id="mgmt" class="cats">Database Management Tools:</span><br />
        <button id="reload" type="button" class="btn 
            btn-danger">Reload Database</button>&nbsp;
            [Drops All Tables and Loads All Tables]<br />
        <button id="drall" type="button" class="btn 
            btn-danger">Drop All Tables</button><br />
        <button id="ldall" type="button" class="btn 
            btn-danger">Load All Tables</button>&nbsp;&nbsp;
            [NOTE: Tables must not exist]<br />
        <button id="exall" type="button" class="btn 
            btn-secondary">Export All Tables</button>&nbsp;&nbsp;
            [NOTE: Creates .sql file]<br />
        <button id="dbchanges" type="button" class="btn 
            btn-secondary">Check for DB Changes</button><br />

        <button id="gensums" type="button" class="btn 
            btn-secondary">Generate Checksums</button>&nbsp;&nbsp;
            [NOTE: New checksums will be placed in Checksums table]<br />
        <button id="show" type="button" class="btn 
            btn-secondary">Show All Tables</button><br />

        <span class="cats">Miscellaneous Tools:</span><br />
        <?php
        if ($editing === 'yes') {
            $allow = "Editing Allowed";
        } else {
            $allow = "No Editing Mode";
        }
        ?>
        <button id="editmode" type="button" class="btn 
        btn-secondary">Change Edit Mode</button>&nbsp;&nbsp;
        <span id="emode" style="color:blue;"><?=$allow;?></span><br />
        <button id="commit" type="button" class="btn 
            btn-secondary">Display Commit</button>&nbsp;&nbsp;[for this site]<br />
        <button id="cleanPix" type="button" class="btn
            btn-secondary">Cleanup Pictures</button>&nbsp;&nbsp;
            [removes photos not related to hikes]<br />
        <button id="gpxClean" type="button" class="btn
            btn-secondary">Cleanup GPX/JSON Files</button>&nbsp;&nbsp;
            [removes gpx/json not specified in database]<br />
        <button id="pinfo" type="button" class="btn 
            btn-secondary">Php Info</button><br />
        <button id="addbk" type="button" class="btn 
            btn-secondary">Add Book</button><br />
    </fieldset><br />

    <fieldset class="afs">
        <legend class="afs">Hike Management</legend>
        <button id="pub" type="button" class="btn 
            btn-danger">Publish Page</button>&nbsp;
            (Move from EHIKES to HIKES)<br/>
        <button id="ehdel" type="button" class="btn 
            btn-secondary">Remove Page</button>
            <span style="color:brown;">(Not implemented at this time)</span><br />
    </fieldset><br />

    <fieldset class="afs">
        <legend class="afs">Visitor Data</legend>
        <div id="vdata">
            <div class="vflex"> 
                <button id="today" type="button" class="btn
                    btn-secondary">Today's Data</button>
            </div>
            <div class="vflex">
                <button id="wk" type="button" class="btn
                    btn-secondary">Last Week</button>
            </div>
            <div class="vflex nbtn">
                Select Month:&nbsp;&nbsp;
                <select id="vmonth">
                    <?=$opts;?>
                </select>&nbsp;&nbsp;
                <button id="dmo" type="button" class="btn
                    btn-secondary">Display</button>
            </div>
            <div class="vflex">
                Range:&nbsp;&nbsp;
                <span id="rg">
                    Start&nbsp;&nbsp;<input id="begin" class="datepicker range"
                        type="text" placeholder="Click to select" />&nbsp;&nbsp;
                    End&nbsp;&nbsp;<input id="end" class="datepicker range"
                        type="text" placeholder="Click to select" />
                </span>&nbsp;&nbsp;
                <button id="range" type="button"
                    class="btn btn-secondary">Display</button>
            </div>
        </div><br />
        <div>
            <button id="arch" type="button" class="btn
                btn-warning">Archive Data</button>&nbsp;&nbsp;
            Archive Year:&nbsp;&nbsp;
            <select id="archyr">
                <?=$archopts;?>
            </select>&nbsp;&nbsp;<span class="vdatnote">
                NOTE: Data will be removed from database</span>
        </div>
    </fieldset><br />

    <fieldset class="afs">
        <legend class="afs">GPX File Edits</legend>
        <form id="revgpx" action="reverseGpx.php" method="POST"
            enctype="multipart/form-data" />
            <span id="revresult">NOTE: This will download a file
                        called "reversed.gpx"</span>
            <div id="filebrowse">
                <label for="gpx2edit">Upload GPX File:</label>
                <input type="file" name="gpx2edit" />
            </div>
            <button id="revall" type="button" class="btn 
                btn-secondary">Reverse All Tracks</button><br />
            <button id="revsgl" type="button" class="btn 
                btn-secondary">Reverse Only Specified:</button>
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

<!-- modal for displaying results of checksum differences -->
<div id="chksum_results" class="modal" tabindex="-1">
    <div class="modal-dialog" style="max-width:60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checksum Results</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="last_load"></div>
                <div id="next_load"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="admintools.js"></script>
</body>
</html>
