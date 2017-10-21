<?php
require "setenv.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Load TSV</title>
    <meta charset="utf-8" />
    <meta name="description" content="Load TSV table from XML" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Load TSV Table from XML</p>

<div style="margin-left:24px;" id="tools">
<?php
$query = "LOAD XML LOCAL INFILE '../data/database.xml' INTO TABLE TSV ROWS "
    . "IDENTIFIED BY '<picDat>';";
$tsv = mysqli_query($link,$query);
if (!$tsv) {
    die ("load_TSV.php: Failed to load TSV table from XML using LOAD");
}
$addindx = "ALTER TABLE TSV ADD indxNo SMALLINT AFTER picIdx";
$addcol = mysqli_query($link,$addindx);
if (!$addcol) {
    die("<p>load_TSV.php: Failed to add indxNo column to TSV</p>");
} else {
    echo '<p>EHIKES Table created; Definitions are shown in the table below</p>';
}
# now add the indxNo info:
$xml = simplexml_load_file('../database/database.xml');
if (!$xml) {
    $errmsg = '<p style="color:red;font-size:18px;margin-left:16px">' .
        'Failed to load xml database.</p>';
    die($errmsg);
} else {
    echo '<p>XML Database successfully opened.</p>';
}
foreach ($xml->row as $row) {
    if ($row->tsv->picDat->count() !== 0) {
        
    }
}
?>
</div>
</body>
</html>