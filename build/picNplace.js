// This routine is called early - before html - in order to set up drag/drop functions
var dragRow;
var draggedImg;  // pic, noncap pic, or iframe
var draggedInsert;
var draggedCap;  // textarea or text div
var targetInsert; // global
var maxRow = 850;  // current row size for images (coordinated with editDB.php)
var rowHeight = [];
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    setTimeout( function() {
    	reduceImgCnt(ev.target.id);}, 500);
}
function reduceImgCnt(imgId) {
	// target id will be either a photo, noncaptioned pic or iframe (map)
	// find the row and position in row to remove the collection associated w/target
	
	// ------ detach image:
	var imgTargId = '#' + imgId;
	if (imgId === 'newpic') {
		dragRow = -1;  // indicates not from a row
		draggedImg = $(imgTargId).detach();
	} else {
		var rowId = $(imgTargId).parent().attr('id');
		var rowNoPos = rowId.length - 1;
		dragRow = parseInt(rowId.charAt(rowNoPos)); // row nos can only be 1 digit: 0-5
		rowId = '#' + rowId;
		var $targRowChildren = $(rowId).children();
		var targCnt = 0;
		var nodeNo;
		$targRowChildren.each( function() {
			if (this.id === imgId) {
				draggedImg = $(this).detach(); // detach keeps a copy out of DOM
				nodeNo = targCnt;
			}
			targCnt++;
		});
		// use the node no. to identify the insert and cap that accompany this img.
		var insId = '#insRow' + dragRow;
		var $insDivChildren = $(insId).children();
		var insertTarget = $insDivChildren[nodeNo+1].id;  // skip over the "lead" insert node
		// ------ detach the insert:
		$insDivChildren.each( function() {
			if (this.id === insertTarget) {
				draggedInsert = $(this).detach();
			}
		});
		// ------ detach corresponding caption field area (may be textarea or div w/text)
		var capDiv = '#caps' + dragRow;
		var $capDivChildren = $(capDiv).children();
		var capTarget = $capDivChildren[nodeNo].id;
		$capDivChildren.each( function() {
			if (this.id === capTarget) {
				draggedCap = $(this).detach();
			}
		});
	}
}  // end function redrawSmaller

/*
 *  DROP FUNCTIONS:
 */
