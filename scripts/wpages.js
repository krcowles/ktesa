$( function () { // when page is loaded...
    // object locations: $xyz
    var $images = $('img');
    var noOfPix = $images.length;
    var $maps = $('iframe');
    var $mapAddr = document.getElementById('theMap');
    var	fullMap = $maps.attr('src');
    var $desc = $('.captionList li');
    var $links = $('.lnkList li');
    // argument passed to popup function
    var picSel;
    // textual vars
    var htmlLnk;
    var desc;
    var htmlDesc;
    var FlickrLnk;
    // caption position vars
	var capTop = new Array();
	var capLeft = new Array();
	var capWidth = new Array();
	var picId;
	var $picEl;
	var picPos;
	// map position vars
	var $mapLoc;
	var mapPos;
	var mapLeft;
	var mapRight;
	var mapWidth;
	var mapBot;
	var lnkLoc;
	var msg;
	
	
	/* START THE LOCATION-SETTING BASED ON 1st OPENING OF BROWSER WINDOW
		NOTE: offset() only retrieves left and top positions in doc */
	// INITIAL positioning array for caption locations
	for ( var i=0; i<noOfPix; i++ ) {
		picId = 'pic' + i;
		$picEl = document.getElementById(picId);
		picPos = $picEl.getBoundingClientRect();
		capTop[i] = picPos.top + 'px';
		capLeft[i] = picPos.left + 'px';
		capWidth[i] = picPos.right - picPos.left + 'px';
	}
	// INITIAL link to full-size map below iframe
	mapPos = $mapAddr.getBoundingClientRect();
	mapLeft = mapPos.left;
	mapRight = mapPos.right;
	mapWidth = mapRight - mapLeft;
	mapBot = mapPos.bottom + 10;
	msg = 'Left: ' + mapLeft + '; Right: ' + mapRight;
	$('#dbug').append(msg);
	// text is approx. 160 px long
	lnkLoc = ( mapWidth - 160 ) / 2;
	mapLeft += lnkLoc;
	htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
			mapBot + 'px;" href="' + fullMap + '">Click for full-page map</a>';
	$('.lnkList').after(htmlLnk);
	
	$(window).resize( function() {
		msg = 'resized';
		$('#dbug').append(msg);
		// set up a positioning array for caption locations
		for ( var i=0; i<noOfPix; i++ ) {
			picId = '#pic' + i;
			$picEl = $(picId);
			picPos  = $picEl.offset();
			capTop[i] = picPos.top + 'px';
			capLeft[i] = picPos.left + 'px';
		}
		// place link to full-size map below iframe
		//mapPos = $mapAddr.getBoundingClientRect();
		$mapLoc = $('#theMap');
		mapPos = $mapLoc.offset();
		mapLeft = mapPos.left;
		mapLeft += lnkLoc;
		$('#mapLnk').remove();
		htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
				mapBot + 'px;" href="' + fullMap + '">Click for full-page map</a>';
		$('.lnkList').after(htmlLnk);
	});
	
    // function to popup the description for the picture 'selected'
    function picPop(picTarget) {
        // get the image number
        var argLgth = picTarget.length;
        var picNo = picTarget.substring(3,argLgth);
        // get the corresponding description
        desc = $desc[picNo].textContent;
        // form the popup and turn it on
        htmlDesc = '<p class="capLine">' + desc + '</p>';
        $('.popupCap').prepend(htmlDesc);
        $('.popupCap').css('position','absolute');
        $('.popupCap').css('top',capTop[picNo]);
        $('.popupCap').css('left',capLeft[picNo]);
        $('.popupCap').css('width',capWidth[picNo]);
        $('.popupCap').css('z-index','10');
        $('.popupCap').css('display','block');
    }
    
    // popup a description when mouseover a picture
    $images.css('z-index','1'); // keep pix in the background
    $images.on('mouseover', function(ev) {
        var eventObj = ev.target;
        picSel = eventObj.id;
        picPop(picSel);
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
	}); 

});