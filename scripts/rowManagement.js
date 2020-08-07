var winWidth = $(window).width(); 
// initialize variables for original page load:
var resizeFlag = false;  // semaphore: don't execute resize event code if true
var unProcSpace = 0;  // used to detect multiple small incremental growth in resizing
var prevWidth = winWidth; // last established window size (recalculated during resize event)
var tooLittle = 8;  // don't grow images if re-size only increased width by this amount or less
var initLoad = true;
var initSize = winWidth - pageMargin;
if (initSize > 946) { // redraw rows
    sizeProcessor();
} else {
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
$(window).resize( function() {
    // resize top part of page per new viewport:
    if ( newStyle && (window.innerHeight < (vpHeight - 10) || 
            window.innerHeight > (vpHeight + 10)) ) {
        vpHeight = window.innerHeight;
        usable = vpHeight - sidePnlLoc;
        mapHeight = Math.floor(0.65 * usable);
        chartHeight = Math.floor(0.35 * usable);
        pnlHeight = (mapHeight + chartHeight) + 'px';
        mapHeight += 'px';
        chartHeight += 'px';
        $('#mapline').css('height',mapHeight);
        $('#chartline').css('height',chartHeight);
        $('#sidePanel').css('height',pnlHeight);
    }
    winWidth = $(window).width();
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
            calcPos();
    }
});
function sizeProcessor() {
    winWidth = $(window).width();  // get new window width
    killEvents();  // NO $photos object NOW....
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
    if (initLoad) {  // initial page width larger than 946
        drawRows(picRowWidth);
    } else {  // normal resize event
        var delta = unProcSpace + Math.abs(winWidth - prevWidth);
        if (delta <= tooLittle) {
            unProcSpace = delta;
        } else {
           if (picRowWidth <= rowWidth) {
               drawRows(rowWidth);
           } else {
               drawRows(picRowWidth);
           }
        }
    }
}

