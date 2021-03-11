"use strict"
/**
 * @fileoverview For responsive design, the only window resize to occur
 * will be vertical/horizontal, so rows can always be simply calculated
 * based on the viewport width, vw, which is defined in logo.js
 * 
 * @author Ken Cowles
 * @version 1.0 Original release for responsive pages
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
var picSetupDone = $.Deferred();
var imgspan = '<span class="helper"></span>';

/**
 * Function to place one photo per row
 * @param {number} wd
 * @param {number} ht 
 * @param {number} item 
 * 
 * @return {null}
 */
const onePer = (wd, ht, item) => {
    rowHtml += '<img  id="pic' + item + '" width="' + wd + '" height="' + ht +
        '" src="' + "/pictures/zsize/" + piclnks[item] + "_z.jpg" +
        '" alt="' + capts[item] + '" />\n';
    return;
};
/**
 * 
 * @param {number} wd 
 * @param {number} item1
 * @param {number} item2
 * 
 * @return {null}
 */
const makePair = (picwd, item1, item2) => {  // always landscape images
    // set div to height of tallest image
    let height1 = Math.floor(picwd/aspects[item1]);
    let height2 = Math.floor(picwd/aspects[item2]);
    let divht;
    let top1;
    let top2;
    if (height1 === height2) {
        divht = height1;
        top1 = 0;
        top2 = 0;
    } else {
        divht = height2 > height1 ? height2 : height1;
        let topspace = height2 > height1 ? height2 - height1 : height1 - height2;
        topspace = parseInt(topspace/2);
        top1 = height1 < height2 ? topspace : 0;
        top2 = height2 < height1 ? topspace : 0;
    }
    // row div
    rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +'px;height:' + divht +
        'px;margin-left:6px;" class="ImgRow">' + "\n";
    // images
    rowHtml += '<img  id="pic' + item1 + '" width="' + picwd + '" height="' + height1 +
        '" src="' + "/pictures/zsize/" + piclnks[item1] + "_z.jpg" + '" alt="' +
        capts[item1] + '" style="top:' + top1 + 'px;margin-right:1px;float:left;" />\n';
    rowHtml += '<img  id="pic' + item2 + '" width="' + picwd + '" height="' + height2 +
        '" src="' + "/pictures/zsize/" + piclnks[item2] + "_z.jpg" + '" alt="' +
        capts[item2] + '" style="top:' + top2 + 'px;float:left;" />\n';
    rowHtml += "</div>\n";
    rowNo++;
    return;
}

/**
 * Row creation
 */
var Wmax;
var rowNo = 0;
var rowHtml = '';
if (vw <= 415) {
    // only one image per row
    Wmax = Math.floor(vw - 12);  // 6px margin on each side of row
    for (let i=0; i<itemcnt; i++) {
        if (aspects[i] >= 1.00) { // landscape
            let pwd = Wmax;
            let pht = Math.floor(Wmax/aspects[i]);
            rowHtml += '<div id="row' + rowNo + '" style="width:' + pwd +
                'px;margin-left:6px;" class="ImgRow">' + "\n";
            onePer(pwd, pht, i);
        } else {  // portrait
            let pwd = Math.floor(aspects[i] * Wmax);
            let pht = Math.floor(pwd/aspects[i]);
            rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +
                'px;margin-left:6px;" class="ImgRow">' + "\n";
            onePer(pwd, pht, i);
        }
        rowHtml += "</div>\n";
        rowNo++;
    }
} else {
    // two images per row if landscape, else 1 image per row
    Wmax = Math.floor((vw - 8)); // 4px margin on each side of row
    let halfmax = Math.floor(Wmax/2) - 1; // 1 px between images
    // organize by groups of portraits and landscapes
    let ports = [];
    let lands = [];
    for (let i=0; i<itemcnt; i++) {
        if (aspects[i] < 1.00) {
            ports.push(i);
        } else {
            lands.push(i);
        }
    }
    // proceed with pairings first, if any
    let pairs = 0;
    let solo  = lands.length === 1 ? true : false; 
    if (!solo) {
        pairs = parseInt(lands.length/2);
        solo  = lands.length % 2 === 0 ? false : true;
    }
    for (let j=0; j<pairs; j++) {
        let a = 2 * j;
        let b = 2 * j + 1;
        makePair(halfmax, lands[a], lands[b]);
    }
    if (solo) {
        let solomax = 1.3 * halfmax;
        rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +
            'px;margin-left:6px;" class="ImgRow">' + "\n";
        let enditem = lands.length - 1;
        // get itemno for this item
        let itemno = lands[enditem];
        let lastHt = Math.floor(Wmax/aspects[itemno]);
        onePer(solomax,  lastHt, lands[enditem]);
        rowHtml += "</div>\n";
        rowNo++;
    }
    // now add portrait images:
    for (let k=0; k<ports.length; k++) {
        rowHtml += '<div id="row' + rowNo + '" style="width:' + Wmax +
            'px;margin-left:6px;" class="ImgRow">' + "\n";
        let pwd = Math.floor(1.2 * halfmax);
        let pht = Math.floor(pwd/aspects[ports[k]]);
        onePer(pwd, pht, ports[k]);
        rowHtml += "</div>\n";
        rowNo++;
    }
}
$('#imgArea').html(rowHtml);
picSetupDone.resolve();
