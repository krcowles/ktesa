
<?php
/**
 * This script presents the html that comprises the top-of-the-page panel.
 * It consists of two basic parts: a navigation bar with drop-down menus,
 * and a ktesa logo div, which contains a pattern bar (div) and the text
 * and images for the logo. The menus have some variable content controlled
 * by php and javascript: e.g. icon showing which page is currently active;
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../accounts/getLogin.php";
?>
<div id="panel">
    <!-- Navigation Bar -->
    <div id="navbar">
        <ul id="allMenus">
            <li id="explore" class="menu-main">
                <div class="menu-item">
                    <span class="menu-text">Explore&nbsp;</span>
                    <div class="menuIcons menu-open"></div>
                </div>
                <div id="menu-explore" class="menu-default">
                    <ul class="menus">
                        <li><div id="home">Home</div></li>
                        <li><div id="table">Table Only</div></li>
                        <li id="ifadmin"><div id="atools">Admintools</div></li>
                        <li><div id="yours">Show Favorites</div></li>
                    </ul>
                </div>
            </li>
            <li id="contrib" class="menu-main">
                <div class="menu-item">
                    <span class="menu-text">Contribute&nbsp;</span>
                    <div class="menuIcons menu-open"></div>
                </div>
                <div id="menu-contrib" class="menu-default">
                    <ul class="menus">
                        <li><div id="newPg">Create New Page</div></li>
                        <li><div id="edits">Continue Editing Your Pages</div></li>
                        <li><div id="epubs">Edit A Published Page</div></li>
                        <li><div id="pubReq">Submit for Publication</div></li>
                    </ul>
                </div>
            </li>
            <li id="members" class="menu-main">
                <div class="menu-item">
                    <span class="menu-text">Members&nbsp;</span>
                    <div class="menuIcons menu-open"></div>
                </div>
                <div id="menu-members" class="menu-default">
                    <ul class="menus">
                        <li><div id="lin">Log in</div></li>
                        <li><div id="lout">Log out</div></li>
                        <li><div id="chgpass">Change Password</div></li>
                        <li><div id="forgot">Forgot Password</div></li>
                        <li><div id="join">Become a Member</div></li>
                    </ul>
                </div>
            </li>
            <li id="help" class="menu-main">
                <div class="menu-item">
                    <span class="menu-text">Help&nbsp;</span>
                    <div class="menuIcons menu-open"></div>
                </div>
                <div id="menu-help" class="menu-default">
                <ul class="menus">
                    <li><div id="about">About this site</div></li>
                    <li id="ifuser"><div id="ctoggle">Reject Cookies</div></li>
                    <li id="policy"><div id="privacy">Privacy Policy</div></li>
                    <li><div id="contact">Contact Us</a></div></li>
                </ul>
                </div>
            </li>
        </ul>
    </div>
    <!-- ktesa Logo -->
    <div id="logo">
        <div id="pattern"></div> <!-- ktesa pattern bar -->
        <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
        <p id="logo_left">Hike New Mexico</p>
        <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        <p id="logo_right">w/Tom &amp; Ken</p>
    </div>
    <a id="support" href="mailto:admin@nmhikes.com">Support</a>
</div>
<p id="cookie_state"><?=$_SESSION['cookie_state'];?></p>
<?php if (isset($_SESSION['cookies'])) : ?>
<p id="cookies_choice"><?=$_SESSION['cookies'];?></p>
<?php endif; ?>

<?php if (isset($admin) && $admin) : ?>
<p id="admin">admin</p>
<?php endif; ?>

<!-- the current environment: -->
<p id="appMode"><?=$appMode;?></p>

<!-- Modal Windows HTML -->
<div id="email_password">
    You will be assigned a temporary login:<br />
    <form id="emailform" action="#" method="post">
        <div id="maildata">
            <span id="mailtxt">Please enter your email address</span><br />
            <input id="femail" type="email" name="femail" required /><br />
        </div>
        <button id="sendmail">Send email</button>
        <br />
    </form>
</div>

<script src="../scripts/modal_setup.js"></script>
<script src="../scripts/menuControl.js"></script>
<script src="../scripts/validateUser.js"></script>

