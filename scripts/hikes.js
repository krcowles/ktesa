$( function () { // when page is loaded...

/* Zoom detection is not provided as standard, and is browser-dependent. The methodology
 * employed here works well only where the devicePixelRatio property actually reveals
 * browser pixel-scaling. As an example, the method works for Firefox and Chrome, but
 * not Safari. Safari always reports "2" for retina displays, and "1" otherwise.
 * This ratio is used to decide whether or not to allow image sizing to take place.
 * Where devicePixelRatio is not supported, another method is used (ratio of current window
 * to available screen width). The latter works only for zooming out, not on zooming in.
 */
var usePixelRatio;
(function browserType() { 
     if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1 ) {
        usePixelRatio = false; }
    else if(navigator.userAgent.indexOf("Chrome") != -1 ) {
    	usePixelRatio = true; }
    else if(navigator.userAgent.indexOf("Safari") != -1) {
        usePixelRatio = false; }
    else if(navigator.userAgent.indexOf("Firefox") != -1 ) {
        usePixelRatio = true; }
    else if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )) { //IF IE > 10 
        usePixelRatio = false; }  
    else {
        usePixelRatio = false; }
}());
var zoomMax = screen.width;
var winWidth = $(window).width();
var winrat; // ratio of window to available screen width: use when can't use devicePixelRatio

/* The following global variable assignments are associated with the routines
 * which manage the sizing of rows (with fixed margin as window frame grows/shrinks).
 */
const GROW = 1;
const SHRINK = 0;

// window size and margin calculations; NOTE: innerWidth provides the dimension inside the border
var bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
if (bodySurplus < 24) {
	bodySurplus = 24;
}   // var can actually be negative on initial load if frame is smaller than body min-width
var maxRow = 0; // width of biggest row on initial page loading (used to maintain consistent margin)
var initMarg; // this is space between rows of images & page border (calc after maxRow is determined)
var minWidth = $('body').css('min-width'); // normally 960
var pxLoc = minWidth.indexOf('px');
minWidth = parseFloat(minWidth.substring(0,pxLoc));
// staging the initial execution
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
var imgNoAtEnd0;  // image no of last img in row0 - used to identify when to add imgs/row


// generic
var msg, i, j, k, n;

// Variables used as a basename to construct keys for storing data in session memory
var ssdat;
var rowcnt;

// GPSV map options
var mapDisplayOpts = '&show_markers_url=true&street_view_url=true&map_type_url=GV_HYBRID&zoom_url=%27auto%27&zoom_control_url=large&map_type_control_url=menu&utilities_menu=true&center_coordinates=true&show_geoloc=true&marker_list_options_enabled=true&tracklist_options_enabled=true';

/*  MAPS, PHOTO LINKS, CAPTIONS and POP-UP VARIABLES
 *  Note that map links are expected to be in the form of relative urls (../maps/xyz_geomap.html)
 *  and will be re-set to point to the php map-processing page using 'mapDisplayOpts'
 */
// jQuery objects & variables
var $images = $('img[id^="pic"]');
var noOfPix = $images.length;
var $maps = $('iframe');
var mapPresent = false;
if ($maps.length) {
	mapPresent = true;
	var orgMapLink = $('#theMap').attr('src');
	var fullMap = orgMapLink + mapDisplayOpts;
}
var $desc = $('.captionList li');
var $links = $('.lnkList li');
// space down for map link when map is in bottom row
var $rowDivs = $('div[class="Solo"]');
if ($rowDivs.length === 0) {
	$('#postPhoto').css('margin-top','40px');
}
var $rows = $('div[id^="row"]');
var noOfRows = $rows.length;
var rowht;
var imgwd;
var rowHts = new Array();
var rowWds = new Array();

