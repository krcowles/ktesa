<?php
/** 
 * Restore columns so that lat follow lng followed by preview
 */
require "../php/global_boot.php";
$restoreReq = "ALTER TABLE `EHIKES` CHANGE COLUMN " .
    "`preview` `preview` VARCHAR(100) AFTER `lng`;";
$pdo->query($restoreReq);
echo "RESTORED";
