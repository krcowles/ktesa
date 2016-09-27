$( function () { // when page is loaded...

	// generic
	var msg;
	var i;
    // object locations
    var $images = $('img[id^="pic"]');
    var noOfPix = $images.length;
    var $maps = $('iframe');
    var	fullMap = $maps.attr('src');
    var $desc = $('.captionList li');
    var $links = $('.lnkList li');
    // argument passed to popup function
    var picSel;
    // string vars
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
	// for stashing into session storage
	var mleft;
	var mbot;
	var pwidth;
	var pleft;
	var ptop;
<<<<<<< HEAD
		
	$images.each( function() {
		$(this).css('cursor','pointer');
	});
=======

	$images.each( function() {
		$(this).css('cursor','pointer');
	});		
>>>>>>> BurntMesa
	/* problems with refresh in Chrome prompted the use of the following technique
	   which "detects" a refresh condition and restores previously loaded values.
	   User gets a window alert if sessionStorage is not supported and and is advised
	   about potential refresh issues */
	if ( window.sessionStorage ) {
		var tst = sessionStorage.getItem('prevLoad');
		if ( !tst ) { 
			//msg = '<p>NORMAL ENTRY</p>';
			i = 0;
			$images.each( function() {
				capWidth[i] = this.width + 14 + 'px'; // account for border and margin (14)
				//msg = '<p>image' + i + ' width is ' + capWidth[i] + '</p>';
				//$('#dbug').append(msg);
				pwidth = 'pwidth'+ i;
				sessionStorage.setItem(pwidth,capWidth[i]);
				i++;
			});
			// NOTE: ASSUMPTION: width = height !!!
			mapWidth = $maps.attr('width');
			mapWidth = parseFloat(mapWidth);
			lnkLoc = ( mapWidth - 160 ) / 2;
			mapPos = $maps.offset();
			mapLeft = mapPos.left + lnkLoc;
			sessionStorage.setItem('mleft',mapLeft);
			mapBot = mapPos.top + mapWidth + 15;
			sessionStorage.setItem('mbot',mapBot);
			// get caption locations
			calcPos(); 
		} else {  // Refresh: need to reload items for placing captions & map link
			for ( i=0; i<noOfPix; i++ ) {
				pwidth = 'pwidth' + i;
				capWidth[i] = sessionStorage.getItem(pwidth);
			}
			mapLeft = sessionStorage.getItem('mleft');
			mapBot = sessionStorage.getItem('mbot');
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
		for ( i=0; i<noOfPix; i++ ) {
			capWidth[i] = $images[i].width + 14 + 'px'; // account for border and margin (14)
			//msg = '<p>image' + i + ' width is ' + capWidth[i] + '</p>';
			//$('#dbug').append(msg);
			pwidth = 'pwidth'+ i;
			sessionStorage.setItem(pwidth,capWidth[i]);
		}
		mapWidth = $maps.attr('width');
		mapWidth = parseFloat(mapWidth);
		lnkLoc = ( mapWidth - 160 ) / 2;
		mapPos = $maps.offset();
		mapLeft = mapPos.left + lnkLoc;
		mapBot = mapPos.top + mapWidth + 15;
		calcPos();
	}  // end of session storage IF

	// make map link and place below map
	htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
			mapBot + 'px;" href="' + fullMap + '" target="_blank">Click for full-page map</a>';
	$('.lnkList').after(htmlLnk);
	if ( window.sessionStorage ) { 
		sessionStorage.setItem('prevLoad','2.71828'); // Euler's number
	}

	// function to calculate current & (potentially) store location of images/captions
	function calcPos() {
		for ( var j=0; j<noOfPix; j++ ) {
			picId = '#pic' + j;
			picPos = $(picId).offset();
			capTop[j] = picPos.top + 'px';
			capLeft[j] = picPos.left + 'px';
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
    
    // popup a description when mouseover a picture
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
	
	// WHEN WINDOW RESIZES (because left margin may change)
	$(window).resize( function() {
		//msg = '<p>WINDOW RESIZED</p>';
		//$('#dbug').append(msg);
		calcPos();
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
		var tst = document.getElementById('mapLnk');
		var tstParent = tst.parentNode;
		tstParent.removeChild(tst);
		htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
				mapBot + 'px;" href="' + fullMap + '" target="_blank">Click for full-page map</a>';
		$('.lnkList').after(htmlLnk);
	});

});
