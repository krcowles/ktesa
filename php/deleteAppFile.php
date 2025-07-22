<?php
/**
 * Simple script to remove a file from the appGpxFiles directory
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$file2delete = filter_input(INPUT_POST, 'fname');
$loc = "../appGpxFiles/{$file2delete}";
unlink($loc);
