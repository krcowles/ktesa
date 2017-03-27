// This routine is called early - before html - in order to set up drag function
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    setTimeout( function() {
    	redrawSmaller(ev.target.id);}, 800);
}
function redrawSmaller(imgId) {
	// if iframe:
	if (imgId === 'map0') {
		
	
	} else {
		// get the image no. so insert can also be identified:
		var imgIdPos = imgId.length - 1;
		var imgNo = imgId.charAt(imgIdPos);  //very unlikely more than 10 pix in a row!
		var imgTargId = '#' + imgId;
		var rowId = $(imgTargId).parent().attr('id');
		var rowNoPos = rowId.length - 1;
		var rowNo = rowId.charAt(rowNoPos); // row nos can only be 1 digit: 0-5
		rowId = '#' + rowId;
		var $rowChildNodes = $(rowId).children();
		$rowChildNodes.each( function() {
			if (this.id === imgId) {
				$(this).remove();
			}
		});	
		var insTargId = 'ins' + imgNo;
		var insId = '#insRow' + rowNo;
		var $insChildNodes = $(insId).children();
		$insChildNodes.each( function() {
			if (this.id === insTargId) {
				$(this).remove();
			}
		});
	}
}
