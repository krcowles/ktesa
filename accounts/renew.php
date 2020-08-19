<?php
/**
 * This script will allow the user to renew his/her password.
 * The only way to get here is via the login process which examines
 * the user's expiration date and redirects here if it has
 * expired (or is about to expire) and the user has confirmed he/she
 * wishes to renew.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Password Renewal</title>
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
<p id="trail">Renew Password</p>
<p id="page_id" style="display:none">Admin</p>

<div id="container">
    <h3>Please enter and confirm a new password:</h3>
    <form id="form" method="POST" action="create_user.php">
        <input type="hidden" name="submitter"  value="renew" />
        <input type="hidden" name="username" value="<?=$username;?>" />
        <table>
            <tbody>
                <tr>
                    <td>Enter a new password:</td>
                    <td class="space"></td>
                    <td><input id="password" type="password"
                        name="password" size="20" /></td>
                    <td class="space"></td>
                    <td>Show Password:</td>
                    <td><input id="ckbox"
                        type="checkbox" /></td>
                </tr>
                <tr>
                    <td>Confirm password:</td>
                    <td class="space"></td>
                    <td><input id="confirm" type="password" 
                        name="confirm" size="20" /></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table><br />
        <button id="formsubmit">Submit</button>
    </form>
</div>   <!-- end of container -->

<script src="../scripts/menus.js"></script>
<script src="renew.js"></script>

</body>
</html>
