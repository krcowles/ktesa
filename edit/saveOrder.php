<?php
/**
 * This script will save the current photo ordering to the ETSV table
 * by using the sort id's provided via the post.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('ajax');

$order = filter_input(INPUT_POST, 'sort');
$sort = json_decode($order);

for ($j=0; $j<count($sort); $j++) {
    $newSortReq = "UPDATE `ETSV` SET `org` = ? WHERE `picIdx` = ?;";
    $newSort = $pdo->prepare($newSortReq);
    $newSort->execute([$j, $sort[$j]]);
}
