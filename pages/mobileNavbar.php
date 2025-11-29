<?php
/**
 * This script pertains to mobile usage, and adds the 'tools'
 * menu, offering options for offline maps. Note that some
 * features, such as the logo and brand icon, are omitted in certain
 * cases where more space is desired, such as offline map pages. In
 * these cases, it is expected that the page is invoked with the query
 * string "?logo=no". The appended javascript platform detection
 * sets up availability for all the associated page globals. Some
 * code is provided to test on a non-mobile platform, and is to be
 * commented out for production.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../accounts/getLogin.php";
require "../admin/mode_settings.php";
$use_logo = isset($_GET['logo']) ? false : true;
$js_logo  = $use_logo ? 'true' : 'false';
$logged_in = isset($_SESSION['userid']) ? true : false;
$policy = urlencode("PrivacyPolicy.pdf");
?>
<p id="appMode" style="display:none"><?=$appMode;?></p>
<!-- navbar-dark cause light collapsed icon (hampurger) -->
<nav id="nav" class="navbar navbar-expand-sm navbar-dark">
    <div class="container-fluid"> 
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#ktesaMenu" aria-controls="ktesaMenu"
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ktesaMenu">
            <?php if ($use_logo) : ?>
            <a id="maxPanel" class="navbar-brand" href="../pages/about.php">
                <img src="../images/logos/logo32.png" /></a>
            <?php endif; ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                        id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Explore
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item"
                            href="../pages/mapOnly.php">Map Page</a>
                        </li>
                        <li><a class="dropdown-item"
                            href="../pages/responsiveTable.php">Table Page</a>
                        </li>
                        <li><a class="dropdown-item"
                            href="../pages/responsiveFavs.php">Show Favorites</a>
                        </li>
                        <div id="admintools">
                            <div class="dropdown-divider"></div>
                            <li><a id="adminmenu" class="dropdown-item"
                                href="../admin/admintools.php">Admintools</a></li>
                        </div>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Members
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a id="login" class="dropdown-item"
                            href="../accounts/unifiedLogin.php?form=log"
                            target="_self">Login</a>
                        </li>
                        <li><a id="logout" class="dropdown-item"
                            href="#">Logout</a>
                        </li>
                        <li><a id="chg" class="dropdown-item"
                            href="#">Change Password</a>
                        </li>
                        <li><a id="bam" class="dropdown-item"
                            href="../accounts/unifiedLogin.php?form=reg"
                            target="_self">Become a Member</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Tools
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item"
                            href="../pages/saveOffline.php?logo=no"
                            target="_blank">Save Offline Map</a>
                        </li>
                        <li><a class="dropdown-item"
                            href="../pages/useOffline.html"
                            target="_blank">Use Offline Map</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Help
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item"
                            href="../pages/responsiveAbout.php">About This Site</a>
                        </li>
                        <li><a class="dropdown-item"
                            href="../php/postPDF.php?doc=<?=$policy;?>"
                            target="_blank">Privacy Policy</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php if ($use_logo) : ?>
<!-- ktesa Logo -->
<div id="logo">
    <div id="pattern"></div> <!-- ktesa pattern bar -->
    <div id="leftside" class="logo_items">
        <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
        <p id="logo_left">Hike New Mexico</p>
    </div>
    <div id="ctr" class="logo_items"></div>
    <div id="rightside" class="logo_items">
        <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        <p id="logo_right">w/Tom &amp; Ken</p>
    </div>
</div>
<?php endif; ?>

<!-- login data -->
<p id="cookie_state"><?= $_SESSION['cookie_state'];?></p>
<?php if (isset($admin) && $admin) : ?>
<p id="admin">admin</p>
<?php endif; ?>

<!-- Change Password Modal -->
<div id="cpw" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You will receive an email to reset/change your password<br />
                Enter email: <input id="cpwmail" type="email"
                    required="required" /></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary"
                    id="send">Send Email</button>
            </div>
        </div>
    </div>
</div>
<!-- Login modal when lockout condition is encountered -->
<div class="modal fade" id="lockout" tabindex="-1"
    aria-labelledby="Lockout" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    You are locked out</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You are currently locked out and can login again in 
                <span class="lomin"></span> minutes. You may continue
                to wait, or you may reset your password by selecting
                that option below.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Wait</button>
                <button id="force_reset" type="button" class="btn btn-success">
                    Reset my password</button>
            </div>
        </div>
    </div>
</div>
<!-- info modal when ajax errors occur -->
<div id="ajaxerr" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">An Error Has Occurred</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                We are sorry, but an error has occurred. The admin has been notified.
                We apologize for any inconvenience.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../scripts/ktesaOfflineDB.js"></script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/loginState.js"></script>
<script src="../scripts/cacheDeleteFct.js"></script>
<script src="../scripts/navMenu.js"></script>
