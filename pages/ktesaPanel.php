<?php
/**
 * This script presents the html that comprises the top-of-the-page
 * menu-driven navigation bar and ktesa logo for every viewable page.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../accounts/getLogin.php";
$policy = urlencode("PrivacyPolicy.pdf");

// imbedded MySQL 'COUNT' function crashing on server, but not on localhost...so:
if (isset($_SESSION['userid'])) {
    $euser = $_SESSION['userid'];
    if ($euser === '1' || $euser === '2') {
        $edits = "SELECT `indxNo` FROM `EHIKES`;";
    } else {
        $edits = "SELECT `indxNo` FROM `EHIKES` WHERE `usrid`={$euser};";
    }
    $ecount = $pdo->query($edits)->fetchAll(PDO::FETCH_ASSOC);
    $user_ehikes = count($ecount); // no. of hikes currently in edit by user
} else {
    $user_ehikes = 0;
}
?>

<script type="text/javascript">
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
    // New: Panel sub-menu js 
    document.addEventListener("DOMContentLoaded", function(){
        // make it as accordion for smaller screens
        if (window.innerWidth < 992) {
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

<!-- 'navbar-dark' class results in a light-colored collapsed icon ("hampurger") -->
<p id="uhikes" style="display:none"><?=$user_ehikes;?></p>
<nav id="nav" class="navbar navbar-expand-sm navbar-dark">
    <div class="container-fluid"> 
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#ktesaMenu" aria-controls="ktesaMenu"
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ktesaMenu">
            <a class="navbar-brand" href="../pages/about.php">
                <img src="../images/logo32.png" alt="Brand Icon" />
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
                            href="../edit/hikeEditor.php?age=new&show=all">
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
                        href="#">Filter Hikes... &raquo;</a>
                        <ul class="submenu dropdown-menu">
                            <li><a class="dropdown-item" id="fhmiles"
                                href="#">Miles from hike</a></li>
                            <li><a class="dropdown-item" id="fhloc"
                                href="#">Miles from location</a></li>
                        </ul>
                    </li>
                    <li><a id="sorter" class="dropdown-item"
                        href="#">Sort Options... &raquo;</a>
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
                            href="../accounts/unifiedLogin.php?form=log">Login</a>
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
                        <li><a id="updte_sec" class="dropdown-item" href="#">
                            Security Questions</a></li>
                    </ul>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    Help
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a id="aboutpg" class="dropdown-item"
                            href="../pages/about.php">About This Site</a>
                        </li>
                        <li><a class="dropdown-item" 
                            href="../php/postPDF.php?doc=<?=$policy;?>">
                            Privacy Policy</a>
                        </li>
                        <li id="change_cookies"><a id="usrcookies"
                            class="dropdown-item" href="#">Accept Cookies</a>
                        </li>
                    </ul>
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
<div id="membennies" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Membership Explained</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ap" class="modal-body">
            <p>Membership is <em>free</em>. And as a member, you can create
                your own hike page, or edit an existing one. All you
                need is a gpx track file(s), photos taken during the
                hike, a good description, and external references, if
                any (books, weblinks, blogs, etc).</p>
            <p>Another benefit is that you can save 'favorites' and map
                them on a separate page (Explore->Show Favorites)</p>
            <p>Join now and start creating!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- login data -->
<p id="cookie_state"><?= $_SESSION['cookie_state'];?></p>
<?php if (isset($_SESSION['cookies'])) : ?>
<p id="cookies_choice"><?= $_SESSION['cookies'];?></p>
<?php endif; ?>
<?php if (isset($admin) && $admin) : ?>
<p id="admin">admin</p>
<?php endif; ?>

<?php require "../pages/modals.php"; ?>

<script src="../scripts/menuControl.js"></script>
<script src="../scripts/panelMenu.js"></script>
<script src="../scripts/sendResetMail.js"></script>
