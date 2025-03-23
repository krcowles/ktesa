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
<!-- Photo entry and management section -->
<style type="text/css">
    .gallery { height: 100px; width: 100%; box-sizing: content-box; }
    .gallery ul {margin: 0; padding: 0; list-style-type: none; }
    .gallery ul li { padding: 7px; border: 2px solid #ccc; float: left;
        margin: 10px 7px; background: none; width: auto; height: auto; }
</style>

<div><strong>Manage Your Photos Below, or&nbsp;&nbsp;</strong>
    <a class="btn btn-secondary" href="#wloc" role="button">Manage Waypoints</a>
    <span id="thumbstat">&nbsp;&nbsp;Your thumbnail image&nbsp;&nbsp;
        <a class="btn <?=$btncolor;?>" href="#thumbpic"
            role="button"><?=$tstat;?></a>&nbsp;&nbsp;been saved
    </span>
    <div style="margin-top:8px;">
        <strong>To Import Photos From Another Hike:&nbsp;&nbsp;</strong>
        <div style="display:inline-block" class="ui-widget">
            <style type="text/css">
                ul.ui-widget {
                    width: 300px;
                    clear: both;
                }
            </style>
            <input id="gethike" type="text" placeholder="Type name of hike" />
        </div>
    </div>
</div>

<hr />
<p>NOTE: If you wish to upload .heic files, you must first convert them to .jpg
    using the following utility:</p>
<button id="heic" type="button" class="btn btn-secondary">
    HEIC/HEIF Converter</button>
<hr />

<p id="ehno" style="display:none;"><?= $hikeNo;?></p>

<form id="f2" class="box" action="saveTab2.php" method="POST">

<div id="pupld"> <!-- a div around photos for DnD -->
    <span class="userupld">Add Photos using drag-and-drop onto the page,
        or select:</span>
    <span class="box__input">
        <input type="file" name="files[]" id="file" class="inputfile"
            data-multiple-caption="&nbsp;&nbsp;{count} files selected" multiple />
        <label for="file">
            <span>&nbsp;&nbsp;Choose one or more photos&hellip;</span>
        </label>
    </span>
    <br />
    <div id="preload">
        <p id="ldg">Processing images&hellip;Please wait</p>
        <img src="../images/loader-64x/Preloader_4.gif"
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
    <h5>Please check the boxes corresponding to the pictures you wish to
            include on the hike page, and those you wish to include on the geomap.
        </h5><br />
    <div id="picchks">
        <input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;
            Use All Photos on Hike Page<br />
        <input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;
            Use All Photos on Map
    </div>
    <div id="picbox">
        <div class="gallery">
            <ul class="reorder-photos-list">
            <?= $html;?>
            </ul>
        </div>
    </div>
<?php else : ?>
    <p id="nophotos">There are no photos to edit<p>
<?php endif; ?>
</div> <!-- end pupld -->

<hr />
<!-- add or display thumb/preview image -->
<div id="thumbpic">
    <p id="orgthumb" style="display:none;"><?=$org_prev;?></p>
    <input id="uccrop" type="hidden" name="uccrop" value="0" />
    <input id="ucprev" type="hidden" name="ucprev" value="0" />
    <h4 id="ifnothmb">Use this space to upload a representative photo for use
        as a thumbnail in the side table of hikes. There are two options:</h4>
    <?php if (!empty($preview_name)) : ?>
        <div id="thmb_saved">
            <p style="color:brown;font-size:18px;">You have already saved the
                following preview for this hike.<br /><br />
                You may <button id="redo_thumb" type="button" 
                    class="btn btn-danger">Replace Preview</button>
                    and start over if you wish.
            </p>
            <img id="current_preview" src="<?=$prevImg;?>" width="300"
                height="225" alt="preview image" />
        </div>
    <?php else : ?>
        <ol>
            <li>
                <label for="selthmb" class="file_label">Select Photo</label>
                &nbsp;&nbsp;<input id="selthmb" class="file_input" type="file" />
                Select a photo (or drop into 'Crop Box'). Then move
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

<!-- waypoint section -->
<hr id="wloc" />
<div id="wpteds">
    <input id="wpt_format" type="hidden" name="wpt_format" value="<?=$wpt_pref;?>" />
    <?= $wptedits;?>
</div> <!-- end of tab2 waypoints div -->

</form>
<!-- Add Photo Location modal -->
<div id="photoloc" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Photo Location</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ap" class="modal-body">
                <p id="tsvid" style="display:none;"></p>
                You can <span id="modtype">add</span> location data to this photo.
                Please enter decimal degrees for the desired photo location
                (e.g. 35.4913334).
                <br />
                <table>
                    <tbody>
                    <tr>
                        <td>Latitude:</td>
                        <td><textarea id="piclat" style=
                            "height:28px;width:120px;line-height:14px;"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Longitude:</td>
                        <td><textarea id="piclng" style=
                            "height:28px;width:120px;line-height:14px;"></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <br />
                <button id="setloc" type="button" class="btn-sm btn-success">
                    Add Data</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var edit_mode = true;
    // formatted lat/lngs
    var gpxLatDeg = JSON.parse('<?=$jsgpxLatDeg;?>');
    var gpxLatDM  = JSON.parse('<?=$jsgpxLatDM;?>');
    var gpxLatDMS = JSON.parse('<?=$jsgpxLatDMS;?>');
    var gpxLngDeg = JSON.parse('<?=$jsgpxLngDeg;?>');
    var gpxLngDM  = JSON.parse('<?=$jsgpxLngDM;?>');
    var gpxLngDMS = JSON.parse('<?=$jsgpxLngDMS;?>');
    var wLatDeg   = JSON.parse('<?=$jswLatDeg;?>');
    var wLatDM    = JSON.parse('<?=$jswLatDM;?>');
    var wLatDMS   = JSON.parse('<?=$jswLatDMS;?>');
    var wLngDeg   = JSON.parse('<?=$jswLngDeg;?>');
    var wLngDM    = JSON.parse('<?=$jswLngDM;?>');
    var wLngDMS   = JSON.parse('<?=$jswLngDMS;?>');
    // list of waypoint symbols supported by this app
    var wpt_icons = <?=$jsSymbols;?>;
</script>
<script src="../scripts/popupCaptions.js"></script>
<script src="photoSelect.js"></script>
<script src="makeThumbs.js"></script>
<script src="waypoints.js"></script>
<div class="popupCap"></div>
<input type="hidden" name="usepics" value="<?= $inclPix;?>" />
<input type="hidden" name="hikeno" value="<?= $hikeNo;?>" />
