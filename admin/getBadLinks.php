<?php
/**
 * This maintenance script will walk through the links found on the hike
 * pages reference section (REFS table) in order to validate their existence.
 * Some sites become obsolete and may need to be updated. Since the php
 * get_headers() function seems to generate errors and NOT return 'false'
 * as the manual would suggest, it is necessary to handle those errors
 * by throwing an ErrorException [lines18-21], which can then be seen by a
 * try-catch block. This catches, at least, timeouts when a site no longer
 * exists. Note that because of timeouts, this script can take quite awhile
 * to complete. Also, when attempting to get headers from an unsecured http 
 * site, it is necessary to introduce the stream_context_set_default() function
 * in order to prevent more errors :: thanks to Stackoverflow.com:
 * https://stackoverflow.com/questions/40830265/php-errors-with-get-headers-and-ssl
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$first = filter_input(INPUT_POST, 'first');
$size = filter_input(INPUT_POST, 'size');

set_error_handler(
    function ($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }   
);
// prepare get_headers() for SSL problems with http sites:
stream_context_set_default(
    [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]
);
$caller = 'links';
require "getRefsLinks.php";

// begin the validation process

$hike_nos = [];
for ($i=$first; $i<$first+$size; $i++) {
    try {
        $hdrs = get_headers($links[$i]);
    } catch (Exception $ex) {
        array_push($bad_lnks, $links[$i]);
        array_push($hike_nos, $hikenos[$i]);
    }
}
// Format for use in javascript ajax: contents of url array must be strings
//$jsLinks = [];
//foreach ($bad_lnks as $bad) {
//    array_push($jsLinks, "'" . $bad . "'");
//}
$return_data = [$hike_nos, $bad_lnks];
echo json_encode($return_data);
