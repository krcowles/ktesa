/**
 * @fileoverview This script supplies routines to popup photo captions
 * over the top of each photo when mouseover occur. It is used either by
 * the hike page (hikePageTemplate.php) or by the editor (editDB.php).
 *
 * @author Ken Cowles
 * @version 1.0 Designed to reduce duplication of code in previous scripts
 */
var $photos;
var captions = [];
// in edit mode, photo positions are not available unless tab2 is active
var photosDisplayed = false;
// initial settings on page load or refresh
if (edit_mode === true) { // edit_mode is defined only in editDB.php
    $photos = $('.allPhotos');
    if ($('#t2').hasClass('active')) {
        photosDisplayed = true;
    }
    else { // wait until tab2 is clicked and page is displayed
        $('#t2').on('click', function (ev) {
            ev.preventDefault();
            setTimeout(function () {
                photosDisplayed = true;
                initializePopupCaptions();
            }, 100);
        });
    }
}
else { // used on hike pages
    $photos = $('img[id^="pic"]');
    photosDisplayed = true;
}
var noOfPix = $photos.length;
// globals for locating and sizing caption to popup
var picSel;
var capTop = [];
var capLeft = [];
var capWidth = [];
var picPos;
// setup after load:
if (photosDisplayed) {
    initializePopupCaptions();
}
/**
 * This kicks off all captioning, whether initially on page load, or when
 * tab2 is clicked in edit mode
 */
function initializePopupCaptions() {
    captureWidths();
    calculatePositions();
    associateCaptions();
    initActions();
}
/**
 * Function to capture -- current -- image widths
 */
function captureWidths() {
    for (var k = 0; k < noOfPix; k++) {
        var item = $photos[k];
        capWidth[k] = $(item).width() + 'px';
    }
    return;
}
/**
 * Function to save the top/left positioning of each photo
 */
function calculatePositions() {
    for (var m = 0; m < noOfPix; m++) {
        var item = $photos[m];
        picPos = $(item).offset();
        capTop[m] = Math.round(picPos.top) + 'px';
        capLeft[m] = Math.round(picPos.left) + 'px';
    }
}
/**
 * Associate each photo with its respective caption
 */
function associateCaptions() {
    // for edit mode, the popup consists of the image name, not the caption
    $photos.each(function () {
        captions.push(this.alt);
    });
}
/**
 * Establish the behaviors when mousing over/out
 */
function initActions() {
    $photos.each(function () {
        $(this).css('cursor', 'pointer');
    });
    // popup a description when mouseover a photo
    $photos.css('z-index', '1'); // keep pix in the background
    $photos.on('mouseover', function (ev) {
        var targ = ev.target;
        var selected = targ.alt;
        picPop(selected);
    });
    // kill the popup when mouseout
    $photos.on('mouseout', function () {
        $('.popupCap > p').remove();
        $('.popupCap').css('display', 'none');
    });
}
/**
 *  The function that actually places the popup on the photo
 */
function picPop(caption) {
    var picNo = 0;
    // which photo is being processed?
    for (var x = 0; x < noOfPix; x++) {
        if (caption == captions[x]) {
            picNo = x;
            break;
        }
    }
    var htmlDesc = '<p class="capLine">' + caption;
    if (phMaps[picNo] == 0) {
        htmlDesc += '<br /><span style="color:brown">No Location Data: ' +
            'Photo Cannot Be Mapped</span></p>';
    }
    else {
        htmlDesc += '</p>';
    }
    $('.popupCap').css('display', 'block');
    $('.popupCap').css('position', 'absolute');
    $('.popupCap').css('top', capTop[picNo]);
    $('.popupCap').css('left', capLeft[picNo]);
    $('.popupCap').css('width', capWidth[picNo]);
    $('.popupCap').css('z-index', '10');
    $('.popupCap').prepend(htmlDesc);
}
// turn off events during resize or forced reset until finished resizing
function killEvents() {
    $photos.off('mouseover');
    $photos.off('mouseout');
    $photos.off('click');
    $photos = null;
}
/**
 * During re-ordering of photos in the edit mode, items need to be re-established
 * in their new order; noOfPix remains the same
 */
function forcedReset() {
    killEvents();
    $('.popupCap').children().remove();
    $('.popupCap').css('display', 'none');
    $photos = $('.allPhotos');
    captions = [];
    capTop = [];
    capLeft = [];
    capWidth = [];
    initializePopupCaptions();
}
