<?php
/**
 * This script will process the admin tools request to either delete a
 * page from EHIKES, or to publish it to the main site. The outcome is
 * determined by the query string parameter 'act'
 * PHP Version 7.0
 * 
 * @package Admin_Tools
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$act = filter_input(INPUT_GET, 'act');
if ($act === 'rel') {
    $msg = "Publish";
} elseif ($act === 'del') {
    $msg = "Remove";
}
$usr = 'mstr';
$age = 'new';
$show = 'all';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?php echo $msg;?> Hike from EHIKES</title>
    <meta charset="utf-8" />
    <meta name="description" content="Select hike to release from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../build/tables.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>
<?php require "../pages/pageTop.html"; ?>
<p id="trail">EHIKES Available to <?php echo $msg;?></p>
<p id="action" style="display:none"><?php echo $act;?></p>
<?php
require '../php/makeTables.php';
?>
<script src="../scripts/jquery-1.12.1.js"></script>
<script type="text/javascript">
    var enos = <?php echo $enos;?>;
</script>
<script src ="reldel.js"></script>
</body>
</html>
