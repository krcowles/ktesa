$( function() {  // wait until document is loaded...

var canvasEl = document.getElementById('grph');
var winWidth = $(window).width();
var bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
if (bodySurplus < 24) {
    bodySurplus = 24;
}
// calculate space available for canvas:
var chartWidth = $('body').innerWidth() - bodySurplus;
chartWidth *= 0.745;
var vpHeight = window.innerHeight;
var sidePnlPos = $('#sidePanel').offset();
var sidePnlLoc = parseInt(sidePnlPos.top);
var usable = vpHeight - sidePnlLoc;
var chartHeight = Math.floor(0.35 * usable);
canvasEl.height = chartHeight;
canvasEl.width = chartWidth;

var coords = {};  // data points by which to mark the track
var noOfXincs;
var prevCHairs = false;
var imageData;
var hikeDat = [{ x: 0, y: 5150 },
    { x: 0.16, y: 5153},
    { x: 0.32, y: 5160},
    { x: 0.48, y: 5190},
    { x: 0.64, y: 5192},
    { x: 0.80, y: 5200},
    { x: 0.96, y: 5202},
    { x: 1.12, y: 5210},
    { x: 1.28, y: 5200},
    { x: 1.44, y: 5190},
    { x: 1.60, y: 5175},
    { x: 1.76, y: 5165},
    { x: 1.92, y: 5155},
    { x: 2.08, y: 5150},
    { x: 2.24, y: 5155},
    { x: 2.40, y: 5161},
    { x: 2.56, y: 5169},
    { x: 2.72, y: 5174},
    { x: 2.88, y: 5181},
    { x: 3.04, y: 5186},
    { x: 3.20, y: 5190},
    { x: 3.36, y: 5182},
    { x: 3.52, y: 5179},
    { x: 3.68, y: 5171},
    { x: 3.84, y: 5163},
    { x: 4.00, y: 5152}];
// data object for the chart:
var dataDef = { title: "",
    minY: 5150,
    maxY: 5210,
    xLabel: 'Distance (miles)', 
    yLabel: 'Elevation (feet)',
    labelFont: '10pt Arial', 
    dataPointFont: '8pt Arial',
    renderTypes: [ChartObj.renderType.lines, ChartObj.renderType.points],
    dataPoints: hikeDat
};
// render the chart using predefined objects
ChartObj.render('grph', dataDef);

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
canvasEl.onmousemove = function (e) {
    var loc = window2canvas(canvasEl, e.clientX, e.clientY);
    coords = dataReadout(loc);
    /*
    if (coords.x !== -1) {
        var msg = "X val: " + coords.x + ", Y val: " + coords.y +
            ", pixels in for crosshair: " + coords.px;
    }
    $('#dloc').text(msg);
    */
    // in order to be able to 'erase' crosshairs as we move...
    if (!prevCHairs) {
        imageData = context.getImageData(0,0,canvasEl.width,canvasEl.height);
        prevCHairs = true;
    } else {
        context.putImageData(imageData, 0, 0);
    }
    drawLine(coords.px,margin.top,coords.px,margin.top+yMax,'Tomato',1);
    drawLine(margin.left,coords.py,margin.left+xMax,coords.py);
    infoBox(coords.px,coords.py,coords.x.toFixed(2),coords.y);
};
function dataReadout(mousePos) {
    var xDat = 0;
    var yDat = 0;
    if (mousePos.x > margin.left) {
        var chartPos = mousePos.x - margin.left;
        var chartY = mousePos.y - margin.top;
        var lastEl = hikeDat.length - 1;
        var maxMile = hikeDat[lastEl].x;
        var unitsPerPixel = maxMile/xMax;
        var xDat = chartPos * unitsPerPixel;
        var bounds = findNeighbors(xDat);
        if (bounds.u === bounds.l) {
            yDat = hikeDat[bounds.u].y;
            noOfXincs = bounds.u;
        } else {
            var higher = hikeDat[bounds.u].x;
            var lower = hikeDat[bounds.l].x;
            var extrap = (xDat - lower)/(higher - lower);
            if (extrap >= 0.5) {
                xDat = higher;
                yDat = hikeDat[bounds.u].y;
                noOfXincs = bounds.u;
            } else {
                xDat = lower;
                yDat = hikeDat[bounds.l].y;
                noOfXincs = bounds.l;
            }
        }
        return {
            x: xDat,
            y: yDat,
            px: margin.left + noOfXincs * xInc,
            py: margin.top + (rgMax - yDat) * ratio
        };
    } else {
        return { x: -1, y: -1 };
    }    
}
function findNeighbors(xDataPt) {
    for (var k=0; k<hikeDat.length; k++) {
        if (hikeDat[k].x === xDataPt) {
            upper = k;
            lower = k;
            break;
        } else {
            if (xDataPt < hikeDat[k].x) {
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

$(window).resize( function() {
    $('#dbox').append("<p>RESIZE</p>");
});

}); // end of page-loading wait statement
