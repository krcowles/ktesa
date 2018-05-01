<?php
/**
 * This script is a deterrent for users to gain access to the admin page.
 * This is the script invoked from index.html, and unless the password 
 * entered on the main page matches an entry for either of the current 
 * site masters, the script will go no further. Hence, the actual url for
 * the admin page remains unseen.
 * PHP Version 7.0
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once 'adminFunctions.php';
$master_pass = filter_input(INPUT_POST, 'madmin');
$status = masterVerify($master_pass);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Master Verification</title>
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
<p id="trail">Master Verification</p>
<?php if ($status[0]) : ?>
<form id="mval" action="<?= $status[1];?>" method="POST">
    <input type="hidden" name="mchk" value="go" />
    <span style="margin-left:24px;">This page will redirect in 2 seconds</span>
</form>
<script type="text/javascript">
    setTimeout(function() {
        document.getElementById('mval').submit();
    }, 2000);
</script>
<?php else : ?>
    <p>SORRY - Password not found...</p>
<?php endif; ?>
