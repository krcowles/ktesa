<?php
/**
 * The html for the specified hike table is created here by first collecting the
 * table type and its associated data from the tableData.php script.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "tableData.php";
?>
<!-- REFERENCE TABLE OF HIKES -->
<table class="sortable">
    <colgroup>
        <col style="width:210px">
        <col style="width:108px">
        <col style="width:160px">
        <col style="width:80px">
        <col style="width:84px">
        <col style="width:110px">
        <col style="width:86px">
        <col style="width:64px">
    </colgroup>
    <!-- total col widths: 902 -->
    <thead>
        <tr>
            <th class="hdr_row" data-sort="std">Hike/Trail Name</th>
            <th class="hdr_row" data-sort="std">Locale</th>
            <th class="hdr_row" data-sort="std">WOW Factor</th>
            <th class="hdr_row" data-sort="lan">Length</th>
            <th class="hdr_row" data-sort="lan">Elev Chg</th>
            <th class="hdr_row" data-sort="std">Difficulty</th>
            <th class="hdr_row" data-sort="icn">Exposure</th>
            <th class="hdr_row" data-sort="no">By Car</th>
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
            <td><?= $hikeLocale[$j];?></td>
            <td><?= $hikeWow[$j];?></td>
            <td><?= $hikeLgth[$j];?> miles</td>
            <td><?= $hikeElev[$j];?> ft</td>
            <td><?= $hikeDiff[$j];?></td>
            <td><?= $hikeExpIcon[$j];?></td>
            <td style="text-align:center"><a href="<?= $hikeDirections[$j];?>"
                target="_blank"><?= $dirIcon;?></a></td>
        </tr>
    <?php endfor; ?>
<?php endif; ?>
    </tbody>
</table>
