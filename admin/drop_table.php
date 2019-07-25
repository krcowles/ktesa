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
$list = showTables($pdo, '');
$show = $list[0];
// is table already dropped?
if (!in_array($table, $show)) {
    throw new Exception("{$table} has already been dropped");
}
// foreign key dependencies?
$ehike = ($table === "EHIKES" ? true : false);
if ($ehike && (in_array("EGPSDAT", $show) || in_array("EREFS", $show) 
    || in_array("ETSV", $show))
) {
    $msg = "You cannot drop EHIKES until all other E-tables are dropped";
    throw new Exception($msg);
}
$pdo->query("DROP TABLE {$table}");
$list = showTables($pdo, '');
$show = $list[0];
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>DROP <?= $table;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the specified Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body {
            background-color: #eaeaea;
            margin: 0px;
        }
    </style>
</head>

<body>
<?php require "../pages/pageTop.php"; ?>
<p id="trail">DROP <?= $table;?> Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>Removing any previous instantiation of table <?= $table;?></p>
    <ul>
    <?php for ($i=0; $i<count($show); $i++) : ?>
        <li><?= $show[$i];?></li>
    <?php endfor; ?>
    </ul>
    <p>DONE</p>
</div>

</body>
</html>
