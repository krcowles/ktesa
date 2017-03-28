// This routine is called early - before html - in order to set up drag function
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    setTimeout( function() {
    	redrawSmaller(ev.target.id);}, 800);
}
function redrawSmaller(imgId) {
	// target id will be either a photo, noncaptioned pic or iframe (map)
	// find the row and position in row to remove the collection associated w/target
	var imgTargId = '#' + imgId;
	var rowId = $(imgTargId).parent().attr('id');
	var rowNoPos = rowId.length - 1;
	var rowNo = parseInt(rowId.charAt(rowNoPos)); // row nos can only be 1 digit: 0-5
	rowId = '#' + rowId;
	var $targRowChildren = $(rowId).children();
	var targRowChildCnt = $targRowChildren.length; // use this in case === 1?
	var targNodeNo = 0;
	var rowChildNo;
	$targRowChildren.each( function() {
		if (this.id === imgId) {
			$(this).remove();
			rowChildNo = targNodeNo;
		}
		targNodeNo++;
	});
	if (rowNo > 0) {
		// calculate no of prior inserts based on each row's child count
		var insCnt = 0;
		var $rowChildNodes;
		for (n=0; n<rowNo; n++) {
			rowId = '#row' + n;
			$rowChildNodes = $(rowId).children();
			insCnt += $rowChildNodes.length;
		}
		rowChildNo += insCnt;
	}
	var insId = '#insRow' + rowNo;
	var targIns = 'ins' + rowChildNo;
	var $insDivChildren = $(insId).children();
	$insDivChildren.each( function() {
		if (this.id === targIns) {
			$(this).remove();
		}
	});
	// identify the corresponding caption textarea if a pic
	if (imgId.substring(0,3) === 'pic') {
		var picNoLgth = imgId.length;
		var picNo = imgId.substring(3,picNoLgth);
		var txtA = '#capTxt' + picNo;
		$(txtA).remove();
	}
}  // end function redrawSmaller
