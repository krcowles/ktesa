"use strict";
/// <reference path='./canvas.d.ts' />
/**
 * @fileoverview This file supplies functions and variables to draw
 * an elevation profile on the page with a given gpx track name. It
 * also allows the user to check/uncheck tracks to change the tracks
 * that are displayed on the map and correlate the topmost checked
 * track with the elevation chart that is displayed.
 * @author Ken Cowles
 *
 * @version 3.0 Added Cluster Page compatibility
 * @version 3.1 Added mobile page width control
 * @version 4.0 Typescripted, some type errors corrected
 * @version 4.1 Modified viewport calculations to improve zoom performance
 * @version 4.2 Segregated #adnote for inclusion only in non-mobile case
 * @version 4.3 Fixed #advisory positioning when scrolling
 * @version 5.0 Added echart measurement between points
 */
//GPSV iframe: The following code addresses tracklist checkboxes in the iframe map
var trackNames = [];
var checkboxes = [];
var box_states = [];
var lastTrack = 0; // indx in box_states
// global charting vars
var canvasEl = document.getElementById('grph');
var coords; // x,y location of mouse in chart
var indxOfPt;
var prevCHairs = false;
var imageData = new ImageData(10, 10);
var clearModal = document.getElementById('ediff');
var clearPoints = new bootstrap.Modal(clearModal);
// vars for setting chart dimension;
var fullWidth;
var chartHeight;
// vars for measuring between points:
var pointA = { set: false, miles: 0, elev: 0 };
var pointB = { set: false, miles: 0, elev: 0 };
var clrBtn = document.getElementById('eclr');
// misc.
var $dnote = $('<div><em>Click points on chart to calculate differences</em></div>');
var cluspage = $('#cpg').text() === 'yes' ? true : false;
var do_resize = true;
var trackNumber; // global used to identify current active track (topmost in tracklist)
var chartConst;
var pnlMarg;
var $jqnote = $('#advisory');
if (!mobile) {
    chartConst = 0.768; // panel = 23% in CSS
    // Hide/unhide side panel (changes width of elevation profile chart)
    var orgWidth;
    $('#hide').on('click', function () {
        orgWidth = canvasEl.width;
        $('#sidePanel').css('display', 'none');
        $('#chartline').width(fullWidth);
        canvasEl.width = fullWidth;
        // redraw the chart
        drawChart(trackNumber);
        $('iframe').width(fullWidth);
        $('#unhide').css('display', 'block');
    });
    $('#unhide').on('click', function () {
        $('#sidePanel').css('display', 'block');
        $('#chartline').width(orgWidth);
        canvasEl.width = orgWidth;
        $('#chartline').height(chartHeight);
        canvasEl.height = chartHeight;
        // redraw the chart
        drawChart(trackNumber);
        $('iframe').width(orgWidth);
        $('#unhide').css('display', 'none');
    });
    $('#adnote').on('mouseover', function () {
        var asc_desc_pos = $(this).offset();
        $(this).next().css({
            top: asc_desc_pos.top + 16,
            left: asc_desc_pos.left
        });
        $jqnote.show();
    });
    $('#adnote').on('mouseout', function () {
        $jqnote.hide();
    });
}
else {
    chartConst = 1.0000;
}
$jqnote.hide();
function clearChart() {
    drawChart(trackNumber);
    pointA = { set: false, miles: 0, elev: 0 };
    pointB = { set: false, miles: 0, elev: 0 };
}
/**
 * Once a track is identified for display, show that gpx file's data in the
 * side panel.
 */
var displayTrackSidePanel = function (trkname) {
    var data = panelData[trkname];
    $('#ascent').text(data["ascent"]);
    $('#descent').text(data["descent"]);
    $('#hdiff').text(data["diff"]);
    $('#hlgth').text(data["miles"]);
    $('#hmmx').text(data["feet"]);
    $('#hlog').text(data["logistics"]);
    $('#hexp').text(data["expo"]);
    $('#hseas').text(data["seasons"]);
    $('#hwow').text(data["wow"]);
};
/**
 * This function turns on the topmost checked tracklist box. If all boxes
 * are unchecked, the last box checked remains displayed in elevation chart.
 */
