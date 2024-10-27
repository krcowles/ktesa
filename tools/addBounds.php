<?php
/**
 * This is a temporary script to update the HIKES/EHIKES tables
 * with a column called 'bounds' which holds the hikes boundbox
 * as a comma-separated list.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require '../php/global_boot.php';

$addHikeBoxReq  = "ALTER TABLE `HIKES`  ADD COLUMN `bounds` VARCHAR(42) AFTER `lng`;";
$addEhikeBoxReq = "ALTER TABLE `EHIKES` ADD COLUMN `bounds` VARCHAR(42) AFTER `lng`;";
$pdo->query($addHikeBoxReq);
$pdo->query($addEhikeBoxReq);
