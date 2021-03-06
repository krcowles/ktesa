<?php
/**
 * This script is invoked in multiple scenarios and merely sets the parameters
 * for a table of hikes to be created by 'makeTables.php'. The parameters are
 * pulled from the query string or $_SESSION user credentials.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$userid = $_SESSION['userid'];

$age = filter_input(INPUT_GET, 'age');
$show = filter_input(INPUT_GET, 'show');
$pubreq = isset($_GET['pub']) ? filter_input(INPUT_GET, 'pub') : false;
$msg= '';

$navbar = $pubreq ? 'Publish' : 'Edit';
if ($age === 'old') {
    $pageType = 'EditPub';
    $show = 'all';
    $msg = 'an editable version of the hike';
} else {
    $show = 'usr';
    if ($pubreq) {
        $pageType = 'PubReq';
    } else {
        $pageType = 'Edit';
        $msg = 'the current state of your in-edit hike page';
    }
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
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="tables.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>

<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Select A Hike To <?=$navbar;?></p>

<p id="page_id" style="display:none"><?=$pageType;?></p>

<div>
    <?php if ($pubreq) : ?>
        <p style="text-align:center;font-size:18px;">Click on the hike you
            wish to publish; an email notificartion will be sent to the
            admin.<br />You will be advised when the hike has been published.</p>
    <?php else : ?>
        <p style="text-align:center;font-size:18px;">When you click on a hike
            in the table below, you will be presented with <?=$msg;?>.</p>
        <?php if ($pageType === 'EditPub') : ?>
            <p style="text-align:center;"><span style="color:brown;"><strong>NOTE:
                </strong></span>
            Rows that are grayed out are already in-edit and cannot be edited
            further until released</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div><br />

<?php require "../php/makeTables.php"; ?>

</div>

<script type="text/javascript">
    var age = "<?=$age;?>";
    var inEdits = <?=$jsInEdit;?>;
</script>
<script src="../scripts/menus.js"></script>
<script src="hikeEditor.js"></script>

</body>
</html>