// argument passed to popup function
var picSel;
// string vars photo links
var htmlLnk;
var desc;
var htmlDesc;
var FlickrLnk;
// caption position vars
var capTop = new Array();
var capLeft = new Array();
var capWidth = new Array();
var picId;
var picPos;
// map position vars
var mapPos;
var mapLeft;
var mapWidth;
var mapHeight;
var mapBot;
var lnkLoc;
// keys for stashing data into session storage
var mleft;
var mbot;
var pwidth;
var pleft;
var ptop;

/*  --- EXECUTION BEGINS HERE ---
 *      Functions are listed after the active code and constitute a significant 
 *      portion of the code utilized to effect the management of rows.
 *      The operation of the code is somewhat dependent on whether or not sessionStorage
 *      is available with the browser used, which most browsers support.
 */
/* Not implemented at this time....
// HIDE/SHOW images on click: definition:
$('#photoDisplay').on('click', function() {
	if ($(this).text() == 'Hide Images') {
		this.textContent = 'Show Images';
		$rows.each( function() {
			$(this).css('display','none');
		});
		
	} else {
		this.textContent = 'Hide Images';
		$rows.each( function() {
			$(this).css('display','block');
		});
	}
});
*/
var sessSupport = window.sessionStorage ? true : false

/* problems with refresh in Chrome prompted the use of the following technique
   which "detects" a refresh condition and restores previously loaded values.
   User gets a window alert if sessionStorage is not supported and and is advised
   about potential refresh issues */
if ( sessSupport ) {
	var tst = sessionStorage.getItem('prevLoad');
	if ( !tst ) { 
		// NORMAL FIRST-TIME ENTRY:
		getOrgDat();
		captureWidths();
		// get caption locations
		calcPos(); 
	} else {  // REFRESH ENTRY
		// retrieve location data (pic/iframe data is string type and does not need 
		//   to be converted to numeric)
		for ( i=0; i<noOfPix; i++ ) {
			pwidth = 'pwidth' + i;
			capWidth[i] = sessionStorage.getItem(pwidth);
		}
		if (mapPresent) {
			mapLeft = sessionStorage.getItem('mleft');
			mapBot = sessionStorage.getItem('mbot');
		}
		for ( i=0; i<noOfPix; i++ ) {
			pleft = 'pleft' + i;
			capLeft[i] = sessionStorage.getItem(pleft);
			ptop = 'ptop' + i;
			capTop[i] = sessionStorage.getItem(ptop);
		}
		// retrieve initial image data: some of these need to be type numeric
		noOfImgs = parseFloat(sessionStorage.getItem('imgCnt'));
		initMarg = parseFloat(sessionStorage.getItem('firstMarg'));
		maxRow = parseFloat(sessionStorage.getItem('mrowwidth'));
		var initRowDat = [];
		for (i=0; i<noOfImgs; i++) {
			// these are all type string and will be used as such in row creation
			ssdat = 'ssdat' + i + '_0';
			initRowDat[0] = sessionStorage.getItem(ssdat);
			ssdat = 'ssdat' + i + '_1';
			initRowDat[1] = sessionStorage.getItem(ssdat);
			ssdat = 'ssdat' + i + '_2';
			initRowDat[2] = sessionStorage.getItem(ssdat);
			ssdat = 'ssdat' + i + '_3';
			initRowDat[3] = sessionStorage.getItem(ssdat);
			ssdat = 'ssdat' + i + '_4';
			initRowDat[4] = sessionStorage.getItem(ssdat);
			ssdat = 'ssdat' + i + '_5';
			initRowDat[5] = sessionStorage.getItem(ssdat);
			orgImgList.push(initRowDat);
			initRowDat = [];
		}
		for (j=0; j<noOfRows; j++) {
			rowcnt = "row" + j + "Count";
			orgRowCnts[j] = parseFloat(sessionStorage.getItem(rowcnt));
		}
	}  // end of session storage value check
}  else {
	window.alert('Browser does not support Session Storage\nRefresh may cause problems');
	// code with no session storage support...
	getOrgDat();
	captureWidths();
	// get caption locations
	calcPos();
}  // end of session storage IF

