<?php
/**
 * A simple script to list all the tables currently residing in the
 * connected database.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.php>
 * @license No license to date
 */
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Show Database Tables</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the USERS Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type='text/css'>
        body { background-color: #eaeaea; }
    </style>
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">SHOW Database Tables</p>
<div style="margin-left:16px;font-size:18px;">

<?php
$data = $pdo->query("SHOW TABLES;");
$tbls = $data->fetchALL(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($tbls as $row) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
?>
    <p>DONE</p>
</div>
</body>
</html>
