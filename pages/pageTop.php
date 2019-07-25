<?php
/**
 * This script presents the html that comprises the top-of-the-page,
 * page-width elements: a Navigation Links bar and a Logo bar.
 * Php is needed in order to determine whether or not to include
 * the 'Log Me Out' link in the navigation section.
 * PHP Version 7.1
 * 
 * @package Main
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$logout =  (isset($_COOKIE['nmh_mstr']) || isset($_COOKIE['nmh_id'])) ? true : false;
?>
<div id="pgtop">
    <div id="nav">
        <span id="lnks">Navigation Links:
            <span id="firstlnk"><a class="navs" href="../index.php"
                target="_self">Home</a></span>
            <span><a class="navs" href="mapPg.php?tbl=T"
                target="_self">Table</a></span>
            <span><a class="navs" href="mapPg.php?tbl=D"
                target="_self">Map+Table</a></span>
            <span><a class="navs" href="mapPg.php?tbl=M"
                target="_self">Map</a></span>
            <?php if ($logout) : ?>
            <span id="logout"><a id="lolnk" class="navs" 
                href=#>Log Me Out</a></span>
            <?php endif; ?>
        </span>
    </div>
    <div id="logo">
        <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
        <p id="logo_left">Hike New Mexico</p>
        <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        <p id="logo_right">w/Tom &amp; Ken</p>
    </div>
    <?php if ($logout) : ?>
    <script type="text/javascript">
        // not supported on IE8.0 and earlier
        document.addEventListener("DOMContentLoaded", function() {
            var lo = document.getElementById('logout').addEventListener(
                'click', function(evt) {
                    evt.preventDefault();
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', '../php/logout.php');
                    xhr.onload = function() {
                        alert("You are now logged out");
                        window.open('../index.php', '_self');
                        //document.getElementById('logout').style.display = 'none;'
                    }
                    xhr.send();
                }
            );
        });
    </script>
    <?php endif; ?>
</div>
