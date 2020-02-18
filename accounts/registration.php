<?php
/**
 * This script allows the user to submit information pertinent to becoming
 * a registered user, which allows him/her to create new pages and to edit
 * those pages. 
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require_once "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New User Registration</title>
    <meta charset="utf-8" />
    <meta name="description" content="New user sign-up" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="registration.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { margin: 0px; }
        #formsubmit {
            width: 190px;
            height: 28px;
            font-size: 18px;
            color: brown;
            margin-bottom: 18px;
        }
        #formsubmit:hover {
            cursor: pointer;
            background-color: honeydew;
            font-weight: bold;
        }
    </style>
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
    <script src="../scripts/jquery.validate.min.js"></script>
    <script src="../scripts/jquery.validate.password.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">New User Registration</p>
<p id="page_id" style="display:none">Reg</p>

<div id="container">
<h2>
    Welcome to the New Mexico Hikes Registration Page!
</h2>
<p>Please fill out the form below, making sure to supply at least the "required"
    information.</p>
<form method="POST" action="create_user.php">
<fieldset>
    <legend>Required Information</legend>
    <label for="firstname">First Name: [Max 20 Characters]</label>
    <input type="text" name="firstname" size="30" 
            maxlength="20" value="" /><br />
    <label for="lastname">Last Name: [Max 30 Characters]</label>
    <input type="text" name="lastname" size="40" 
            maxlength="30" value="" /><br />
    <label for="usr">Supply a User Name: [Max 32 Characters]</label>
    <input type="text" name="username" size="30" 
            maxlength="32" value="" /><br /><br />
    <p id="pnote">Note: Passwords must be at least 8 characters long and 
        should contain a mix of characters (alpha, numeric, special). They
        are automatically set to expire in 1 year, at which time you will
        need to set a new password.</p>
    <label for="password">Enter a password: </label>
    <input id="passwd" type="password" name="password" size="20"
            class="password" />&nbsp;
    <div class="password-meter">
        <div class="password-meter-message"></div>
        <div class="password-meter-bg">
            <div class="password-meter-bar"></div>
        </div>
        <br />
        <div id ="confirm">
        <label for="confirm_password">Confirm password: </label>
        <input id="confirm_password" type="password" 
                name="confirm_password" size="20" class="required" />
        </div>
    </div><br />
    <label for="email">email address: </label>
    <input id="email" type="text" name="email" size="40" 
            class="email" />
</fieldset>
<fieldset>
    <legend>Optional</legend>
    <label for="facebook">Facebook URL: [Max 100 Characters]</label>
    <input type="text" name="facebook" size="50" 
        maxlength="100" class="url" /><br />
    <label for="twitter">Twitter Handle: [Max 20 Characters]</label>
    <input type="text" name="twitter" size="20" maxlength="20" /><br />
    <label for="bio">Anything you would like us to know about you?
        [Max 500 Characters]</label><br />
    <textarea name="bio" cols="80" rows="10" maxlength="500"></textarea>
</fieldset><br />
<input type="hidden" name="submitter" value="create" />
<button id="formsubmit" type="submit">Submit My Info</button>
</form>
</div>   <!-- end of container -->
<script src="../scripts/menus.js"></script>
<script src="registration.js"></script>

</body>
</html>