if ( sessSupport ) { 
	sessionStorage.setItem('prevLoad','2.71828'); // Euler's number
}
// Establish a link below the iframe map for full page map display:
if (mapPresent) {
	// make map link and place below map
	htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
		mapBot + 'px;" href="' + fullMap + '" target="_blank">Click for full-page map</a>';
$('.lnkList').after(htmlLnk);
}
/* CALCULATE THE POINT at which the image sizer can add a photo to the first row and re-size
 * If the current winWidth is greater than the width of the page's initially loaded row width
 * PLUS the next available image in the next row (row1), recalculate the number of images per row
 * and resize the rows. Note: no need if there is only one row.
 */ 
imgNoAtEnd0 = document.getElementById('row0').childNodes.length - 1;
tippingPoint(imgNoAtEnd0);  // set point at which to recalculate no of images per row
 
// Now that everything is done, enable events
eventSet(); // turn on the image events
resizeFlag = false;

// if the winWidth > triggerWidth, grow the rows before proceeding
// remember that triggerWidth is arbitrarily established during var declarations
if (winWidth > triggerWidth) {
	// set previous width to starting point to execute routine properly (make prevWidth < winWidth)
	prevWidth = triggerWidth;
	sizeProcessor();
}

/* EVENT MANAGEMENT DURING A RE-SIZE and RECALCULATION OF ROWS
 * During a resize, all the events associated with setting up captions and links
 * are contained in a function call. This way, all events can be enabled together, or
 * turned off together (see killEvents). Obviously, eventSet is also called after page load.
 */
function eventSet() {
	$images.each( function() {
		$(this).css('cursor','pointer');
	});
	// popup a description when mouseover a photo
	$images.css('z-index','1'); // keep pix in the background
	$images.on('mouseover', function(ev) {
		var eventObj = ev.target;
		picSel = eventObj.id;
		var picHdr = picSel.substring(0,3);
		if ( picHdr == 'pic' ) {
			picPop(picSel);
		}
	});
	// kill the popup when mouseout
	$images.on('mouseout', function() {
		$('.popupCap > p').remove();
		$('.popupCap').css('display','none');
	});
	// clicking images:
	$images.on('click', function(ev) {
		var clickWhich = ev.target;
		var picSrc = clickWhich.id;
		var picHdr = picSrc.substring(0,3);
		// again, no id for class='chart', hence no album links
		if ( picHdr == 'pic' ) {
			var picIndx = picSrc.indexOf('pic') + 3;
			var picNo = picSrc.substring(picIndx,picSrc.length);
			var j = 0;
			$('.lnkList li').each( function() {
				if ( j == picNo ) {
					FlickrLnk = this.textContent;
				}
				j++;
			});
			window.open(FlickrLnk);
		}
	}); 
}
// turn off events during resize until finished resizing
function killEvents() {
	$images.off('mouseover');
	$images.off('mouseout');
	$images.off('click');    // specifying multiple events in one call gave error
	$images = null;
}

/* SOME FUNCTIONS TO SIMPLIFY MAIN ROUTINE CALLS:
 *  1. Function to capture -Initially Loaded- image data and save it in Session 
 *     Storage: getOrgDat();   This will be used to restore image rows as they were
 *     at load time whenever the window shrinks to or below the threshold value;
 *  2. Function to capture CURRENT image widths: captureWidths()
 *  3. Function to capture image positions on the current page:  calcPos()
 *  4. Function to display photo captions as popups:  picPop()
 *  5. Function to determine 'triggerPoint': window frame size at which rows can contain
 *     additional images:  tippingPoint(); NOTE: At this time only one image per row will
 *     be added or subtracted - this function is presumed to enable future changes
 */ 
