<?php
/**
 * This script will compares LKUSERS ("Last Known USERS") with USERS.
 * If USERS has additional entries, they are identified. To update the
 * LKUSERS table, use the "Update USERS Table" button on the admintools page.
 * Since updates copy USERS into LKUSERS, the rows can be compared sequentially.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$userids = $pdo->query("SELECT `userid` FROM `USERS`;")->fetchAll(PDO::FETCH_NUM);
$lkusers = $pdo->query("SELECT `userid` FROM `LKUSERS`;")->fetchAll(PDO::FETCH_NUM);
$newusers = [];
if (count($userids) > count($lkusers)) {
    for ($k=count($lkusers); $k<count($userids); $k++) {
        $newReq= "SELECT `userid`,`username`,`first_name`,`last_name` " .
            "FROM `USERS` WHERE `userid`=:id;";
        $newbie = $pdo->prepare($newReq);
        $newbie->execute([$userids[$k][0]]);
        $user = $newbie->fetch(PDO::FETCH_ASSOC);
        array_push($newusers, $user);
    }
} 
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Results for New Users</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #dfdfdf; }
        #page { margin-left: 36px; }
        table, th, td { border-collapse: collapse; padding: 6px; font-size: 18px; }
        table { border: 2px solid black; }
        th { background-color: darkslategray; color: white; }
        th { text-align: center; }
        td { border-bottom: 1px solid slategray; }
    </style>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<div id="page">
<?php if (count($newusers) === 0) : ?>
    <h3>There are no new users</h3>
<?php else : ?>
    <h3>The following new users have been identified:</h3>
    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($newusers as $user) : ?>
            <tr>
                <td><?=$user['userid'];?></td>
                <td><?=$user['username'];?></td>
                <td><?=$user['first_name'];?></td>
                <td><?=$user['last_name'];?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
<?php endif; ?>
</div>

<script src="../scripts/menus.js"></script>
</body>

</html>
