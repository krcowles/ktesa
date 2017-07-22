$( function () { // when page is loaded...

var $photos = $('.allPhotos');
var noOfPix = $photos.length;
var picSel;
var capTop = new Array();
var capLeft = new Array();
var capWidth = new Array();
var picId;
var picPos;
// keys for stashing data into session storage
var pwidth;
var pleft;
var ptop;
window.alert("FIRST " + phTitles[0]);
var sessSupport = window.sessionStorage ? true : false;
/* problems with refresh in Chrome prompted the use of the following technique
   which "detects" a refresh condition and restores previously loaded values.
   User gets a window alert if sessionStorage is not supported and and is advised
   about potential refresh issues */
if ( sessSupport ) {
	var tst = sessionStorage.getItem('prevLoad');
        if ( !tst ) { 
		// NORMAL FIRST-TIME ENTRY:
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
            for ( i=0; i<noOfPix; i++ ) {
                    pleft = 'pleft' + i;
                    capLeft[i] = sessionStorage.getItem(pleft);
                    ptop = 'ptop' + i;
                    capTop[i] = sessionStorage.getItem(ptop);
            }
        }
}  else {
	window.alert('Browser does not support Session Storage\nRefresh may cause problems');
	// code with no session storage support...
	captureWidths();
	// get caption locations
	calcPos();
}  // end of session storage IF
// function to capture *current* image widths & map link loc
function captureWidths() {
    $photos.each( function(i) {
        capWidth[i] = this.width + 'px';
        pwidth = 'pwidth'+ i;
        if (sessSupport) {
                sessionStorage.setItem(pwidth,capWidth[i]);
        }
    });
}
function calcPos() {
    $photos.each( function(j) {
        picPos = $(this).offset();
        capTop[j] = Math.round(picPos.top) + 'px';
        capLeft[j] = Math.round(picPos.left) + 'px';
        if ( sessSupport ) {
            ptop = 'ptop' + j;
            pleft = 'pleft' + j;
            sessionStorage.setItem(ptop,capTop[j]);
            sessionStorage.setItem(pleft,capLeft[j]);
        } 
    });
}
// function to popup the description for the picture 'selected'
function picPop(photoName) {
    for (var x=0; x<noOfPix; x++) {
        if (photoName == phTitles[x]) {
            picNo = x;
            break;
        }
    }
    htmlDesc = '<p class="capLine">' + photoName + '</p>';
    $('.popupCap').css('display','block');
    $('.popupCap').css('position','absolute');
    $('.popupCap').css('top',capTop[picNo]);
    $('.popupCap').css('left',capLeft[picNo]);
    $('.popupCap').css('width',capWidth[picNo]);
    $('.popupCap').css('z-index','10');
    $('.popupCap').prepend(htmlDesc);
}
// enable pointer to indicate 'mouseover-able'
$photos.each( function() {
    $(this).css('cursor','pointer');
});
// popup a description when mouseover a photo
$photos.css('z-index','1'); // keep pix in the background
$photos.on('mouseover', function(ev) {
    var selected = ev.target.alt;
    picPop(selected);
});
// kill the popup when mouseout
$photos.on('mouseout', function() {
    $('.popupCap > p').remove();
    $('.popupCap').css('display','none');
});

$(window).resize( function() {
    captureWidths();
    calcPos();
});

});


