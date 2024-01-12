<?php
/**
 * This script is invoked in multiple scenarios and merely sets the parameters
 * for a table of hikes to be created by 'makeTables.php'. The parameters are
 * pulled from the query string or $_SESSION user credentials.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
if (!isset($_SESSION['userid'])) {
    echo "Your session has expired, or you are not a registered user...";
    $ip = getIpAddress();
    throw new Exception("No userid id - session expired or illegal access: " . $ip);
}
$userid = $_SESSION['userid'];

$age = filter_input(INPUT_GET, 'age');
$show = filter_input(INPUT_GET, 'show');
$pubreq = isset($_GET['pub']) ? filter_input(INPUT_GET, 'pub') : false;
$msg= '';
$include_previews = 'false';

$navbar = $pubreq ? 'Publish' : 'Edit';
if ($age === 'old') {
    $pageType = 'EditPub';
    $show = 'all';
    $msg = 'an editable version of the hike';
} else {
    // age is 'new'
    if ($pubreq) {
        $pageType = 'PubReq';
        $show = 'usr';
        // Note: admin won't need to use this option
    } else {
        $pageType = 'Edit';
        $msg = 'the current state of your in-edit hike page';
        $include_previews = 'true';
    }
}
// get a complete list of hikes for use in the autocomplete widget
if ($pageType === 'EditPub') {
    include "../pages/autoComplHikes.php";
}
// prepare list of existing hikes in edit mode
$editPgReq = "SELECT `pgTitle` FROM `EHIKES`;";
$nowInEdit = $pdo->query($editPgReq)->fetchAll(PDO::FETCH_COLUMN);
$jsInEdit = json_encode($nowInEdit);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>List In-Edit Hikes</title>
    <meta charset="utf-8" />
    <meta name="description"
            content="Select hike to edit from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/hikeEditor.css" rel="stylesheet" />
    <link href="../styles/tables.css" type="text/css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Select A Hike To <?=$navbar;?></p>
<p id="active" style="display:none"><?=$pageType;?></p>
<p id="appMode" style="display:none"><?=$appMode;?></p>

<div>
    <?php if ($pubreq) : ?>
        <p id="pubrequest"><?=$pubreq;?></p>
        <p id="hdr_notes">Click on the hike you wish to publish; an email
            notification will be sent to the admin.<br />
            You will be advised when the hike has been published.
        </p>
    <?php else : ?>
        <p id="clicking">When you click on a hike in the table below,
            you will be presented with <?=$msg;?>.
        </p>
        <?php if ($pageType === 'EditPub') : ?>
            <p id="editpub_note"><span style="color:brown;">
                <strong>NOTE:</strong></span> Rows that are grayed out
                are already in-edit and cannot be edited further until released
            </p>
            <div id="searcher" class="ui-widget">
                <style type="text/css">
                    ul.ui-widget {
                        width: 300px;
                        clear: both;
                    }
                </style>
                Scroll to:&nbsp;&nbsp;
                <input id="search" class="search" placeholder="Hike Name" />
                <span id="clear">X</span>
            </div>
            <script type="text/javascript">
                var hikeSources = <?=$jsItems;?>;
            </script>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div>

<div id="prev_btns" style="position:absolute"></div>

<?php require "../php/makeTables.php"; ?>
</div>
<div id="ineditModal">
    This hike is already in edit
</div>

<script type="text/javascript">
    var age = "<?=$age;?>";
    var inEdits = <?=$jsInEdit;?>;
    var include_previews = <?=$include_previews;?>;
    var include_search = "<?=$pageType;?>";
</script>
<script src="hikeEditor.js"></script>
<script src="../scripts/columnSort.js"></script>

</body>
</html>