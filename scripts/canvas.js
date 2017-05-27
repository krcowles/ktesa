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
var xInc;
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
        return Math.round(xMax/(data.dataPoints.length -1));
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
    var xInc = getXInc();
    var xPos = margin.left;
    context.fillStyle = 'Blue';
    ty = margin.top + yMax + 16;
    for (var j=0; j<data.dataPoints.length; j++) {
        //x axis labels
        txt = data.dataPoints[j].x;
        txtSize = context.measureText(txt);
        context.fillText(txt, xPos, ty);
        xPos += xInc;
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
var infoBox = function infoBox(xloc,yloc,xval,yval) {
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
    xInc = getXInc();
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
        var ptX = (i * xInc) + margin.left;

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
    if (value > 999) { // add coma
        var yTh = Math.round(value/1000);
        var yRem = value % 1000;
        newval = yTh + ',' + yRem;
    }
    return newval;
}
