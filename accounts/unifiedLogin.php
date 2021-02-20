<?php
/**
 * This page allows the user to login to the site as a member,
 * whether a new member registration, change password request,
 * 'Forgot password' request, expired/renewable membership,
 * or rejected cookies. In each case the user is sent a one-time
 * secure code as a password, and must select a new password
 * to continue. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No Liscense to date
 */
session_start();
require "../php/global_boot.php";
$form  = filter_input(INPUT_GET, 'form');
$code  = isset($_GET['code']) ? filter_input(INPUT_GET, 'code') : '';
if ($form === 'reg') {
    $title = "Sign Up";
} elseif ($form === 'renew') {
    $title = "Set Password";
} elseif ($form === 'log') {
    $title = "Log in";
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?=$title;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Unified log in page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous" /> 
    <link href="unifiedLogin.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    <script type="text/javascript">var page = 'unified';</script>
</head>

<body>
<div id="logo">
    <div id="pattern"></div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike New Mexico</span>
        </div>
        <div id="center"><?=$title;?></div>
        <div id="rightside">
            <span id="logo_right">w/Tom &amp; Ken</span>
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        </div>
    </div>   
</div>

<p id="formtype" style="display:none;"><?=$form;?></p>
<div id="container">
<?php if ($form === 'reg') : ?>
    <form id="form" action="#" method="post">
        <input type="hidden" name="submitter" value="create" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
            <p>Sign up for free access to nmhikes.com!</p>
            <p id="sub">Create and edit your own hikes<br />
            <a id="policylnk" href="#">Privacy Policy</a>
            </p>
            <div>
                <div class="pseudo-legend">First Name</div>
                <div id="line1" class="lines"></div>
                <input id="fname" type="text"
                    placeholder="First Name" name="firstname"
                    autocomplete="given-name" required />
            </div>
            <div>
                <div class="pseudo-legend">Last Name</div>
                <div id="line2" class="lines"></div>
                <input id="lname" type="text"
                    placeholder="Last Name" name="lastname"
                    autocomplete="family-name" required />
            </div>
            <div>
                <div class="pseudo-legend">Username</div>
                <div id="line3" class="lines"></div>
                <input id="uname" type="text"
                    placeholder="User Name" name="username"
                    autocomplete="username" required />
            </div>
            <div>
                <div class="pseudo-legend">Email</div>
                <div id="line4" class="lines"></div>
                <input id="email" type="email"
                    required placeholder="Email" name="email"
                    autocomplete="email" /><br /><br />
            </div>
            <div>
                <button id="formsubmit">Submit</button>
            </div> 
    </form>
<?php elseif ($form === 'renew') : ?>
    <h3>Reset Passsword:</h3>
    <form id="form" action="#" method="post">
        <input type="hidden" name="code" value="<?=$code;?>" />
        <input id="usrchoice" type="hidden" name="cookies" value="nochoice" />
        <?php if (!empty($code)) : ?>
            <span>One-time code</span>
            <input type="password" name="one-time" autocomplete="off"
                value="<?=$code;?>" /><br /><br />  
        <?php endif; ?>
        <input id="password" type="password" name="password"
            autocomplete="new-password" required placeholder="New Password" /><br />
        Show password&nbsp;&nbsp;&nbsp;
        <input id="ckbox" type="checkbox" /><br /><br />
        <input id="confirm" type="password" name="confirm"
            autocomplete="new-password" required="required"
            placeholder="Confirm Password" /><br /><br />
        <button id="formsubmit">Submit</button>
    </form>
<?php elseif ($form === 'log') : ?>
    <div class="container">
        <h3 id="hdr">Member Log in</h3><br />
        <form id="form" action="#" method="post">
            <input id="usrchoice" type="hidden" name="cookies"
                value="nochoice" />
            <input class="logger" id="username" type="text" placeholder="Username"
                name="username" autocomnplete="username" required /><br /><br />
            <input class="logger" id="password" type="password" name="oldpass"
                placeholder="Password" size="20" autocomplete="password"
                required/><br /><br />
            <button id="formsubmit">Submit</button><br /><br />
        </form>

        <!-- For 'Forgot password' and 'Renew password -->
        <button type="button" class="btn btn-outline-secondary"
        data-bs-toggle="modal" data-bs-target="#cpw">
        Forgot Password?
        </button>
        <div class="modal fade" id="cpw" tabindex="-1"
                aria-labelledby="ResetPassword" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Reset Password</h5>
                        <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Enter your email below. You will receive an email link to 
                        reset your password<br />
                        <input id="forgot" type="email" required 
                            placeholder="Enter your email" /><br /><br />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button id="send">Send</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>
</div>   <!-- end of #container -->

<div id="cookie_banner">
    <h3>This site uses cookies to save member usernames</h3>
    <p>Accepting cookies allows automatic login. If you reject cookies,
    no cookie data will be collected, and you must login each visit.
    <br />You may change your decision later via the Help menu.
    </p>
    <div id="cbuttons">
        <button id="accept">Accept</button>
        <button id="reject">Reject</button>
    </div>
</div>

<script type="text/javascript">
    window.mobileAndTabletCheck = function() {
        let check = false;
        (function(a){
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    };
    var mobile = mobileAndTabletCheck() ? true : false;
</script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/loginState.js"></script>
<script src="../scripts/validateUser.js"></script>
<script src="unifiedLogin.js"></script>

</body>
</html>
