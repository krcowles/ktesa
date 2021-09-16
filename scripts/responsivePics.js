"use strict";
/**
 * @fileoverview For responsive design, the only window resize to occur
 * will be vertical/horizontal, so rows can always be simply calculated
 * based on the viewport width, vw, which is defined in logo.js
 *
 * @author Ken Cowles
 * @version 1.0 Original release for responsive pages
 * @version 1.1 Typescripted
 */
var hike = $('#trail').text();
// Decode array data passed via php from responsivePage.php
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
var picSetupDone = $.Deferred();
var imgspan = '<span class="helper"></span>';
var capbtn = '<button>Caption</button>';
/**
 * Function to place one photo per row
 */
var onePer = function (wd, ht, item) {
    rowHtml += '<div id="' + item + '" class="imgs"><img  id="pic' + item +
        '" width="' + wd + '" height="' + ht + '" src="/pictures/zsize/' +
        piclnks[item] + '_z.jpg" alt="trail photo" /><p id="pup' + item +
        '">' + capts[item] + '</p></div>\n';
    return;
};
/**
 * Function which places a side-by-side pair of photos in a row
 */
var makePair = function (picwd, item1, item2) {
    // set div to height of tallest image
    var ht1 = parseFloat(aspects[item1]);
    var ht2 = parseFloat(aspects[item2]);
    var height1 = Math.floor(picwd / ht1);
    var height2 = Math.floor(picwd / ht2);
    var divht;
    var top1;
    var top2;
    if (height1 === height2) {
        divht = height1;
        top1 = 0;
        top2 = 0;
    }
    else {
        divht = height2 > height1 ? height2 : height1;
        var topspace = height2 > height1 ? height2 - height1 : height1 - height2;
        topspace = topspace / 2;
        top1 = height1 < height2 ? topspace : 0;
        top2 = height2 < height1 ? topspace : 0;
    }
    // row div
    rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax + 'px;height:' + divht +
        'px;margin-left:6px;" class="ImgRow">' + "\n";
    // images
    rowHtml += '<div id="' + item1 + '" class="imgs"><img  id="pic' + item1 +
        '" width="' + picwd + '" height="' + height1 + '" src="/pictures/zsize/' +
        piclnks[item1] + '_z.jpg" alt="trail photo" style="top:' + top1 +
        'px;margin-right:1px;float:left;" /><p id="pup' + item1 + '">' +
        capts[item1] + '</p></div>\n';
    rowHtml += '<div id="' + item2 + '" class="imgs"><img  id="pic' + item2 +
        '" width="' + picwd + '" height="' + height2 + '" src="/pictures/zsize/' +
        piclnks[item2] + '_z.jpg" alt="trail photo" style="top:' + top2 +
        'px;float:left;" /><p id="pup' + item2 + '">' + capts[item2] + '</p></div>\n';
    rowHtml += "</div>\n";
    rowNo++;
    return;
};
/**
 * Image Row creation - based on screen size
 */
var Wmax;
var rowNo = 0;
var rowHtml = '';
if (vw <= 415) {
    // only one image per row
    Wmax = Math.floor(vw - 12); // 6px margin on each side of row
    for (var i = 0; i < itemcnt; i++) {
        var item_aspect = parseFloat(aspects[i]);
        if (item_aspect >= 1.00) { // landscape
            var pwd = Wmax;
            var pht = Math.floor(Wmax / item_aspect);
            rowHtml += '<div id="row' + rowNo + '" style="width:' + pwd +
                'px;margin-left:6px;" class="ImgRow">' + "\n";
            onePer(pwd, pht, i);
        }
        else { // portrait
            var pwd = Math.floor(item_aspect * Wmax);
            var pht = Math.floor(pwd / item_aspect);
            rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +
                'px;margin-left:6px;" class="ImgRow">' + "\n";
            onePer(pwd, pht, i);
        }
        rowHtml += "</div>\n";
        rowNo++;
    }
}
else {
    // two images per row if landscape, else 1 image per row
    Wmax = Math.floor((vw - 8)); // 4px margin on each side of row
    var halfmax = Math.floor(Wmax / 2) - 1; // 1 px between images
    // organize by groups of portraits and landscapes
    var ports = [];
    var lands = [];
    for (var i = 0; i < itemcnt; i++) {
        var item = parseFloat(aspects[i]);
        if (item < 1.00) {
            ports.push(i);
        }
        else {
            lands.push(i);
        }
    }
    // proceed with pairings first, if any
    var pairs = 0;
    var solo = lands.length === 1 ? true : false;
    if (!solo) {
        pairs = Math.floor(lands.length / 2);
        solo = lands.length % 2 === 0 ? false : true;
    }
    for (var j = 0; j < pairs; j++) {
        var a = 2 * j;
        var b = 2 * j + 1;
        makePair(halfmax, lands[a], lands[b]);
    }
    if (solo) {
        var solomax = 1.3 * halfmax;
        rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +
            'px;margin-left:6px;" class="ImgRow">' + "\n";
        var enditem = lands.length - 1;
        // get itemno for this item
        var itemno = lands[enditem];
        var item = parseFloat(aspects[itemno]);
        var lastHt = Math.floor(Wmax / item);
        onePer(solomax, lastHt, lands[enditem]);
        rowHtml += "</div>\n";
        rowNo++;
    }
    // now add portrait images:
    for (var k = 0; k < ports.length; k++) {
        var item = parseFloat(aspects[ports[k]]);
        rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +
            'px;margin-left:6px;" class="ImgRow">' + "\n";
        var pwd = Math.floor(1.2 * halfmax);
        var pht = Math.floor(pwd / item);
        onePer(pwd, pht, ports[k]);
        rowHtml += "</div>\n";
        rowNo++;
    }
}
$('#imgArea').html(rowHtml);
picSetupDone.resolve();
