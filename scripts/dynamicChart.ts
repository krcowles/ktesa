/// <reference path='./canvas.d.ts' />
declare var mobile: boolean;
declare var panelData: PanelData; // on hikePageTemplate.php
declare var chartPlaced: JQueryDeferred<void>; // in responsivePage.js (mobile only)
interface PanelData {
    [id: string]: HikeObject;
}
interface HikeObject {
    diff: string;
    expo: string;
    feet: number;
    logistics: string;
    miles: number;
    seasons: string;
    wow: string;
}
interface Bounds {
    u: number;
    l: number;
}
interface MousePosition {
    x: number;
    y: number;
}
interface GPSCoords {
    lat: number;
    lng: number;
}

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
 */
//GPSV iframe: The following code addresses tracklist checkboxes in the iframe map
var trackNames: string[] = [];
var checkboxes:JQuery<ChildNode>[] = [];
var box_states: number[] = [];
var lastTrack = 0; // indx in box_states
// global charting vars
var canvasEl = document.getElementById('grph') as HTMLCanvasElement;
var coords: Coords;  // x,y location of mouse in chart
var indxOfPt: number;
var prevCHairs = false;
// vars for setting chart dimension;
var fullWidth: number;
var chartHeight: number;
// misc.
var cluspage = $('#cpg').text() === 'yes' ? true : false;
var resizeFlag = true;
var trackNumber: number   // global used to identify current active track (topmost in tracklist)
var chartConst: number;
var pnlMarg: number;

if (!mobile) {
    chartConst = 0.77;
    // Hide/unhide side panel (changes width of elevation profile chart)
    var orgWidth: number;
    $('#hide').on('click', function() {
        orgWidth = <number>canvasEl.width;
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
        $('#chartline').width(orgWidth);
        canvasEl.width = orgWidth;
        $('#chartline').height(chartHeight);
        canvasEl.height = chartHeight;
        // redraw the chart
        drawChart(trackNumber);
        $('iframe').width(orgWidth);
        $('#unhide').css('display','none');
    });
} else {
    chartConst = 1.0000;
}

/**
 * Once a track is identified for display, show that gpx file's data in the
 * side panel.
 * 
 * @return {null}
 */
const displayTrackSidePanel = (trkname: string) => {
    let data = panelData[trkname];
    $('#hdiff').text(data["diff"]);
    $('#hlgth').text(data["miles"]);
    $('#hmmx').text(data["feet"]);
    $('#hlog').text(data["logistics"]);
    $('#hexp').text(data["expo"]);
    $('#hseas').text(data["seasons"]);
    $('#hwow').text(data["wow"]);
}
/**
 * This function turns on the topmost checked tracklist box. If all boxes
 * are unchecked, the last box checked remains displayed in elevation chart.
 * 
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
var mapdiv = <HTMLIFrameElement>document.getElementById('mapline');
mapdiv.onload = function() {
    setTimeout(function() {
        let tracklist_class = 'gv_tracklist_item';
        // get HTMLCollection of tracks in tracklist]
        let gpsvIframeDoc = <Window>mapdiv.contentWindow;
        let gvTracks = gpsvIframeDoc.document.getElementsByClassName(tracklist_class);
        for (let j=0; j<gvTracks.length; j++) {
            let classEl = gvTracks[j];
            let classChild = <ChildNode>classEl.firstChild; // this is a table
            let $tbl = $(classChild);
            let $tblrow = $tbl.find('tr'); // should only be one row;
            let $items = $tblrow.find('td');
            // $items[0] is the checkbox; $items[1] contains the track name
            let item1 = <Element>$items[1];
            let trackItem = <HTMLElement>item1.firstChild;
            let trackName = trackItem.innerHTML;
            trackNames.push(trackName);
            let checkbox = <ChildNode>$items[0].firstChild;
            let $checkbox = $(checkbox);
            checkboxes.push($checkbox);
            // initialize box_states array (tracks 'checkbox checked' T/F)
            if (cluspage) {
                for (let k=0; k<checkboxes.length; k++) {
                    box_states[k] = 1;
                }
            } else {
                if (j>0) {
                    box_states[j] = 0;
                    // on page load, all boxes are checked: leave top box on only
                    $checkbox.trigger('click');
                } else {
                    box_states[0] = 1;
                }
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

if (!mobile) {
    var lmarg = $('#sidePanel').css('margin-left');
    let str_marg = lmarg.substr(0, lmarg.length - 2); // remove 'px' at end
    pnlMarg = parseInt(str_marg);
} else {
    pnlMarg = 0;
}
setChartDims();

/**
 * This function establishes the chart dimensions on the page
 */
