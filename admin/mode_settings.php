<?php
/**
 * This file contains the variables that define the environment for the 
 * current version of code. Each version (e.g. host server or local machine:
 * main version, any test version in sub-directories, etc) will have a unique
 * copy of this file, the variables of which can be toggled via the admin tools.
 * There are currently three variables:
 *   1. dbState: 'test' or 'main', defining which db will be connected
 *   2. appMode: 'development' or 'production', defining the error methodology
 *   3. editing: 'yes' or 'no', depending on whether this site allows hke edits
 * PHP Version 7.4
 *
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$dbState = 'main';
$appMode = 'production';
$editing = 'yes';
 