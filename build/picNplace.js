/* This script is called early - before html - in order to define the drag/drop functions
 * specified in the html. However, some variables, which depend on html elements, require
 * definition in order to use the drag/drop functions, and are thus included here, with
 * a timeout sufficient to allow page loading, so that those variables can be built before
 * drag/drop is invoked. These variables are needed to enable the 'save' php code.
 */
 // Create initial row arrays for storing rowdata to pass to save routine
var rcnts = [];
var rhts = [];
var rstrs = [];
var rowstr = '';
var rowindx = 0;
var row0 = [];  // row arrays must be globals
var row1 = [];
var row2 = [];
var row3 = [];
var row4 = [];
var row5 = [];
setTimeout( loadArrays, 1200);
function loadArrays() {
	var $irows = $('div[id^="row"]');
	$irows.each( function() {
		var $imgs = $(this).children();
		var cnt = $imgs.length;
		rcnts.push(cnt);
		var first = true;
		$imgs.each( function() {
			if (first) {
				var rowht = $(this).height();
				rhts.push(rowht);
				first = false;
			}
			var imgid = this.id
			imgid = imgid.substr(0,3);
			if (imgid === 'pic') {
				rowstr += "p^";
				rowstr += $(this).width() + "^";
				rowstr += $(this).attr('src') + "^";
				rowstr += $(this).attr('alt') + "^";
			} else if (imgid === 'map') {
				ifrm = document.getElementById('theMap');
				rowstr += "f^";
				rowstr += ifrm.width + "^";
				rowstr += ifrm.src + "^";
			} else {
				rowstr += "n^";
				rowstr += $(this).width() + "^";
				rowstr += $(this).attr('src') + "^"
			}
			var rstrlen = rowstr.length - 1;
			rowstr = rowstr.substring(0,rstrlen);   // strip off end "^"
			rstrs.push(rowstr);
			rowstr = '';
		});
		switch (rowindx) {
			case 0:
				row0 = rstrs;
				break;
			case 1:
				row1 = rstrs;
				break;
			case 2:
				row2 = rstrs;
				break;
			case 3:
				row3 = rstrs;
				break;
			case 4: 
				row4 = rstrs;
				break;
			case 5:
				row5 = rstrs;
				break;
		}
		rstrs = [];
		rowindx++;
	});
}
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
var moveString;  // database string for item being moved
var targetInsert; // global
var maxRow = 850;  // current row size for images (coordinated with editDB.php)
var rowHeight = [];
var dragBorder = 10;
var xcnt = 0; // no of images brought in from external source
/* These two functions are called when items are being moved in order to identify which
 * row is being affected, return the correct data, and then transfer the new data back
 * to the affected row.
 */
var tmpArray = [];
function getRow(targetRow) {
	switch (targetRow) {
		case 0:
			tmpArray = row0;
			break;
		case 1:
			tmpArray = row1;
			break;
		case 2:
			tmpArray = row2;
			break;
		case 3:
			tmpArray = row3;
			break;
		case 4:
			tmpArray = row4;
			break;
		case 5:
			tmpArray = row5;
	}
	return tmpArray;
}
function setRow(changedRow,newArray) {
	switch (changedRow) {
		case 0:
			row0 = newArray;
			break;
		case 1:
			row1 = newArray;
			break;
		case 2:
			row2 = newArray;
			break;
		case 3:
			row3 = newArray;
			break;
		case 4:
			row4 = newArray;
			break;
		case 5:
			row5 = newArray;
	}
}
// filter out the moveString from the array and return reduced array
function removeString(str) {
	return str != moveString;
}
// update the string for the image to the new image width
function updateWidth(targetString,newWidth) {
	var targArray = targetString.split("^");
	targArray[1] = newWidth;
	var newString = targArray.join("^");
	return newString;
}
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
		dragRow = -1;  // indicates not from a row (not used at this time)
		var xwidth = parseInt($('#newpic').width());
		var xsrc = $('#newpic').attr('src');
		// provide a name for tracking
		var xid = 'ext' + xcnt;
		xcnt++;
		draggedImg = $(imgTargId).detach();
		draggedImg[0].id = xid;
		// make a corresponding insert:
		var xmarg = xwidth + insertDelta;
		var xInsHtml = '<img style="float:left;margin-left:' + xmarg + 
			'px;" id="insX" ondrop="drop(event)" ondragover="allowDrop(event)"' +
			' height="30" width="30" src="insert.png" alt="drop-point" />';
		$('#xInsert').append(xInsHtml);
		draggedInsert = $('#insX').detach(); // to get the jQuery object equivalent
		// provide textarea to add in caption:
		var xcap = xwidth - 12;  // empirical offset for textareas
		var xCapHtml = '<textarea id="capAreaX" style="height:60px;margin-right:8px;' +
			'width:' + xcap + 'px;"></textarea>';
		$('#xCap').append(xCapHtml);
		draggedCap = $('#capAreaX').detach();
		// create an image string for saving in database
		moveString = 'p^' + xwidth + '^' + xsrc + '^EXTERNAL IMAGE';
		// no item count to change or row ht yet
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
		// ------ detach database string to be relocated
		// reduce img count in dragRow:
		var cnt = rcnts[dragRow] - 1
		rcnts[dragRow] = cnt;
		// pull out image and save in moveString; adjust row parameters to reflect change
		var rowArray = getRow( dragRow );
		moveString = rowArray[nodeNo];
		rowArray = rowArray.filter(removeString);
		setRow(dragRow,rowArray);
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
			diWd = Math.floor(diScale * diHt);  
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
		// adjust imgWidth in moveString
		moveString = updateWidth(moveString,diWd);
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
		// place relocatable database string:
		var strid = '#r' + dropRow;
		$(strid).append(moveString);
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
	/* The last item to take care of is to update the inputs on the page that hold
	 * the row strings to be passed on to saveChanges.php. */ 
	$('#rcnts').val(JSON.stringify(rcnts));
	$('#rhts').val(JSON.stringify(rhts));
	$('#r0').val(JSON.stringify(row0));
	$('#r1').val(JSON.stringify(row1));
	$('#r2').val(JSON.stringify(row2));
	$('#r3').val(JSON.stringify(row3));
	$('#r4').val(JSON.stringify(row4));
	$('#r5').val(JSON.stringify(row5));
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
	// update rowhts array:
	rhts[dropRow] = newHt;
	// ------ fit existing images in row:
	var rowWidths = [];  // save the row's image widths for use in calc. insert margins
	var imgEl;
	$imgs.each( function(imgNo) {
		imgEl = this.id;
		imgEl = imgEl.substring(0,3);
		if (imgEl === 'map') { 
			var ifrm = document.getElementById('theMap');
			var mapWd = ifrm.width;  // note: iframe is smaller than div+marg
			var newWd = Math.floor(scale * mapWd);
			ifrm.width = newWd;  // map is square
			ifrm.height = newWd;
			rowWidths.push(newWd);
		} else {
			var newWd = Math.floor(scale * this.width);
			this.width = newWd;
			this.height = newHt;
			rowWidths.push(newWd);
		}
		var modArray = getRow(dropRow);
		var oldString = modArray[imgNo];
		var newString = updateWidth(oldString,newWd);
		modArray[imgNo] = newString;
		setRow(dropRow,modArray);
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




