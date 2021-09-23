"use strict";
/**
 * @fileoverview Size the viewport and place elements in it, then add the rows
 * of photos.
 *
 * @author Ken Cowles
 * @version 1.0 Replaces picRowFormation.ts/js and is compatible with bootstrap
 */
var itemcnt;
var cluster_page = $('#cpg').text() === 'yes' ? true : false;
if (!cluster_page) {
    // Decode the photo array data passed via php:
    var descs = d.split("|");
    var alblnks = al.split("|");
    var piclnks = p.split("|");
    var capts = c.split("|");
    var aspects = as.split("|");
    var widths = w.split("|");
    if (descs[0] !== '') {
        var itemcnt = descs.length;
    }
    else {
        var itemcnt = 0;
    }
}
else {
    itemcnt = 0;
}
// globals used in multiple scripts
var winWidth = $(window).width();
var vpHeight;
var pnlBox; // caluclated once on page load
var pnlWidth;
var initLoad = true;
var canvasEl;
// jquery page elements
var $panel;
var $mapEl;
var $chartEl;
// Nominal settings for drawing picture rows
var pageMargin = 36;
var maxRowHt = 260;
var rowWidth = 940;
$(function () {
    // define globals
    $panel = $('#sidePanel'); // always present on page load
    $mapEl = $('#mapline');
    $chartEl = $('#chartline');
    canvasEl = document.getElementById('grph');
    // the following should not change throughout resizing, etc.
    var pnlMarg = parseInt($panel.css('margin-left')) +
        parseInt($panel.css('margin-right'));
    var pnlBorder = parseInt($panel.css('border-left-width')) +
        parseInt($panel.css('border-right-width'));
    var pnlPad = parseInt($panel.css('padding-left')) +
        parseInt($panel.css('padding-right'));
    pnlBox = pnlMarg + pnlBorder + pnlPad;
    // set viewport
    if ($('#mapline').length) {
        setViewport();
    }
    /**
     * Now create the html that comprises the rows of photos:
     * The rows will be drawn on page load, and again for window resize events
     * (see rowManagement.ts/js)
     */
    if (!cluster_page) {
        var initSize = winWidth - pageMargin;
        drawRows(initSize);
        $('html').css('overflow-x', 'scroll');
    }
});
/**
 * Establishing the viewport layout occurs on page load and also on
 * window resizing. The layout depends on the whether or not the
 * side panel is displayed. Don't mess with chart height, once established,
 * for mobile devices.
 */
function setViewport() {
    var canvasWidth;
    var panelDisplay = $panel.css('display') !== 'none' ? true : false;
    // Height calcs
    vpHeight = window.innerHeight;
    var consumed = $('#nav').height() + $('#logo').height();
    var usable = vpHeight - consumed;
    var mapHt = Math.floor(0.65 * usable);
    var chartHt = Math.floor(0.35 * usable);
    var pnlHeight = (mapHt + chartHt);
    $mapEl.height(mapHt);
    $chartEl.height(chartHt);
    $panel.height(pnlHeight);
    // Width calcs
    winWidth = $(window).width();
    if (panelDisplay) {
        pnlWidth = pnlBox + $panel.width();
    }
    else {
        pnlWidth = 0;
    }
    var availWidth = winWidth - pnlWidth;
    $mapEl.width(availWidth);
    $chartEl.width(availWidth);
    // set up canvas inside chartline div
    if (chartHt < 100) {
        $chartEl.height(100);
        canvasEl.height = 100;
    }
    else {
        canvasEl.height = chartHt;
    }
    if (panelDisplay) {
        pnlWidth = $panel.width();
    }
    else {
        pnlWidth = 0;
    }
    canvasWidth = availWidth;
    canvasEl.width = canvasWidth;
}
/**
 * Function pertinent to drawing rows of photos
 */
function drawRows(useWidth) {
    if (itemcnt !== 0) {
        /*
         * Begin the process by starting with all images set to the same
         * height [maxRowHt] for initial placement in a row:
         */
        var widthAtMax = [];
        for (var j = 0; j < itemcnt; j++) {
            var item_aspect = parseFloat(aspects[j]);
            widthAtMax[j] = Math.floor(maxRowHt * item_aspect);
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
        for (var n = 0; n < itemcnt; n++) {
            if (leftMostImg === false) {
                currWidth += 1;
            }
            currWidth += widthAtMax[n]; // place next pic in row
            leftMostImg = false;
            if (n < photocnt) {
                itype[n] = "photo"; // popups need to know if captioned
            }
            else {
                itype[n] = "image"; // no popup
            }
            // when currWidth exceeds useWidth, then force fit to useWidth
            if (currWidth >= useWidth) {
                // this row is now filled
                rowComplete = true;
                scale = useWidth / currWidth;
                rowHt = Math.floor(scale * maxRowHt);
                rowHtml += '<div id="row' + rowNo +
                    '" class="ImgRow">' + "\n";
                for (var k = imgStartNo; k < n + 1; k++) { // "n' was the last img added
                    // for each pic in this row, resize to fit
                    if (k === imgStartNo) {
                        styling = ''; // don't add left-margin to leftmost image
                    }
                    else {
                        styling = 'margin-left:1px;';
                    }
                    if (itype[k] === "photo") {
                        iwidth = Math.floor(scale * widthAtMax[k]);
                        rowHtml += '<img id="pic' + k + '" style="' +
                            styling + '" width="' + iwidth + '" height="' +
                            rowHt + '" src="' + "/pictures/zsize/" + piclnks[k] + "_z.jpg" + '" alt="' +
                            capts[k] + '" />' + "\n";
                    }
                    else {
                        iwidth = Math.floor(scale * widthAtMax[k]);
                        rowHtml += '<img style="' + styling + '" width="' +
                            iwidth + '" height="' + rowHt + '" src="' +
                            "../images/" + piclnks[k] + '" alt="Additional non-captioned image" />' + "\n";
                    }
                } // end of for each image -> fit
                imgStartNo = n + 1;
                rowNo++;
                rowHtml += "</div>\n";
                leftMostImg = true;
                currWidth = 0;
            }
            if ((n === itemcnt - 1) && !rowComplete) {
                // in this case, last row will not be filled, so no scaling
                if (mobile) {
                    rowHtml += '<div id="row' + rowNo + '" class="ImgRow mobile">' + "\n";
                }
                else {
                    rowHtml += '<div id="row' + rowNo + '" class="ImgRow">' + "\n";
                }
                for (var l = imgStartNo; l < n + 1; l++) {
                    if (l === imgStartNo) {
                        styling = ''; // don't add left-margin to leftmost image
                    }
                    else {
                        styling = 'margin-left:1px;';
                    }
                    if (itype[l] === 'photo') {
                        rowHtml += '<img id="pic' + l + '" style="' +
                            styling + '" width="' + widthAtMax[l] + '" height="' +
                            maxRowHt + '" src="' + "/pictures/zsize/" + piclnks[l] + "_z.jpg" + '" alt="' +
                            capts[l] + '" />' + "\n";
                    }
                    else {
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
        return;
    }
}
