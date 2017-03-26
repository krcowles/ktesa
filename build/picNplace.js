// This routine is called early - before html - in order to set up drag function
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    setTimeout( function() {
    	redrawSmaller(ev.target.id);
    	}, 2000);
    //redrawSmaller(ev.target.id);
}
function redrawSmaller(imgId) {
	var img = '#' + imgId;
	var rowId = $(img).parent().attr('id');
	rowId = '#' + rowId;
	var $rowChildNodes = $(rowId).children();
	$rowChildNodes.each( function() {
		if (this.id === imgId) {
			$(this).remove();
		}
	});	
}
