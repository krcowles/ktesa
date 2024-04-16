<?php
/**
 * This page allows the user to login to the site as a member, whether a new member
 * registration, a change password request, 'Forgot password' request, expired or
 * renewable membership, or rejected cookies. In each case the user is sent a
 * one-time secure code as a password, and must select a new password to continue. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No License to date
 */
session_start();
require "../php/global_boot.php";

$form   = filter_input(INPUT_GET, 'form');
$code   = isset($_GET['code']) ? filter_input(INPUT_GET, 'code') : '';
$ix     = isset($_GET['ix']) ? filter_input(INPUT_GET, 'ix') : false;
$newusr = isset($_GET['reg']) ? true : false;
$title   = "Query string missing!"; // otherwise error fct called
if ($form === 'reg') {
    $title = "Complete Registration";
} elseif ($form === 'renew') {
    $title = "Set Password";
} elseif ($form === 'log') {
    $title = "Log in";
}
if ($newusr) {
    $btn_note = 'Complete Your Security Questions';
} else {
    $btn_note = 'Review/Edit Security Questions';
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <!-- there is no navbar on this page -->
    <title><?=$title;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Unified login page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/unifiedLogin.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script type="text/javascript">
        var page = 'unified';
        var mobile, isMobile, isTablet, isAndroid, isiPhone, isiPad;
        window.addEventListener("load", () => { // useragent yields TRUE OR NULL!
            isMobile = navigator.userAgent.toLowerCase().match(/mobile/i) ? 
                true : false;
            isTablet = navigator.userAgent.toLowerCase().match(/tablet/i) ?
                true : false;
            isAndroid = navigator.userAgent.toLowerCase().match(/android/i) ?
                true : false;
            isiPhone = navigator.userAgent.toLowerCase().match(/iphone/i) ?
                true : false;
            isiPad = navigator.userAgent.toLowerCase().match(/ipad/i) ?
                true : false;
            mobile = isMobile && !isTablet && !isiPad ? true : false;
        });
    </script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<!-- only the logo is presented on this page, no navbar -->
<div id="logo">
    <div id="pattern"></div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike New Mexico</span>
        </div>
        <div id="center"><?=$title;?></div>
        <div id="rightside">
            <span id="logo_right">w/Tom &amp; Ken</span>
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        </div>
    </div>   
</div>

<p id="appMode" style="display:none;"><?=$appMode;?></p>
<p id="formtype" style="display:none;"><?=$form;?></p>
<div id="container">  <!-- only one of the three sections will appear on page -->
<?php if ($form === 'reg') : ?>
    <form id="form" action="#" method="post">
        <input type="hidden" name="submitter" value="create" />
        <p>Sign up for free access to nmhikes.com!</p>
        <p id="sub">Create and edit your own hikes<br />
        <a id="policylnk" href="#">Privacy Policy</a>
        </p>
        <div class="mobinp">
            <div class="pseudo-legend">First Name</div>
            <div id="line1" class="lines"></div>
            <input id="fname" type="text" class="wide"
                placeholder="First Name" name="firstname"
                autocomplete="given-name" required />
        </div>
        <div class="mobinp">
            <div class="pseudo-legend">Last Name</div>
            <div id="line2" class="lines"></div>
            <input id="lname" type="text" class="wide"
                placeholder="Last Name" name="lastname"
                autocomplete="family-name" required />
        </div>
        <div id="name_req" class="mobtxt"><p>Username must be at least 6
            characters, no spaces</p>
        </div>
        <div class="mobinp">
            <div class="pseudo-legend">Username</div>
            <div id="line3" class="lines"></div>
            <input id="uname" type="text" class="wide"
                placeholder="User Name" name="username"
                autocomplete="username" required />
        </div>
        <div class="mobinp">
            <div class="pseudo-legend">Email</div>
            <div id="line4" class="lines"></div>
            <input id="email" type="email" class="wide"
                required placeholder="Email" name="email"
                autocomplete="email" /><br /><br />
        </div>
        <div class="mobinp">
            <button id="formsubmit">Submit</button>
        </div> 
    </form>
<?php elseif ($form === 'renew') : ?>
    <h3>Reset Passsword:</h3>
    <form id="form" action="#" method="post">
        <input type="hidden" name="code" value="<?=$code;?>" />
        <?php if ($ix !== false) : ?>
            <p id="ix" style="display:none;"><?=$ix;?></p>
        <?php else : ?>
            <p><strong>ERROR: missing uid</strong></p>
        <?php endif; ?>
        <input id="usrchoice" type="hidden" name="cookies" 
            value="nochoice" class="wide" />
        <span class="mobtxt">Your <span id="precode">Pre-populated</span>
            One-time code</span>
        <input id="one-time" type="password" name="one-time" autocomplete="off"
            value="<?=$code;?>" class="wide" /><br /> 
        <div id="pexpl">
            **&nbsp;Your new password must be 10 characters or more and contain
            upper and lower case letters and at least 1 number and 1 special
            character.
        </div>
        <div>
            <input id="password" type="password" name="password"
                autocomplete="new-password" required class="wide renpass"
                placeholder="New Password" /><br />
            <div id="usrinfo">
                <span id="wk">Weak</span>
                <span id="st">Strong</span>&nbsp;&nbsp;
                <button id="showdet">Show Why</button>&nbsp;&nbsp;
                Show password&nbsp;&nbsp;&nbsp;
                <input id="ckbox" type="checkbox" /><br /><br />
            </div>
        </div> 
        <input id="confirm" type="password" name="confirm" class="wide mobinp"
            autocomplete="new-password" required="required"
            placeholder="Confirm Password" /><br />
        <div>
            <button id="rvw" class="rvw_new" type="button" 
                class="btn btn-warning"><?=$btn_note;?>
        </button>
        </div> <br />
        <button type="submit" id="formsubmit" class="btn mobinp">
            Submit</button>     
    </form>
<?php elseif ($form === 'log') : ?>
    <div class="container">
        <h3 id="hdr">Member Log in</h3><br />
        <form id="form" action="#" method="post">
            <input id="usrchoice" type="hidden" name="cookies"
                value="nochoice" />
            <input class="logger wide" id="username" type="text"
                placeholder="Username" name="username" autocomnplete="username"
                required /><br /><br />
            <input class="logger wide" id="password" type="password"
                name="oldpass" placeholder="Password" size="20"
                autocomplete="password" required/><br /><br />
            <button id="formsubmit" type="submit" class="btn btn-secondary">
                Submit</button><br /><br />
        </form>
        <!-- For 'Forgot password' and 'Renew password Modal -->
        <button id="logger" type="button" class="btn btn-outline-secondary"
        data-bs-toggle="modal" data-bs-target="#cpw" onclick="this.blur();">
        Forgot Username/Password?
        </button>
    </div>
<?php endif; ?>
</div>   <!-- end of #container -->
<?php require "unifiedLoginModals.html"; ?>

<div id="cookie_banner">
    <h3>This site uses cookies to save member usernames</h3>
    <p>Accepting cookies allows automatic login. If you reject cookies,
    no cookie data will be collected, and you must login each visit.
    Please read the Help->Policy document for more details.
    <br />You may change your decision later via the Help menu.
    </p>
    <div id="cbuttons">
        <button id="accept" type="button" class="btn btn-secondary">
            Accept Cookies</button>
        <button id="reject" type="button" class="btn btn-secondary">
            Reject Cookies</button>
    </div>
</div>

<script src="../scripts/logo.js"></script>
<script src="../scripts/validateUser.js"></script>
<script src="../scripts/sendResetMail.js"></script>
<script src="../scripts/passwordStrength.js"></script>
<script src="unifiedLogin.js"></script>

</body>
</html>
