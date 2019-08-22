<?php
/**
 * This function tests whether or not a cookie will be stored by the client
 * by using an http redirect. One limitation is that cookies may not remain 
 * disabled for a long time, and the method could potentially increase
 * session-hacking risk. Code adopted from
 * https://stackoverflow.com/questions/531393/
 *  how-to-detect-server-side-whether-cookies-are-disabled
 * PHP Version 7.1
 * 
 * @package Display
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

/**
 * This function attempts to send a cookie to the client, then, via
 * a redirect header, checks to see if it has been set.
 * 
 * @return boolean A boolean value as a result of the enquiry.
 */
function clientCookieEnabled()
{
    $cn = 'cookie_enabled';
    if (isset($_COOKIE[$cn])) {
        return true;
    } elseif (isset($_SESSION[$cn]) && $_SESSION[$cn] === false) {
        return false;
    }
    // saving cookie ... and after it we have to redirect to get this
    setcookie($cn, '1');
    // redirect to get the cookie
    if (!isset($_GET['nocookie'])) {
        header("location: ".$_SERVER['REQUEST_URI'].'?nocookie');
    }
    // cookie isn't available
    $_SESSION[$cn] = false;
    return false;
}
