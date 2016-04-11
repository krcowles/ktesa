$( function () {
    var $images = $('img');
    var $maps = $('iframe');
    var picSel;
    var mapHt = $maps.attr('height');
    var msg;
    var desc;
    var htmlDesc;
    
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
        $('.popupCap').css('top',capTop);
        $('.popupCap').css('left',capLeft);
        $('.popupCap').css('width',capWidth);
        $('.popupCap').css('z-index','10');
        $('.popupCap').css('display','block');
    }
    
    $images.css('z-index','1'); // keep pix in the background
    
    $images.on('mouseover', function(ev) {
        var eventObj = ev.target;
        picSel = eventObj.id;
        picPop(picSel);
    });
    
    // kill the popup
    $images.on('mouseout', function() {
        $('.popupCap > p').remove();
        $('.popupCap').css('display','none');
    });
    
	$images.on('click', function(ev) {
		var clickWho = ev.target;
		var jim = clickWho.id;
		var msg2 = '<p>DOM target: ' + jim + '</p>';
		$('#info').append(msg2);
	});
	$maps
	.on('mouseover', function(ev) {
	    $('#info').append(mapHt);
	});
	
});