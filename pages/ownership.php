<?php
/**
 * This page outlines the ownership proposal and allows a viewer
 * to contact the author with regard to proprietorship of the site.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to data
 */
session_start();
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Own This Site!</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Offer to ownership" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/ownership.css" rel="stylesheet" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <?php require "../pages/favicon.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="appMode" style="display:none;"><?=$appMode;?></p>
<div id="contents">
<h1>The Reason</h1>
<p>This site has been continously maintained and updated by the authors for the
    past 10 years - and now it is time to pass it on! One author remains, has
    reached an age well past retirement, and would love to see the site continue
    on. This would involve minimal maintenance work, as the site has been rather
    thoroughly tested over the years. 
</p>
<h1>Range of Responsibilities</h1>
<p>The author wishes this to be a complete success and would support any interested
    individual in the transfer of all intellectual property at no charge. The
    minimal involvement would be to pay the site fees and act as admin, having no
    interaction with the code - it shouldn't need any. There is a yearly domain
    renewal fee of about $22, along with the host server account renewal fee of about
    $50. The latter is a 'business' account and could be downgraded to a single
    domain account for a smaller fee. Or, if desired, a different host service could
    be utilized. The admin is responsible only for reviewing and publishing any user
    hikes submitted for approval, but also has access to all the site tools.
</p><p>
    If you would rather continue on as a developer to make further improvements or
    to make changes, the codebase is maintained in github, but needs the addition of
    the pictures directory (holding all site photos) and the databases currently
    residing on the server. The databases are backed up in the codebase as SQL files,
    and can be reloaded at any time. There is also a third-party database
    cross-referencing ip addresses to locations (visitors are tracked), which does
    not reside in the github repository. There is a good deal of documentation,
    including code walk-throughs and flowcharts, and the
    author will gladly help the interested party setup his/her development
    environment. As long as the author is alive and able, he would be willing to help
    with any issues that arise, free of charge.
</p>
<p>If any of this interests you, you may contact me via email. Please note, the admin
    email is not read on a daily basis, but I will get back to you! Thank you for
    your interest.
</p>
<div id="emailer">
    <form id="emailit" method="post" action="../admin/new_owner_email.php">
        <input id="sender" type="email" placeholder="Enter your email address" />
        <br /><br />
        <textarea id="message"
            placeholder="Your message/level of interest"></textarea>
        <br /><br />
        <button id="sendreq" type="submit" class="btn btn-success">Send</button>
    </form>
</div>
    
</div>

<script src="../scripts/ownership.js"></script>
</body>

</html>
