<?php
/**
 * This script will replace the contents of LKUSERS with the contents
 * of USERS. LKUSERS is used to check when one or more new users have
 * been added to the db on the site.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$dropReq = "DROP TABLE `LKUSERS`;";
$dropper = $pdo->query($dropReq);
$copyReq = "CREATE TABLE `LKUSERS` SELECT * FROM `USERS`";
$copier  = $pdo->query($copyReq);
echo "Done";