var plotTopMost = function () {
    for (var n = 0; n < box_states.length; n++) {
        if (box_states[n] === 1) {
            lastTrack = n;
            break;
        }
    }
    // find index in preloaded tracks:
    trackNumber = gpsvTracks.indexOf(trackNames[lastTrack]);
    canvasEl.onmousemove = null;
    drawChart(trackNumber);
};
// one-time tracklist setup when iframe is loaded:
var mapdiv = document.getElementById('mapline');
mapdiv.onload = function () {
    setTimeout(function () {
        var tracklist_class = 'gv_tracklist_item';
        // get HTMLCollection of tracks in tracklist]
        var gpsvIframeDoc = mapdiv.contentWindow;
        var gvTracks = gpsvIframeDoc.document.getElementsByClassName(tracklist_class);
        for (var j = 0; j < gvTracks.length; j++) {
            var classEl = gvTracks[j];
            var classChild = classEl.firstChild; // this is a table
            var $tbl = $(classChild);
            var $tblrow = $tbl.find('tr'); // should only be one row;
            var $items = $tblrow.find('td');
            // $items[0] is the checkbox; $items[1] contains the track name
            var item1 = $items[1];
            var trackItem = item1.firstChild;
            var trackName = trackItem.innerHTML;
            trackNames.push(trackName);
            var checkbox = $items[0].firstChild;
            var $checkbox = $(checkbox);
            checkboxes.push($checkbox);
            // initialize box_states array (tracks 'checkbox checked' T/F)
            if (cluspage) {
                for (var k = 0; k < checkboxes.length; k++) {
                    box_states[k] = 1;
                }
            }
            else {
                if (j > 0) {
                    box_states[j] = 0;
                    // on page load, all boxes are checked: leave top box on only
                    $checkbox.trigger('click');
                }
                else {
                    box_states[0] = 1;
                }
            }
        }
        // click behaviors:
        checkboxes.forEach(function (box, indx) {
            box.on('click', function () {
                // validate checkbox states
                if (box.is(":checked")) {
                    for (var k = 0; k < box_states.length; k++) {
                        if (k === indx) {
                            box_states[k] = 1;
                            break;
                        }
                    }
                }
                else {
                    box_states[indx] = 0;
                }
                plotTopMost();
            });
        });
        $.when(allTracks).then(function () {
            trackNumber = gpsvTracks.indexOf(trackNames[0]) === -1 ?
                0 : gpsvTracks.indexOf(trackNames[0]);
            /* NOTE: If the track name is empty in the json file,
             * trackNumber = -1. In this case, the trackNumber
             * needs to be set to 0, else no echart is displayed.
             * The json file should be fixed to prevent confusion.
             */
            drawChart(trackNumber);
        });
    }, 200);
};
if (!mobile) {
    if ($('#sidePanel').css('display') !== 'none') {
        var pnlBorders = parseFloat($('#sidePanel').css('border-left-width')) +
            parseFloat($('#sidePanel').css('border-right-width'));
        var pnlMargins = parseFloat($('#sidePanel').css('margin-left')) +
            parseFloat($('#sidePanel').css('margin-right'));
        pnlMarg = Math.ceil(pnlBorders) + Math.ceil(pnlMargins);
    }
    else {
        pnlMarg = 0;
    }
}
else {
    pnlMarg = 0;
}
setChartDims();
/**
 * This function establishes the chart dimensions on the page
 */
