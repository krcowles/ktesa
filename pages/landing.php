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
require "../php/global_boot.php";
require "../accounts/getLogin.php";
require "siteHikes.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Mobile site for New Mexico Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous" />
    <link href="../styles/landing.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
</head>

<body>

<div id="logo">
    <div id="pattern"></div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike New Mexico</span>
        </div>
        <div id="center" class="dropdown">
            <button class="btn-sm btn-secondary dropdown-toggle" type="button"
                id="memberOpts" data-bs-toggle="dropdown" aria-expanded="false">
                Membership
            </button>
            <ul class="dropdown-menu" aria-labelledby="memberOpts">
                <a id="login" class="dropdown-item" href="../accounts/unifiedLogin.php?form=log">Login</a>
                <a id= "logout" class="dropdown-item" href="#">Logout</a>
                <a id="bam" class="dropdown-item" href="../accounts/unifiedLogin.php?form=reg">Become a member</a>
                <div id="admintools">
                    <div class="dropdown-divider"></div>
                    <a id="adminmenu" class="dropdown-item" href="../admin/admintools.php">Admintools</a>
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
    
    <!-- datalist not supported by bootstrap; disabled in CSS -->
    <div id="hide">
        <input id="searchbar" placeholder="Enter hike" list="hikelist" />
        <?=$datalist;?> 
        <a id="goto" type="button" class="btn btn-secondary" href="#">
            View Hike Page</a>
    </div>
        
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

<script src="../scripts/logo.js"></script>
<script src="../scripts/landing.js"></script>
<script src="../scripts/loginState.js"></script>
<script type="text/javascript">var hikeObjects = <?=$jsonHikes;?>;</script>
</body>

</html>
