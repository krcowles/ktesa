<?php
/**
 * This script presents the html that comprises the top-of-the-page
 * menu-driven navigation bar and ktesa logo for every viewable page.
 * See mobileNavbar.php for mobile implementation. All bootstrap submenu
 * operation derived from: https://bootstrap-menu.com/detail-multilevel.html
 * NOTE: Recent addition of 'Own This Site!' animation compliments of
 * https://alvarotrigo.com/blog/css-text-animations/ (with mods);
 * Every call for the panel is preceded by session_start and global_boot.php
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../accounts/getLogin.php";
$policy = urlencode("PrivacyPolicy.pdf");

// imbedded MySQL 'COUNT' function crashing on server, but not on localhost...so:
if (isset($_SESSION['userid'])) {
    if (isAdmin()) {
        $edits = "SELECT `indxNo` FROM `EHIKES`;";
    } else {
        $edits
            = "SELECT `indxNo` FROM `EHIKES` WHERE `usrid`={$_SESSION['userid']};";
    }
    $ecount = $pdo->query($edits)->fetchAll(PDO::FETCH_ASSOC);
    $user_ehikes = count($ecount); // no. of hikes currently in edit by user
} else {
    $user_ehikes = 0;
}
?>

<script type="text/javascript">
    <?php if ($mobileTesting) : ?>
    var mobile = true;
    <?php else : ?>
    var isMobile, isTablet, isAndroid, isiPhone, isiPad, mobile;
    isMobile = navigator.userAgent.toLowerCase().match(/mobile/i) ? 
        true : false;
    isTablet = navigator.userAgent.toLowerCase().match(/tablet/i) ?
        true : false;
    isAndroid = navigator.userAgent.toLowerCase().match(/android/i) ?
        true : false;
    isiPhone = navigator.userAgent.toLowerCase().match(/iphone/i) ?
        true : false;
    isiPad = navigator.userAgent.toLowerCase().match(/ipad/i) ?
        true : false;
    mobile = isMobile && !isTablet;
    <?php endif; ?>
    // New: Panel sub-menu js 
    document.addEventListener("DOMContentLoaded", function(){
        // make it as accordion for smaller screens
        if (window.innerWidth < 800) {
            // close all inner dropdowns when parent is closed
            document.querySelectorAll('.navbar .dropdown').forEach(
                function(everydropdown){
                everydropdown.addEventListener('hidden.bs.dropdown', function () {
                // after dropdown is hidden, then find all submenus
                    this.querySelectorAll('.submenu').forEach(function(everysubmenu){
                    // hide every submenu as well
                    everysubmenu.style.display = 'none';
                    });
                })
            });
            document.querySelectorAll('.dropdown-menu a').forEach(function(element){
                element.addEventListener('click', function (e) {
                    let nextEl = this.nextElementSibling;
                    if(nextEl && nextEl.classList.contains('submenu')) {
                        // prevent opening link if link needs to open dropdown
                        e.preventDefault();
                        if(nextEl.style.display == 'block') {
                            nextEl.style.display = 'none';
                        } else {
                            nextEl.style.display = 'block';
                        }
                    }
                });
            });
        }
    }); 
</script>
<style>
    .animate-character {
        text-transform: uppercase;
        background-image: linear-gradient(
            -225deg,
            lightsteelblue 0%,
            paleturquoise 29%,
            palevioletred 67%,
            navajowhite 100%
        );
        background-size: auto auto;
        background-clip: border-box;
        background-size: 200% auto;
        color: #fff;
        background-clip: text;
        /*text-fill-color: transparent;*/
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: textclip 2s linear infinite;
        display: inline-block;
        font-size: 16px;
        position: relative;
        top: 8px;
        left: 8px;
    }

    @keyframes textclip {
        to {
            background-position: 200% center;
        }
        }
 </style>