function allowDrop(ev) {
	ev.preventDefault();
	targetInsert = ev.target.id;
}
function drop(ev) {
	ev.preventDefault();
	setTimeout( function() {
    	increaseImgCnt(targetInsert);}, 200);
}
function increaseImgCnt(targ) {
	var i, j, k;
	var dropRow;
	var dropParentNode;
	var rowId; // jQuery selector
	var dropChildNode;
	var dropInsParent;
	var dropInsChild;
	var dropCapDiv;
	var dropCapChild;
	var currWidth = 30;   // current design: width of symbol used to indicate insertion points
	
	// get the row number for the drop from insertRow:
	var childId = '#' + targetInsert;
	var insParentRowId = $(childId).parent().attr('id');
	dropRow = insParentRowId.replace('insRow','');
	
	// corresponding image row id:
	rowId = '#row' + dropRow;
	var $dropChildren = $(rowId).children();
	// scale this image to the current rowht:
	var currRowHt = $dropChildren.eq(0).height();
	var diHt = draggedImg[0].height;
	var diScale = currRowHt/diHt;
	diWd = Math.floor(diScale * draggedImg[0].width);
	draggedImg[0].height = currRowHt;
	draggedImg[0].width = diWd;
	// also scale the inserts and captions:
	var insMarg = draggedInsert.css('margin-left');
	insMarg = parseInt(insMarg.replace('px',''));
	insMarg = Math.floor(diScale * insMarg);
	draggedInsert.css('margin-left','insMarg');
	var capWidth = draggedCap.width();
	capWidth = Math.floor(diScale * capWidth) + 10;
	draggedCap.width(capWidth);
	
	// see if the newly added (scaled) image fits or if the row needs to be re-sized:
	var imgEl;
	$dropChildren.each( function () {
		imgEl = this.id;
		imgEl = imgEl.substring(0,3);
		if (imgEl === 'map') { 
			var $mapDiv = $(this).children().eq(0);
			currWidth += $mapDiv.width() + 10;
		} else {
			currWidth += this.width + 10;  // 10 = space between images ($beta in php)
		}
	});
	var insWidth = currWidth + diWd + 10;
	/* CHECK TO SEE IF INCOMING WIDTH IS TOO BIG FOR CURRENT ROW MAX:
	 * IF SO, call 'fitToNewRow()'
	 */
	if (insWidth > maxRow) { fitToNewRow(dropRow, insWidth); }
	var insertAtLead = false;
	if (targetInsert.substring(0,3) === 'lea') { insertAtLead = true; }
	/* 
	 * The drop row is extracted from either the 'lead' insert (followed by row no)
	 * or, the insert number (get parent row's attribute id)
	 * There are three pieces to drop: insert, img, and caption field
	 */
	rowId = 'row' + dropRow;
	dropParentNode = document.getElementById(rowId);
	if (insertAtLead) {
		// first, insert the image:
		dropChildNode = dropParentNode.firstChild;
		dropParentNode.insertBefore(draggedImg[0],dropChildNode);
		// now the insert:
		dropInsParent = document.getElementById(insParentRowId);
		var dropInsChildren = dropInsParent.childNodes;
		dropInsChild = dropInsChildren[1];
		dropInsParent.insertBefore(draggedInsert[0],dropInsChild);
		// now the caption field:
		rowId = 'caps' + dropRow;
		dropCapDiv = document.getElementById(rowId);
		dropCapChild = dropCapDiv.firstChild;
		dropCapDiv.insertBefore(draggedCap[0],dropCapChild);
	} else { 
		// which 'insert' child is the target in the node list? (will correspond to row el)
		dropInsParent = document.getElementById(insParentRowId);
		var targChildren = dropInsParent.childNodes;
		var childNodeNo = 0;
		for (var i=1; i<targChildren.length; i++) {  // skip over 'lead' insert
			if(targChildren[i].id === targetInsert) {  
				childNodeNo = i;  
				break;
			}
		}
		// insert image:
		var rowChildren = dropParentNode.childNodes;
		dropChildNode = rowChildren[childNodeNo];
		dropParentNode.insertBefore(draggedImg[0],dropChildNode);
		// next, the insert:
		dropInsChild = targChildren[childNodeNo]; // skip over "leadX" insert
		dropInsParent.insertBefore(draggedInsert[0],dropInsChild);
		// now the caption field: 
		rowId = 'caps' + dropRow;
		dropCapDiv = document.getElementById(rowId);
		var dropCapChildren = dropCapDiv.childNodes;
		dropCapChild = dropCapChildren[childNodeNo];
		dropCapDiv.insertBefore(draggedCap[0],dropCapChild);
	}  // end of if-else
}
function fitToNewRow(rowToFit, triggerWidth) {
	var row = '#row' + rowToFit;
	var $items = $(row).children();
	var scale = maxRow/triggerWidth;
	var oldHt = $items.eq(0).attr('height');
	var newHt = Math.floor(scale * oldHt);
	// ------ fit images:
	var tstDiv;
	$items.each( function() {
		tstDiv = this.id;
		tstDiv = tstDiv.substring(0,3);
		if (tstDiv === 'map') { 
			var ifrm = document.getElementById('theMap');
			var mapWd = ifrm.width;
			mapWd = Math.floor(scale * mapWd);
			ifrm.width = mapWd;
			ifrm.height = newHt;
		} else {
			this.width = Math.floor(scale * this.width);
			this.height = newHt;
		}
	});
	draggedImg[0].width = Math.floor(scale * draggedImg[0].width);
	draggedImg[0].height = newHt;
	// ------ fit inserts:
	var newMarg;
	var insRow = '#insRow' + rowToFit;
	var $inserts = $(insRow).children();
	var skipLead = false;
	$inserts.each( function() {
		if (skipLead) {
			newMarg = $(this).css('margin-left');
			newMarg = parseInt(newMarg.replace('px',''));
			newMarg = Math.floor(scale * newMarg);
			$(this).css('margin-left', newMarg);
		} else {
			skipLead = true;
		}
	});
	newMarg = draggedInsert.css('margin-left');
	newMarg = parseInt(newMarg.replace('px',''));
	newMarg = Math.floor(scale * newMarg) - 10  // 10 for inter-image space
	draggedInsert.css('margin-left',newMarg);
	// ------ fit caption areas:
	var caprow = '#caps' + rowToFit
	var $capFields = $(caprow).children();
	$capFields.each( function() {
		if ( $(this).hasClass('notTA') ) {
			var cwidth = $(this).css('width');
			cwidth = parseInt(cwidth.replace('px',''));
			cwidth = Math.floor(scale * cwidth);
			$(this).css('width',cwidth);
		} else {
			// for textarea, clientWidth must be used instead of width: (read only)
			var cwidth = parseInt(this.clientWidth);
			$(this).width( Math.floor(scale * cwidth) - 10 );
		}
	});
	var dwidth = draggedCap.width();
	draggedCap.width( Math.floor(scale * dwidth) - 6 );
}




