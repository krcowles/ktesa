<?php
/**
 * Drop CLUSTERS
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$pdo->query("DROP TABLE `CLUSTERS`;");
$pdo->query("DROP TABLE `CLUSHIKES`;");
header("Location: admintools.php");
