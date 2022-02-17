<?php
/**
 * When a user modifies (or simply reviews) the security questions via the
 * 'Members->Update Sec. Questions' submenu, the questions list and 
 * corresponding answers are updated in the Users table.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require_once "../php/global_boot.php";

$ques_str = filter_input(INPUT_POST, 'questions');
$answer1  = strToLower(filter_input(INPUT_POST, 'an1'));
$answer2  = strToLower(filter_input(INPUT_POST, 'an2'));
$answer3  = strToLower(filter_input(INPUT_POST, 'an3'));
$userid   = filter_input(INPUT_POST, 'ix');

$UpdateReq = "UPDATE `USERS` SET `questions`=?,`an1`=?,`an2`=?,`an3`=? WHERE ".
    "`userid`=?;";
$update = $pdo->prepare($UpdateReq);
$update->execute([$ques_str, $answer1, $answer2, $answer3, $userid]);
echo "ok";
