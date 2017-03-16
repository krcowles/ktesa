$( function () { // when page is loaded...

/* The following global variable assignments are associated with the routines
 * which manage the sizing of rows (with fixed margin as window frame grows/shrinks).
 */
const GROW = 0;
const SHRINK = 1;
// window size and margin calculations; NOTE: innerWidth provides the dimension inside the border
var winWidth = $(window).width(); // document width at page load
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
var initFlag = true; // state of initial loading process
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
	var	fullMap = $maps.attr('src') + mapDisplayOpts;
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

// point map links (if present) to the php-map processing page
var link;
var rel_link;
var mapname;
var mapnameLgth;
$('a').each( function() {
	link = $(this).attr('href');
	rel_link = link.substring(0,8);
	if (rel_link == '../maps/') {
		mapnameLgth = link.length;
		mapname = link.substring(8,mapnameLgth);
		link = '../maps/gpsvMapTemplate.php?map_name=' + mapname + mapDisplayOpts;
		$(this).attr('href',link);
	}
});
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
if (noOfRows > 1) {
	var $rowImgs = $($rows[1]).children();
	var row1TargWidth = $($rowImgs[0]).width();
	triggerPoint = maxRow + row1TargWidth;
} else {
	triggerPoint = 10000; // ain't gonna happen
}
// if the winWidth > triggerWidth, grow the rows before proceeding
// remember that triggerWidth is arbitrarily established during var declarations
if (winWidth > triggerWidth) {
	// set previous width to starting point to execute routine properly
	prevWidth = triggerWidth;
	imageSizer(winWidth);
	prevWidth = winWidth;
}
// Now that everything is done, enable events
eventSet(); // turn on the image events
initFlag = false; 
resizeFlag = false;

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


/* PROCESSING WINDOW RESIZE EVENTS
 * Re-establish photo locations to recalculate captions, as well as the iframe 
 * full-page map link, if present;
 * Note: the semaphore 'resizeFlag' prevents multiple recursive calls during rapid
 * resize triggering. The timeout allows a quiet period until another trigger can occur.
 */
$(window).resize( function() {
	if (resizeFlag === false) {
		resizeFlag = true;
		setTimeout( function() {	
			winWidth = $(window).width();  // get new window width
			imageSizer(winWidth);
			prevWidth = winWidth;
			// now re-calc image & iframe positions
			captureWidths();
			calcPos();
			if (mapPresent) {
				// place link to full-size map below iframe;
				var lnkNode = document.getElementById('mapLnk');
				var lnkParent = lnkNode.parentNode;
				lnkParent.removeChild(lnkNode);
				var mapLnk = $('iframe').attr('src');
				htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
						mapBot + 'px;" href="' + mapLnk + '" target="_blank">Click for full-page map</a>';
				$('.lnkList').after(htmlLnk);	
			}
			resizeFlag = false;  // can now process another resize event
		}, 400);
	}  // end of resizeFlag = false
});

/* ROW-SIZING AND RE-DRAWING FUNCTIONS: CALLED AS NEEDED FROM RESIZE (or LOAD-TIME)
 * The last two functions do the work of re-sizing (same no of images per row as 
 * previously maintained) or re-drawing (when more or fewer images per row can
 * or should be supported). Re-sizing is conditional - if the resize has grown or
 * shrunk the window frame 'available space' in excess of 'tooLittle'. Re-draws are
 * dependent on the increase being sufficiently large to accommodate another picture
 * (based on size at load time) in a row, or conversely, the decrease is sufficiently 
 * large to warrant reducing the number of images per row. redrawRows() is only called
 * from the imageSizer() function.
 */
 function restoreOrgDat() {
 	var rowHtml;
 	imgNo = 0;
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
		$($rows[k]).replaceWith(rowHtml);
	}  // end of row-creation for loop
}
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
	if (targWidth <= triggerWidth) {  // then restore to original size
		if (prevWidth > triggerWidth) {
			// don't bother if previous width was already <= triggerWidth; already restored
			killEvents();
			restoreOrgDat();			
			// were there additional rows owing to increased image size?
			if (redrawn) {
				var orgCnt = orgRowCnts.length; // original no of rows when loaded
				var excess = noOfRows - orgCnt;
				var indx;
				for (i=0; i<excess; i++) {  // won't execute if excess == 0
					indx = orgCnt + i;
					$rows[indx] = null;
				}
				noOfRows = $rows.length;
			}
			// test process
			if (noOfRows !== orgRowCnts.length) {
				window.alert("Resize no of rows got garbled");
			}
			$images = $('img[id^="pic"]');
			captureWidths();
			calcPos();
			eventSet();
		} // end of restore to original
	} else { // targWidth > triggerWidth
		if (targWidth >= prevWidth) { // grow, but:
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
		} 
		if ( runAlgorithm === true ) {
			// by definition, targWidth > triggerWidth => bodySurplus is positive
			nxtWidth = targWidth - bodySurplus - initMarg;
			// check to see if need to redraw rows due to growth or shrinkage:
			if (redrawn === false && nxtWidth >= triggerPoint ) {
				redrawRows(GROW);
			} else { 
				if (redrawn === true && nxtWidth < triggerPoint) {
					redrawRows(SHRINK);
				}
			}
			// DOES killevents need to be invoked???
			// resize images to fit in this larger-than-triggerWidth frame, larger or smaller
		    // than they were previously...
			$rows.each( function() {
				$imgInRow = $(this).children();
				// get current row widths to calculate scaling factor
				curWidth = 0;
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
						newImgHtml = '<img id="' + this.id + '" style=' + newStyle +
							' height="' + newHt + '" width="' + imgWidth + '" src="' +
							this.src + '" alt="" />';
					}
					$(this).replaceWith(newImgHtml);
					newWidth = 100; // anything but 0
					
				});  // end of processing each image in the row
			});  // end of processing each row
			$images = $('img[id^="pic"]');
			if ( initFlag === false ) {
				captureWidths();
				calcPos();
				eventSet();
			}
		} // end of "run algorithm" section
	} // end of else (re-size rows)
} // end of imageSizer function
function redrawRows(direction) {
	var remaining = noOfImgs;
	var imgIndx = 0;
	var dynIndx;
	var bigRowHt;
	var widthAdj; 
	var thisImg;
	var imgSrc;
	var rowHtml;
	
	if (direction === GROW) {
		//msg = '<p>Add image to first row and redraw</p>';
		//$('#dbug').append(msg);
		// re-calculate no of images per row and no of rows:
		if (noOfRows != 1) {  // unlikely, but just in case
			for (i=0; i<noOfRows; i++) {
				bigRows[i] = $($rows[i]).children().length + 1
				if (remaining > bigRows[i]) {
					remaining -= bigRows[i];
				} else {
					bigRows[i] = remaining;
					break;
				}
			}
		}
		// remake the new row html (all same hts) and replace current row html
		for (j=0; j<bigRows.length; j++) {
			rowHtml = '<div id="row' + j + '" class="ImgRow">';
			dynIndx = imgIndx + bigRows[j];
			for (k=imgIndx; k<dynIndx; k++) {  // the k-th image of all the images
				// any "borrowed" successor image in the new row must be scaled to current ht/wdth
				if (k === imgIndx) { // this will be the new row's consistent height
					bigRowHt = orgImgList[k][2];
				} else {
					if (orgImgList[k][2] != bigRowHt) {
						//scale width accordingly
						widthAdj = parseFloat(bigRowHt)/parseFloat(orgImgList[k][2]);
						thisImg = Math.floor(widthAdj * parseFloat(orgImgList[k][3]));
					} else {
						thisImg = orgImgList[k][3];
					}
				}
				imgSrc = orgImgList[k][4];
				if (orgImgList[j][1] === 'theMap') {
					rowHtml += '<iframe id="theMap" height="' + bigRowHt  +
						'" width="' + thisImg + '" src="' + imgSrc + '"></iframe>'; 
				} else {
					if (orgImgList[j][0] === 'class') { // it's a class attribute-based item
						rowHtml += '<img class="' + orgImgList[k][1] + '" height="' + bigRowHt + 
							'" width="' + thisImg + '" src="' + imgSrc + '" alt="" />';
					} else { // it's an id attribute-based item
						rowHtml += '<img id="' + orgImgList[k][1] + '" height="' + bigRowHt +
							'" width="' + thisImg + '" src="' + imgSrc + '" alt="" />';
					}
				}
			}
			rowHtml += '</div>';
			$($rows[j]).replaceWith(rowHtml);
			imgIndx += bigRows[j];
		}
		if (noOfRows > bigRows.length) {
			$($rows[noOfRows-1]).remove();
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
/*  may be used in debug process...
function readDat() {
	for (var n=0; n<noOfImgs; n++) {
		msg = '<p>For image' + n + ': ';
		for (var w=0; w<5; w++) {
			msg += orgImgList[n][w] + ', ';
			}
	msg += '<p>';
	$('#dbug').append(msg);
	}
} */
 
 
 
 
 
 
 
 
 
 
 
 
});  // end of 'page (DOM) loading complete'
