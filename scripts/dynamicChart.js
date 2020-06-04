/**
 * @fileoverview This file supplies functions and variables to draw
 * an elevation profile on the page with a given gpx track name.
 * @author Tom Sandberg
 * @author Ken Cowles
 */
var resizeFlag = true;
var trackNumber;   // global used in hide/unhide & window.resize
// Hide/unhide side panel (changes width of elevation profile chart)
$('#hide').on('click', function() {
    $('#sidePanel').css('display', 'none');
    $('#chartline').width(fullWidth);
    canvasEl.width = fullWidth;
    // redraw the chart
    drawChart(trackNumber);
    $('iframe').width(fullWidth);
    $('#unhide').css('display','block');
});
$('#unhide').on('click', function() {
    $('#sidePanel').css('display','block');
    $('#chartline').width(chartWidth);
    canvasEl.width = chartWidth;
    $('#chartline').height(chartHeight);
    canvasEl.height = chartHeight;
    // redraw the chart
    drawChart(trackNumber);
    $('iframe').width(chartWidth);
    $('#unhide').css('display','none');
});

/**
 * ----- GPSV iframe:
 * The following code addresses tracklist checkboxes in the iframe map
 */
var trackNames = [];
var checkboxes = [];
var box_states = [];
var lastTrack = 0; // indx in box_states
/**
 * This function turns on the topmost checked tracklist box. If all boxes
 * are unchecked, the last box checked remains displayed in elevation chart.
 * 
 * @param {array} cbarray The set of neighboring checkboxes in the tracklist
 * @return {null}
 */
const plotTopMost = () => {
    for (let n=0; n<box_states.length; n++) {
        if (box_states[n] === 1) {
            lastTrack = n;
            break;
        }
    }
    // find index in preloaded tracks:
    trackNumber = gpsvTracks.indexOf(trackNames[lastTrack]);
    canvasEl.onmousemove = null;
    drawChart(trackNumber);    
}
// one-time tracklist setup when iframe is loaded:
var mapdiv = document.getElementById('mapline');
mapdiv.onload = function() {
    setTimeout(function() {
        let tracklist_class = 'gv_tracklist_item';
        // get HTMLCollection of tracks in tracklist
        let gvTracks = mapdiv.contentWindow.document.getElementsByClassName(tracklist_class);
        for (let j=0; j<gvTracks.length; j++) {
            let $tbl = $(gvTracks[j].firstChild);
            let $tblrow = $tbl.find('tr'); // should only be one row;
            let $items = $tblrow.find('td');
            // $items[0] is the checkbox; $items[1] contains the track name
            let trackName = $items[1].firstChild.innerText;
            trackNames.push(trackName);
            let checkbox = $items[0].firstChild;
            let $checkbox = $(checkbox);
            checkboxes.push($checkbox);
            if (j>0) {
                box_states[j] = 0;
                // on page load, all boxes are checked: leave top box on only
                $checkbox.click();
            } else {
                box_states[0] = 1;
            }
        }
        // click behaviors:
        checkboxes.forEach(function(box, indx) {
            box.on('click', function() {
                // validate checkbox states
                if (box.is(":checked")) {
                    for (let k=0; k<box_states.length; k++) {
                        if (k === indx) {
                            box_states[k] = 1;
                            break;
                        }
                    }
                } else {
                    box_states[indx] = 0;
                }
                plotTopMost();
            });
        });
        $.when(allTracks).then(function() {
            trackNumber = gpsvTracks.indexOf(trackNames[0]);
            drawChart(trackNumber);
        });
    }, 200);
}

// global charting vars
var canvasEl = document.getElementById('grph');
var coords = {};  // x,y location of mouse in chart
var indxOfPt;
var prevCHairs = false;

// vars for setting chart dimension;
var fullWidth;
var chartHeight;
var chartWidth;
var lmarg = $('#sidePanel').css('margin-left');
var pnlMarg = lmarg.substr(0, lmarg.length-2); // remove 'px' at end
setChartDims();

/**
 * This function establishes the chart dimensions on the page
 * @return {null}
 */
function setChartDims() {
    // calculate space available for canvas: (panel width = 23%)
    fullWidth = $('body').innerWidth();
    chartWidth = Math.floor(0.77 * fullWidth) - pnlMarg;
    var vpHeight = window.innerHeight;
    var sidePnlPos = $('#sidePanel').offset();
    var sidePnlLoc = parseInt(sidePnlPos.top);
    var usable = vpHeight - sidePnlLoc;
    chartHeight = Math.floor(0.35 * usable);
    if (chartHeight < 100) {
        $('#chartline').height(100);
        canvasEl.height = 100;
    } else {
        $('#chartline').height(chartHeight);
        canvasEl.height = chartHeight;
    }
    $('#chartline').width(chartWidth);
    canvasEl.width = chartWidth;
    $('iframe').width(chartWidth);
    return;
}