<!-- 'navbar-dark' class results in a light-colored collapsed icon ("hamburger") -->
<p id="uhikes" style="display:none"><?=$user_ehikes;?></p>
<p id="appMode" style="display:none;"><?=$appMode;?></p>
<p id="editMode" style="display:none;"><?=$editing;?></p>
<nav id="nav" class="navbar navbar-expand-sm navbar-dark">
    <div class="container-fluid"> 
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#ktesaMenu" aria-controls="ktesaMenu"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ktesaMenu">
            <!-- Hidden brand when collapsed -->
            <a class="navbar-brand" href="../pages/about.php">
                <img src="../images/logos/logo32.png" alt="Brand Icon" />
            </a>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                        id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Explore
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a id="homepg" class="dropdown-item"
                            href="../index.html" target="_self">Home</a>
                        </li>
                        <li><a id="tblpg" class="dropdown-item"
                            href="../pages/tableOnly.php">Table Page</a>
                        </li>
                        <li><a id="favpg" class="dropdown-item"
                            href="../pages/favTable.php">Show Favorites</a>
                        </li>
                        <div id="admintools">
                            <div class="dropdown-divider"></div>
                            <li><a id="adminpg" class="dropdown-item"
                                href="#">Admintools</a></li>
                        </div>
                    </ul>
                </li>
                <li id="contrib" class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                        id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Contribute
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a id="createpg" class="dropdown-item"
                            href="../edit/startNewPg.php">Create New Page</a>
                        </li>
                        <li><a id="conteditpg" class="dropdown-item"
                            href="../edit/hikeEditor.php?age=new">
                            Continue Editing Your Page</a>
                        </li>
                        <li><a id="editpubpg" class="dropdown-item"
                            href="../edit/hikeEditor.php?age=old">
                            Edit a Published Page</a>
                        </li>
                        <li><a id="pubreqpg" class="dropdown-item"
                            href="../edit/hikeEditor.php?age=new&pub=usr">
                            Submit for Publication</a>
                        </li>
                    </ul>
                </li>
                <li id="homepgfilt" class="nav-item dropdown" id="myDropdown">
                <a class="nav-link dropdown-toggle" href="#"
                        data-bs-toggle="dropdown"> 
                    Filter / Sort
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item"
                        href="#">Filter Hikes... &rArr;</a>
                        <ul class="submenu dropdown-menu">
                            <li><a class="dropdown-item" id="fhmiles"
                                href="#">Miles from hike</a></li>
                            <li><a class="dropdown-item" id="fhloc"
                                href="#">Miles from location</a></li>
                        </ul>
                    </li>
                    <li><a id="sorter" class="dropdown-item"
                        href="#">Sort Options... &rArr;</a>
                        <ul class="submenu dropdown-menu">
                            <li><a class="dropdown-item" id="sort_rev"
                                href="#">Reverse Sort Order</a></li>
                            <li><a class="dropdown-item" id="sort_diff"
                                href="#">Sort by Difficulty</a></li>
                            <li><a class="dropdown-item" id="sort_dist"
                                href="#">Sort by Hike Length</a></li>
                            <!-- Nice to have later...
                            <li><a class="dropdown-item" id="sort_last"
                                href="#">Sort by Last Hiked Date</a></li> -->
                        </ul>
                    </li>
                </ul>
                </li>
                <li class="nav-item">
                    <a id="editgpx" class="nav-link active" aria-current="page"
                        href="#">Edit GPX File
                    </a>
                </li>
                </li>
                <li id="memspace" class="nav-item">
                    &nbsp;&nbsp;&nbsp;
                </li>
                <li id="benefits" class="nav-item">
                    <a id="benies" class="nv-link active" aria-current="page"
                        href="#">
                        <img id="memben" src="../images/benies.png"
                        alt="Member Benefits" />
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Members
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a id="login" class="dropdown-item"
                            href="#">Login</a>
                        </li>
                        <li><a id="logout" class="dropdown-item"
                            href="#">Logout</a>
                        </li>
                        <li><a id="chg" class="dropdown-item"
                            href="#">Change Password</a>
                        </li>
                        <li><a id="bam" class="dropdown-item"
                            href="../accounts/unifiedLogin.php?form=join"
                            target="_self">Become a Member</a>
                        </li>
                        <li><a id="updte_sec" class="dropdown-item" href="#">
                            Security Questions</a></li>
                    </ul>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    More&hellip;
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a id="latest" class="dropdown-item" href="#">
                            Recent Hikes</a>
                        </li>
                        <li id="hiking_club"><a class="dropdown-item"
                            href="../pages/clubAssets.php">Club Assets</a>
                        </li>
                        <li><a id="aboutpg" class="dropdown-item"
                            href="../pages/about.php">About This Site</a>
                        </li>
                        <li><a class="dropdown-item" 
                            href="../php/postPDF.php?doc=<?=$policy;?>">
                            Privacy Policy</a>
                        </li>
                    </ul>
                </li>
                <li id="owner" class="nav-item">
                    <a id="ownit" class="nv-link active" aria-current="page"
                        href="../pages/ownership.php">
                        <span class="animate-character">Own this site!</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
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
<!-- login data [cookie_state is always assigned] -->
<p id="cookie_state"><?= $_SESSION['cookie_state'];?></p>

<?php $enrolled = $_SESSION['club_member'] ?? "N"; ?>
<p id="club_member" style="display:none;"><?=$enrolled;?></p>

<?php if (isset($admin) && $admin) : ?>
<p id="admin">admin</p>
<?php endif; ?>

<?php require "../pages/panelModals.php"; // all modals required by ktesaPanel ?>

<script src="../scripts/menuControl.js"></script>
<script src="../scripts/panelMenu.js"></script>
