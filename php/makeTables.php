<?php
/**
 * The html for the specified hike table is created here by first collecting the
 * table type and its associated data from the tableData.php script. Note that 
 * the Table Only page uses checkboxes for each hike (w/gpx) whereas the
 * hike editor does not. This necissitated an identifier, $pageType, which
 * must be defined by the caller.
 * PHP Version 7.1
 * 
 * @package Hike_Table
 * @author  Tom Sandberg nd Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "tableData.php";
$cbox = $pageType == 'FullTable' ? true : false;
?>
<!-- REFERENCE TABLE OF HIKES -->
<table class="sortable">
    <colgroup>
        <?php if ($cbox) : ?>
        <col style="width:30px">
        <?php endif; ?>
        <col style="width:210px">
        <col style="width:108px">
        <col style="width:160px">
        <col style="width:80px">
        <col style="width:84px">
        <col style="width:110px">
        <col style="width:86px">
        <col style="width:64px">
    </colgroup>
    <thead>
        <tr>
            <?php if ($cbox) : ?>
            <th class="hdr_row">Use</th>
            <?php endif; ?>
            <th class="hdr_row" data-sort="std">Hike/Trail Name</th>
            <th class="hdr_row" data-sort="std">Locale</th>
            <th class="hdr_row" data-sort="std">WOW Factor</th>
            <th class="hdr_row" data-sort="lan">Length</th>
            <th class="hdr_row" data-sort="lan">Elev Chg</th>
            <th class="hdr_row" data-sort="std">Difficulty</th>
            <th class="hdr_row">Exposure</th>
            <th class="hdr_row">By Car</th>
        </tr>
    </thead>
    <tbody>
<?php if ($entries === 0) : ?>
    <tr><td>You have no hikes to edit</td></tr>
<?php else : ?>
    <?php for ($j=0; $j<$entries; $j++) : ?>
        <?php if ($hikeMarker[$j] === 'Visitor Ctr') : ?>
        <tr class="indxd" <?= $hikeHiddenDat[$j];?> 
            data-org-hikes="<?= $hikeColl[$j];?>">
        <?php elseif ($hikeMarker[$j] === 'Cluster') : ?>
        <tr class="clustered" data-cluster="<?= $hikeGroup[$j];?>"
            <?= $hikeHiddenDat[$j];?> data-tool="<?= $groupName[$j];?>">
        <?php elseif ($hikeMarker[$j] === 'At VC') : ?>
        <tr class="vchike"  data-vc="<?= $hikeColl[$j];?>" <?= $hikeHiddenDat[$j];?>>
        <?php else : ?>
        <tr class="normal" <?= $hikeHiddenDat[$j];?>>
        <?php endif; ?>
        <?php if ($hikeMarker[$j] === 'Visitor Ctr') : ?>
        <?php if ($cbox) : ?>
        <td></td>
        <?php endif; ?>
        <td><a href="<?= $pgLink[$j];?>"
            target="_blank"><?= $hikeName[$j];?></a></td>
        <td><?= $hikeLocale[$j];?></td>
        <td>See Indx</td>
        <td>0* miles</td>
        <td>0* ft</td>
        <td>See Index</td>
        <td>See Index</td>
    <?php else : ?>
        <?php if ($cbox) : ?>
        <td data-track="<?= $hikeGpx[$j];?>"><input type="checkbox" /></td>
        <?php endif; ?>
        <td><a href="<?= $pgLink[$j];?>"
            target="_blank"><?= $hikeName[$j];?></a></td>
        <td><?= $hikeLocale[$j];?></td>
        <td><?= $hikeWow[$j];?></td>
        <td><?= $hikeLgth[$j];?> miles</td>
        <td><?= $hikeElev[$j];?> ft</td>
        <td><?= $hikeDiff[$j];?></td>
        <td><?= $hikeExpIcon[$j];?></td>
    <?php endif; ?>
    <td style="text-align:center"><a href="<?= $hikeDirections[$j];?>"
        target="_blank"><?= $dirIcon;?></a></td></tr>
    <?php endfor; ?>
<?php endif; ?>
    </tbody>
</table>
