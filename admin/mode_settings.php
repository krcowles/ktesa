<?php
/**
 * This file contains the variables that define the environment for the 
 * current version of code. Each version (e.g. host server or local machine:
 * main version, any test version in sub-directories, etc) will have a unique
 * copy of this file, the variables of which can be toggled via the admin tools.
 * There are currently two variables:
 *   1. dbState: 'test' or 'main', defining which db will be connected
 *   2. appMode: 'development' or 'production', defining the error methodology
 * The default states are 'main' and 'production'.
 * PHP Version 7.0
 *
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$dbState = 'main';
$appMode = 'production';
 