function setChartDims() {
    // calculate space available for canvas: (panel width = 23%)
    fullWidth = Math.floor(document.body.clientWidth);
    if (!mobile) { // don't mess with chart height for mobile!
        var chartWidth = Math.floor(chartConst * fullWidth) - pnlMarg;
        var vpHeight = window.innerHeight;
        var sidePnlPos = $('#sidePanel').offset();
        var sidePnlLoc = sidePnlPos.top;
        var usable = vpHeight - sidePnlLoc;
        chartHeight = Math.floor(0.35 * usable);
        if (chartHeight < 100) {
            $('#chartline').height(100);
            canvasEl.height = 100;
        }
        else {
            $('#chartline').height(chartHeight);
            canvasEl.height = chartHeight;
        }
        if ($('#sidePanel').css('display') !== 'none') {
            $('#chartline').width(chartWidth);
        }
        else {
            chartWidth = fullWidth;
        }
    }
    else {
        chartWidth = fullWidth;
    }
    canvasEl.width = chartWidth;
    $('iframe').width(chartWidth);
    return;
}
var chartNote = function () {
    var cloc = canvasEl.getBoundingClientRect();
    $dnote.css({
        top: cloc.top,
        left: cloc.left + 74,
        zIndex: '500',
        position: 'absolute',
        display: 'inline-block',
        color: 'brown'
    });
    $('#chartline').append($dnote);
};
/**
 * This function will draw the selected elevation profile in the canvas element
 * Note however that the sidepanel data may not correspond to the track -number-
 * as the track number is not necessarily in the same order as the track names.
 * Therefore, use the name of the track from gpsvTracks.
 */
function drawChart(trackNo) {
    var chartData = defineData(trackNo);
    ChartObj.render('grph', chartData);
    crossHairs(trackNo);
    if (typeof panelData === 'object') {
        displayTrackSidePanel(gpsvTracks[trackNo]);
        if (mobile) {
            chartPlaced.resolve();
        }
    }
    chartNote();
    return;
}
/**
 * The data being sent to the ChartObj is supplied here
 */
function defineData(track) {
    // data object for the chart:
    var dataDef = {
        title: gpsvTracks[track],
        minY: trkMins[track],
        maxY: trkMaxs[track],
        xLabel: 'Distance (miles)',
        yLabel: 'Elevation (feet)',
        labelFont: '10pt Arial',
        dataPointFont: '8pt Arial',
        renderTypes: { lines: ChartObj.renderType.lines, points: ChartObj.renderType.points },
        dataPoints: trkRows[track]
    };
    return dataDef;
}
/**
 * This function sets up or hides the crosshairs when the mouse is in bounds
 * of the elevation profile chart
 */
function crossHairs(trackno) {
    canvasEl.onmousemove = function (e) {
        var loc = window2canvas(canvasEl, e.clientX, e.clientY);
        coords = dataReadout(loc, trackno);
        if (!prevCHairs) {
            imageData = context.getImageData(0, 0, canvasEl.width, canvasEl.height);
            prevCHairs = true;
        }
        else {
            context.putImageData(imageData, 0, 0);
        }
        drawLine(coords.px, margin.top, coords.px, margin.top + yMax, 'Tomato', 1);
        drawLine(margin.left, coords.py, margin.left + xMax, coords.py, null, null);
        if (coords.x !== -1) {
            var mapObj = {
                lat: trkLats[trackno][indxOfPt],
                lng: trkLngs[trackno][indxOfPt]
            };
            infoBox(coords.px, coords.py, coords.x.toFixed(2), coords.y.toFixed(), mapObj);
        }
    };
    canvasEl.onmouseout = function () {
        context.putImageData(imageData, 0, 0);
        prevCHairs = false;
        var mapFrame = document.getElementById('mapline');
        var mapFrameWin = mapFrame.contentWindow;
        mapFrameWin.chartMrkr.setMap(null);
    };
    canvasEl.onmousedown = function (e) {
        var ctxt = canvasEl.getContext("2d");
        var loc = window2canvas(canvasEl, e.clientX, e.clientY);
        var mark = dataReadout(loc, trackno);
        if (mark.x !== -1) {
            if (!pointA.set) {
                pointA.set = true;
                pointA.miles = mark.x;
                pointA.elev = mark.y;
                drawDot(ctxt, mark.px, mark.py);
                imageData = ctxt.getImageData(0, 0, canvasEl.width, canvasEl.height);
            }
            else if (!pointB.set) {
                pointB.set = true;
                pointB.miles = mark.x;
                pointB.elev = mark.y;
                drawDot(ctxt, mark.px, mark.py);
                var mdiff = (pointB.miles - pointA.miles).toFixed(2);
                var melev = (pointB.elev - pointA.elev).toFixed(1);
                var modal_miles = document.getElementById('emiles');
                var modal_elev = document.getElementById('eelev');
                modal_miles.textContent = mdiff;
                modal_elev.textContent = melev;
                imageData = context.getImageData(0, 0, canvasEl.width, canvasEl.height);
                ctxt.putImageData(imageData, 0, 0);
                clearPoints.show();
            }
        }
    };
    return;
}
/**
 * Translate the mouse coords into canvas coords
 */
