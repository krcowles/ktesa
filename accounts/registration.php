<?php
/**
 * This is the sign-up form for a user to become a memeber,
 * allowing him/her to create and edit personal hike pages
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No Liscense to date
 */
session_start();
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New User Registration</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="New Member Registration Form" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="registration.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">New Member Registration</p>

<div id="container">
    <form id="form" action="create_user.php" method="post">
        <input type="hidden" name="submitter" value="create" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
        <div id="registration">
            <p>Sign up for free access to nmhikes.com!</p>
            <p id="sub">Create and edit your own hikes</p>
            <div class="user-input leftmost">
                <div class="pseudo-legend">First Name</div>
                <div id="line1" class="lines"></div>
                <input id="fname" class="signup" type="text"
                    placeholder="First Name" name="firstname"
                    autocomplete="given-name" />
            </div>
            <div class="user-input">
                <div class="pseudo-legend">Last Name</div>
                <div id="line2" class="lines"></div>
                <input id="lname" class="signup" type="text"
                    placeholder="Last Name" name="lastname"
                    autocomplete="family-name" />
            </div><br />
            <div class="user-input leftmost">
                <div class="pseudo-legend">Username</div>
                <div id="line3" class="lines"></div>
                <input id="uname" class="signup" type="text"
                    placeholder="User Name" name="username"
                    autocomplete="username" />
            </div>
            <div class="user-input">
                <div class="pseudo-legend">Email</div>
                <div id="line4" class="lines"></div>
                <input id="email" class="signup" type="email"
                    placeholder="Email" name="email"
                    autocomplete="email" />
            </div><br />

            <div class="user-input leftmost">
                <div class="pseudo-legend">Password</div>
                <div id="line5" class="lines"></div>
                <input id="pword" class="signup" type="password"
                    placeholder="Password" name="password"
                    autocomplete="new-password" />
            </div>
            <span id="showit">&nbsp;&nbsp;Show password:<input id="cb"
                    type="checkbox" /></span>
            <button id="submit">Submit</button>    
        </div>
    </form>
</div>

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
<script src="registration.js"></script>

</body>
</html>