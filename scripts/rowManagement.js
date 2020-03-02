/* Zoom detection is not provided as standard, and is browser-dependent. 
 * The methodology employed here works well only where the devicePixelRatio 
 * property actually reveals browser pixel-scaling. As an example, the method 
 * works for Firefox and Chrome, but not Safari: Safari always reports "2" 
 * for retina displays, and "1" otherwise. This ratio is otherwise used to 
 * decide whether or not to allow image sizing to take place. 
 * Where devicePixelRatio is not supported (e.g. Safari), another method is 
 * used: ratio of current window to available screen width). The latter 
 * works only for zooming out, not on zooming in.
 */
var usePixelRatio;
(function browserType() { 
    if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) !== -1 ) {
        usePixelRatio = false; }
    else if(navigator.userAgent.indexOf("Chrome") !== -1 ) {
    	usePixelRatio = true; }
    else if(navigator.userAgent.indexOf("Safari") !== -1) {
        usePixelRatio = false; }
    else if(navigator.userAgent.indexOf("Firefox") !== -1 ) {
        usePixelRatio = true; }
    else if((navigator.userAgent.indexOf("MSIE") !== -1 ) || (!!document.documentMode === true )) { //IF IE > 10 
        usePixelRatio = false; }  
    else {
        usePixelRatio = false; }
}());
var zoomMax = screen.width;
var winWidth = $(window).width();
var winrat; // ratio of window to available screen width: use when can't use devicePixelRatio

// initialize variables for original page load:
var resizeFlag = false;  // semaphore: don't execute resize event code if true
var unProcSpace = 0;  // used to detect multiple small incremental growth in resizing
var prevWidth = winWidth; // last established window size (recalculated during resize event)
var tooLittle = 8;  // don't grow images if re-size only increased width by this amount or less

// initial page operation
var initLoad = true;
var initSize = winWidth - 24;
if (initSize > 946) { // redraw rows
    sizeProcessor();
}
initLoad = false;
//

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
    winrat = winWidth/zoomMax;
    var runSizer = false;
    // var pxrat = window.devicePixelRatio; This can change over time!!
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
    var picRowWidth = winWidth - 24;
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

