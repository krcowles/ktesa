<?php
require_once "../mysql/setenv.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Load REFS</title>
    <meta charset="utf-8" />
    <meta name="description" content="Load REFS table from XML" />
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
<p id="trail">Load REFS Table from XML</p>

<div style="margin-left:24px;" id="tools">
<?php
function realType($typeltr) {
    switch ($typeltr) {
        case 'b':
            return 'Book:';
            break;
        case 'p':
            return 'Photo Essay:';
            break;
        case 'n':
            return 'No references found';
            break;
        case 'w':
            return 'Website:';
            break;
        case 'h': 
            return 'Website:';
            break;
        case 'a':
            return 'App:';
            break;
        case 'd':
            return 'Downloadable Doc:';
            break;
        case 'l':
            return 'Blog:';
            break;
        case 'r':
            return 'Related Site:';
            break;
        case 'o':
            return 'Map:';
            break;
        case 'm':
            return 'Magazine:';
            break;
        case 's':
            return 'News Article:';
            break;
        case 'g':
            return 'Meetup Group:';
            break;
        default:
            return "Contact Site Master";
    }
}
$query = "LOAD XML LOCAL INFILE '../data/database.xml' INTO TABLE REFS ROWS "
    . "IDENTIFIED BY '<ref>';";
$ref = mysqli_query($link,$query);
if (!$ref) {
    die ("load_REFS.php: Failed to load REFS table from XML using LOAD");
}
# now add the indxNo info:
$xml = simplexml_load_file('../data/database.xml');
if (!$xml) {
    $errmsg = '<p style="color:red;font-size:18px;margin-left:16px">' .
        'Failed to load xml database.</p>';
    die($errmsg);
} else {
    echo '<p>XML Database successfully opened.</p>';
}
$indices = [];
$reftypes = [];
$indx = 0;
# NOTE: the loop skips over index pages which have no picDat
foreach ($xml->row as $row) {
    $indx++;
    $rcnt = $row->refs->ref->count();
    if ($rcnt !== 0) {
        for ($j=0; $j<$rcnt; $j++) {
            $rtype = $row->refs->ref[$j]->rtype->__toString();
            $fulltype = realType($rtype);
            array_push($reftypes,$fulltype);
            array_push($indices,$indx);
        }
    }
}
# there should be a one-to-one correspondence based on original load
for ($k=0; $k<count($indices); $k++) {
    $refrow = $k + 1;
    $placeIndx = "UPDATE REFS SET indxNo = '{$indices[$k]}' WHERE refId = '{$refrow}';";
    $newdat = mysqli_query($link,$placeIndx);
    if (!$newdat) {
        die ("load_REFS.php: Failed to update REFS with new indxNo value: " . 
                mysqli_error() );
    }
    $chgtype = "UPDATE REFS SET rtype = '{$reftypes[$k]}' WHERE refId = '{$refrow}';";
    $newtype = mysqli_query($link,$chgtype);
    if (!$newtype) {
        die ("load_REFS.php: Failed to update REFS with new rtype: " . 
                mysqli_error());
    }
    echo "." . $k . ".";
    $warn = mysqli_query($link,"SHOW WARNINGS;");
    $notes = mysqli_fetch_row($warn);
    if (mysqli_num_rows($notes) !== 0) {
        foreach ($notes as $err) {
            echo '<p>' . $err . '</p>';
        }
    }
    flush();
}
echo "<br />Data loaded";
require "ref_scrub.php";
echo "<br />Data scrubbed";
?>
</div>
</body>
</html>