function setChartDims() {
    // calculate space available for canvas: (panel width = 23%)
    fullWidth = <number>$('body').innerWidth();
    if (!mobile) { // don't mess with chart height for mobile!
        var chartWidth = Math.floor(chartConst * fullWidth) - pnlMarg;
        var vpHeight = window.innerHeight;
        var sidePnlPos = <JQuery.Coordinates>$('#sidePanel').offset();
        var sidePnlLoc = sidePnlPos.top;
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
    } else {
        chartWidth = fullWidth;
    }
    canvasEl.width = chartWidth;
    $('iframe').width(chartWidth);
    return;
}

/**
 * This function will draw the selected elevation profile in the canvas element
 */
function drawChart(trackNo: number) {
    var chartData: ChartData = defineData(trackNo);
    ChartObj.render('grph', chartData);
    crossHairs(trackNo);
    if (typeof panelData === 'object') {
        displayTrackSidePanel(trackNames[trackNo]);
        if (mobile) {
            chartPlaced.resolve();
        }
    }
    return;
}
/**
 * The data being sent to the ChartObj is supplied here
 */
function defineData(track: number): ChartData {
    // data object for the chart:
    var dataDef: ChartData = {
        title: gpsvTracks[track],
        minY: trkMins[track],
        maxY: trkMaxs[track],
        xLabel: 'Distance (miles)', 
        yLabel: 'Elevation (feet)',
        labelFont: '10pt Arial', 
        dataPointFont: '8pt Arial',
        renderTypes: {lines: ChartObj.renderType.lines, points: ChartObj.renderType.points},
        dataPoints: trkRows[track]
    };
    return dataDef;
}
/**
 * This function sets up or hides the crosshairs when the mouse is in bounds
 * of the elevation profile chart
 */
function crossHairs(trackno: number) {
    // onmouseout needs to have initialized ImageData() interface object:
    var imageData = new ImageData(10, 10);
    canvasEl.onmousemove = function (e) {
        var loc = window2canvas(canvasEl, e.clientX, e.clientY);
        coords = dataReadout(loc, trackno);
        if (!prevCHairs) {
            imageData = context.getImageData(0,0,canvasEl.width,canvasEl.height);
            prevCHairs = true;
        } else {
            context.putImageData(imageData, 0, 0);
        }
        drawLine(<number>coords.px, margin.top, <number>coords.px, margin.top + yMax, 'Tomato', 1);
        drawLine(margin.left, <number>coords.py, margin.left + xMax, <number>coords.py, null, null);
        if (coords.x !== -1) {
            var mapObj = <GPSCoords>{ 
                lat: trkLats[trackno][indxOfPt],
                lng: trkLngs[trackno][indxOfPt]
            };
            infoBox(<number>coords.px, <number>coords.py, coords.x.toFixed(2), coords.y.toFixed(), mapObj);
        }
    };
    canvasEl.onmouseout = function () {
        context.putImageData(imageData,0,0);
        prevCHairs = false;
        let mapFrame = <HTMLIFrameElement>document.getElementById('mapline');
        let mapFrameWin = <MapWindow>mapFrame.contentWindow;
        mapFrameWin.chartMrkr.setMap(null);
    }
    return;
}

/**
 * Translate the mouse coords into canvas coords
 */
function window2canvas(canvas: HTMLCanvasElement, x: number, y: number): Coords {
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
function dataReadout(mousePos: MousePosition, trackno: number): Coords {
    var xDat = 0;
    var yDat = 0;
    if (mousePos.x > margin.left) {
        var chartPos = mousePos.x - margin.left;
        //var chartY = mousePos.y - margin.top;
        var lastEl = trkRows[trackno].length - 1;
        var maxMile = trkRows[trackno][lastEl].x;
        var unitsPerPixel = maxMile/xMax;
        var xDat = chartPos * unitsPerPixel;
        if (xDat <= maxMile) {
            var bounds: Bounds = findNeighbors(xDat, trackno);
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
 */
function findNeighbors(xDataPt: number, trackno: number): Bounds {
    let upper = 0;
    let lower = 0;
    for (var k=0; k<trkRows[trackno].length; k++) {
        if (trkRows[trackno][k].x === xDataPt) {
            upper = k;
            lower = k;
            break;
        } else {
            if (xDataPt < trkRows[trackno][k].x) {
                upper = k;
                lower = k-1;
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
$(window).on('resize', function() {
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
    return; 
});
