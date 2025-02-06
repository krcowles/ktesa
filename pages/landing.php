<?php
/**
 * This is the mobile landing site for New Mexico Hikes.
 * Viewport data was derived from https://yesviz.com/viewport/
 * Major phone brands are supported for offline maps; omitted
 * are some Chinese, brands but they have similar characteristics
 * and should work with this script. Tablets and watches are not
 * explicitly supported, as these do not represent useful hiking
 * platforms. The very large pixel Sony Xperia (540 x 960) may
 * not have optimal visual display, but will still render properly.
 *   Portrait:  widths range from 320-440 (540); 
 *   Landscape: widths range from 480-960. 
 * Portrait views will vary slightly in text and object placement
 * compared to landscape views. The js will adjust views accordingly.
 * Portrait content is designed to fit in the 320/480px state; 
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require_once "../php/global_boot.php";
require_once "../accounts/getLogin.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Mobile site for New Mexico Hikes" />
    <meta name="author" content="Ken Cowles" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/landing.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>

<div id="logo">
    <div id="pattern">
    </div>
    <div id="pgheader">
        <div id="leftside">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <span id="logo_left">Hike New Mexico</span>
        </div>
        
        <!-- minimal functionality "navbar" -->
        <div id="center">
            <select id="membership">
                <option id="sao"    value="sao">Member Options:</option>
                <option id="login"  value="login">Login          </option>
                <option id="logout" value="logout">Logout        </option>
                <option id="bam"    value="bam">Become a member  </option>>      
            </select>
        </div>

        <div id="rightside">
            <span id="logo_right">w/Tom &amp; Ken</span>
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        </div>
    </div>   
</div>

<p id="cookie_state"><?= $_SESSION['cookie_state'];?></p>
<?php if (isset($_SESSION['cookies'])) : ?>
<p id="cookies_choice"><?= $_SESSION['cookies'];?></p>
<?php endif;?>
    
<h2 id="welcome">The New Mexico Hiking Site</h2>
<div class="landing_content">
    <h4 id="detail">(A free site)</h4> 
    <p id="opts">Choose from the following
        <span id="vopts"> viewing options:</span></p>

    <div class="usr_choices">
        <div class="flexitem">
                <div class="pair" id="choice1">
                    <p id="tbldesc">Hike Table</p>
                    <img id="table" class="icons" src="../images/tbl.jpg" 
                        alt="image of table of hikes" />
                </div>
                <div class="pair" id="choice2">
                    <p id="home">Map &amp; markers</p>
                    <img id="map" class="icons" src="../images/mapmrkrs.jpg"
                        alt="map with markers" />
                </div>
        </div>
    </div>
    <div id="bennies">
        Membership is free!<br />Benefits include :
        <ul id="memlist">
            <li>Save/Display Favorites</li>
            <li>Create your own hike page<br /><em>[Laptops/desktops only]</em></li>
            <li>Edit existing hike pages<br /><em>[Laptops/desktops only]</em></li>
            <li>Coming soon... Offline maps</li>
        </ul>
    </div>
</div>

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

<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<script src="../scripts/viewMgr.js"></script>
<script src="../scripts/landing.js"></script>
<script src="../scripts/loginState.js"></script>
</body>

</html>
