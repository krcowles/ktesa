"use strict"
/**
 * @fileoverview This script actually places the pictues in the rows on load or resize
 * 
 * @author Ken Cowles
 * @version 1.0 Original release
 * @version 1.1 Updated to allow left shift of last row images for mobile
 */
var hike = $('#trail').text();
// Decode array data passed via php:
var descs = d.split("|");
var alblnks = al.split("|");
var piclnks = p.split("|");
var capts = c.split("|");
var aspects = as.split("|");
var widths = w.split("|");
if (descs[0] !== '') {
    var itemcnt = descs.length;
} else {
    var itemcnt = 0;
}
// photocnt passed via php - shows how many of above have captions

// NOMINAL INITIAL SETTINGS:
const pageMargin = 36;
const maxRowHt   = 260;	
const rowWidth   = 940;  // see note at end of module; if 950, imgs may wrap

function drawRows(useWidth) {
    if (itemcnt !== 0) {
        /*
         * Begin the process by starting with all images set to the same
         * height [maxRowHt] for initial placement in a row:
         */ 
        var widthAtMax = [];
        for (var j=0; j<itemcnt; j++) {
            widthAtMax[j] = Math.floor(maxRowHt * aspects[j]);
        }
        var rowNo = 0;
        var currWidth = 0;
        var scale;
        var rowHt;
        var imgStartNo = 0;
        var rowHtml = '';
        var styling;
        var iwidth;
        var rowComplete = false;
        var itype = [];
        // row width calculation will include 1px between each image
        var leftMostImg = true;
        // calculation loop: place pix in row till exceeds rowWdith, then fit
        for (var n=0; n<itemcnt; n++) {
           if (leftMostImg === false) {
                currWidth += 1;
            }
            currWidth += widthAtMax[n]; // place next pic in row
            leftMostImg = false;
            if (n < photocnt) {
                itype[n] = "photo";  // popups need to know if captioned
            } else {
                itype[n] = "image";  // no popup
            }
            // when currWidth exceeds useWidth, then force fit to useWidth
            if (currWidth >= useWidth) { 
                // this row is now filled
                rowComplete = true;
                scale = useWidth/currWidth;
                rowHt = Math.floor(scale * maxRowHt);
                rowHtml += '<div id="row' + rowNo + 
                    '" class="ImgRow">' + "\n";
                for (var k=imgStartNo; k<n+1; k++) { // "n' was the last img added
                    // for each pic in this row, resize to fit
                    if (k === imgStartNo) {
                        styling = ''; // don't add left-margin to leftmost image
                    } else {
                        styling = 'margin-left:1px;';
                    }
                    if (itype[k] === "photo") {
                        iwidth = Math.floor(scale * widthAtMax[k]);
                        rowHtml += '<img id="pic' + k + '" style="' +
                            styling + '" width="' + iwidth + '" height="' +
                            rowHt + '" src="' + "/pictures/zsize/" + piclnks[k] + "_z.jpg" + '" alt="' +
                            capts[k] + '" />' + "\n";
                    } else {
                        iwidth = Math.floor(scale * widthAtMax[k]);
                        rowHtml += '<img style="' + styling + '" width="' +
                            iwidth + '" height="' + rowHt + '" src="' +
                            "../images/" + piclnks[k] + '" alt="Additional non-captioned image" />' + "\n";
                    }
                }  // end of for each image -> fit
                imgStartNo = n+1;
                rowNo++;
                rowHtml += "</div>\n";
                leftMostImg = true;
                currWidth = 0;
            }
            if ( (n === itemcnt-1) && !rowComplete ) {
                // in this case, last row will not be filled, so no scaling
                if (mobile) {
                    rowHtml += '<div id="row' + rowNo + '" class="ImgRow mobile">' + "\n";
                } else {
                    rowHtml += '<div id="row' + rowNo + '" class="ImgRow">' + "\n";
                }
                for (var l=imgStartNo; l< n+1; l++) {
                    if (l === imgStartNo) {
                        styling = ''; // don't add left-margin to leftmost image
                    } else {
                        styling = 'margin-left:1px;';
                    }
                    if (itype[l] === 'photo') {
                        rowHtml += '<img id="pic' + l + '" style="' +
                            styling + '" width="' + widthAtMax[l] + '" height="' +
                            maxRowHt + '" src="' + "/pictures/zsize/" + piclnks[l] + "_z.jpg" + '" alt="' +
                            capts[l] + '" />' + "\n";
                    } else {
                        rowHtml += '<img style="' + styling + '" width="' +
                            widthAtMax[l] + '" height="' + maxRowHt + 
                            '" src="' + "../images/" + piclnks[l] + 
                            '" alt="Additional non-captioned image" />' + "\n";
                    }
                }
                rowHtml += "</div>\n";
            }
            rowComplete = false;
        } // end of processing images to fit in rows
        $('#imgArea').html(rowHtml);
    }
}
drawRows(rowWidth);
/* 
 * Note regarding initial row calculations:
 * A width of 960px is used here (actually, 946 to allow small margin on
 * each side of the row) as this is the base minimum window width for any
 * page, below which the window will not shrink, and a scroll bar will appear.
 * Therefore, the rows are set to their minimum width on page load. The
 * rowManagement.js script will grow those rows if the window widens, and
 * will not shrink below this original width when window shrinks.
 * See more detail in the rowManagement.js script. 
 */
