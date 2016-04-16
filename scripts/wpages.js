$( function () { // when page is loaded...
    // object locations
    var $images = $('img');
    var $maps = $('iframe');
    var $desc = $('.captionList li');
    var $links = $('.lnkList li');
    
	// set up a positioning array for caption locations
	var noOfPix = $images.length;
	var capTop = new Array();
	var capLeft = new Array();
	var capWidth = new Array();
	var picId;
	var $picEl;
	var picPos;
	for ( var i=0; i<noOfPix; i++ ) {
		picId = 'pic' + i;
		$picEl = document.getElementById(picId);
		picPos = $picEl.getBoundingClientRect();
		capTop[i] = picPos.top + 'px';
		capLeft[i] = picPos.left + 'px';
		capWidth[i] = picPos.right - picPos.left + 'px';
	}
    var picSel;  // argument passed to popup function
    var htmlLnk;
    var desc;
    var htmlDesc;
    var FlickrLnk;
    
	// place link to full-size map below iframe (relative positioning)
	var fullMap = $maps.attr('src');
	var $mapAddr = document.getElementById('theMap');
	var mapPos = $mapAddr.getBoundingClientRect();
	var mapLeft = mapPos.left;
	var mapWidth = mapPos.right - mapPos.left;
	// text is approx. 160 px
	var lnkLoc = ( mapWidth - 160 ) / 2;
	mapLeft += lnkLoc;
	htmlLnk = '<a style="position:relative; left:' + mapLeft + 
			'px; top:-20px;" href="' + fullMap + '">Click for full-page map</a>';
	$('.lnkList').after(htmlLnk);
	
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
		var clkPicNo = picSrc.substring(picIndx,picSrc.length);
		FlickrLnk = $links[clkPicNo].textContent;
		window.open(FlickrLnk);
	}); 

});