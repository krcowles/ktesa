<?php
/**
 * This script presents the html that comprises the top-of-the-page
 * menu-driven navigation bar and ktesa logo. The supplied incoming
 * variable [$source] indicates whether for a mobile device or other.
 * When mobile, the 'Contribute' menu will not appear.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../accounts/getLogin.php";
$policy = "../accounts/PrivacyPolicy.pdf";
?>
<!-- 'navbar-dark' class results in a light-colored collapsed icon ("hampurger") -->
<nav id="nav" class="navbar navbar-expand-sm navbar-dark">
    <div class="container-fluid"> 
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#ktesaMenu" aria-controls="ktesaMenu"
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ktesaMenu">
            <a class="navbar-brand" href="../pages/about.php">nmhikes.com:</a>
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
                                href="../admin/admintools.php">Admintools</a></li>
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
                            href="../accounts/unifiedLogin.php?form=reg">Become
                                a Member</a>
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
                        <li><a id="aboutpg" class="dropdown-item"
                            href="../pages/about.php">About This Site</a>
                        </li>
                        <li><a class="dropdown-item"
                            href="../php/postPDF.php?doc=<?=$policy;?>"
                            target="_blank">Privacy Policy</a>
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
                Enter email: <input id="rstmail" type="email"
                    required="required" /></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary"
                    id="send" data-bs-dismiss="modal">Send Email</button>
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

<script type="text/javascript">
window.mobileAndTabletCheck = function() {
        let check = false;
        (function(a){
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    };
    var mobile = mobileAndTabletCheck();
</script>
<script src="../scripts/menuControl.js"></script>
<script src="../scripts/panelMenu.js"></script>
<script src="../scripts/initiateReset.js"></script>
