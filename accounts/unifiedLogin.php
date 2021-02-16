<?php
/**
 * This page allows the user to login to the site as a member,
 * whether a new member registration, change password request,
 * 'Forgot password' request, expired/renewable membership,
 * or rejected cookies. In each case the user is sent a one-time
 * secure code as a password, and must select a new password
 * to continue. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No Liscense to date
 */
session_start();
require "../php/global_boot.php";
$form  = filter_input(INPUT_GET, 'form');
$code  = isset($_GET['code']) ? filter_input(INPUT_GET, 'code') : '';
if ($form === 'reg') {
    $title = "Sign Up";
} elseif ($form === 'renew') {
    $title = "Set Password";
} elseif ($form === 'log') {
    $title = "Log in";
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?=$title;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Unified log in page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous" /> 
    <link href="unifiedLogin.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    <script type="text/javascript">var page = 'unified';</script>
</head>

<body>
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

<p id="formtype" style="display:none;"><?=$form;?></p>
<div id="container">
<?php if ($form === 'reg') : ?>
    <form id="form" action="#" method="post">
        <input type="hidden" name="submitter" value="create" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
            <p>Sign up for free access to nmhikes.com!</p>
            <p id="sub">Create and edit your own hikes<br />
            <a id="policylnk" href="#">Privacy Policy</a>
            </p>
            <div>
                <div class="pseudo-legend">First Name</div>
                <div id="line1" class="lines"></div>
                <input id="fname" type="text"
                    placeholder="First Name" name="firstname"
                    autocomplete="given-name" required />
            </div>
            <div>
                <div class="pseudo-legend">Last Name</div>
                <div id="line2" class="lines"></div>
                <input id="lname" type="text"
                    placeholder="Last Name" name="lastname"
                    autocomplete="family-name" required />
            </div>
            <div>
                <div class="pseudo-legend">Username</div>
                <div id="line3" class="lines"></div>
                <input id="uname" type="text"
                    placeholder="User Name" name="username"
                    autocomplete="username" required />
            </div>
            <div>
                <div class="pseudo-legend">Email</div>
                <div id="line4" class="lines"></div>
                <input id="email" type="email"
                    required placeholder="Email" name="email"
                    autocomplete="email" /><br /><br />
            </div>
            <div>
                <button id="formsubmit">Submit</button>
            </div> 
    </form>
<?php elseif ($form === 'renew') : ?>
    <h3>Reset Passsword:</h3>
    <form id="form" action="#" method="post">
        <input type="hidden" name="code" value="<?=$code;?>" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
        <?php if (!empty($code)) : ?>
            <span>One-time code</span>
            <input type="password" name="one-time" autocomplete="off"
                value="<?=$code;?>" /><br /><br />  
        <?php endif; ?>
        <input id="password" type="password" name="password" autocomplete="new-password"
            required placeholder="New Password" /><br />
        Show password&nbsp;&nbsp;&nbsp;<input id="ckbox" type="checkbox" /><br /><br />
        <input id="confirm" type="password" name="confirm" autocomplete="new-password"
            required="required" placeholder="Confirm Password" /><br /><br />
        <button id="formsubmit">Submit</button>
    </form>
<?php elseif ($form === 'log') : ?>
    <div class="container">
        <h3 id="hdr">Member Log in</h3><br />
        <form id="form" action="#" method="post">
            <input id="usrchoice" type="hidden" name="cookies"
                value="nochoice" />
            <input class="logger" id="username" type="text" placeholder="Username"
                name="username" autocomnplete="username" required /><br /><br />
            <input class="logger" id="password" type="password" name="oldpass"
                placeholder="Password" size="20" autocomplete="password"
                required/><br /><br />
            <button id="formsubmit">Submit</button><br /><br />
        </form>

        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#fpass">
        Forgot Password?
        </button>
        <div class="modal fade" id="fpass" tabindex="-1" aria-labelledby="ResetPassword" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Enter your email below. You will receive an email link to 
                        reset your password<br />
                        <input id="forgot" type="email" required 
                            placeholder="Enter your email" /><br /><br />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="send">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>   <!-- end of #container -->

<div id="cookie_banner">
    <h3>This site uses cookies to save member usernames</h3>
    <p>Accepting cookies allows automatic login. If you reject cookies,
    no cookie data will be collected, and you must login each visit.
    <br />You may change your decision later via the Help menu.
    </p>
    <div id="cbuttons">
        <button id="accept">Accept</button>
        <button id="reject">Reject</button>
    </div>
</div>

<script src="../scripts/logo.js"></script>
<script src="../scripts/validateUser.js"></script>
<script src="unifiedLogin.js"></script>

</body>
</html>
