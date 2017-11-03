<?php
require_once "../mysql/setenv.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Load GPSDAT</title>
    <meta charset="utf-8" />
    <meta name="description" content="Load GPSDAT table from XML" />
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
<p id="trail">Load GPSDAT Table from XML</p>

<div style="margin-left:24px;" id="tools">
<?php
$xml = simplexml_load_file('../data/database.xml');
if (!$xml) {
    $errmsg = '<p style="color:red;font-size:18px;margin-left:16px">' .
        'Failed to load xml database.</p>';
    die($errmsg);
} else {
    echo '<p>XML Database successfully opened.</p>';
}
foreach ($xml->row as $row) {
    $indx = $row->indxNo;
    echo $indx;
    if ($row->dataProp->prop->count() !== 0) {
        foreach ($row->dataProp->prop as $pdat) {
            $type = mysqli_real_escape_string($link,"P");
            $plbl = $pdat->plbl->__toString();
            $lbl = mysqli_real_escape_string($link,$plbl);
            $purl = $pdat->purl->__toString();
            $url = mysqli_real_escape_string($link,$purl);
            $pcot = $pdat->pcot->__toString();
            $cot = mysqli_real_escape_string($link,$pcot);
            $preq = "INSERT INTO GPSDAT (indxNo,datType,label,url,clickText) " .
                "VALUES ('{$indx}','P','{$lbl}','{$url}','{$cot}');";
            }
            $presult = mysqli_query($link,$preq);
            if (!$presult) {
                die ("load_GPSDAT.php: failed to load propdat at row {$indx}: " .
                    mysqli_error($link));
            }
    }
    if ($row->dataAct->act->count() !== 0) {
        foreach ($row->dataAct->act as $adat) {
            echo $a . " ";
            $type = mysqli_real_escape_string($link,"A");
            $albl = $adat->albl->__toString();
            $lbl = mysqli_real_escape_string($link,$albl);
            $aurl = $adat->aurl->__toString();
            $url = mysqli_real_escape_string($link,$aurl);
            $acot = $adat->acot->__toString();
            $cot = mysqli_real_escape_string($link,$acot);
            $areq = "INSERT INTO GPSDAT (indxNo,datType,label,url,clickText) " .
                "VALUES ('{$indx}','A','{$lbl}','{$aurl}','{$cot}');";
            $aresult = mysqli_query($link,$areq);
            if (!$aresult) {
                die ("load_GPSDAT.php: failed to load actdat at row {$indx}: " .
                    mysqli_error($link));
            }
        }
    }
}
?>
<p>DONE!</p>
</div>
</body>
</html>