function getOrgDat() {
	var rwidth;
	var $rowImgSet;
	var curRowHt;
	var rowDat = [5]; // each item in orgImgList is an array of [0] attr. type (id or class),
				  // [1] id/class, [2] height, [3] width, [4] src attribute
	i = 0;
	$rows.each( function() {  // get the row width for each loaded image row
		rwidth = 0;
		$rowImgSet = $(this).children();
		curRowHt = $($rowImgSet[0]).height(); // all heights are the same
		j = 0;
		$rowImgSet.each( function() { // establish id or class
			if (this.id === '') {
				rowDat[0] = 'class';
				rowDat[1] = $(this).attr('class');
			} else {
				rowDat[0] = 'id';
				rowDat[1] = this.id;
			}
			rowDat[2] = curRowHt;
			rwidth += parseFloat(this.width);
			rowDat[3] = parseFloat(this.width);
			rowDat[4] = this.src; 
			if ( $(this).attr('alt') !== 'undefined' ) {
				rowDat[5] = $(this).attr('alt');
			} else {
				rowDat[5] = '';
			}
			orgImgList.push(rowDat);
			if (sessSupport) {
				// save the data in browser memory
				ssdat = 'ssdat' + noOfImgs + '_0';
				sessionStorage.setItem(ssdat, rowDat[0]);
				ssdat = 'ssdat' + noOfImgs + '_1';
				sessionStorage.setItem(ssdat, rowDat[1]);
				ssdat = 'ssdat' + noOfImgs + '_2';
				sessionStorage.setItem(ssdat, rowDat[2]);
				ssdat = 'ssdat' + noOfImgs + '_3';
				sessionStorage.setItem(ssdat, rowDat[3]);
				ssdat = 'ssdat' + noOfImgs + '_4';
				sessionStorage.setItem(ssdat, rowDat[4]);
				ssdat = 'ssdat' + noOfImgs + '_5';
				sessionStorage.setItem(ssdat, rowDat[5]);
			}
			noOfImgs++;
			rowDat = [];  //  orgImgList.push will NOT work properly without this!!!
			j++
		});
		orgRowCnts.push(j);
		if (sessSupport) {
			rowcnt = "row" + i + "Count";
			sessionStorage.setItem(rowcnt,j);
		}
		i++;
		if ( maxRow < rwidth ) {
			maxRow = rwidth;  
		}
	});
	initMarg = minWidth - maxRow; // this will be the "constant" amt. of margin for rows
	if (initMarg < 24) {
		initMarg = 24;
	}
	if (sessSupport) {
		sessionStorage.setItem('firstMarg',initMarg);
		sessionStorage.setItem('imgCnt',noOfImgs);
		sessionStorage.setItem('mrowwidth',maxRow);
	}
}		
// function to capture *current* image widths & map link loc
function captureWidths() {
	i = 0;
	$images.each( function() {
		capWidth[i] = this.width + 'px';
		pwidth = 'pwidth'+ i;
		if (sessSupport) {
			sessionStorage.setItem(pwidth,capWidth[i]);
		}
		i++;
	});
	if (mapPresent) {
		//ASSUMPTION: width = height for iframes
		mapWidth = $('iframe').attr('width');
		mapWidth = parseFloat(mapWidth);
		lnkLoc = ( mapWidth - 160 ) / 2;
		mapPos = $('iframe').offset();
		mapLeft = mapPos.left + lnkLoc;
		mapBot = mapPos.top + mapWidth + 15;
		if (sessSupport) {
			sessionStorage.setItem('mleft',mapLeft);
			sessionStorage.setItem('mbot',mapBot);
		}
	}
}
// function to calculate current & (potentially) store location of images/captions
function calcPos() {
	for ( var j=0; j<noOfPix; j++ ) {
		picId = '#pic' + j;
		picPos = $(picId).offset();
		capTop[j] = Math.round(picPos.top) + 'px';
		capLeft[j] = Math.round(picPos.left) + 'px';
		if ( sessSupport ) {
			ptop = 'ptop' + j;
			pleft = 'pleft' + j;
			sessionStorage.setItem(ptop,capTop[j]);
			sessionStorage.setItem(pleft,capLeft[j]);
		} 
	}
}
// function to popup the description for the picture 'selected'
function picPop(picTarget) {
	// get the image number
	var argLgth = picTarget.length;
	var picNo = picTarget.substring(3,argLgth);
	// get the corresponding description
	desc = $desc[picNo].textContent;
	htmlDesc = '<p class="capLine">' + desc + '</p>';
	$('.popupCap').css('display','block');
	$('.popupCap').css('position','absolute');
	$('.popupCap').css('top',capTop[picNo]);
	$('.popupCap').css('left',capLeft[picNo]);
	$('.popupCap').css('width',capWidth[picNo]);
	$('.popupCap').css('z-index','10');
	$('.popupCap').prepend(htmlDesc);
}
// function to determine window size that is threshold for re-calculating # of images/row
function tippingPoint(oldLastImg) {
	if (noOfRows > 1) {
		var nxtImgSize = $images[oldLastImg].width;
		triggerPoint = maxRow + nxtImgSize;
	} else {
		triggerPoint = 10000; // ain't gonna happen
	}
}

