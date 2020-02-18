<?php
/**
 * This script presents the html that comprises the top-of-the-page panel.
 * It consists of two basic parts: a navigation bar with drop-down menus,
 * and a ktesa logo div, which contains a pattern bar (div) and the text
 * and images for the logo. The menus have some variable content controlled
 * by php and javascript: e.g. icon showing which page is currently active;
 * disabled log out when not logged in, etc.
 * PHP Version 7.1
 * 
 * @package Main
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../accounts/getLogin.php";
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
                        <li><div id="yours">Your Hikes</div>
                            <ul class="subs">
                                <!--
                                <li><div id="pubs">View Published Hikes</div></li>
                                -->
                                <li><div id="viewEds">View In-Edit Hikes</div></li>
                            </ul>
                        </li>
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
                        <li><div id="newPg">Create New Hike</div></li>
                        <li><div id="edits">Continue Editing Your Hikes</div></li>
                        <li><div id="epubs">Edit Your Published Hike</div></li>
                        <!--
                        <li><div id="pubReq">Submit for Publication</div></li>
                        -->
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
</div>
<p id="login_result"><?= $uname;?></p>
<p id="cookieStatus"><?= $cstat;?></p>
<div id="usr_login">
    <table id="loginTbl">
        <tbody>
            <tr>
                <td>User name:</td>
                <td>
                    <input id="usrid" type="text" size="20" name="name" />
                </td>
            </tr>
            <tr>
                <td>User Password:</td>
                <td>
                    <input id="upass" type="password" name="password" size="20" />
                </td>
            <tr>
        </tbody>
    </table>
    <button id="enter">Login</button><br />
</div>
<script src="../accounts/getLogin.js"></script>
<script src="../scripts/modal_setup.js"></script>