function window2canvas(canvas, x, y) {
    /* it is necessary to get bounding rect each time as the user may have
     * scrolled the window down (or resized), and the rect is measured wrt/viewport
     */
    var container = canvasEl.getBoundingClientRect();
    return {
        x: x - container.left * (canvas.width / container.width),
        y: y - container.top * (canvas.height / container.height)
    };
}
/**
 * From the canvas x,y (translated by window2canvas), where to draw crosshairs
 */
function dataReadout(mousePos, trackno) {
    var xDat = 0;
    var yDat = 0;
    if (mousePos.x > margin.left) {
        var chartPos = mousePos.x - margin.left;
        //var chartY = mousePos.y - margin.top;
        var lastEl = trkRows[trackno].length - 1;
        var maxMile = trkRows[trackno][lastEl].x;
        var unitsPerPixel = maxMile / xMax;
        var xDat = chartPos * unitsPerPixel;
        if (xDat <= maxMile) {
            var bounds = findNeighbors(xDat, trackno);
            if (bounds.u === bounds.l) {
                yDat = trkRows[trackno][bounds.u].y;
                indxOfPt = bounds.u;
            }
            else {
                var higher = trkRows[trackno][bounds.u].x;
                var lower = trkRows[trackno][bounds.l].x;
                var extrap = (xDat - lower) / (higher - lower);
                if (extrap >= 0.5) {
                    xDat = higher;
                    yDat = trkRows[trackno][bounds.u].y;
                    indxOfPt = bounds.u;
                }
                else {
                    xDat = lower;
                    yDat = trkRows[trackno][bounds.l].y;
                    indxOfPt = bounds.l;
                }
            }
            return {
                x: xDat,
                y: yDat,
                px: margin.left + pxPerMile * xDat,
                py: margin.top + (rgMax - yDat) * ratio
            };
        }
        else {
            return { x: -1, y: -1 };
        }
    }
    else {
        return { x: -1, y: -1 };
    }
}
/**
 * Get upper/lower locs of point
 */
function findNeighbors(xDataPt, trackno) {
    var upper = 0;
    var lower = 0;
    for (var k = 0; k < trkRows[trackno].length; k++) {
        if (trkRows[trackno][k].x === xDataPt) {
            upper = k;
            lower = k;
            break;
        }
        else {
            if (xDataPt < trkRows[trackno][k].x) {
                upper = k;
                lower = k - 1;
                break;
            }
        }
    }
    return {
        u: upper,
        l: lower
    };
}
/**
 * Redraw when there is a window resize
 * @return {null}
 */
$(window).on('resize', function () {
    if (do_resize) {
        $dnote.remove();
        prevCHairs = false;
        do_resize = false;
        setTimeout(function () {
            canvasEl.onmousemove = null;
            setChartDims();
            var chartData = defineData(trackNumber);
            ChartObj.render('grph', chartData);
            crossHairs(trackNumber);
            chartNote();
            do_resize = true;
        }, 300);
    }
    return;
});
