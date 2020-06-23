<?php
/**
 * This script will allow the user to renew his/her password if he/she
 * has opted to do so.
 * PHP Version 7.1
 * 
 * @package Main
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$user  = filter_input(INPUT_GET, 'user');
$here = getcwd();

$usr_req = "SELECT * FROM USERS WHERE username = :usr;";
$dbdata = $pdo->prepare($usr_req);
$dbdata->execute(['usr' => $user]);
$userdata = $dbdata->fetch(PDO::FETCH_ASSOC);
$id = $userdata['userid'];
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Profile/Password Renewal</title>
    <meta charset="utf-8" />
    <meta name="description" content="User update password et al" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../accounts/registration.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { margin: 0px;}
        #formsubmit {
            width: 230px;
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
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
    <script src="../scripts/jquery.validate.min.js"></script>
    <script src="../scripts/jquery.validate.password.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Renew/Reset Registration</p>
<p id="page_id" style="display:none">Admin</p>

<div id="container">
<p>Please update your password, and any other data at this time</p>
<form id="form" method="POST" action="create_user.php">
<input type="hidden" name="submitter" value="renew" />
<fieldset>
    <legend>Password Information</legend>
    <p id="pnote">Note: Passwords must be at least 8 characters long and 
        should contain a mix of characters (alpha, numeric, special). They
        are automatically set to expire in 1 year, at which time you will
        need to set a new password.</p>
    <label for="password">Enter a password: </label>
    <input id="passwd" type="password" name="password" size="20"
        class="password" required />&nbsp;
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
    </fieldset>
    <fieldset>
    <legend>Other User Data</legend>
    <label for="firstname">First Name: [Max 20 Characters]</label>
    <input type="text" name="firstname" size="30" 
            maxlength="20" value="<?= $userdata['first_name'];?>" /><br />
    <label for="lastname">Last Name: [Max 30 Characters]</label>
    <input type="text" name="lastname" size="40" 
            maxlength="30" value="<?= $userdata['last_name'];?>" /><br />
    <label for="usr">User Name: [Max 32 Characters]</label>
    <input type="text" name="username" size="30" 
            maxlength="32" value="<?= $userdata['username'];?>" /><br /><br />
    <label for="email">email address: </label>
    <input id="email" type="text" name="email" size="40" 
            class="email" value="<?= $userdata['email'];?>" /><br />
    <label for="facebook">Facebook URL: [Max 100 Characters]</label>
    <input type="text" name="facebook" size="50" 
        maxlength="100" class="url" value="<?= $userdata['facebook_url'];?>" /><br />
    <label for="twitter">Twitter Handle: [Max 20 Characters]</label>
    <input type="text" name="twitter" size="20" maxlength="20" 
        value="<?= $userdata['twitter_handle'];?>" /><br />
    <label for="bio">Anything you would like us to know about you?
        [Max 500 Characters]</label><br />
    <textarea name="bio" cols="80" rows="10" 
        maxlength="500"><?= $userdata['bio'];?></textarea>
</fieldset><br />
<button id="formsubmit">Submit My Updates</button>
</form>
</div>   <!-- end of container -->
<script src="../scripts/menus.js"></script>
<script src="renew.js"></script>
</body>
</html>
