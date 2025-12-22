<?php
/**
 * Show non-existent links currently embedded on hike pages
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$default_size = 50;
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>List Bad Links</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for urls in REFS that no longer work" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <?php require "../pages/favicon.html";?>
    <link href="../styles/linkValidate.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
<body>

<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Hike Page Link Check</p>
<p id="active" style="display:none">Admin</p>

<div id="constraints" class="margins">
    <p id="prelim" class="bottom">In order to prevent choking the
        script with timeouts, a limited number of links will be tested
        at a time, with a chance for the admin to continue to the next set,
        or to stop after each test execution.<br />
        NOTE: The routine defaults to testing <?=$default_size;?> links
        per execution. Expect to wait 2-5 minutes per bad link
        uncovered. If you wish to change the test lot size from
        <?=$default_size;?>, specify the new size below:<br/>
        No of links to check per test: <input id="new_size" type="text" 
        value="50" />
    </p>
    <p class="bottom">Start with link <input id="start" type="text" value="0" /></p>
    <button id="test">Begin Test</button></p>
</div>

<div id="loading" class="margins">
    <p class="bottom">Testing links will take some time!</p>
    <img id="gif" src="../images/loader-64x/Preloader_5.gif" 
        height="128" width="128" />
</div>

<div id="contents" class="margins">
    <p>The following links are no longer valid:</p>

    <table id="lnk_results">
        <thead>
            <tr>
                <th>Hike Page No.</th>
                <th>Non-working Link</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table><br />

    <div>
    <button id="del_lnks" type="button" class="btn btn-secondary">
        Delete Links</button>
    </div>
</div>

<script src="linkValidate.js"></script>

</body>
</html>
