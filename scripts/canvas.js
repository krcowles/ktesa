// define some globals for the functions below:
var margin = {};
var renderType = {};
var xMax;
var yMax;
var maxYValue = 0;
var maxXValue = 0;
var ratio = 0;
var chartWidth;
var chartHeight;
var data = {};
var context;
var chart_ranges = [50, 100, 200, 600, 1000, 2000, 3000, 4000, 6000];
var deltaY;
var median;
var rgMax;
var pxPerMile;
// INFO BOX VARS (CONSTANTS)
var xwidth = 72;
var ywidth = 40;
var tipPt;   // when to shift infobox from left side to right side of crosshairs
var vertTip; // when to shift infobox from over to under crosshairs
var xshift = 12; 
var yshift = 16;
// define the main Chart Object Container
var ChartObj = function() {
    margin = { top: 20, left: 64, right: 8, bottom: 50 };
    renderType = { lines: 'lines', points: 'points' };
    
    return { renderType: renderType,  // 1st object member is an object
        render: function(canvasId, dataObj) { // 2nd obj member is render fct
            data = dataObj;
            deltaY = data.maxY - data.minY;
            median = data.minY + deltaY/2;
            vertTip = data.minY + .78 * deltaY;
            maxYValue = data.maxY;
            var lastEl = data.dataPoints.length - 1;
            maxXValue = data.dataPoints[lastEl].x;
            tipPt = maxXValue/2;
            var canvas = document.getElementById(canvasId);
            chartHeight = canvas.height;
            chartWidth = canvas.width;
            xMax = chartWidth - (margin.left + margin.right);
            yMax = chartHeight - (margin.top + margin.bottom);
            context = canvas.getContext("2d");
            context.clearRect(0, 0, canvas.width, canvas.height);
            let chartTitle = "Track: " + data.title;
            renderChart(chartTitle);
        }
    };
} ();
var renderChart = function (title) {
    renderBackground();
    renderText(title);
    renderLinesAndLabels();

    //render data based upon type of renderType(s) that client supplies
    if (data.renderTypes == undefined || data.renderTypes == null) {
        data.renderTypes[0] = [renderType.lines];
    }
    renderData(data.renderTypes[0]);
};
var getMaxDataYValue = function () {
        for (var i = 0; i < data.dataPoints.length; i++) {
            if (data.dataPoints[i].y > maxYValue) maxYValue = data.dataPoints[i].y;
        }
        
    };
var getXInc = function() {
        var lastWP = data.dataPoints.length - 1;
        var pxPerInc;
        var lastX = parseFloat(data.dataPoints[lastWP].x);
        //var lastX = parseFloat(lastX.toFixed(2));
        pxPerMile = xMax/lastX;
        if (lastX <= 2) {
            var incr = 0.1;
        } else if (lastX <= 5) {
            var incr = 0.5;
        } else {
            var incr = 1.0;
        }
        pxPerInc = incr * pxPerMile;
        //lastX = parseFloat(lastX.toFixed(1));
        for (var j=0; j<lastWP; j++) {
            if (j * incr > lastX) {
                var noOfRegIncs = j + 1;
                break;
            }
        }
        return {
            XaxisPx:  pxPerInc,  // value in pixels for each incremental x-axis "tick"
            XaxisVal: incr,  // value in miles for each incremental x-axis "tick"
            MaxXIncs: noOfRegIncs, // max no of ticks on x-axis for given track
            LastXVal: lastX  // the last x position value (miles)
        };
    };
