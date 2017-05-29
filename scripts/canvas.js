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
var chart_ranges = [50,100,200,500,1000,2000,3000,4000];
var deltaY;
var median;
var rgMax;
var pxPerMile;
// INFO BOX VARS (CONSTANTS)
var xwidth = 72;
var ywidth = 40;
var tipPt;  // when to shift infobox from left side to right side of crosshairs
var xshift = 12; 
var yshift = 16;
// define the main Chart Object Container
var ChartObj = function() {
    margin = { top: 20, left: 64, right: 5, bottom: 50 };
    renderType = { lines: 'lines', points: 'points' };
    
    return { renderType: renderType,  // 1st object member is an object
        render: function(canvasId, dataObj) { // 2nd obj member is render fct
            data = dataObj;
            deltaY = data.maxY - data.minY;
            median = data.minY + deltaY/2;
            maxYValue = data.maxY;
            var lastEl = data.dataPoints.length - 1;
            maxXValue = data.dataPoints[lastEl].x;
            tipPt = Math.round(maxXValue/2);
            var canvas = document.getElementById(canvasId);
            chartHeight = canvas.height;
            chartWidth = canvas.width;
            xMax = chartWidth - (margin.left + margin.right);
            yMax = chartHeight - (margin.top + margin.bottom);
            context = canvas.getContext("2d");
            renderChart();
        }
    };
} ();
var renderChart = function () {
    renderBackground();
    renderText();
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
        var lastX = parseFloat(data.dataPoints[lastWP].x);
        var lastX = parseFloat(lastX.toFixed(2));
        pxPerMile = xMax/lastX;
        if (lastX <= 2) {
            var incr = 0.1;
            lastX = lastX.toFixed(1);
        } else {
            var incr = 0.25;
        }
        for (var j=0; j<50; j++) {
            if (j * incr > lastX) {
                var noOfRegIncs = j;
                break;
            }
        }
        var lastValDelta = lastX - (noOfRegIncs-1)*incr;
        var lastValpx = pxPerMile * lastValDelta;
        return {
            XaxisPx: xMax/noOfRegIncs,  // value in pixels
            XaxisVal: incr,  // value in miles
            RegXIncs: noOfRegIncs,
            LastXInc: lastValpx,
            LastXVal: lastX
        };
    };
var renderBackground = function () {
    context.fillStyle = "White";
    context.fillRect(margin.left, margin.top, xMax, yMax);
    
};
var renderText = function renderText() {
    var labelFont = (data.labelFont != null) ? data.labelFont : '10pt Arial';
    context.font = labelFont;
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
    tx = -1 * (yMax/2);
    ty = 20;
    context.fillStyle = 'DarkGreen';
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
    var gridCtr = chart_ranges[chartNo] / 2
    var gridSpacing = chart_ranges[chartNo]/noOfGrids;
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
        context.fillStyle = 'DarkGreen';
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
    var xPos = margin.left;
    context.fillStyle = 'Blue';
    var ty = margin.top + yMax + 16;
    var lastSize;
    var txt;
    var xInc = xAxisData.XaxisVal;
    if (xInc === 0.1) {
        var single = true;
    } else {
        var single = false;
    }
    context.textAlign = "center";
    for (var j=0; j<xAxisData.RegXIncs; j++) {
        //x axis labels
        txt = j * xInc;
        // for some reason, there is an occasional digit about 20 decimals out...
        if (single) {
            txt = txt.toFixed(1);
        } else {
            txt = txt.toFixed(2);
        }
        context.fillText(txt, xPos, ty);
        xPos += xAxisData.XaxisPx;
        lastSize = context.measureText(txt);
    }
    xPos -= xAxisData.XaxisPx;  // back up to previous label
    // one more label:
    var lastXSize = context.measureText(xAxisData.LastXVal);
    var minSpace = 4; // set minimum space between prevX and lastX labels
    if ( (lastSize.width/2 + minSpace + lastXSize.width/2) < xAxisData.LastXInc &&
            (xAxisData.LastXInc + lastSize.width/2) < xAxisData.XaxisPx ) {
        xPos += xAxisData.LastXInc;
        context.fillText(xAxisData.LastXVal,xPos,ty);
    } else {
        xPos += xAxisData.XaxisPx;
        txt = xAxisData.RegXIncs * xAxisData.XaxisVal;
        context.fillText(txt,xPos,ty);
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
    yval = Thousands(yval);
    var feet = yval + ' ft';
    if (xval > tipPt) {
        xloc -= (xwidth + xshift);
    } else {
        xloc += xshift;
    }
    yloc -= (ywidth + yshift);
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
        }
        newval = yTh + ',' + yRem;
    }
    return newval;
}
