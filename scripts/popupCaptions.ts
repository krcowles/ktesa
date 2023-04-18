declare var edit_mode: boolean; // only defined in editDB.php
/**
 * @fileoverview This script supplies routines to popup photo captions
 * over the top of each photo when mouseover occur. It is used either by
 * the hike page (hikePageTemplate.php) or by the editor (editDB.php).
 * 
 * @author Ken Cowles
 * @version 1.0 Designed to reduce duplication of code in previous scripts
 * @version 2.0 Redesigned method to show unmappable pix
 */
// global vars
var $photos: any; //JQuery<HTMLImageElement> | null;
var captions: string[] = [];
var noOfPix: number;
// globals for locating and sizing caption to popup
var picSel: string;
var capTop: string[] = [];
var capLeft: string[] = [];
var capWidth: string[] = [];
var picPos: JQuery.Coordinates;
// in edit mode, photo positions are not available unless tab2 is active
var photosDisplayed = false;

// when the page load is completed
$(function() {
// initial settings on page load or refresh
if (typeof edit_mode !== 'undefined' && edit_mode === true) {
    $photos = $('.allPhotos');
    if ($('#t2').hasClass('active')) {
        photosDisplayed = true;
    } else {  // wait until tab2 is clicked and page is displayed
        $('#t2').on('click', function(ev) {
            ev.preventDefault();
            setTimeout( function() {
               photosDisplayed = true;
               initializePopupCaptions();
            }, 100); 
        });  
    }
} else { // used on hike pages
    $photos = $('img[id^="pic"]');
    photosDisplayed = true;
}
noOfPix = $photos.length;
// setup after load:
if (photosDisplayed) {
   initializePopupCaptions();
}

});

// global functions
/**
 * This kicks off all captioning, whether initially on page load, or when
 * tab2 is clicked in edit mode
 */
function initializePopupCaptions() {
    captureWidths();
    calculatePositions();
    associateCaptions();
    initActions();
    return;
}
/**
 * Function to capture -- current -- image widths
 */
 function captureWidths(): void {
    for (let k=0; k<noOfPix; k++) {
        let item = <HTMLElement>$photos[k];
        capWidth[k] = $(item).width() + 'px';
    }
    return;
}
/**
 * Function to save the top/left positioning of each photo
 */
function calculatePositions() {
    for (let m=0; m<noOfPix; m++) {
        let item = <HTMLElement>$photos[m];
        picPos = <JQuery.Coordinates>$(item).offset();
        capTop[m] = Math.round(picPos.top) + 'px';
        capLeft[m] = Math.round(picPos.left) + 'px';
    }
    return;
}
/**
 * Associate each photo with its respective caption
 */
function associateCaptions() {
    // for edit mode, the popup consists of the image name, not the caption
    for (let q=0; q<noOfPix; q++) {
        let img = $photos[q];
        captions.push(img.alt);
    }
    return;
}
/**
 * Establish the behaviors when mousing over/out
 */
function initActions() {
    $photos.css('cursor','pointer');
    // popup a description when mouseover a photo
    $photos.css('z-index','1'); // keep pix in the background
    $photos.on('mouseover', function(ev: MouseEvent) {
        var targ = <HTMLImageElement>ev.target;
        var selected = targ.id;
        var picCap = targ.alt;
        picPop(selected, picCap);
    });
    // kill the popup when mouseout
    $photos.on('mouseout', function() {
        $('.popupCap > p').remove();
        $('.popupCap').css('display','none');
    });
    for (let t=0; t<noOfPix; t++) {
        let item = <HTMLElement>$photos[t];
        $(item).on('click', function() {
            /**
             * To avoid annoying behavior in editor when trying to move a photo:
             */
            if (typeof edit_mode === 'undefined') {
                var zphoto = $(this).attr('src');
                window.open(zphoto,"_blank");
            }
        });
    }
    return;
}
/**
 *  The function that actually places the popup on the photo
 */
function picPop(tsvId: string, caption: string) {
    // need picNo reference:
    var picNo = 0;
    for (var x=0; x<noOfPix; x++) {
        if (caption == captions[x]) {
            picNo = x;
            break;
        }
    }
    var nomapper = false;
    $('.mpguse').each(function() {
        if ($(this).val() == tsvId && $(this).hasClass('nomap')) {
            nomapper = true;
            return;
        }
    });
    var htmlDesc = '<p class="capLine">' + caption;
    if (nomapper) {
            htmlDesc += '<br /><span style="color:brown">No Location Data: ' +
                'Photo Cannot Be Mapped</span></p>';
        } else {
            htmlDesc += '</p>';
        } 
    $('.popupCap').css('display','block');
    $('.popupCap').css('position','absolute');
    $('.popupCap').css('top',capTop[picNo]);
    $('.popupCap').css('left',capLeft[picNo]);
    $('.popupCap').css('width',capWidth[picNo]);
    $('.popupCap').css('z-index','10');
    $('.popupCap').prepend(htmlDesc);
    return;
}
// turn off events during resize or forced reset until finished resizing
function killEvents() {
    $photos.off('mouseover');
    $photos.off('mouseout');
    $photos.off('click');
    $photos = null;
    return;
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
    return;
}
