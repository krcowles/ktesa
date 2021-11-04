<?php
require "../php/global_boot.php";

$nextReq = "SHOW TABLE STATUS FROM `nmhikesc_main`;";
$nexts = $pdo->query($nextReq)->fetchAll(PDO::FETCH_ASSOC);
foreach ($nexts as $next) {
    echo $next['Name'] . ": " . $next['Auto_increment'] . "<br />";
}