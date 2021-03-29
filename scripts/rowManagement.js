"use strict";
/**
 * @fileoverview This script manages image sizes in a row
 *
 * @author Ken Cowles
 * @version 1.0 Original release
 * @version 1.1 added jsDocs
 * @version 2.0 Typescripted, with some type errors corrected
 */
var winWidth = $(window).width();
// initialize variables for original page load:
var resizeFlag = false; // semaphore: don't execute resize event code if true
var unProcSpace = 0; // used to detect multiple small incremental growth in resizing
var prevWidth = winWidth; // last established window size (recalculated during resize event)
var tooLittle = 8; // don't grow images if re-size only increased width by this amount or less
var initLoad = true;
var initSize = winWidth - pageMargin;
if (initSize > 946) { // redraw rows
    $photos = $('img[id^="pic"]');
    sizeProcessor();
}
else {
    $('html').css('overflow-x', 'scroll');
}
initLoad = false;
/*
 * PROCESSING WINDOW RESIZE EVENTS
 * Re-establish photo locations to recalculate captions.
 * Note: the semaphore 'resizeFlag' prevents multiple recursive calls during
 * rapid resize triggering. The timeout allows a quiet period until
 * another trigger can occur.
 */
$(window).on('resize', function () {
    // resize top part of page per new viewport:
    if (window.innerHeight < (vpHeight - 10) ||
        window.innerHeight > (vpHeight + 10)) {
        vpHeight = window.innerHeight;
        usable = vpHeight - sidePnlLoc;
        var mapHt_1 = Math.floor(0.65 * usable);
        chartHt = Math.floor(0.35 * usable);
        pnlHeight = (mapHt_1 + chartHt) + 'px';
        var mapHeight_1 = mapHt_1 + 'px';
        var chartHeight_1 = chartHt + 'px';
        $('#mapline').css('height', mapHeight_1);
        $('#chartline').css('height', chartHeight_1);
        $('#sidePanel').css('height', pnlHeight);
    }
    winWidth = $(window).width();
    if (winWidth < 960) {
        $('html').css('overflow-x', 'scroll');
    }
    else {
        $('html').css('overflow-x', 'hidden');
    }
    var runSizer = false;
    if (resizeFlag === false) {
        runSizer = true;
    }
    if (runSizer) {
        resizeFlag = true;
        setTimeout(function () {
            sizeProcessor();
            resizeFlag = false; // can now process another resize event
        }, 400);
    }
    else { // when zoom modifies image sizes:
        captureWidths();
        calcPos();
    }
});
function sizeProcessor() {
    winWidth = $(window).width(); // get new window width
    killEvents(); // NO $photos object NOW....
    imageSizer();
    prevWidth = winWidth;
    $photos = $('img[id^="pic"]');
    // now re-calc image positions
    captureWidths();
    calcPos();
    eventSet();
}
function imageSizer() {
    var picRowWidth = winWidth - pageMargin;
    if (initLoad) { // initial page width larger than 946
        drawRows(picRowWidth);
    }
    else { // normal resize event
        var delta = unProcSpace + Math.abs(winWidth - prevWidth);
        if (delta <= tooLittle) {
            unProcSpace = delta;
        }
        else {
            if (picRowWidth <= rowWidth) {
                drawRows(rowWidth);
            }
            else {
                drawRows(picRowWidth);
            }
        }
    }
}
