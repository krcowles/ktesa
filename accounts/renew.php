<?php
/**
 * This script will allow the user to renew his/her password.
 * One way to get here is via the login process which examines
 * the user's expiration date and allows a redirect here if it has
 * expired, or is about to expire (and the user has confirmed he/she
 * wishes to renew). In this case, all login credentials are already
 * established. Another way is via the 'Forgot Password' mail
 * link. In this case, there are no user login credentials.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$tmpcode  = isset($_GET['code']) ? filter_input(INPUT_GET, 'code') : '';
$username = empty($tmpcode) ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Change Password</title>
    <meta charset="utf-8" />
    <meta name="description" content="User password update form" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="renew.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Change Password</p>
<p id="page_id" style="display:none">Admin</p>

<div id="container">
    <h3>Please enter and confirm a new password:</h3>
    <form id="form" method="POST" action="create_user.php">
        <input type="hidden" name="submitter"  value="change" />
        <input type="hidden" name="username" value="<?=$username;?>" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
        <table>
            <tbody>
                <tr> 
                    <?php if (!empty($tmpcode)) : ?>
                        <td>One-time Code</td>
                    <?php else : ?>
                        <td>Current Password</td> 
                    <?php endif; ?>
                    <td class="space"></td>
                    <td><input type="password" name="oldpass" size="20"
                        autocomplete="off" value="<?=$tmpcode;?>" /></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="visibility:hidden">
                    <td>linebreak</td>
                </tr>
                <tr>
                    <td>New password:</td>
                    <td class="space"></td>
                    <td><input id="password" type="password"
                        name="password" size="20" autocomplete="new-password" /></td>
                    <td class="space"></td>
                    <td>Show Password:</td>
                    <td><input id="ckbox"
                        type="checkbox" /></td>
                </tr>
                <tr>
                    <td>Confirm:</td>
                    <td class="space"></td>
                    <td><input id="confirm" type="password" name="confirm" 
                        autocomplete="new-password" size="20" /></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table><br />
        <button id="formsubmit">Submit</button>
    </form>
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
<script src="renew.js"></script>

</body>
</html>