/* PROCESSING WINDOW RESIZE EVENTS
 * Re-establish photo locations to recalculate captions, as well as the iframe 
 * full-page map link, if present;
 * Note: the semaphore 'resizeFlag' prevents multiple recursive calls during rapid
 * resize triggering. The timeout allows a quiet period until another trigger can occur.
 */
$(window).resize( function() {
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
	killEvents();  // NO $images object NOW....
	imageSizer(winWidth); // $images object is restored with new values
	prevWidth = winWidth;
	// now re-calc image & iframe positions
	captureWidths();
	calcPos();
	eventSet();
	if (mapPresent) {
		// place link to full-size map below iframe;
		var lnkNode = document.getElementById('mapLnk');
		var lnkParent = lnkNode.parentNode;
		lnkParent.removeChild(lnkNode);
		htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
				mapBot + 'px;" href="' + fullMap + '" target="_blank">Click for full-page map</a>';
		$('.lnkList').after(htmlLnk);	
	}
}

/* ROW-SIZING AND RE-DRAWING FUNCTIONS: CALLED AS NEEDED FROM RESIZE (or at LOAD-TIME)
 *  The imageSizer() function is the main execution program. It first determines whether
 *  or not the rows should be restored to their original form (whenever the window size
 *  is reduced to the triggerWidth or smaller), and if not, whether or not the rows
 *  should be redrawn with a different number of images per row. If the rows need to be
 *  restored to their original form, then restoreOrgDat() is invoked. If the rows need to
 *  have their image count adjusted, then redrawRows() is invoked. If neither of these
 *  two processes are required, then imageSizer will resize the images using the current
 *  number and context of $rows. Note that re-sizing is conditional - only if the window
 *  size has increased the window frame 'available space' in excess of 'tooLittle'. 
 *  These less-than-sufficient increases are accumulated in 'unProcSpace' so that when
 *  that quantity indicates a sufficiently large total increase, resizing will occur.
 */
function imageSizer(targWidth) {
	// local vars
	var newWidth;
	var $imgInRow;
	var newStyle;
	var newImgHtml;
	var space;
	var runAlgorithm = true;
	var nxtWidth;
	var curWidth;
	var scaling;
	var newHt;
	var imgWidth;
	var nxtImgWidth;
	var rowHtml;
	var rowId;
	if (targWidth <= triggerWidth) {  // then restore to original size
		// don't bother if previous width was already <= triggerWidth; (has already been restored)
		if (prevWidth > triggerWidth) {
			restoreOrgDat();			
		} // end of restore to original
	} else { // targWidth > triggerWidth  - there are no other possibilites....
		if (targWidth > prevWidth) { // grow, but:
			// don't bother if increase is too small...
			space = targWidth - prevWidth;
			if ( space <= tooLittle ) {
				runAlgorithm = false;
				unProcSpace += space;
				if ( unProcSpace > tooLittle ) {
					space = unProcSpace;
					unProcSpace = 0;
					runAlgorithm = true;
				}
			} 
		} else {
			// should there be a test for shrinking too little as well?
		}
		if ( runAlgorithm === true ) {
			// calculate the space available for rows after addressing needed margins:
			nxtWidth = targWidth - bodySurplus - initMarg;
			// check to see if need to redraw rows due to growth or shrinkage:
			if (redrawn === false && nxtWidth >= triggerPoint ) {
				if (orgRowCnts.length > 1) {
					redrawRows(GROW);
				}
			} else { 
				if (redrawn === true && nxtWidth < triggerPoint) {
					redrawRows(SHRINK);
				}
			}
		    /* ASSUMPTIONS: there currently exists a viable (though outdated) set of 
		     * rows with an accurate row count. The current height/widths can be
		     * adjusted up/down to obtain new values and the rows redrawn.
		     */
		    for (j=0; j<noOfRows; j++) {
		    	rowId = '#row' + j;
		    	curWidth = 0;
		    	rowHtml = '';
		    	$imgInRow = $(rowId).children();
		    	$imgInRow.each( function() {
					curWidth += parseFloat(this.width);
				});
				scaling = nxtWidth/curWidth;  // either larger or smaller...
				newHt = Math.floor(scaling * $imgInRow[0].height)
				newWidth = 0;
				$imgInRow.each( function() {
		    	if (newWidth === 0) {
						newStyle = '""';
					} else {
						newStyle = '"margin-left:1px;"';
					}
					imgWidth = Math.floor(scaling * this.width);
					if (this.id == 'theMap') {
						newImgHtml = '<iframe id="theMap" style=' + newStyle +
							' height="' + newHt + '" width="' + newHt + '" src="' +
							this.src + '"></iframe>';
					} else {
						if (this.id == '') {
							newImgHtml = '<img class="' + this.class  + '" style=' + 
							  newStyle + ' height="' + newHt + '" width="' + 
							  imgWidth + '" src="' + this.src + '" alt="' +
							  this.alt + '" />';
						} else {
							newImgHtml = '<img id="' + this.id + '" style=' + newStyle +
							  ' height="' + newHt + '" width="' + imgWidth + '" src="' +
							  this.src + '" alt="' + this.alt + '" />';
						}
					}
					rowHtml += newImgHtml;
					newWidth = 100; // anything but 0
				});  // end of processing each image in the row
				$(rowId).html(rowHtml);
				//document.getElementById(rowId).innerHTML = rowHtml;
		    }
		} // end of "run algorithm" section
	} // end of else (re-size rows)
	$images = $('img[id^="pic"]');
} // end of imageSizer function
function restoreOrgDat() {
 	/* NOTE: When rows are re-drawn with more images/row than at original load, it might
 	 * result in fewer rows. This function therefore assumes a 'blank' starting point.
 	 */
 	var rowHtml;
 	var rowDisplay = '';
 	imgNo = 0;
 	$rows.each( function() {
 		$(this).remove();
 	});
	for (k=0; k<orgRowCnts.length; k++) {  // each row in the original page load
		rowHtml = '<div id="row' + k + '" class="ImgRow">';
		for (n=0; n<orgRowCnts[k]; n++) {  // each img in the original row
			if (n === 0) {
				picMarg = '""'; 
			} else {
				picMarg = '"margin-left:1px;"';
			}
			if (orgImgList[imgNo][1] === 'theMap') {
				rowHtml += '<iframe style=' + picMarg + ' id="theMap" height="' + 
				   orgImgList[imgNo][2] + '" width="' + orgImgList[imgNo][2] + '" src="' +
				   orgImgList[imgNo][4] + '"></iframe>';
			} else {
				// construct non-map item	
				rowHtml += '<img style=' + picMarg + ' ' + orgImgList[imgNo][0] + '="' + 
					orgImgList[imgNo][1] + '" height="' + orgImgList[imgNo][2] + 
					'" width="' + orgImgList[imgNo][3] + '" src="' + orgImgList[imgNo][4] +
					'" alt="' + orgImgList[imgNo][5] + '" />';
			}
			imgNo++;
		}  // end of single row creation for loop
		rowHtml += '</div>';
		rowDisplay += rowHtml;
	}  // end of row-creation for loop
	$('.captionList').before(rowDisplay);
	$rows = $('div[id^="row"]');
	noOfRows = $rows.length;
}
function redrawRows(direction) {
	var remaining = noOfImgs;  // total number of images to process
	var imgIndx = 0;
	var dynIndx;
	var bigRowHt;
	var widthAdj; 
	var thisImg;
	var imgSrc;
	var rowHtml;
	var rowId;
	var imgStyle;
	var orgImgDat = [];
	
	if (direction === GROW) {
		/* Add 1 image to each row as able
		 * NOTE: redrawRows() function not called if original load has only 1 row
		 */
		for (i=0; i<noOfRows; i++) {
			rowId = '#row' + i;
			bigRows[i] = $(rowId).children().length + 1
			if (remaining > bigRows[i]) {
				remaining -= bigRows[i];
			} else {
				bigRows[i] = remaining;
				break;
			}
		}
		// remake the new row html (all same hts) and replace current row html
		imgNo = 0;
		for (j=0; j<bigRows.length; j++) {
			rowId = '#row' + j;
			rowHtml = '';
			dynIndx = imgIndx + bigRows[j];
			for (k=imgIndx; k<dynIndx; k++) {
				/* At the beginning of each row, the initial height will be established
				 * as the height of the beginning image when originally loaded. The heights
				 * and widths will be scaled to fit the window when this function returns
				 * to caller
				 */
				thisImg = orgImgList[imgNo][3];
				if (k === imgIndx) { 
					imgStyle = '""';
					bigRowHt = orgImgList[imgNo][2];	
				} else {
					imgStyle = '"margin-left:1px;"';
					if (orgImgList[imgNo][2] != bigRowHt) {
						//scale width accordingly
						widthAdj = parseFloat(bigRowHt)/parseFloat(orgImgList[imgNo][2]);
						thisImg = Math.floor(widthAdj * parseFloat(orgImgList[imgNo][3]));
					} 
				}
				imgSrc = orgImgList[imgNo][4];
				if (orgImgList[imgNo][1] === 'theMap') {
					rowHtml += '<iframe style=' + imgStyle + ' id="theMap" height="' + 
					bigRowHt  + '" width="' + bigRowHt + '" src="' + imgSrc + '"></iframe>'; 
				} else {
					imgAlt = 0;
					if (orgImgList[imgNo][0] === 'class') { // it's a class attribute-based item
						rowHtml += '<img style=' + imgStyle + ' class="' + 
						orgImgList[imgNo][1] + '" height="' + bigRowHt + '" width="' + 
						thisImg + '" src="' + imgSrc + '" alt="' + orgImgList[imgNo][5]+ '" />';
					} else { // it's an id attribute-based item
						rowHtml += '<img style="' + imgStyle + '" id="' + orgImgList[imgNo][1] + 
						'" height="' + bigRowHt + '" width="' + thisImg + '" src="' + 
						imgSrc + '" alt="' + orgImgList[imgNo][5] + '" />';
					}
				}
				imgNo++;
			}
			$(rowId).html(rowHtml);
			imgIndx += bigRows[j];
		}
		/* Adding images to rows may reduce the total row count: */
		if (noOfRows > bigRows.length) {  // if redraw has resulted in fewer rows:
			var excess = noOfRows - bigRows.length;
			var nxtRow = bigRows.length;
			for (i=0; i<excess; i++) {
				rowId = '#row' + nxtRow;
				$(rowId).remove();
				nxtRow++;
			}
		}
		$rows = null;
		$rows = $('div[id^="row"]');
		noOfRows = $rows.length;
		redrawn = true;
	} else {
		restoreOrgDat();
		redrawn = false;
	}
}
 
});  // end of 'page (DOM) loading complete'
