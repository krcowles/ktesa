<?php
/**
 * This is the mobile landing site for New Mexico Hikes.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require_once "../php/global_boot.php";
require_once "../accounts/getLogin.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Mobile site for New Mexico Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/landing.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div id="logo">
    <div id="pattern"></div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike New Mexico</span>
        </div>
        
        <!-- minimal functionality "navbar" -->
        <div id="center" class="dropdown">
            <button class="btn-sm btn-secondary dropdown-toggle" type="button"
                id="memberOpts" data-bs-toggle="dropdown" aria-expanded="false">
                Membership
            </button>
            <ul class="dropdown-menu" aria-labelledby="memberOpts">
                <a id="login" class="dropdown-item"
                    href="../accounts/unifiedLogin.php?form=log"
                    target="_self">Login</a>
                <a id= "logout" class="dropdown-item" href="#">Logout</a>
                <a id="bam" class="dropdown-item"
                    href="../accounts/unifiedLogin.php?form=reg"
                    target="_self">Become a member</a>
                <div id="admintools">
                    <div class="dropdown-divider"></div>
                    <a id="adminmenu" class="dropdown-item"
                        href="../admin/admintools.php">Admintools</a>
                </div>
            </ul>
        </div>

        <div id="rightside">
            <span id="logo_right">w/Tom &amp; Ken</span>
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        </div>
    </div>   
</div>

<p id="cookie_state"><?= $_SESSION['cookie_state'];?></p>
<?php if (isset($_SESSION['cookies'])) : ?>
<p id="cookies_choice"><?= $_SESSION['cookies'];?></p>
<?php endif; ?>
<?php if (isset($admin) && $admin) : ?>
<p id="admin">admin</p>
<?php endif; ?>

<div class="container">
    <h2 id="welcome">Welcome to New Mexico Hikes!</h2>
    <h3>A free site where members can create their own hikes</h3>  
    <p id="usrview">Choose from the following viewing options:</p>
    <div>
        <div id="choice1">
            <p>Hike Table</p>
            <img id="table" src="../images/tbl.jpg"  alt="image of table of hikes" />
        </div>
        <div id="choice2">
            <p>Map &amp; markers</p>
            <img id="map" src="../images/mapmrkrs.jpg" alt="map with markers" />
        </div>
    </div><br />
</div>
<div id="ajaxerr" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">An Error Has Occurred</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                We are sorry, but an error has occurred. The admin has been notified.
                We apologize for any inconvenience.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<script src="../scripts/jquery.js"></script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/landing.js"></script>
<script src="../scripts/loginState.js"></script>
</body>

</html>
