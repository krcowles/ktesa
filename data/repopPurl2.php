<?php
require_once '../mysql/setenv.php';
$xml = simpleXml_load_file('database.xml');
if ($xml === false) {
    die( "No xml load");
}
$purl2s = [];
$rownos = [];
foreach($xml->row as $row) {
    if (strlen($row->spUrl) > 0) {
        $hno = $row->indxNo->__toString();
        $url = $row->spUrl->__toString();
        array_push($rownos,$hno);
        array_push($purl2s,$url);
    }
}
for ($i=0; $i<count($rownos); $i++) {
    $r = intval($rownos[$i]);
    echo "   --row:" . $r;
    $newurl = mysqli_real_escape_string($link,$purl2s[$i]);
    $updateReq = "UPDATE HIKES SET purl2 = '{$newurl}' WHERE indxNo = {$r};";
    $updt = mysqli_query($link,$updateReq);
    if (!$updt) {
        die("Failed on indxNo = {$r}: " . mysqli_error($link));
    }
}
mysqli_free_result($updt);
?>
