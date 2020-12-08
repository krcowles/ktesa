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
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="unifiedLogin.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail"><?=$title;?></p>

<p id="formtype" style="display:none;"><?=$form;?></p>
<div id="container">
<?php if ($form === 'reg') : ?>
    <form id="form" action="#" method="post">
        <input type="hidden" name="submitter" value="create" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
            <p>Sign up for free access to nmhikes.com!</p>
            <p id="sub">Create and edit your own hikes</p>
            <div>
                <div class="pseudo-legend">First Name</div>
                <div id="line1" class="lines"></div>
                <input id="fname" class="signup" type="text"
                    placeholder="First Name" name="firstname"
                    autocomplete="given-name" required />
            </div>
            <div>
                <div class="pseudo-legend">Last Name</div>
                <div id="line2" class="lines"></div>
                <input id="lname" class="signup" type="text"
                    placeholder="Last Name" name="lastname"
                    autocomplete="family-name" required />
            </div>
            <div>
                <div class="pseudo-legend">Username</div>
                <div id="line3" class="lines"></div>
                <input id="uname" class="signup" type="text"
                    placeholder="User Name" name="username"
                    autocomplete="username" required />
            </div>
            <div>
                <div class="pseudo-legend">Email</div>
                <div id="line4" class="lines"></div>
                <input id="email" class="signup" type="email"
                    required placeholder="Email" name="email"
                    autocomplete="email" />
            </div><br />
            <button id="submit">Submit</button>    
    </form>
<?php elseif ($form === 'renew') : ?>
    <h3>Please enter and confirm a new password:</h3>
    <form id="form" action="#" method="post">
        <input type="hidden" name="code" value="<?=$code;?>" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
        <?php if (!empty($code)) : ?>
            <div class="rdiv">
                <span class="rtxt">One-time code</span>
                <input class="renew" type="password" name="one-time"
                    autocomplete="off" value="<?=$code;?>" />
            </div>
        <?php endif; ?>
        <div class="rdiv">
            <span class="rtxt">New password</span>
            <input id="password" class="renew" type="password"
                name="password" size="20" autocomplete="new-password" required />
        </div>
        <div id="showp" class="rdiv">
            <span class="rtxt">Show password</span>
            <input id="ckbox" type="checkbox" />
        </div>
        <div class="rdiv">
            <span class="rtxt">Confirm password</span>
            <input id="confirm" class="renew" type="password"
                name="confirm" autocomplete="new-password" size="20" required />
        </div>
        <button id="formsubmit">Submit</button>
    </form>
<?php elseif ($form === 'log') : ?>
    <h3>Member Log in</h3>
    <form id="form" action="#" method="post">
    <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
    <table>
            <tbody>
                <tr>
                    <td>Username</td>
                    <td></td>
                    <td><input class="logger" id="username" type="text"
                        name="username" autocomnplete="username" required />
                </tr>
                <tr style="visibility:hidden">
                    <td>linebreak</td>
                </tr>
                <tr> 
                    <td>Password</td> 
                    <td></td>
                    <td><input class="logger" id="password" type="password"
                        name="oldpass" size="20" 
                        autocomplete="password" required/></td>
                </tr>

            </tbody>
        </table><br />
        <button id="formsubmit">Submit</button>
</form>

<?php endif; ?>
</div>   <!-- end of container -->

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

<script src="../scripts/menus.js"></script>
<script src="unifiedLogin.js"></script>

</body>
</html>
