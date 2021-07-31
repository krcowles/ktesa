<?php
/**
 * This is the tab for users to add/delete photos with captions,
 * as well as to add/edit waypoints. Note that pictures may also be
 * reordered by dragging and dropping.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
?>
<style type="text/css">
    .gallery { height: 100px; width: 100%; }
    .gallery ul {margin: 0; padding: 0; list-style-type: none; }
    .gallery ul li { padding: 7px; border: 2px solid #ccc; float: left;
        margin: 10px 7px; background: none; width: auto; height: auto; }
    .image_link:link { color: black; text-decoration: none;}
</style>
<span><strong>Manage Your Photos Below</strong><a class="like-button"
    href="#wloc">Manage Waypoints</a></span>
<hr />
<p id="ehno" style="display:none;"><?= $hikeNo;?></p>

<form id="f2" class="box" action="saveTab2.php" method="POST">
<div id="pupld"> <!-- a div around photos for DnD -->
    <span id="userupld">Add Photos using drag-and-drop onto the page,
        or select:</span>
    <span class="box__input">
        <input type="file" name="files[]" id="file" class="inputfile"
            data-multiple-caption="&nbsp;&nbsp;{count} files selected" multiple />
        <label for="file">
            <span>&nbsp;&nbsp;Choose one or more photos&hellip;</span>
        </label>
    </span><br />
    <p id="ldg">Processing images&hellip;Please wait</p>
    <div id="preload"><img src="../images/loader-64x/Preloader_4.gif"
        alt="Loading image" />
    </div>
    <p>
        <em>Edit captions below each photo as needed and assign display options.</em>
    </p>
    <input type="hidden" name="hikeNo" value="<?= $hikeNo;?>" />
    <input type="hidden" name="track" value="<?= $curr_gpx;?>" />

<?php if ($inclPix === 'YES') : ?>
    <style type="text/css">
        .capLine { margin: 0px; font-weight: bold; background-color: #dadada; }
    </style>
    <h4>Please check the boxes corresponding to the pictures you wish to
            include on the hike page, and those you wish to include on the geomap.
        </h4><br />
    <div style="position:relative;top:-14px;margin-left:16px;">
        <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
            Use All Photos on Hike Page<br />
        <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
            Use All Photos on Map
    </div>

    <div style="margin-left:16px;clear:none;overflow:auto;">
        <div class="gallery">
            <ul class="reorder-ul reorder-photos-list">
            <?= $html;?>
            </ul>
        </div>
    </div>
<?php else : ?>
    <p id="nophotos">There are no photos to edit<p>
<?php endif; ?>
</div>

<hr />
<div id="thumbpic">
    <input id="uccrop" type="hidden" name="uccrop" value="0" />
    <input id="ucprev" type="hidden" name="ucprev" value="0" />
    <h4>Use this space to upload a representative photo for use as a thumbnail
    in the side table of hikes. There are two options:</h4>
    <?php if (!empty($preview_name)) : ?>
        <div id="thmb_saved">
            <p style="color:brown;font-size:18px;">You have already saved the
                following preview for this hike.<br /><br />
                You may <button id="redo_thumb">Delete
                Preview</button> and start over if you wish.
            </p>
            <img id="current_preview" src="<?=$prevImg;?>" width="300"
                height="225" alt="preview image" />
        </div>
    <?php else : ?>
        <ol>
            <li>
                <label for="selthmb" class="file_label">Select Photo</label>
                &nbsp;&nbsp;<input id="selthmb" class="file_input" type="file" />
                Select a photo (or drop into 'Crop Box'). Once loaded, You can move
                the cropping rectangle to select the best portion of the photo by
                grabbing the upper-left corner of the box.
            </li>
            <li>
                <label for="selpre" class="file_label">Select Presized</label>
                <input id="selpre" class="file_input" type="file" />&nbsp;&nbsp;
                Select a photo (or drop into 'Pre-sized Box') which is already
                cropped to 300 x 225 pixels.
            </li>
        </ol>
        <div id="boxes">
            <div id="repl">
                <span>Crop Box</span>
            </div>
            <div id="sizeblock">
                <img id="ps" />
            </div>
            <div id="presize">
                <span>Pre-sized Box</span>
            </div>
        </div>
    <?php endif; ?>
</div><br />

<hr id="wloc" />
<?= $wptedits;?>

</form>

<script type="text/javascript">
    var phTitles = <?=$jsTitles;?>;
    var phDescs = <?=$jsDescs;?>;
    var phMaps = <?=$jsMaps;?>;
</script>
<script src="photoSelect.js"></script>
<script src="picPops.js"></script>
<script src="makeThumbs.js"></script>

<div class="popupCap"></div>
<input type="hidden" name="usepics" value="<?= $inclPix;?>" />
<input type="hidden" name="hikeno" value="<?= $hikeNo;?>" />
