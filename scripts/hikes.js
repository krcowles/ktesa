$( function () { // when page is loaded...

/* The following global variable assignments are associated with the routines
 * which manage the sizing of rows (with fixed margin as window frame grows/shrinks).
 */
// window size and margin calculations; NOTE: innerWidth provides the dimension inside the border
var winWidth = $(window).width(); // initial document width
const bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
var maxRow = 0; // width of biggest row on initial page loading (used to maintain consistent margin)
var initMarg; // this is space between rows of images & page border (calc after maxRow is determined)
var minWidth = $('body').css('min-width'); // normally 960
var pxLoc = minWidth.indexOf('px');
minWidth = parseFloat(minWidth.substring(0,pxLoc));
// staging the initial execution
var notInit = true; // state of initial loading process
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
var msg, i, j, k;

// Variable used as a basename to construct keys for storing data in session memory
var ssdat;

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
$images.each( function() {
	$(this).css('cursor','pointer');
});
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
/* problems with refresh in Chrome prompted the use of the following technique
   which "detects" a refresh condition and restores previously loaded values.
   User gets a window alert if sessionStorage is not supported and and is advised
   about potential refresh issues */
if ( window.sessionStorage ) {
	var tst = sessionStorage.getItem('prevLoad');
	if ( !tst ) { 
		// NORMAL FIRST-TIME ENTRY:
		getOrgDat();
		captureWidths();
		// get caption locations
		calcPos(); 
	} else {  // REFRESH ENTRY
		getOrgDat();
		// retrieve location data
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
	}  // end of session storage value check
}  else {
	window.alert('Browser does not support Session Storage\nRefresh may cause problems');
	// code with no session storage support...
	getOrgDat();
	captureWidths();
	// get caption locations
	calcPos();
}  // end of session storage IF
if ( window.sessionStorage ) { 
	sessionStorage.setItem('prevLoad','2.71828'); // Euler's number
}
// Establish a link below the iframe map for full page map display:
if (mapPresent) {
	// make map link and place below map
	htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
		mapBot + 'px;" href="' + fullMap + '" target="_blank">Click for full-page map</a>';
$('.lnkList').after(htmlLnk);
}
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
			orgImgList.push(rowDat);
			if (window.sessionStorage) {
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
			}
			noOfImgs++;
			rowDat = [];  //  orgImgList.push will NOT work properly without this!!!
			j++
		});
		orgRowCnts.push(j);
		i++;
		if ( maxRow < rwidth ) {
			maxRow = rwidth;  
		}
	});
	initMarg = minWidth - maxRow; // this will be the "constant" amt. of margin for rows
	if (initMarg < 24) {
		initMarg = 24;
	}
	if (window.sessionStorage) {
		sessionStorage.setItem('firstMarg',initMarg);
		sessionStorage.setItem('imgCnt',noOfImgs);
		//msg = '<p>Total no of images is ' + noOfImgs + '</p>';
		//$('#dbug').append(msg);
	}
}		
// function to capture *current* image widths & map link loc
function captureWidths() {
	i = 0;
	$images.each( function() {
		capWidth[i] = this.width + 'px';
		pwidth = 'pwidth'+ i;
		if (window.sessionStorage) {
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
		if (window.sessionStorage) {
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
		if ( window.sessionStorage ) {
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


/* PROCESSING WINDOW RESIZES AS EVENTS
 * Re-establish photo locations to recalculate captions
 * as well as the iframe full-page map link, if present
 */
$(window).resize( function() {
	calcPos();  // note: resets position-based session memory variables as well
	if (mapPresent) {
		mapWidth = $maps.attr('width');
		mapWidth = parseFloat(mapWidth);
		lnkLoc = ( mapWidth - 160 ) / 2;
		mapPos = $maps.offset();
		mapLeft = mapPos.left + lnkLoc;
		mapBot = mapPos.top + mapWidth + 15;
		if ( window.sessionStorage ) {
			sessionStorage.setItem('mleft',mapLeft);
			sessionStorage.setItem('mbot',mapBot);
		}
		// place link to full-size map below iframe;
		var mapLinkNode = document.getElementById('mapLnk');
		var mapLinkContainer = mapLinkNode.parentNode;
		mapLinkContainer.removeChild(mapLinkNode);
		htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
				mapBot + 'px;" href="' + fullMap + '" target="_blank">Click for full-page map</a>';
		$('.lnkList').after(htmlLnk);
	}
});

});
