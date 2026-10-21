<?php
/**
 * Before altering...
 * PHP VERSION 8.3.9

 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$sql = <<<SQL
SELECT t.net24, COUNT(*) AS hits
FROM (
    SELECT SUBSTRING_INDEX(`bip`, '.', 3) AS net24
    FROM `BLOCK_LOG`
) AS t
GROUP BY t.net24
ORDER BY hits DESC;
SQL;
$hits = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
foreach ($hits as $hit) {
    echo "{$hit['net24']} : {$hit['hits']}<br>";
}