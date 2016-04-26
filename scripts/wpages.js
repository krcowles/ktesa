$( function () { // when page is loaded...
    // object locations
    var $images = $('img');
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
	var mapBot;
	var lnkLoc;
	var msg;
	// generic
	var i;
	var rx = 0;
	
	// Get element widths: these don't change with resize, scroll or refresh
	// NOTE: can't seem to extract CSS params to calculate specified border & margin...
	i = 0;
	$images.each( function() {
		capWidth[i] = this.width +14; // account for border and margin (14)
		i++;
	});
	mapWidth = $maps.attr('width');
	mapWidth = parseFloat(mapWidth);


	// function to calculate current location of images/captions
	function calcPos() {
		for ( var j=0; j<noOfPix; j++ ) {
			picId = '#pic' + j;
			picPos = $(picId).offset();
			capTop[j] = picPos.top + 'px';
			capLeft[j] = picPos.left + 'px';
			msg = '<p>pic' + j + ' : ' + capTop[j] + ', ' + capLeft[j] + ', </p>';
			$('#dbug').append(msg);
		}
	}
	
	// INITIAL positioning for captions & map locations
	msg = '<p>Top, Left, for images as follows:</p>';
	$('#dbug').append(msg);
	calcPos();
	mapPos = $maps.offset();
	mapLeft = mapPos.left;
	// text is approx. 160 px long
	lnkLoc = ( mapWidth - 160 ) / 2;
	mapLeft += lnkLoc;
	msg = 'current map top is ' + mapPos.top;
	$('#dbug').append(msg);
	mapBot = mapPos.top + mapWidth + 15;
	htmlLnk = '<a id="mapLnk" style="position:absolute; left:' + mapLeft + 'px; top:' +
			mapBot + 'px;" href="' + fullMap + '">Click for full-page map</a>';
	$('.lnkList').after(htmlLnk);
	msg = '<p>Map link is located left at: ' + mapLeft.toFixed(1) + 'px, top is ' +
			mapPos.top.toFixed(1) + 'px and below map at: ' + 
			mapBot.toFixed(1) + 'px</p>';
	$('#dbug').append(msg);
	
	
	// WHEN WINDOW RESIZES:
	$(window).resize( function() {
		rx++;
		msg = '<p>resize ' + rx + 'ht x w = ' + window.innerHeight + ' x ' +
				window.innerWidth + '</p>';
		$('#dbug').append(msg);
		calcPos();
		// place link to full-size map below iframe
		mapPos = $maps.offset();
		mapLeft = mapPos.left + lnkLoc;
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
        $('.popupCap').css('display','block');
        $('.popupCap').css('position','absolute');
        $('.popupCap').css('top',capTop[picNo]);
        $('.popupCap').css('left',capLeft[picNo]);
        $('.popupCap').css('width',capWidth[picNo]);
        $('.popupCap').css('z-index','10');
        $('.popupCap').prepend(htmlDesc);
        msg = '<p>popup loc: ' + capTop[picNo] + ', ' + capLeft[picNo] + ', ' + 
        	capWidth[picNo] + '</p>';
        $('#dbug').append(msg);
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