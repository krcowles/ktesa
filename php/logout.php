<?php
/**
 * This is a simple script to log out the curent user.
 * PHP Version 7.1
 * 
 * @package Main
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
setcookie('nmh_mstr', '', 0, '/');
setcookie('nmh_id', '', 0, '/');
echo "Done";
