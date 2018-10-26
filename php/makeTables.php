<?php
/**
 * The html for the specified hike table is created here by first collecting the
 * table type and its associated data from the tableData.php script.
 * PHP Version 7.0
 * 
 * @package Hike_Table
 * @author  Tom Sandberg nd Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "tableData.php";
?>
<!-- Table Filtering Options -->
<div id="tblfilter">
    <button id="showfilter"><strong>Show Table Filtering Options</strong></button>
    <div id="dispopts">
        <strong style="color:darkblue;">Sort the table of hikes by proximity:</strong><br />
        Hikes within <input id="within" type="text" name="mi" size="4" />&nbsp;miles of&nbsp;&nbsp;
        <label id="loclbl" class="normal">Area:</label>
            <input id="loc" type="radio" name="prox" />
        <div id="selloc" class="hidden">
            (Select)&nbsp;<?php include "../build/localeBox.html";?>
        </div>
        &nbsp;&nbsp;<label id="hikelbl" class="normal">Hike/Trail</label>
            <input id="hike" type="radio" name="prox" />
        <div id="selhike" class="hidden">
            <input id="link" type="text" name="link" size="35"
                value="...select hike by clicking link in table" />
        </div>
        &nbsp;&nbsp;<button id="apply">Apply Filter</button>
    </div>
</div>
<p id="filtnote">
    <strong id="note">NOTE:</strong>
    All table columns can be sorted alphabetically/numerically by clicking
    on the column header at the top of the column. Clicking again reverses
    the sort.
</p>
<!-- REFERENCE TABLE OF HIKES -->
<table class="sortable">
    <colgroup>	
        <col style="width:210px">
        <col style="width:108px">
        <col style="width:160px">
        <?php if ($includeZoom) : ?>
        <col style="width:60px">
        <?php endif; ?>
        <col style="width:80px">
        <col style="width:84px">
        <col style="width:110px">
        <col style="width:86px">
        <col style="width:64px">
    </colgroup>
    <thead>
        <tr>
            <th class="hdr_row" data-sort="std">Hike/Trail Name</th>
            <th class="hdr_row" data-sort="std">Locale</th>
            <th class="hdr_row" data-sort="std">WOW Factor</th>
            <?php if ($includeZoom) : ?>
            <th class="hdr_row">Mapit</th>
            <?php endif; ?>
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
    <td><a href="<?= $pgLink[$j];?>" target="_blank"><?= $hikeName[$j];?></a></td>
    <td><?= $hikeLocale[$j];?></td>
    <td>See Indx</td>
    <?php if ($includeZoom) : ?>
    <td style="text-align:center;"><?= $mapLink[$j];?></td>
    <?php endif; ?>
    <td>0* miles</td>
    <td>0* ft</td>
    <td>See Index</td>
    <td>See Index</td>
    
    <?php else : ?>
    <td><a href="<?= $pgLink[$j];?>" target="_blank"><?= $hikeName[$j];?></a></td>
    <td><?= $hikeLocale[$j];?></td>
    <td><?= $hikeWow[$j];?></td>
    <?php if ($includeZoom) : ?>
    <td style="text-align:center;"><?= $mapLink[$j];?></td>
    <?php endif; ?>
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
