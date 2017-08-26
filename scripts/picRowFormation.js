var database = '../data/database.xml';
var hike = $('#trail').text();
// Decode array data passed via php:
var descs = d.split("|");
var alblnks = al.split("|");
var piclnks = p.split("|");
var capts = c.split("|");
var aspects = as.split("|");
var widths = w.split("|");
var itemcnt = descs.length;
// photocnt passed via php - shows how many of above have captions

// NOMINAL SETTINGS:
var maxRowHt = 260;	
var rowWidth = 950;
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
var imgRows = [];   // holds each row's html
var noProcessed = 0;
var startIndx = 0;
var rowComplete = false; // ???????????? where to put this
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
    // when currWidth exceeds rowWidth, then force fit to rowWidth
    if (currWidth >= rowWidth) { 
        // this row is now filled
        rowComplete = true;
        scale = rowWidth/currWidth;
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
                    rowHt + '" src="' + piclnks[k] + '" alt="' +
                    capts[k] + '" />' + "\n";
            } else {
                iwidth = Math.floor(scale * widthAtMax[k]);
                rowHtml += '<img style="' + styling + '" width="' +
                    iwidth + '" height="' + rowHt + '" src="' +
                    piclnks[k] + '" alt="Additional non-captioned image" />' + "\n";
            }
        }  // end of for each image -> fit
        imgStartNo = n+1;
        rowNo++;
        rowHtml += "</div>\n";
        leftMostImg = true;
        currWidth = 0;
    }
    if ( (n === itemcnt-1) && !rowComplete ) {
        // last row will not be filled, so no scaling
        rowHtml += '<div id="row' + rowNo + 
            '" class="ImgRow">' + "\n";
        for (var l=imgStartNo; l< n+1; l++) {
            if (l === imgStartNo) {
                styling = ''; // don't add left-margin to leftmost image
            } else {
                styling = 'margin-left:1px;';
            }
            if (itype[l] === 'photo') {
                rowHtml += '<img id="pic' + l + '" style="' +
                    styling + '" width="' + widthAtMax[l] + '" height="' +
                    maxRowHt + '" src="' + piclnks[l] + '" alt="' +
                    capts[l] + '" />' + "\n";
            } else {
                rowHtml += '<img style="' + styling + '" width="' +
                    widthAtMax[l] + '" height="' + maxRowHt + 
                    '" src="' + piclnks[l] + 
                    '" alt="Additional non-captioned image" />' + "\n";
            }
        }
        rowHtml += "</div>\n";
    }
    rowComplete = false;
} // end of processing images to fit in rows
$('#imgArea').html(rowHtml);
$('img[id^="pic"]').each( function(indx) {
    $(this).css('cursor','pointer');
    $(this).on('click', function() {
        window.open(alblnks[indx],"_blank");
    });
});
