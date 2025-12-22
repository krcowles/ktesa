<?php
/**
 * This script is invoked when a user wishes to publish a hike-in-edit.
 * If there is missing data, the user will be notified, and if all is
 * wlll, the admin will receive an email.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "../accounts/gmail.php";

$note = '';
$hikeName = filter_input(INPUT_GET, 'name');
$mail_admin  = isset($_GET['mail']) ? true : false;
if ($mail_admin) {
    $hikeNo = filter_input(INPUT_GET, 'hikeNo');
    // Notify EHIKES that a publish request is pending:
    $pdo->query("UPDATE `EHIKES` SET `pubreq`='Y' WHERE `indxNo`={$hikeNo};");
    // Get user's id
    $ehikeReq = "SELECT `usrid` FROM `EHIKES` WHERE `indxNo` = ?;";
    $ehike = $pdo->prepare($ehikeReq);
    $ehike->execute([$hikeNo]);
    $hikeid = $ehike->fetch(PDO::FETCH_ASSOC);
    $user = $hikeid['usrid'];
    $subject = "Publish hike {$hikeNo}";
    $message = "<h2>User {$user} requests publication of hike: {$hikeName} " .
        " [hike number {$hikeNo}]</h2>";
    // Mail it
    $mail->isHTML(true);
    $mail->setFrom('admin@nmhikes.com', 'Do not reply');
    $mail->addAddress(ADMIN, 'Admin');
    $mail->Subject = $subject;
    $mail->Body = $message;
    @$mail->send();
    $note = "<h5 class='pubhdr'>An email has been sent to the admin to " .
        "publish your hike.</h5>";
} else {
    // `pubreq` is not set in EHIKES in this case
    $problems = filter_input(INPUT_GET, 'result');
    $issues = urldecode($problems);
    $note = "<h5 class='pubhdr'>The following data is missing " .
        "from {$hikeName} - please correct the issues and resubmit:<h5>" .
        $issues;
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Request to Publish Hike</title>
    <meta charset="utf-8" />
    <meta name="description" content="User Request to Publish" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/pubrequest.css" rel="stylesheet" />
    <?php require "../pages/favicon.html"; ?>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<!-- body tag must be read prior to invoking bootstrap.js -->
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="active" style="display:none">PubReq</p>

<div id="contents">
    <?=$note;?>
</div>

</body>
</html>