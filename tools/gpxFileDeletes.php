<?php
require "../php/global_boot.php";
$GPXfilenosDeleteReq = "DELETE FROM `GPX` WHERE `fileno`=?;";
$filenos = array("316", "317", "318", "319", "320", "321");
foreach ($filenos as $no) {
    $GPXdeleteRow = $gdb->prepare($GPXfilenosDeleteReq);
    $GPXdeleteRow->execute([$no]);
}
echo "OK";
