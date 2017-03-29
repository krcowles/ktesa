// This routine is called early - before html - in order to set up drag/drop functions
var dragRow;
var draggedImg;
var draggedInsert;
var draggedCap;
var targetInsert; // global

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    setTimeout( function() {
    	reduceImgCnt(ev.target.id);}, 500);
}
function reduceImgCnt(imgId) {
	// target id will be either a photo, noncaptioned pic or iframe (map)
	// find the row and position in row to remove the collection associated w/target
	var imgTargId = '#' + imgId;
	var rowId = $(imgTargId).parent().attr('id');
	var rowNoPos = rowId.length - 1;
	dragRow = parseInt(rowId.charAt(rowNoPos)); // row nos can only be 1 digit: 0-5
	rowId = '#' + rowId;
	var $targRowChildren = $(rowId).children();
	var targRowChildCnt = $targRowChildren.length; // use this in case === 1?
	var targNodeNo = 0;
	var rowChildNo;
	$targRowChildren.each( function() {
		if (this.id === imgId) {
			draggedImg = $(this).detach(); // remove() destroys the item completely
			rowChildNo = targNodeNo;
		}
		targNodeNo++;
	});
	if (dragRow > 0) {
		// calculate no of prior inserts based on each row's child count
		var insCnt = 0;
		var $rowChildNodes;
		for (n=0; n<dragRow; n++) {
			rowId = '#row' + n;
			$rowChildNodes = $(rowId).children();
			insCnt += $rowChildNodes.length;
		}
		rowChildNo += insCnt;
	}
	var insId = '#insRow' + dragRow;
	var targIns = 'ins' + rowChildNo;
	var $insDivChildren = $(insId).children();
	$insDivChildren.each( function() {
		if (this.id === targIns) {
			draggedInsert = $(this).detach();
		}
	});
	// identify the corresponding caption textarea if a pic
	var txtA = '#capArea' + rowChildNo;
	draggedCap = $(txtA).detach();
}  // end function redrawSmaller
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
	
	// get the row number for the drop:
	var childId = '#' + targetInsert;
	var insParentRowId = $(childId).parent().attr('id');
	dropRow = insParentRowId.replace('insRow','');
	// corresponding image row id:
	rowId = '#row' + dropRow;
	var $dropChildren = $(rowId).children();
	$dropChildren.each( function () {
		currWidth += this.width + 10;  // 10 = space between images
	});
	var maxRow = 960;  // min body width (may wish to reduce to allow some margin here)
	var insWidth = currWidth + draggedImg[0].width + 10;
	if (insWidth > maxRow) { fitToNewRow(dropRow, insWidth); }
	var insertAtLead = false;
	if (targetInsert.substring(0,3) === 'lea') { insertAtLead = true; }
	/* 
	 * The drop row is extracted from either the 'lead' insert (followed by row no)
	 * or, the insert number (get parent row's attribute id)
	 * There are three pieces to insert: insert, img, and (optionally) caption
	 */
	if (insertAtLead) {
		// first, insert the image:
		rowId = 'row' + dropRow;
		dropParentNode = document.getElementById(rowId);
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
		rowId = 'row' + dropRow;
		dropParentNode = document.getElementById(rowId);
		// which insert child is the target in the node list? (will correspond to row el)
		dropInsParent = document.getElementById(insParentRowId);
		var targChildren = dropInsParent.childNodes;
		var childNodeNo = 0;  // prevent case where undefined
		for (var i=0; i<targChildren.length; i++) {
			if(targChildren[i+1].id === targetInsert) {
				childNodeNo = i+1;
				break;
			}
		}
		// insert image:
		var rowChildren = dropParentNode.childNodes;
		dropChildNode = rowChildren[childNodeNo];
		dropParentNode.insertBefore(draggedImg[0],dropChildNode);
		// next, the insert:
		dropInsChild = targChildren[childNodeNo];
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
	var scale = 960/triggerWidth;
	var oldHt = $items.eq(0).attr('height');
	var newHt = Math.floor(scale * oldHt);
	$items.each( function() {
		this.width = Math.floor(scale * this.width);
		this.height = newHt;
	});
	draggedImg[0].width = Math.floor(scale * draggedImg[0].width);
	draggedImg[0].height = newHt;
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
	var caprow = '#caps' + rowToFit
	var $capFields = $(caprow).children();
	$capFields.each( function() {
		// for textarea, clientWidth must be used instead of width: (read only)
		var cwidth = parseInt(this.clientWidth);
		$(this).width( Math.floor(scale * cwidth) - 10 );
	});
	var dwidth = draggedCap[0].clientWidth;
	draggedCap.width( Math.floor(scale * dwidth) - 10 );
}










