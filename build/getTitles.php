<?php
/**
 * This module provides the startNewPg.js a means to ajax in the
 * data containing all currenly pusblished hike titles.
 * 
 * @package Page_Creation
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
/** 
 * The following provides db functions needed by this script
 */
require '../mysql/dbFunctions.php';
$link = connectToDb(__FILE__, __LINE__);
$dropQuery =  "DROP PROCEDURE IF EXISTS getTitles";
doQuery($link, $dropQuery, __FILE__, __LINE__);
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
doQuery($link, $titles, __FILE__, __LINE__);
$setVar = "SET @title_list = '';";
doQuery($link, $setVar, __FILE__, __LINE__);
$doProc = "CALL getTitles(@title_list);";
doQuery($link, $doProc, __FILE__, __LINE__);
$result = mysqli_query($link, "SELECT @title_list;") or die(
    __FILE__ . ": Failed to retrieve list of titles " . mysqli_error($link)
);
$row = mysqli_fetch_row($result);
$tlist = explode("^", $row[0]);
// NOTE: initializing title_list to '' is REQUIRED, but seems to create an empty name
array_shift($tlist);
// convert to javascript array:
$hnames = json_encode($tlist);
mysqli_free_result($result);
echo $hnames;