var renderBackground = function () {
    context.fillStyle = "White";
    context.fillRect(margin.left, margin.top, xMax, yMax);
    
};
var renderText = function renderText(title) {
    var labelFont = (data.labelFont != null) ? data.labelFont : '10pt Arial';
    context.font = labelFont;
    context.textAlign = "left";
    var titleTop = chartHeight - 6;
    context.fillStyle = "Brown";
    context.fillText(title, margin.left, titleTop);

    context.textAlign = "center";
    //X-axis text
    var txtSize = context.measureText(data.xLabel);
    // specify position of text placement wrt/canvas:
    var tx = margin.left + (xMax/2) - txtSize.width/2;
    var ty = chartHeight - 16;
    context.fillStyle = 'Blue';
    context.fillText(data.xLabel, tx, ty);
    //Y-axis text
    context.save();
    context.rotate(-Math.PI/2);
    context.font = labelFont;
    // specify position of text placement:
    tx = -1.4 * (yMax/2);
    ty = 20;
    context.fillStyle = 'Blue';
    context.fillText(data.yLabel, tx, ty);
    context.restore();
};
var renderLinesAndLabels = function renderLinesAndLabels() {
    //Vertical scale, horizontal guide lines - arbitrary assignment of qty:
    var noOfGrids = 4;
    // find chart range to use for this range of y values:
    for (var k=0; k<chart_ranges.length; k++) {
        if (deltaY <= 0.85*chart_ranges[k]) {
            var chartNo = k;
            break;
        }
    }
    ratio = yMax / chart_ranges[chartNo];
    var gridCtr = chart_ranges[chartNo] / 2; // feet in center of grid space
    var gridSpacing = chart_ranges[chartNo]/noOfGrids; // feet between grids
    var spaceCtr = gridSpacing/2;
    var dfactor = (chart_ranges[chartNo] < 500) ? 10 : 100;
    var adder = (median % dfactor === 0) ? 0 : spaceCtr;
    var midpt = Math.floor(median/dfactor) * dfactor + adder;
    var rgOffset = midpt - gridCtr;  // grids assumed to otherwise start at 0
    rgMax = rgOffset + chart_ranges[chartNo];
    
    var yInc = yMax / noOfGrids; // no of pixels per grid
    var yPos = 0;  // in pixels also
    var yVal; // data value, NOT in pixels
    var tx;
    context.font = (data.dataPointFont != null) ? data.dataPointFont : '10pt Calibri';
    context.fillStyle = 'Blue';
    // Y AXIS:
    var lgx = margin.left;
    var lgxmax = margin.left + xMax - margin.right;
    for (var i=0; i<noOfGrids; i++) {
        yPos += (i === 0) ? margin.top : yInc;
        // gradient applied to y grid points
        grad = context.createLinearGradient(lgx,yPos,lgx,yPos+yInc);
        grad.addColorStop(0,"#dfecdf");
        grad.addColorStop(1,"White");
        context.fillStyle = grad;
        context.fillRect(lgx,yPos,xMax,yInc);
        //drawLine(margin.left, yPos, margin.left + xMax, yPos, '#E8E8E8');
        //y axis labels  
        yVal = rgMax - (i * gridSpacing);
        yVal = Thousands(yVal);
        var txtSize = context.measureText(yVal);
        // position of y axis labels:
        tx = margin.left - ((txtSize.width >= 14)?txtSize.width:10)+ 5;
        context.fillStyle = 'Blue';
        context.fillText(yVal,tx,yPos+4);
    }
    // Want label at y=0 postion too:
    yPos += yInc;
    yVal = rgOffset;
    yVal = Thousands(yVal);
    var txtSize = context.measureText(yVal);
    tx = margin.left - ((txtSize.width >= 14)?txtSize.width:10)+ 5;
    context.fillText(yVal,tx,yPos+4);
    
    // X AXIS:
    /* When there are so many X-axis datapoints, there is a need to define a
     * reasonable grid spacing and x-value readout. Those calculations are 
     * performed in 'getXInc()'
     */
    var xAxisData = getXInc();
    var xPos = margin.left; // "0" origin for x axis, in pixels
    context.fillStyle = 'Blue';
    var xInc = xAxisData.XaxisVal;  // incremental "tick" miles on X-axis (.1, .5 or 1.0)
    // place x-axis labels just below x-axis horizontal line, ie.
    // from the chart top: top y margin + max y val allowed + 16px further down
    var ty = margin.top + yMax + 16;
    var txt;  // the x-axis tick label
    var remaining;  // distance remaining to plot after the last x-axis tick mark
    var hang;  // leftover track after last regular incremental tick
    var lastTickTxtSize; // px of last regular tick mark label
    var lastPxTaken; // last regular tick pos + 1/2 label width
    var lastSize;
    context.textAlign = "center";
    // print out regularly spaced x-axis ticks
    for (var j=0; j<xAxisData.MaxXIncs; j++) { // j=0 prints out origin
        txt = j * xInc;  // next tick mile
        remaining = xAxisData.LastXVal - txt; // subtract before string conversion!
        txt = txt.toFixed(1); // yields string value
        context.fillText(txt, xPos, ty); // ty is constant here
        if (remaining >= 0 && remaining < xInc) {
            // time to quit!
            break;
        }
        xPos += xAxisData.XaxisPx;
    }
    // if there are miles "left over" after the last tick, and "hang" >= 25% of incr
    hang = remaining/xInc;
    if (hang >= 0.25) {
        // print an "end" label (not at the regular interval of xInc) if room exists
        // check the space left at 70% of remaining
        var nomLoc = 0.7 * remaining;
        nomLoc = parseFloat(nomLoc.toFixed(2));  // rd to 100th's
        var endLabel = parseFloat(txt) + nomLoc;
        endLabel = xInc > 0.11 ? endLabel.toFixed(1) : endLabel.toFixed(2);
        /* 
         * see if there is enough room after last regular tick text:
         * - the last tick is at xPos
         * - half the text width extends beyond this limit, as text is centered;
         * - allow additional 6px of space between labels
         */
        lastTickTxtSize = context.measureText(txt).width;
        lastPxTaken = xPos + lastTickTxtSize/2 + 6;
        lastSize = context.measureText(endLabel).width;
        // position of endLabel:
        var endLoc = parseFloat(endLabel) - parseFloat(txt);
        endLoc = xPos + (endLoc/xInc)*xAxisData.XaxisPx - lastSize/2;
        if (endLoc - lastPxTaken > 2) {
            endLoc += lastSize/2;
            context.fillText(endLabel, endLoc, ty);
        }
    }
    //Vertical line
    drawLine(margin.left, margin.top, margin.left, margin.top + yMax, 'black',2);
    //Horizontal Line
    drawLine(margin.left, margin.top + yMax, margin.left + xMax, margin.top + yMax, 'black',2);
};
var drawLine = function drawLine(startX, startY, endX, endY, strokeStyle, lineWidth) {
    if (strokeStyle != null) context.strokeStyle = strokeStyle;
    if (lineWidth != null) context.lineWidth = lineWidth;
    context.beginPath();
    context.moveTo(startX, startY);
    context.lineTo(endX, endY);
    context.stroke();
    context.closePath();    
};
var infoBox = function infoBox(xloc,yloc,xval,yval,mapLink) {
    if (iframeWindow.mrkrSet) {
        document.getElementById('mapline').contentWindow.chartMrkr.setMap(null);
    }
    document.getElementById('mapline').contentWindow.drawMarker(mapLink);
    var miles = xval + ' miles';
    var hflip = yval > vertTip ? true : false;
    yval = Thousands(yval);
    var feet = yval + ' ft';
    if (xval > tipPt) {
        xloc -= (xwidth + xshift);
    } else {
        xloc += xshift;
    }
    if (hflip) {
        yloc += yshift;
    } else {
        yloc -= (ywidth + yshift);
    }
    context.fillStyle = 'HoneyDew';
    context.fillRect(xloc,yloc,xwidth,ywidth);
    context.strokeStyle = 'DarkGray';
    context.lineWidth = 3;
    context.strokeRect(xloc,yloc,xwidth,ywidth);
    var txtx = xloc + 6;
    var txty = yloc + 16;
    context.font = data.dataPointFont;
    context.textAlign = "left";
    context.fillStyle = 'DarkGreen';
    context.fillText(feet,txtx,txty);
    txty += 18;
    context.fillStyle = 'Blue';
    context.fillText(miles,txtx,txty);
}
var renderData = function renderData(type) {
    var prevX = 0;
    var prevY = 0;
    var ptY;

    for (var i = 0; i < data.dataPoints.length; i++) {
        var pt = data.dataPoints[i];
        ptY = margin.top + (rgMax - pt.y) * ratio;
        // don't let bad points over-extend:
        if (ptY < margin.top) {
            ptY = margin.top;
        }
        var ptX = margin.left + pxPerMile * data.dataPoints[i].x;
        //var ptX = (i * xInc) + margin.left;

        if (i > 0 && type == renderType.lines) {
            //Draw connecting lines
            drawLine(ptX, ptY, prevX, prevY, 'DarkGreen', 2);
        }
        /*
        if (type == renderType.points) {
            var radgrad = context.createRadialGradient(ptX, ptY, 8, ptX - 5, ptY - 5, 0);
            radgrad.addColorStop(0, 'Green');
            radgrad.addColorStop(0.9, 'White');
            context.beginPath();
            context.fillStyle = radgrad;
            //Render circle
            context.arc(ptX, ptY, 8, 0, 2 * Math.PI, false)
            context.fill();
            context.lineWidth = 1;
            context.strokeStyle = '#000';
            context.stroke();
            context.closePath();
        }
        */
        prevX = ptX;
        prevY = ptY;
    }
}
function Thousands(value) {
    var newval = value;
    if (newval > 999) { // add coma
        var yTh = Math.floor(newval/1000); // truncated to thousands
        var yRem = newval - 1000 * yTh;
        if (yRem === 0) {
            yRem = '000';
        } else if (yRem > 0 && yRem < 10) {
            yRem = '00' + yRem;
        } else if (yRem > 9 && yRem < 100) {
            yRem = '0' + yRem;
        }
        newval = yTh + ',' + yRem;
    }
    return newval;
}
