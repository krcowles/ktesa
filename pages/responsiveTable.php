<?php
/**
 * This page uses bootstrap4 to implement a responsive table design
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
// required by makeTables.php
$age = 'old';
$show = 'all';
$pageType = 'FullTable';
require "respTableData.php";
?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description" content="Table of New Mexico hikes" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/responsiveTable.css" rel="stylesheet" />
</head>

<body>
<?php require "ktesaNavbar.php";?>
<div id="trail">Table of Hikes</div>
<br />

<div id="floater">
    <div id="opts" class="dropdown">
        <button class="btn-sm btn-secondary dropdown-toggle" type="button"
            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            Table Options
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a id="all" class="dropdown-item" href="#">All Hikes</a></li>
            <li><a id="reg" class="dropdown-item" href="#">Hikes by Region</a></li>
            <li><a id="cls" class="dropdown-item" href="#">Hikes Within ...</a></li>
        </ul>
    </div>
    <div id="areas" class="dropdown">
        <button class="btn-sm btn-secondary dropdown-toggle" type="button"
            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            Locales
        </button>
        <ul id="alist" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <?=$ddlist;?>
        </ul>
    </div>
</div><br />

<div class="container">
    <div class="table-responsive-sm">
        <table class="table table-sm table-striped table-hover sortable">
            <thead class="thead-dark">
                <tr>
                    <th class="hdr_row" data-sort="std">Hike/Trail Name</th>
                    <th class="hdr_row" data-sort="lan">Length</th>
                    <th class="hdr_row" data-sort="lan">Elev Chg</th>
                    <th class="hdr_row" data-sort="std">Difficulty</th>
                    <th class="hdr_row">Sun</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($entries === 0) : ?>
                <tr><td>You have no hikes to edit</td></tr>
            <?php else : ?>
                <?php for ($j=0; $j<$entries; $j++) : ?>
                <tr <?= $hikeHiddenDat[$j];?>>
                    <td><a href="<?= $pgLink[$j];?>"
                        target="_blank"><?= $hikeName[$j];?></a></td>
                    <td><?= $hikeLgth[$j];?> miles</td>
                    <td><?= $hikeElev[$j];?> ft</td>
                    <td><?= $hikeDiff[$j];?></td>
                    <td><?= $hikeExpIcon[$j];?></td>
                </tr>
                <?php endfor; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="near" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hikes within radius</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Enter Radius: <input id="miles" type="text" placeholder="miles" />
                <br /><br />Select Region: <?=$regions;?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary"
                    id="show">Show hikes</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">var regions = <?=$locale_groups;?>;</script>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<script src="../scripts/jquery.js"></script>
<script src="../scripts/responsiveTable.js"></script>
</body>
</html>