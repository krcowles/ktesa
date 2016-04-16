$( function () { // when page is loaded...
    var $images = $('img');
    var $maps = $('iframe');
    var picSel;
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
        // find the image position; set top/left for the caption to display
        var $picEl = document.getElementById(picTarget);
        var picPos = $picEl.getBoundingClientRect();
        var capTop = picPos.top + 'px';
        var capLeft = picPos.left + 'px'; 
        var capWidth = picPos.right - picPos.left + 'px';
        var i = 0;
        // get the corresponding description
        $('.captionList li').each( function() {
            if ( i == picNo ) {
                desc = this.textContent;
            }
            i++;
        });
        // form the popup and turn it on
        htmlDesc = '<p class="capLine">' + desc + '</p>';
        $('.popupCap').prepend(htmlDesc);
        $('.popupCap').css('position','absolute');
        $('.popupCap').css('top',capTop);
        $('.popupCap').css('left',capLeft);
        $('.popupCap').css('width',capWidth);
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