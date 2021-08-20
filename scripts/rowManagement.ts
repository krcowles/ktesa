/**
 * @fileoverview This script manages image sizes in a row
 * 
 * @author Ken Cowles
 * @version 1.0 Original release 
 * @version 1.1 added jsDocs
 * @version 2.0 Typescripted, with some type errors corrected; jsDocs no longer needed
 * @version 3.0 Updated for new 'popupCaptions.js' routine (replaced 'captions.js')
 */
 
// initialize variables for original page load:
var resizeFlag = false;  // semaphore: don't execute resize event code if true
var unProcSpace = 0;  // used to detect multiple small incremental growth in resizing
var prevWidth = winWidth; // last established window size (recalculated during resize event)
var tooLittle = 8;  // don't grow images if re-size only increased width by this amount or less

/**
 * PROCESSING WINDOW RESIZE EVENTS
 * Note: the semaphore 'resizeFlag' prevents multiple recursive calls during 
 * rapid resize triggering. The timeout allows a quiet period until 
 * another trigger can occur.
 */
$(window).on('resize', function() {
    // resize top part of page per new viewport:
    if (window.innerHeight < (vpHeight - 10) || 
        window.innerHeight > (vpHeight + 10)) {
        setViewport();
    }
    winWidth = <number>$(window).width();
    if (winWidth < 960) {
        $('html').css('overflow-x', 'scroll');
    } else {
        $('html').css('overflow-x', 'hidden');
    }
    var runSizer = false;
    if (resizeFlag === false) {
        runSizer = true;
    }
    if (runSizer) {
            resizeFlag = true;
            setTimeout( function() {
                sizeProcessor();
                resizeFlag = false;  // can now process another resize event
            }, 400);
    } else {  // when zoom modifies image sizes:
            captureWidths();
            calculatePositions();
    }
});

function sizeProcessor() {
    winWidth = <number>$(window).width();  // get new window width
    var picRowWidth = winWidth - pageMargin;
    var delta = unProcSpace + Math.abs(winWidth - prevWidth);
    if (delta <= tooLittle) {
        unProcSpace = delta;
    } else {
        killEvents();
        captions = [];
        capTop = [];
        capLeft = [];
        capWidth = [];
        $('.popupCap').children().remove();
        $('.popupCap').css('display', 'none');
        if (picRowWidth <= rowWidth) {
            drawRows(rowWidth); // observe min pg width
        } else {
            drawRows(picRowWidth);
        }
        $photos = $('img[id^=pic');
        initializePopupCaptions();
    }
    prevWidth = winWidth;
}
