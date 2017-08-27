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

/*
 * Now set up for sizing rows to fit available window width, and by esp after resize
 */
var GROW = 1;
var SHRINK = 0;

// Window size: NOTE: innerWidth provides the dimension inside the border
var bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
if (bodySurplus < 24) {
    bodySurplus = 24;
}   // var can actually be negative on initial load if frame is smaller than body min-width
var maxRow = 0; // width of biggest row on initial page loading (used to maintain consistent margin)
var initMarg; // this is space between rows of images & page border (calc after maxRow is determined)
var minWidth = $('body').css('min-width'); // normally 960
var pxLoc = minWidth.indexOf('px');
minWidth = parseFloat(minWidth.substring(0,pxLoc));

// initialize variables for original page load:
var resizeFlag = true;  // semaphore: don't execute resize event code if true
var noOfImgs = 0;
var unProcSpace = 0;  // used to detect multiple small incremental growth in resizing
var prevWidth = winWidth; // last established window size (recalculated during resize event)
var triggerWidth = minWidth + 40; // nominal 20px on each side
var tooLittle = 8;  // don't grow images if re-size only increased width by this amount or less
var orgImgList = [];  // id/class, height & width of each image consecutively as loaded
var orgRowCnts = []; // the initial image counts in each row
// Variables associated with redrawing rows due to changing no of images in rows
var triggerPoint;
var redrawn = false;
var bigRows = [];
var imgNo;
var orgRow1Strt;  // first img in row1: used to determine pt at which image can be added to row
var LRnotFilled;  // if the last row is not "filled" with images, process sizing differently
var LRscaling;    // in the case of above 'true', grow by this factor instead of filling the row

// generic
var msg, i, j, k, n;

// Variables used as a basename to construct keys for storing data in session memory
var ssdat;
var rowcnt;

// locate rows of images
var $rows = $('div[id^="row"]');
var noOfRows = $rows.length;
var rowht;
var imgwd;
var rowHts = new Array();
var rowWds = new Array();
/* Calculate the last two row's widths: last & next-to-last; if the last row's width is
 * less than the next-to-last row's width by at least 12px, then apply the special
 * scaling factor to the last row, instead of completely filling that row. This will
 * happen when the LRnotFilled flag is true. This has to be set before beginning execution.
 */
if (noOfRows > 1) {
    var LRid = '#row' + (noOfRows -1); // last row
    var NLRid = '#row' + (noOfRows -2);  // next-to-last row
    var $NLRimgs = $(NLRid).children();
    var NLRwidth = 0;
    $NLRimgs.each( function() {
        NLRwidth += parseFloat(this.width);
    });
    var $LRimgs = $(LRid).children();
    var LRwidth = 0;
    $LRimgs.each( function() {
        LRwidth += parseFloat(this.width);
    });
    if (NLRwidth > (LRwidth + 12)) {  // 12 is arbitrary - just enough space to operate row-filled
        LRnotFilled = true;
    } else {
        LRnotFilled = false;
    }
} else {
    LRnotFilled = false;
}
var LRFlagAtLoad = LRnotFilled;

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
	if (usePixelRatio) {
		if (resizeFlag === false && window.devicePixelRatio === 1) {
			runSizer = true;
		}
	} else {
		if (resizeFlag === false && (winrat > 0.95 && winrat < 1.05)) {
			runSizer = true; 
		}
	}
	if (runSizer) {
		resizeFlag = true;
		setTimeout( function() {
			alert("RESIZE");
                    //sizeProcessor();
			resizeFlag = false;  // can now process another resize event
		}, 400);
	} else {  // when zoom modifies image sizes:
		captureWidths();
		calcPos();
	}
});