/**
 * This function will draw the selected elevation profile in the canvas element
 * @param {number} trackNo integer representing track item in gpsv tracklist
 * @return {null}
 */
function drawChart(trackNo) {
    var chartData = defineData(trackNo);
    ChartObj.render('grph', chartData);
    crossHairs(trackNo);
    return;
}
/**
 * The data being sent to the ChartObj is supplied here
 * @param {number} track integer representing track item in gpxv tracklist
 * @return {object}
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
        renderTypes: [ChartObj.renderType.lines, ChartObj.renderType.points],
        dataPoints: trkRows[track]
    };
    return dataDef;
}
/**
 * This function sets up or hides the crosshairs when the mouse is in bounds
 * of the elevation profile chart
 * @return {null}
 */
function crossHairs(trackno) {
    var imageData = {};
    canvasEl.onmousemove = function (e) {
        var loc = window2canvas(canvasEl, e.clientX, e.clientY);
        coords = dataReadout(loc, trackno);
        if (!prevCHairs) {
            imageData = context.getImageData(0,0,canvasEl.width,canvasEl.height);
            prevCHairs = true;
        } else {
            context.putImageData(imageData, 0, 0);
        }
        drawLine(coords.px,margin.top,coords.px,margin.top+yMax,'Tomato',1);
        drawLine(margin.left,coords.py,margin.left+xMax,coords.py);
        if (coords.x !== -1) {
            var mapObj = { 
                lat: trkLats[trackno][indxOfPt],
                lng: trkLngs[trackno][indxOfPt]
            };
            infoBox(coords.px,coords.py,coords.x.toFixed(2),coords.y.toFixed(),mapObj);
        }
    };
    canvasEl.onmouseout = function (e) {
        context.putImageData(imageData,0,0);
        prevCHairs = false;
        document.getElementById('mapline').contentWindow.chartMrkr.setMap(null);
    }
    return;
}

/**
 * Translate the mouse coords into canvas coords
 * @param {object} canvas 
 * @param {number} x mouse position x
 * @param {number} y mouse position y
 * @return {object}
 */
function window2canvas(canvas,x,y) {
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
 * @param {object} mousePos 
 * @return {object}
 */
function dataReadout(mousePos, trackno) {
    var xDat = 0;
    var yDat = 0;
    if (mousePos.x > margin.left) {
        var chartPos = mousePos.x - margin.left;
        var chartY = mousePos.y - margin.top;
        var lastEl = trkRows[trackno].length - 1;
        var maxMile = trkRows[trackno][lastEl].x;
        var unitsPerPixel = maxMile/xMax;
        var xDat = chartPos * unitsPerPixel;
        if (xDat <= maxMile) {
            var bounds = findNeighbors(xDat, trackno);
            if (bounds.u === bounds.l) {
                yDat = trkRows[trackno][bounds.u].y;
                indxOfPt = bounds.u;
            } else {
                var higher = trkRows[trackno][bounds.u].x;
                var lower = trkRows[trackno][bounds.l].x;
                var extrap = (xDat - lower)/(higher - lower);
                if (extrap >= 0.5) {
                    xDat = higher;
                    yDat = trkRows[trackno][bounds.u].y;
                    indxOfPt = bounds.u;
                } else {
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
        } else {
            return { x: -1, y: -1 };
        }
    } else {
        return { x: -1, y: -1 };
    }    
}
/**
 * Get upper/lower locs of point
 * @param {number} xDataPt 
 * @return {object}
 */
function findNeighbors(xDataPt, trackno) {
    for (var k=0; k<trkRows[trackno].length; k++) {
        if (trkRows[trackno][k].x === xDataPt) {
            upper = k;
            lower = k;
            break;
        } else {
            if (xDataPt < trkRows[trackno][k].x) {
                var upper = k;
                var lower = k-1;
                break;
            }
        }
    }
    return {
        u: upper,
        l: lower
    }
}

/**
 * Redraw when there is a window resize
 * @return {null}
 */
$(window).resize( function() {
    if (resizeFlag) {
        prevCHairs = false;
        resizeFlag = false;
        setTimeout( function() {
            canvasEl.onmousemove = null;
            setChartDims();
            var chartData = defineData(trackNumber);
            ChartObj.render('grph', chartData);
            crossHairs(trackNumber);
            resizeFlag = true; 
        }, 300);      
    }  
});
