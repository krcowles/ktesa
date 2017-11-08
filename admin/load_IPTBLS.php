<?php
require_once '../mysql/setenv.php';
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Populate IPTBLS</title>
    <meta charset="utf-8" />
    <meta name="description" content="Fill the IPTBLS table w/xml database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
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
    <p id="trail">Load IPTBLS From XML</p>
    <div style="margin-left:16px;font-size:18px;">
        <p>Use database.xml to populate the IPTBLS table in the 'mysql' database...</p>

<?php
    echo "<p>mySql Connection Opened.</p>";
    $db = simplexml_load_file('../data/database.xml');
    if (!$db) {
        $errmsg = '<p style="color:red;font-size:18px;margin-left:16px">' .
            'Failed to load xml database.</p>';
        die($errmsg);
    } else {
        echo '<p>XML Database successfully opened.</p>';
    }
    # Extract each row's variables and load into mysql IPTBLS table
    foreach ($db->row as $row) {
        if ($row->marker == 'Visitor Ctr') {
            $ipno = $row->indxNo;
            $k =0;
            foreach ($row->content->tblRow as $ipdat) {
                $co = mysqli_real_escape_string($link,$ipdat->compl->__toString());
                $nm = mysqli_real_escape_string($link,$ipdat->tdname->__toString());
                $pg = mysqli_real_escape_string($link,$ipdat->tdpg->__toString());
                if ($ipdat->tdmiles == '') {
                    $mi = 0;
                } else {
                    $mi = mysqli_real_escape_string($link,$ipdat->tdmiles->__toString());
                }
                if ($ipdat->tdft == '') {
                    $ft = 0;
                } else {
                    $ft = mysqli_real_escape_string($link,$ipdat->tdft->__toString());
                }
                $ex = mysqli_real_escape_string($link,$ipdat->tdexp->__toString());
                $al = mysqli_real_escape_string($link,$ipdat->tdalb->__toString());
                $iptblreq = "INSERT INTO IPTBLS (indxNo,compl,tdname,tdpg," .
                    "tdmiles,tdft,tdexp,tdalb) VALUES ('{$ipno}','{$co}','{$nm}'," .
                    "'{$pg}','{$mi}','{$ft}','{$ex}','{$al}');";
                #echo "---{$k}---" . $iptblreq;
                #$k++;
                $iptbl = mysqli_query($link,$iptblreq);
                if (!$iptbl) {
                    die("<p>load_IPTBLS.php: Failed to load row into IPTBLS: " .
                        mysqli_error($link) . "</p>");
                }
            }
            $warn = mysqli_query($link,"SHOW WARNINGS;");
            $notes = mysqli_fetch_row($warn);
            if (mysqli_num_rows($notes) !== 0) {
                foreach ($notes as $err) {
                    echo '<p>' . $err . '</p>';
                }
            }
        }
    }
?>
        <p>DONE!</p>
    </div>
</body>
</html>