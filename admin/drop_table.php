<?php
/**
 * A simple script to drop the indicated table (only) from the database.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$table = filter_input(INPUT_GET, 'tbl');
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>DROP <?php echo $table;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the specified Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body {background-color: #eaeaea;}
    </style>
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">DROP <?php echo $table;?> Table</p>
<div style="margin-left:16px;font-size:18px;">

<?php
if ($table === 'EHIKES') {
    echo "NOTE: EHIKES cannot be dropped until all other Exx Tables " .
        "are dropped due to foreign keys";
}
echo "<p>Removing any previous instantiation of table '{$table}':</p>";
 $remtbl = "DROP TABLE {$table};";
try {
    $pdo->query($remtbl);
}
catch (PDOException $e) {
    pdoErr($remtbl, $e);
}

$remaining = $pdo->query("SHOW TABLES;");
$tbls = $remaining->fetchAll(PDO::FETCH_BOTH);
echo "<ul>\n";
foreach ($tbls as $row) {
    echo "<li>" . $row[0] . "</li>\n";
}
echo "</ul><br />DONE";
?>
    
</div>
</body>
</html>
