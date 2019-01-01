// Establish location of picture rows before attempting to capture positions
var newStyle;
if ($('#mapline').length) {
    newStyle = true;
    // setting up map & chart to occupy viewport space
    var vpHeight = window.innerHeight;
    var sidePnlPos = $('#sidePanel').offset();
    var sidePnlLoc = parseInt(sidePnlPos.top);
    var usable = vpHeight - sidePnlLoc;
    var mapHeight = Math.floor(0.65 * usable);
    var chartHeight = Math.floor(0.35 * usable);
    var pnlHeight = (mapHeight + chartHeight) + 'px';
    mapHeight += 'px';
    chartHeight += 'px';
    $('#mapline').css('height',mapHeight);
    $('#chartline').css('height',chartHeight);
    $('#sidePanel').css('height',pnlHeight);
} else {
    newStyle = false;
}

// begin caption popup code
var $photos = $('img[id^="pic"]');
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
function picPop(picTarget) {
    // get the corresponding description
    var picidlgth = picTarget.length;
    picNo = parseInt(picTarget.substring(3,picidlgth));
    var jqid = '#' + picTarget;
    var desc = $(jqid).attr('alt');
    htmlDesc = '<p class="capLine">' + desc + '</p>';
    $('.popupCap').css('display','block');
    $('.popupCap').css('position','absolute');
    $('.popupCap').css('top',capTop[picNo]);
    $('.popupCap').css('left',capLeft[picNo]);
    $('.popupCap').css('width',capWidth[picNo]);
    $('.popupCap').css('z-index','10');
    $('.popupCap').prepend(htmlDesc);
}
function eventSet() {
    // popup a description when mouseover a photo
    $photos.css('z-index','1'); // keep pix in the background
    // enable pointer to indicate 'mouseover-able'
    $photos.each( function(indx) {
        $(this).css('cursor','pointer');
    });
    $photos.on('mouseover', function(ev) {
        var eventObj = ev.target;
        picSel = eventObj.id;
        var picHdr = picSel.substring(0,3);
        if ( picHdr === 'pic' ) {
            picPop(picSel);
        }
    });
    // kill the popup when mouseout
    $photos.on('mouseout', function() {
        $('.popupCap > p').remove();
        $('.popupCap').css('display','none');
    });
    $photos.each( function(indx) {
        $(this).on('click', function() {
            var zpic = "/pictures/zsize/" + piclnks[indx] + "_z.jpg";
            window.open(zpic,"_blank");
        });
    });
}
// turn off events during resize until finished resizing
function killEvents() {
    $photos.off('mouseover');
    $photos.off('mouseout');
    $photos.off('click');
    $photos = null;
}
eventSet();
