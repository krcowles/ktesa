// This routine is called early - before html - in order to set up drag/drop functions

// the following are established to echo the constants used in editDB.php
var alpha = 30;
var beta = 10;
var insertDelta = beta - alpha;  // to be added to image width to determine margin-left
// globals to capture detached images and info about drag/drop rows
var dragRow;
var dropRow;
var draggedImg;  // pic, noncap pic, or iframe
var draggedInsert;
var draggedCap;  // textarea or text div
var targetInsert; // global
var maxRow = 850;  // current row size for images (coordinated with editDB.php)
var rowHeight = [];
var dragBorder = 10;
/*
 *  --------------  DRAG EVENT PROCESSOR --------------
 *  The event processor captures the id of the drag item, then, after a short
 *  timeout, passes control to the 'reduceImgCnt' routine to handle the capturing of
 *  the drag column (insert, image, and caption field) intems and shrinking the row
 *  --------------------------------------------------- */
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    setTimeout( function() {
    	reduceImgCnt(ev.target.id);}, 500);
}
/*  ------------- REDUCE IMAGE COUNT IN ROW ------------
 *  This function detaches the dragged image and saves it in a variable for use by the
 *  drop routine. If the image being dragged is the 'newpic' externally-captured
 *  <img>, the rowId (of the dragged item) is established as -1 so that it can be
 *  identified by the drop routines. There will be no further processing in that case.
 *  Otherwise, the rowId (of the dragged item) is stored in the global variable, and
 *  the routine detaches the corresponding insert and caption - also saved for use by 
 *  the drop routine.
 *  ---------------------------------------------------- */
