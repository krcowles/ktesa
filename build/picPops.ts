declare var descs: string;
declare var capts: string;
declare var phMaps: string[];
var $photos: JQuery<HTMLElement>
var noOfPix: number;
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
    
var emode = false; // edit mode requires caption offset
var pageType = $('#ptype').text();
if (pageType.substring(0,4) === 'Edit' && pageType.length > 4) {
    pageType = 'Edit';
    var newUplds = true;
} else {
    var newUplds = false;
}
var caps = true;
// for our friendly typescript that can't see that $photos actually does get defined:
$photos = $('img[id^="pic"]');
if (pageType === 'Hike') {
    $photos = $('img[id^="pic"]');
    var phTitles = descs.slice();
    var phDescs = capts.slice();
} else if (pageType === 'Validate' || pageType == 'Finish' || pageType == 'Edit') {
    $photos = $('.allPhotos');
}
if (pageType === 'Edit') { 
    // the edit page requires caption offset for checkboxes
    emode = false;
    caps = false;
}
// Don't activate executeCaptions until that div is tab-selected:
if( pageType === 'Edit' && !newUplds) {
    $('#t2').on('click', function(ev) {
        ev.preventDefault();
        setTimeout( function() {
            noOfPix = $photos.length;
            executeCaptions();
        }, 100); 
    });  
} else {
    noOfPix = $photos.length;
    executeCaptions();
}
function executeCaptions() {
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
                for (let i=0; i<noOfPix; i++ ) {
                        pwidth = 'pwidth' + i;
                        capWidth[i] = sessionStorage.getItem(pwidth);
                }
                for (let i=0; i<noOfPix; i++ ) {
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
            capWidth[i] = this.style.width + 'px';
            pwidth = 'pwidth'+ i;
            if (sessSupport) {
                    sessionStorage.setItem(pwidth,capWidth[i]);
            }
        });
    }
    function calcPos() {
        $photos.each( function(j) {
            picPos = <JQuery.Coordinates>$(this).offset();
            if (emode) {
                capTop[j] = Math.floor(picPos.top) + 20 + 'px';
            } else {
                capTop[j] = Math.floor(picPos.top) + 'px';
            }
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
    function picPop(photoName: string) {
        var picNo = 0;
        for (var x=0; x<noOfPix; x++) {
            if (photoName == phTitles[x]) {
                picNo = x;
                break;
            }
        }
        if (caps) {
            var htmlDesc = '<p class="capLine">' + photoName +
                ': ' + '<em>' + phDescs[picNo] + '</em></p>';
        } else {
            var htmlDesc = '<p class="capLine">' + photoName;
            if (phMaps[picNo] == '0') {
                htmlDesc += '<br /><span style="color:brown">No Location Data: ' +
                    'Photo Cannot Be Mapped</span></p>';
            } else {
                htmlDesc += '</p>';
            }
        }
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
        var targ = <HTMLImageElement>ev.target;
        var selected = targ.alt;
        picPop(selected);
    });
    // kill the popup when mouseout
    $photos.on('mouseout', function() {
        $('.popupCap > p').remove();
        $('.popupCap').css('display','none');
    });

    $(window).on('resize', function() {
        captureWidths();
        calcPos();
    });
}
// display message when mouseover on disabled map
var $mapmsg = $('<p id="nmp">No Location Data:<br />Photo Cannot Be Displayed on Map</p>');
$mapmsg.css({
    position: 'absolute',
    color: 'brown'
});
//var $mapbtns = $('input[type=checkbox]:disabled');
var $nomap = $('.nomap');
$nomap.each(function() {
    $(this).on('mouseover', function() {
        $(this).css('cursor', 'pointer');
        var loc = <JQuery.Coordinates>$(this).offset();
        $mapmsg.css({
            top: loc.top - 20,
            left: loc.left + 60
        });
        $(this).after($mapmsg);
        return;
    });
    $(this).on('mouseout', function() {
        var mout = $('#nmp');
        mout.remove();
        return;
    });  
});
