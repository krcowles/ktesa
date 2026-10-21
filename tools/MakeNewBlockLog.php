<?php
/**
 * This function will set up the current BLOCK_LOG table for the field 'subnet',
 * added recently. It will then create a new BLOCK_LOG table (BLOCK_LOG_NEW)
 * and make sure that the subnet column is a unique key to reduce the table size.
 * The new table is then stuffed with the old tables fields to ensure current data.
 * PHP VERSION 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
// Step by step change of BLOCK_LOG:
$update_log = "UPDATE `BLOCK_LOG` SET `subnet` = SUBSTRING_INDEX(`bip`, '.', 3)" . " WHERE `bip` NOT LIKE '%:%';";
$make_table = "CREATE TABLE `BLOCK_LOG_NEW` LIKE `BLOCK_LOG`;";
$alter_table = "ALTER TABLE `BLOCK_LOG_NEW` ADD UNIQUE KEY `uq_subnet` " .
"(`subnet`);";
$fill_table = <<<NEWTABLE
INSERT INTO `BLOCK_LOG_NEW` (`bip`, `subnet`, `hits`, `bdatetime`, `last_seen`)
SELECT MIN(`bip`), `subnet`, COUNT(*), MIN(`bdatetime`), MAX(`bdatetime`)
FROM `BLOCK_LOG`
GROUP BY `subnet`;
NEWTABLE;
$table_swap = "RENAME TABLE `BLOCK_LOG` TO `BLOCK_LOG_OLD`, `BLOCK_LOG_NEW` " . "TO `BLOCK_LOG`;";
$updater = $pdo->query($table_swap);
echo "DONE";