function reduceImgCnt(imgId) {	
	// ------ detach image:
	var imgTargId = '#' + imgId;
	if (imgId === 'newpic') { // for an externally sourced image
		dragRow = -1;  // indicates not from a row
		var xwidth = parseInt($('#newpic').width());
		draggedImg = $(imgTargId).detach();
		// make a corresponding insert:
		var xmarg = xwidth + insertDelta;
		var xInsHtml = '<img style="float:left;margin-left:' + xmarg + 
			'px;" id="insX" ondrop="drop(event)" ondragover="allowDrop(event)"' +
			' height="30" width="30" src="insert.png" alt="drop-point" />';
		$('#xInsert').append(xInsHtml);
		draggedInsert = $('#insX');
		// provide textarea to add in caption:
		var xcap = xwidth - 11;  // empirical offset for textareas
		var xCapHtml = '<textarea id="capAreaX" style="height:60px;margin-right:8px;' +
			'width:' + xcap + 'px;"></textarea>';
		$('#xCap').append(xCapHtml);
		draggedCap = $('#capAreaX');
	} else {
		// get row number from which item is being dragged
		var rowId = $(imgTargId).parent().attr('id');
		dragRow = parseInt(rowId.replace('row',''));
		// identify the node number so that it can be used to extract inserts & captions
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
		// use the node no. to identify the insert that accompanies this img.
		var insId = '#insRow' + dragRow;
		var $insDivChildren = $(insId).children();
		var insertTarget = $insDivChildren[nodeNo+1].id;  // add 1 to skip over the "lead" insert node
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
}
/*
 *  --------------  ALLOW DROP: HANDLER --------------
 *  The event processor identifies the id of the target (insert at which the drop is
 *  being processed) and stores it in the global variable 'targetInsert'
 *  ---------------------------------------------------- */
function allowDrop(ev) {
	ev.preventDefault();
	targetInsert = ev.target.id;
}
/*
 *  --------------  DROP EVENT PROCESSOR --------------
 *  This event processor receives the target id from the 'allowDrop()' routine. After
 *  a short timeout, it passes control to the 'increaseImgCnt()' handler to add the 
 *  dragged items to the correct location, sizing as needed.
 *  --------------------------------------------------- */
function drop(ev) {
	ev.preventDefault();
	setTimeout( function() {
    	increaseImgCnt(targetInsert);}, 200);
}
/*
 *  ----- ADD DROP ITEMS HANDLER: increasImgCnt() ------
 *  This routine identifies the row in which the image (et al) is to be dropped, and
 *  also the target node corresponding to the insert point. First, it scales the image
 *  to the new row, if needed, and calculates the row width with this image in it. If
 *  the new row width exceeds the specified limit (maxRow), then it calls the routine:
 *  'fitToNewRow()', which will size the row back to current specs. After this, the
 *  items are dropped back into place, one by one: image, insert, and caption field.
 *  --------------------------------------------------- */ 
function increaseImgCnt(targ) {
	var i, j, k;
	var dropParentNode;
	var rowId; // jQuery selector
	var dropChildNode;
	var dropInsParent;
	var dropInsChild;
	var dropCapDiv;
	var dropCapChild;
	var currWidth = 0;
	var diWd;
	var mapNode;
	var insMarg;
	
	// get the row number for the drop from insertRow:
	var childId = '#' + targetInsert;
	var insParentRowId = $(childId).parent().attr('id');
	dropRow = parseInt(insParentRowId.replace('insRow',''));
	
	// corresponding (target) image row id:
	rowId = '#row' + dropRow;
	var empty = false;
	var $dropChildren = $(rowId).children();
	if ($dropChildren.length === 0) { empty = true; }
	// scale the dragged items to the current row height, if needed
	if (dropRow !== dragRow && !empty) {
		var currRowHt = $dropChildren.eq(0).height();
		// the dragged image:
		if (draggedImg[0].id === 'map0') {
			mapNode = draggedImg[0].firstChild;
			var diHt = mapNode.height;
			var diScale = currRowHt/diHt;
			diWd = Math.floor(diScale * diHt);  // height & width are equal for map
			mapNode.height = diWd;
			mapNode.width = diWd;
			diScale = currRowHt/(parseInt(diHt) + dragBorder); // in case map is at end, correct scaling
		} else {
			var diHt = draggedImg[0].height;
			var diScale = currRowHt/diHt;
			diWd = Math.floor(diScale * draggedImg[0].width);
			draggedImg[0].height = currRowHt;
			draggedImg[0].width = diWd;
		}
		// the dragged insert margin:
		insMarg = draggedInsert.css('margin-left');
		insMarg = parseInt(insMarg.replace('px',''));
		insMarg = Math.floor(diScale * insMarg) + 'px';
		draggedInsert.css('margin-left',insMarg);
		// the caption field:
		var capWidth = draggedCap.width();
		capWidth = Math.floor(diScale * capWidth);
		draggedCap.css('width',capWidth);
	}
	var insertAtLead = false;
	if (targetInsert.substring(0,3) === 'lea') { insertAtLead = true; }
	
	// find node number of image to use:
	dropInsParent = document.getElementById(insParentRowId);
	var targChildren = dropInsParent.childNodes;
	var childNodeNo = 0;
	for (var i=1; i<targChildren.length; i++) {  // skip over 'lead' insert
		if(targChildren[i].id === targetInsert) {  
			childNodeNo = i;  
			break;
		}
	}
	/* CHECK TO SEE IF INCOMING WIDTH IS TOO BIG FOR CURRENT ROW MAX */
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
	if (insWidth > maxRow) { 
		fitToNewRow(insWidth);
	}
	/* 
	 * The drop row is extracted from either the 'lead' insert (followed by row no)
	 * or, the insert number (get parent row's attribute id)
	 * There are three pieces to drop: insert, img, and caption field
	 */
	rowId = 'row' + dropRow;
	dropParentNode = document.getElementById(rowId);
	if (insertAtLead) {
		if (dropParentNode.childElementCount === 0) {
			dropParentNode.appendChild(draggedImg[0]);
			dropInsParent.appendChild(draggedInsert[0]);
			var capId = '#caps' + dropRow;
			$(capId).append(draggedCap);
		} else {
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
		}
	} else { 
		// insert image:
		var rowChildren = dropParentNode.childNodes;
		dropChildNode = rowChildren[childNodeNo];
		dropParentNode.insertBefore(draggedImg[0],dropChildNode);
		// next, the insert:
		var insLoc = '#' + targetInsert;
		$(insLoc).after(draggedInsert);
		// now the caption field: 
		rowId = 'caps' + dropRow;
		dropCapDiv = document.getElementById(rowId);
		var dropCapChildren = dropCapDiv.childNodes;
		dropCapChild = dropCapChildren[childNodeNo];
		dropCapDiv.insertBefore(draggedCap[0],dropCapChild);
	}  // end of if-else
}
/*
 *  -------------------  fitToNewRow -------------------
 *  This routine accepts as a parameter the current over-sized width. 
 *  From this it derives a scale by which to reduce image proportions 
 *  so that they will fit within the specs (maxRow). Note that the row
 *  scale cannot be exactly applied to the row of inserts, as that
 *  row has different internal fixed elements (ie inserts).
 *  ---------------------------------------------------- */
function fitToNewRow(triggerWidth) {
	// determine image-scaling factor and new row height:
	var scale = maxRow/triggerWidth;
	var row = '#row' + dropRow;
	var $imgs = $(row).children();
	if ($imgs.eq(0).attr('id') === 'map0') {
		var mapNode = document.getElementById('theMap');
		var oldHt = mapNode.width + 8;  // height & width the same, but appear diff on page
	} else {
		var oldHt = $imgs.eq(0).attr('height');
	}
	var newHt = Math.floor(scale * oldHt);
	// ------ fit existing images in row:
	var rowWidths = [];  // save the row's image widths for use in calc. insert margins
	var imgEl;
	$imgs.each( function() {
		imgEl = this.id;
		imgEl = imgEl.substring(0,3);
		if (imgEl === 'map') { 
			var ifrm = document.getElementById('theMap');
			var mapWd = ifrm.width;  // note: iframe is smaller than div+marg
			mapWd = Math.floor(scale * mapWd);
			ifrm.width = mapWd;  // map is square
			ifrm.height = mapWd;
			rowWidths.push(mapWd);
		} else {
			var newWd = Math.floor(scale * this.width);
			this.width = newWd;
			this.height = newHt;
			rowWidths.push(newWd);
		}
	});
	if (draggedImg[0].id === 'map0') {
		var mapNode = draggedImg[0].firstChild;
		var mapDims = Math.floor(scale * mapNode.width);
		mapNode.height = mapDims;
		mapNode.width = mapDims;
	} else {
		draggedImg[0].width = Math.floor(scale * draggedImg[0].width);
		draggedImg[0].height = newHt;
	}
	// ------ fit inserts: each insert margin is the image width + insertDelta
	var newMarg;
	var insRow = '#insRow' + dropRow;
	var $inserts = $(insRow).children();
	var skipLead = false;
	var node = 0;
	$inserts.each( function() {
		if (skipLead) {
			newMarg = (rowWidths[node] + insertDelta) + 'px';
			node++;
			$(this).css('margin-left', newMarg);
		} else {
			skipLead = true;
		}
	});
	// the dragged insert should use its corresponding image width
	if (draggedImg[0].id === 'map0') {
		newMarg = (parseInt(mapNode.width) + dragBorder + insertDelta) + 'px';
	} else {
		newMarg = draggedImg[0].width + insertDelta + 'px';
	}
	draggedInsert.css('margin-left',newMarg);
	// ------ fit caption areas:
	var caprow = '#caps' + dropRow;
	var $capFields = $(caprow).children();
		
	var j = 0;	
	var cwidth;
	$capFields.each( function() { // a one-to-one correspondence w/row imgs (widths)
		cwidth = rowWidths[j];
		if ( !$(this).hasClass('notTA') ) {
			cwidth -= 11;   // textareas don't seem to follow css pixels....
		}
		$(this).css('width',cwidth);
		j++;
	});
	var dwidth = draggedCap.width();
	var newCapWd = Math.floor(scale * dwidth);
	draggedCap.css('width',newCapWd);
}




