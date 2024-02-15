interface GPX_ObjectArray {
    [index:number]: GPX_Object;
    length: number;
    forEach: (anonymous: any) => GPX_Object;
}
interface GPX_Object {
    [key:string]: GPX_File_Object;
    
}
interface GPX_File_Object {
    name: string;
    files: string[];
    forEach: (anonymous: any) => string;
}
declare var gpx_file_list: GPX_ObjectArray;
declare var photocnt: number;
declare var d: string;
declare var al: string;
declare var p: string;
declare var c: string;
declare var as: string;
declare var w: string;
declare var mobile: boolean;
/**
 * @fileoverview Size the viewport and place elements in it, then add the rows
 * of photos.
 * 
 * @author Ken Cowles
 * @version 1.0 Replaces picRowFormation.ts/js and is compatible with bootstrap
 * @version 1.1 Modified setViewport() to improve zoom performance
 */
var itemcnt: number;
var cluster_page = $('#cpg').text() === 'yes' ? true : false;
if (!cluster_page) {
    // Decode the photo array data passed via php:
    var descs   = d.split("|");
    var alblnks = al.split("|");
    var piclnks = p.split("|");
    var capts   = c.split("|");
    var aspects = as.split("|");
    var widths  = w.split("|");
    if (descs[0] !== '') {
        var itemcnt = descs.length;
    } else {
        var itemcnt = 0;
    }
} else {
    itemcnt = 0;
}
// globals
var multiDwnld = new bootstrap.Modal(<HTMLElement>document.getElementById('multigpx'));
var appMode  = $('#appMode').text() as string;
var hikegpx  = $('#gpx').text() as string;
var winWidth = document.body.clientWidth;
var vpHeight: number;
var sidePnlLoc: number;
var pnlTopBottom: number;
var initLoad = true;
// jquery page elements
var $panel: JQuery<HTMLElement>;
var $mapEl: JQuery<HTMLElement>;
var $chartEl: JQuery<HTMLElement>;
// Nominal settings for drawing picture rows
const pageMargin = 36;
const maxRowHt   = 260;	
const rowWidth   = 940;
$(function() { 
    // after page load, initialize globals defined above...
    $panel   = $('#sidePanel'); // always present on page load
    $mapEl   = $('#mapline');
    $chartEl = $('#chartline');
    pnlTopBottom = parseFloat($panel.css('border-top-width')) +
        parseFloat($panel.css('border-bottom-width'));
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
 * Establishing the viewport layout occurs on page load and also on window resizing.
 * This routine establishes the basic vertical space consumed by the map and chart,
 * but dynamicChart.js completely defines all chart parameters (and window.resize)
 */
function setViewport() {
    vpHeight = document.body.clientHeight;
    var sidePnlPos = <JQuery.Coordinates>$('#sidePanel').offset(); // NOTE: includes border box
    sidePnlLoc = sidePnlPos.top;
    var usable = vpHeight - sidePnlLoc;
    var mapHt = Math.floor(0.65 * usable);
    var chartHt = Math.floor(0.35 * usable);
    var pnlHeight = (mapHt + chartHt) - pnlTopBottom;
    $panel.height(pnlHeight); // Fits panel to page when debug/inspect
    $mapEl.height(mapHt);
    $chartEl.height(chartHt);
}
/** 
 * Function pertinent to drawing rows of photos
 */
function drawRows(useWidth: number) {
    if (itemcnt !== 0) {
        /*
         * Begin the process by starting with all images set to the same
         * height [maxRowHt] for initial placement in a row:
         */ 
        var widthAtMax = [];
        for (var j=0; j<itemcnt; j++) {
            let item_aspect = parseFloat(aspects[j]);
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
        return;
    }
}
/**
 * The side panel has an option to download the hike's gpx file, and
 * the GPS Data section allows downloads of gpx files as well. NOTE:
 * couldn't get php headers to download via $.post, so created a local
 * download file and then removed it after downloading via javascript.
 */
function downloadURI(gpxfile:string):void {
    var link = document.createElement("a");
    link.download = gpxfile;
    link.href = gpxfile;
    link.click();
    $.post("deleteGpx.php", {gpx: gpxfile});
}
// To download a gpx file:
$('#dwn').on('click', function() {
    $('#idfiles').empty();  
    if (gpx_file_list.length > 1) {
        var ajax_files: string;
        for (let k=0; k<gpx_file_list.length; k++) {
            var dwnldItem = gpx_file_list[k];
            var gpx_val = Object.keys(dwnldItem);
            var gpx_filename = gpx_val[0];
            var json_arr = dwnldItem[gpx_filename];
            ajax_files = '';
            json_arr.forEach(function(file:string, i:number) {
                if (i === 0) {
                    ajax_files = file;
                } else {
                    ajax_files += ',' + file;
                }
            });      
            var list_el = '<li><a class="dwnldgpx" href="#">' +
                gpx_filename + '</a><span class="jfiles" ' +
                'style="display:none;">' + ajax_files + '</span></li>';
            $('#idfiles').append(list_el);
        }
        multiDwnld.show();
    } else {
        // singleton
        var ajax_files = '';
        var main = gpx_file_list[0];
        var gpx_val = Object.keys(main);
        var gpx_filename = gpx_val[0];
        var json_arr = main[gpx_filename];
        json_arr.forEach(function(file:string, i:number) {
            if (i === 0) {
                ajax_files = file;
            } else {
                ajax_files += ',' + file;
            }
        });
        $.post("makeGpx.php", {name: gpx_filename, json_files: ajax_files},
            function() {
                downloadURI(gpx_filename);
                multiDwnld.hide();
            }
        );
    }
});
$('body').on('click', '.dwnldgpx', function() {
    var gpx = $(this).text() as string;
    var file_list = $(this).next().text() as string;
    $.post("makeGpx.php", {name: gpx, json_files: file_list}, function() {
        downloadURI(gpx);
        multiDwnld.hide();
    });
    return;
});
// When there are gpx files in the GPS Data section:
if ($('.gpxview').length) {
    $('.gpxview').each(function() {
        $(this).on('click', function(ev) {
            ev.preventDefault();
            let path     = <string>$(this).attr('href');
            let filename = path.substring(7);
            let file_display = '../php/viewGpxFile.php?gpx=' + filename;
            window.open(file_display, "_blank");
        });
    });
}
// If there is a kml file, process it via displayKml.php
if ($('.mapfile').length) {
    $('.mapfile').each(function() {
        $(this).on('click', function(ev) {
            let path = <string>$(this).attr('href');
            let filename = path.substring(7);
            let file_ext = filename.replace(/^.*\./, '');
            if (file_ext === 'kml') {
                ev.preventDefault();
                let kmlfile = '../maps/displayKml.php?kml=' + filename;
                window.open(kmlfile, "_blank");
            }
        });
    });
}
