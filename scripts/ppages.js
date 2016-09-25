$( function () { // when page is loaded...

/* NOTE: this script has not yet been fully vetted, but seems to work */

    // object locations
    var $images = $('img[id^="pic"]');
    var noOfPix = $images.length;
    var $desc = $('.captionList li');
    var $links = $('.lnkList li');
    // argument passed to popup function
    var picSel;
    // string vars
    var imId;
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
	// for stashing into session storage
	var iwidth;
	var ileft;
	var itop;
	// generic
	var msg;
	var i;
	
	$images.each( function() {
		$(this).css('cursor','pointer');
	});
	/* problems with refresh in Chrome prompted the use of the following technique
	   which "detects" a refresh condition and restores previously loaded values.
	   User gets a window alert if sessionStorage is not supported and and is advised
	   about potential refresh issues */
	if ( window.sessionStorage ) { 
		var tst = sessionStorage.getItem('prevLoad');
		if ( !tst ) {
			// get caption locations & widths - both 'pics' and 'caps'
			calcPos(); 
		} else {  // Refresh: need to reload items for placing captions & map link
			for ( i=0; i<noOfPix; i++ ) {
				iwidth = 'iwidth' + i;
				capWidth[i] = sessionStorage.getItem(iwidth);
				ileft = 'ileft' + i;
				capLeft[i] = sessionStorage.getItem(ileft);
				itop = 'itop' + i;
				capTop[i] = sessionStorage.getItem(itop);
			}
		}  // end of session storage value check
	}  else {
		window.alert('Browser does not support Session Storage\nRefresh may cause problems');
		// code with no session storage support...
		calcPos();
	}  // end of session storage IF
	if ( window.sessionStorage ) { 
		sessionStorage.setItem('prevLoad','2.71828'); // Euler's number
	}

	// function to calculate current & (potentially) store location of images/captions
	function calcPos() {
		for ( var j=0; j<noOfPix; j++ ) { 
			// check to see if pic, capImg, or noCapImg
			var imId = $images[j].id;
			imId = imId.substring(0,3);
			if ( imId != 'noC' ) { // don't do anything for the 'noCapImg'
				if ( imId == 'cap' ) {
					picId = '#capImg' + (j);
				} else {
					picId = '#pic' + (j);
				}
				capWidth[j] = $(picId).width() + 14 + 'px';
				picPos = $(picId).offset();
				capTop[j] = picPos.top + 'px';
				capLeft[j] = picPos.left + 'px';
				if ( window.sessionStorage ) {
					iwidth = 'iwidth' + (j);
					itop = 'itop' + (j);
					ileft = 'ileft' + (j);
					sessionStorage.setItem(iwidth,capWidth[j]);
					sessionStorage.setItem(itop,capTop[j]);
					sessionStorage.setItem(ileft,capLeft[j]);
				} 
			}
		}
	}
			
    // function to popup the description for the picture 'selected'
    function picPop(popCap) {
        desc = $desc[popCap].textContent;
        htmlDesc = '<p class="capLine">' + desc + '</p>';
        $('.popupCap').css('display','block');
        $('.popupCap').css('position','absolute');
        $('.popupCap').css('top',capTop[popCap]);
        $('.popupCap').css('left',capLeft[popCap]);
        $('.popupCap').css('width',capWidth[popCap]);
        $('.popupCap').css('z-index','10');
        $('.popupCap').prepend(htmlDesc);
        //msg = '<p>popup loc: ' + capTop[popCap] + ', ' + capLeft[popCap] + ', ' + 
        //	capWidth[popCap] + '</p>';
        //$('#dbug').append(msg);
    }
    
    // popup a description when mouseover a picture
    $images.css('z-index','1'); // keep pix in the background
    $images.on('mouseover', function(ev) {
        var imNo;
        var eventObj = ev.target;
        picSel = eventObj.id;
        var picType = picSel.substring(0,3);
        if ( picType != 'noC' ) {
        	if ( picType == 'cap' ) {
        		imNo = picSel.substring(6,picSel.length);
        	} else {
        		imNo = picSel.substring(3,picSel.length);
        	}
        	picPop(imNo);
        }
    });
    
    // kill the popup when mouseout
    $images.on('mouseout', function() {
        $('.popupCap > p').remove();
        $('.popupCap').css('display','none');
    });
    
    // clicking images:
	$images.on('click', function(ev) {
		var clickItem = ev.target;
		var picSrc = clickItem.id;
		var picType = picSrc.substring(0,3);
		if ( picType == 'pic') {
			var picNo = picSrc.substring(3,picSrc.length);
			var j = 0;
			$links.each( function() {
				if ( j == picNo ) {
					FlickrLnk = this.textContent;
				}
				j++;
			});
			window.open(FlickrLnk);
		}
	}); 
	
	// WHEN WINDOW RESIZES (because left margin may change)
	$(window).resize( function() {
		calcPos();
	});

});
