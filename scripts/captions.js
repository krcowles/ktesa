"use strict";
/**
 * @fileoverview Calculate and save photos parameters for the purpose of situating
 * captions accordingly
 *
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
// Establish location of picture rows before attempting to capture positions
if ($('#mapline').length) {
    // setting up map & chart to occupy viewport space
    var vpHeight = window.innerHeight;
    var sidePnlPos = $('#sidePanel').offset();
    var sidePnlLoc = sidePnlPos.top;
    var usable = vpHeight - sidePnlLoc;
    var mapHt = Math.floor(0.65 * usable);
    var chartHt = Math.floor(0.35 * usable);
    var pnlHeight = (mapHt + chartHt) + 'px';
    var mapHeight = mapHt + 'px';
    var chtHeight = chartHt + 'px';
    $('#mapline').css('height', mapHeight);
    $('#chartline').css('height', chtHeight);
    $('#sidePanel').css('height', pnlHeight);
}
// begin caption popup code
var $photos = $('img[id^="pic"]');
var noOfPix = $photos.length;
var picSel;
var capTop = [];
var capLeft = [];
var capWidth = [];
var picPos;
// keys for stashing data into session storage
var pwidth;
var pleft;
var ptop;
var sessSupport = window.sessionStorage ? true : false;
/* problems with refresh in Chrome prompted the use of the following technique
   which "detects" a refresh condition and restores previously loaded values.
   User gets a window alert if sessionStorage is not supported and and is advised
   about potential refresh issues */
if (sessSupport) {
    var tst = sessionStorage.getItem('prevLoad');
    if (!tst) {
        // NORMAL FIRST-TIME ENTRY:
        captureWidths();
        // get caption locations
        calcPos();
    }
    else { // REFRESH ENTRY
        // retrieve location data (pic/iframe data is string type and does not need 
        //   to be converted to numeric)
        for (var i_1 = 0; i_1 < noOfPix; i_1++) {
            pwidth = 'pwidth' + i_1;
            capWidth[i_1] = sessionStorage.getItem(pwidth);
        }
        for (var i_2 = 0; i_2 < noOfPix; i_2++) {
            pleft = 'pleft' + i_2;
            capLeft[i_2] = sessionStorage.getItem(pleft);
            ptop = 'ptop' + i_2;
            capTop[i_2] = sessionStorage.getItem(ptop);
        }
    }
}
else {
    window.alert('Browser does not support Session Storage\nRefresh may cause problems');
    // code with no session storage support...
    captureWidths();
    // get caption locations
    calcPos();
} // end of session storage IF
/**
 * function to capture *current* image widths & map link loc
 */
function captureWidths() {
    for (var k = 0; k < $photos.length; k++) {
        var item = $photos[k];
        capWidth[k] = item.clientWidth + 'px';
        pwidth = 'pwidth' + k;
        if (sessSupport) {
            sessionStorage.setItem(pwidth, capWidth[k]);
        }
    }
    return;
}
/**
 * function to save the top/left positioning of each photo
 */
function calcPos() {
    for (var m = 0; m < $photos.length; m++) {
        var item = $photos[m];
        picPos = $(item).offset();
        capTop[m] = Math.round(picPos.top) + 'px';
        capLeft[m] = Math.round(picPos.left) + 'px';
        if (sessSupport) {
            ptop = 'ptop' + m;
            pleft = 'pleft' + m;
            sessionStorage.setItem(ptop, capTop[m]);
            sessionStorage.setItem(pleft, capLeft[m]);
        }
    }
}
/**
 * function to popup the description for the picture 'selected'
 */
function picPop(picTarget) {
    // get the corresponding description
    var picidlgth = picTarget.length;
    var picNo = parseInt(picTarget.substring(3, picidlgth));
    var jqid = '#' + picTarget;
    var desc = $(jqid).attr('alt');
    var htmlDesc = '<p class="capLine">' + desc + '</p>';
    $('.popupCap').css('display', 'block');
    $('.popupCap').css('position', 'absolute');
    $('.popupCap').css('top', capTop[picNo]);
    $('.popupCap').css('left', capLeft[picNo]);
    $('.popupCap').css('width', capWidth[picNo]);
    $('.popupCap').css('z-index', '10');
    $('.popupCap').prepend(htmlDesc);
}
function eventSet() {
    // popup a description when mouseover a photo
    $photos.css('z-index', '1'); // keep pix in the background
    // enable pointer to indicate 'mouseover-able'
    for (var n = 0; n < $photos.length; n++) {
        var item = $photos[n];
        $(item).css('cursor', 'pointer');
    }
    $photos.on('mouseover', function (ev) {
        var eventObj = ev.target;
        picSel = eventObj.id;
        var picHdr = picSel.substring(0, 3);
        if (picHdr === 'pic') {
            picPop(picSel);
        }
    });
    // kill the popup when mouseout
    $photos.on('mouseout', function () {
        $('.popupCap > p').remove();
        $('.popupCap').css('display', 'none');
    });
    var _loop_1 = function (t) {
        var item = $photos[t];
        $(item).on('click', function () {
            var zpic = "/pictures/zsize/" + piclnks[t] + "_z.jpg";
            window.open(zpic, "_blank");
        });
    };
    for (var t = 0; t < $photos.length; t++) {
        _loop_1(t);
    }
}
// turn off events during resize until finished resizing
function killEvents() {
    $photos.off('mouseover');
    $photos.off('mouseout');
    $photos.off('click');
    $photos = null;
}
eventSet();
