<?php
/**
 * This script will display all of the information relative to the 
 * site's php installation. Default is INFO_ALL, which shows everything.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
phpinfo();
// Show just the module information:
// phpinfo(8) yields identical results.
// phpinfo(INFO_MODULES);
