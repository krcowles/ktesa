/**
 * @fileoverview Calculate and save photos parameters for the purpose of situating
 * captions accordingly
 * 
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
// Establish location of picture rows before attempting to capture positions
if ($('#mapline').length) {
    // setting up map & chart to occupy viewport space
    var vpHeight = window.innerHeight;
    var sidePnlPos = <JQuery.Coordinates>$('#sidePanel').offset();
    var sidePnlLoc = sidePnlPos.top;
    var usable = vpHeight - sidePnlLoc;
    var mapHt = Math.floor(0.65 * usable);
    var chartHt = Math.floor(0.35 * usable);
    var pnlHeight = (mapHt + chartHt) + 'px';
    var mapHeight   = mapHt + 'px';
    var chtHeight = chartHt + 'px';
    $('#mapline').css('height', mapHeight);
    $('#chartline').css('height', chtHeight);
    $('#sidePanel').css('height',pnlHeight);
}

// begin caption popup code
var $photos: any = $('img[id^="pic"]');
var noOfPix = $photos.length;
var picSel: string;
var capTop: string[] = [];
var capLeft: string[] = [];
var capWidth: string[] = [];
var picPos: JQuery.Coordinates;
// keys for stashing data into session storage
var pwidth: string;
var pleft: string;
var ptop: string;
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
            for ( let i=0; i<noOfPix; i++ ) {
                    pwidth = 'pwidth' + i;
                    capWidth[i] = <string>sessionStorage.getItem(pwidth);
            }
            for ( let i=0; i<noOfPix; i++ ) {
                    pleft = 'pleft' + i;
                    capLeft[i] = <string>sessionStorage.getItem(pleft);
                    ptop = 'ptop' + i;
                    capTop[i] = <string>sessionStorage.getItem(ptop);
            }
        }
}  else {
        window.alert('Browser does not support Session Storage\nRefresh may cause problems');
        // code with no session storage support...
        captureWidths();
        // get caption locations
        calcPos();
}  // end of session storage IF

/** 
 * function to capture *current* image widths & map link loc
 */
function captureWidths(): void {
    for (let k=0; k<$photos.length; k++) {
        let item = <HTMLElement>$photos[k];
        capWidth[k] = item.clientWidth + 'px';
        pwidth = 'pwidth'+ k;
        if (sessSupport) {
                sessionStorage.setItem(pwidth,capWidth[k]);
        }
    }
    return;
}
/**
 * function to save the top/left positioning of each photo
 */
function calcPos() {
    for (let m=0; m<$photos.length; m++) {
        let item = <HTMLElement>$photos[m];
        picPos = <JQuery.Coordinates>$(item).offset();
        capTop[m] = Math.round(picPos.top) + 'px';
        capLeft[m] = Math.round(picPos.left) + 'px';
        if ( sessSupport ) {
            ptop = 'ptop' + m;
            pleft = 'pleft' + m;
            sessionStorage.setItem(ptop,capTop[m]);
            sessionStorage.setItem(pleft,capLeft[m]);
        } 
    }
}
/**
 * function to popup the description for the picture 'selected'
 */
function picPop(picTarget: string) {
    // get the corresponding description
    var picidlgth = picTarget.length;
    var picNo = parseInt(picTarget.substring(3, picidlgth));
    var jqid = '#' + picTarget;
    var desc = $(jqid).attr('alt');
    var htmlDesc = '<p class="capLine">' + desc + '</p>';
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
    for (let n=0; n<$photos.length; n++) {
        let item = <HTMLElement>$photos[n];
        $(item).css('cursor','pointer');
    }
    $photos.on('mouseover', function(ev: Event) {
        var eventObj = <HTMLElement>ev.target;
        picSel = eventObj.id
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
    for (let t=0; t<$photos.length; t++) {
        let item = <HTMLElement>$photos[t];
        $(item).on('click', function() {
            var zpic = "/pictures/zsize/" + piclnks[t] + "_z.jpg";
            window.open(zpic,"_blank");
        });
    }
}
// turn off events during resize until finished resizing
function killEvents() {
    $photos.off('mouseover');
    $photos.off('mouseout');
    $photos.off('click');
    $photos = null;
}
eventSet();
