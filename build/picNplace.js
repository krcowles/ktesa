/* This script is called early - before the main html - in order to define 
 * the drag/drop functions specified in the html. Since the routine needs 
 * to update row information whenever changes are made, and the page hasn't
 * been loaded yet, the $rows object is established after a brief timeout.
 */
var $rows; // must be global
var $captions;
setTimeout(rowSetup, 1200);
function rowSetup() {
    $rows = $('div[id^="row"]');
    // NOTE: this is a "live" object -> changes to it will update the page
    var rowid;
    $rows.each( function(rowno) {
        rowid = '#r' + rowno;
        var guts = $(this).html();
        $(rowid).val(guts);
    });
    captureCaps();
}
function captureCaps() {
    $captions = $('textarea[id^="capArea"]');
    $captions.change( function() {
        // get caption id
        var cid = this.id;
        cid = cid.replace("capArea","");
        var rid;
        // since the only items w/captions are photos, correlate to pic no.
        $rows.each( function(rowno) {
            var $rowkids = $(this).children();
            $rowkids.each( function() {
                var kid = this.id;
                if (kid.substring(0,3) === 'pic') {
                    kid = kid.replace("pic","");
                    if (kid === cid) {
                        rid = rowno;
                        return;
                    }
                }    
            });
            // if (rid === undefined) { window.alert("caption not located"); }
        });
        // update rid with new html
        var img2chg = '#pic' + cid;
        var newcap = $(this).val();
        $(img2chg).attr('alt',newcap);
        var rowToUpdate = '#row' + rid;
        var rowhtml = $(rowToUpdate).html()
        var pageRow = '#r' + rid;
        $(pageRow).val(rowhtml);
    });   
}
// the following vars are established to mimic the constants used in editDB.php
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
var dragBorder = 10;
var xcnt = 100; // no of images brought in from external source, start at 100
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
        var xid = 'pic' + xcnt;
        var insid = 'ins' + xcnt;
        var jins = '#' + insid;
        var capid = 'capArea' + xcnt;
        var jcap = '#' + capid;
        xcnt++;
        draggedImg = $('#newpic').detach();
        draggedImg[0].id = xid;
        draggedImg.attr('alt',"Entry");
        // make a corresponding insert:
        var xmarg = xwidth + insertDelta;
        var xInsHtml = '<img style="float:left;margin-left:' + xmarg + 
                'px;" id="' + insid + '" ondrop="drop(event)" ondragover="allowDrop(event)"' +
                ' height="30" width="30" src="insert.png" alt="drop-point" />';
        $('#xInsert').append(xInsHtml);
        draggedInsert = $(jins).detach(); // to get the jQuery object equivalent
        // provide textarea to add in caption:
        var xcap = xwidth - 12;  // empirical offset for textareas
        var xCapHtml = '<textarea id="' + capid + '" style="height:60px;margin-right:8px;' +
                'width:' + xcap + 'px;"></textarea>';
        $('#xCap').append(xCapHtml);
        draggedCap = $(jcap).detach();
        // no row needs modification yet...
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
        // ------ re-write the row html to eliminate this image
        var newrow = $rows.eq(dragRow).html();
        var rid = '#r' + dragRow;
        $(rid).val(newrow);         
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
    var diHt;
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
    // capture the dimensions of the dragged item:
    diWd = draggedImg[0].width;
    diHt = draggedImg[0].height;  
    // scale the dragged items to the current row height, if needed
    if (dropRow !== dragRow && !empty) {
        var currRowHt = $dropChildren.eq(0).height();
        // the dragged image:
        var diScale = currRowHt/diHt;
        diWd = Math.floor(diScale * draggedImg[0].width);
        draggedImg[0].height = currRowHt;
        draggedImg[0].width = diWd;
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
    if ( !empty ) {
        $dropChildren.each( function () {
            currWidth += this.width + 10;  // 10 = space between images ($beta in php)
        });
        var insWidth = currWidth + diWd + 10;
        if (insWidth > maxRow) { 
            fitToNewRow(insWidth);
        }
    }
    /* 
     * The drop row is extracted from either the 'lead' insert (followed by row no)
     * or, the insert number (get parent row's attribute id)
     * There are three pieces to drop: insert, img, and caption field
     */
    var capType = draggedCap[0].id;  // if cap for externally srced img...
    capType = parseInt(capType.replace('capArea',''));
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
    if (capType > 99) {
        $captions.off('change');
        $captions = null;
        captureCaps();
    }
    // update the page:
    var newhtml = $rows.eq(dropRow).html();
    var rid = '#r' + dropRow;
    $(rid).val(newhtml);
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
    var oldHt = $imgs.eq(0).attr('height');
    var newHt = Math.floor(scale * oldHt);
    // ------ fit existing images in row:
    var rowWidths = [];  // save the row's image widths for use in calc. insert margins
    $imgs.each( function(imgNo) {
        var newWd = Math.floor(scale * this.width);
        this.width = newWd;
        this.height = newHt;
        rowWidths.push(newWd);
    });
    draggedImg[0].width = Math.floor(scale * draggedImg[0].width);
    draggedImg[0].height = newHt;
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
    newMarg = draggedImg[0].width + insertDelta + 'px';
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
