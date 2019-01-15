<?php
/**
 * This module provides the startNewPg.js a means to ajax in the
 * data containing all currenly pusblished hike titles. NOTE:
 * PDO w/procedures is slightly different than standard PDO db access.
 * PHP Version 7.1
 * 
 * @package Page_Creation
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require '../php/global_boot.php';
$pdo->exec("DROP PROCEDURE IF EXISTS `getTitles`");
$titles = <<<Proc
CREATE PROCEDURE getTitles(INOUT title_list VARCHAR(10000))
BEGIN
    DECLARE eoloop INTEGER DEFAULT 0;
    DECLARE pt VARCHAR(30) DEFAULT "";
    DECLARE pt_cursor CURSOR FOR 
        SELECT pgTitle FROM HIKES;
    DECLARE CONTINUE HANDLER
        FOR NOT FOUND SET eoloop = 1;
    OPEN pt_cursor;
    get_pgtitles: LOOP
        FETCH pt_cursor INTO pt;
        IF eoloop = 1 THEN
            CLOSE pt_cursor;
            LEAVE get_pgtitles;
        END IF;
        SET title_list = CONCAT(title_list,"^",pt);
    END LOOP get_pgtitles;
END;
Proc;
$pdo->exec($titles);
$pdo->exec("SET @title_list = '';");
$pdo->exec("CALL getTitles(@title_list);");
$result = $pdo->query("SELECT @title_list;");
$row = $result->fetch(PDO::FETCH_NUM);
$tlist = explode("^", $row[0]);
// NOTE: initializing title_list to '' is REQUIRED, but seems to create an empty name
array_shift($tlist);
// convert to javascript array:
$hnames = json_encode($tlist);
echo $hnames;
