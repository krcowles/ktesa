<?php
/**
 * This script will allow the user to renew his/her password if he/she
 * has opted to do so.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$user  = filter_input(INPUT_GET, 'user');
if (empty($user)) {
    throw new Exception("No user name received");
}
$usr_req = "SELECT `username`, `userid`  FROM USERS WHERE " .
    "`username` = :usr;";
$dbdata = $pdo->prepare($usr_req);
$dbdata->execute(['usr' => $user]);
$userdata = $dbdata->fetch(PDO::FETCH_ASSOC);
if ($userdata === false) {
    throw new Exeption("User {$user} not located");
}
$username = $userdata['username'];
$userid = $userdata['userid']; // Not yet used
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Password Renewal</title>
    <meta charset="utf-8" />
    <meta name="description" content="User update password et al" />
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
<p id="trail">Renew/Reset Registration</p>
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
