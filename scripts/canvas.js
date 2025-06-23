"use strict";
/// <reference path='./canvas.d.ts' />
/**
 * @file This script defines the Chart function (an IIFE), which sets the
 *       margin object (chart margins) and the renderType object (defining
 *       available rendering types), and it returns an object holding that
 *       renderType definition along with a rendering function (render).
 *       All specific to charting elevation on the html canvas element.
 *       It appears that bundle.js brings up this module first, hence
 *       the deferred object to time the drawing of picture rows.
 * @author Ken Cowles
 * @version 1.0 First release
 * @version 2.0 Typescripted, with some type errors corrected
 * @version 3.0 Added circle fct to tag elev points for calcs
 */
/**
 * Globals are used to pass data in the chart rendering functions without
 * requiring multiple arguments in calls.
 */
// set specific parameters for elevation chart
var chartWidth;
var chartHeight;
var margin = { top: 20, left: 64, right: 8, bottom: 50 };
var renderType = { lines: 'lines', points: 'points' }; // not using points at this time
// Established in ChartObj render function:
var data;
var xMax;
var yMax;
var context;
var deltaY;
var median;
var maxYValue = 0;
var maxXValue = 0;
var tipPt; // when to shift infobox from left side to right side of crosshairs
var vertTip; // when to shift infobox from over to under crosshairs
var vertConst = 0.62; // % of displayed altitude for above
// renderLinesAndLabels(), renderData():
var ratio;
var rgMax;
var chart_ranges = [50, 100, 200, 600, 1000, 2000, 3000, 4000, 6000];
// getXInc()
var pxPerMile;
// Values set for InfoBox()
var xwidth = 72;
var ywidth = 40;
var xshift = 12;
var yshift = 16;
/**
 * Immediately Executed Function defining margin and render type, and returning
 * an object with renderType specified along with the main 'render' function.
 */
var ChartObj = function () {
    return {
        renderType: renderType,
        render: function (canvasId, dataObj) {
            data = dataObj; // establish the global
            deltaY = data.maxY - data.minY;
            median = data.minY + deltaY / 2;
            vertTip = data.minY + vertConst * deltaY;
            maxYValue = data.maxY;
            var lastEl = data.dataPoints.length - 1;
            maxXValue = data.dataPoints[lastEl].x;
            tipPt = Math.round(maxXValue / 2);
            var canvas = document.getElementById(canvasId);
            chartHeight = canvas.height;
            chartWidth = canvas.width;
            xMax = chartWidth - (margin.left + margin.right);
            yMax = chartHeight - (margin.top + margin.bottom);
            context = canvas.getContext("2d");
            context.clearRect(0, 0, canvas.width, canvas.height);
            var chartTitle = "Track: " + data.title;
            renderChart(chartTitle);
        }
    };
}();
/**
 * This is the main driver for calling the various rendering functions
 */
var renderChart = function (title) {
    renderBackground();
    renderText(title);
    renderLinesAndLabels();
    /*
    //render data based upon type of renderType(s) that client supplies
    if (data.renderTypes == undefined || data.renderTypes == null) {
        data.renderTypes[0] = [renderType.lines];
    */
    renderData(renderType.lines); //data.renderTypes[0]);
};
var getMaxDataYValue = function () {
    for (var i = 0; i < data.dataPoints.length; i++) {
        if (data.dataPoints[i].y > maxYValue)
            maxYValue = data.dataPoints[i].y;
    }
};
/**
 * This function determines how much horizontal distance should be placed
 * between X-axis tick marks.
 */
var getXInc = function () {
    var lastWP = data.dataPoints.length - 1;
    var pxPerInc;
    var lastX = data.dataPoints[lastWP].x;
    pxPerMile = xMax / lastX;
    if (lastX <= 2) {
        var incr = 0.1;
    }
    else if (lastX <= 5) {
        var incr = 0.5;
    }
    else {
        var incr = 1.0;
    }
    pxPerInc = incr * pxPerMile;
    var noOfRegIncs = 0;
    for (var j = 0; j < lastWP; j++) {
        if (j * incr > lastX) {
            var noOfRegIncs = j + 1;
            break;
        }
    }
    return {
        XaxisPx: pxPerInc, // value in pixels for each incremental x-axis "tick"
        XaxisVal: incr, // value in miles for each incremental x-axis "tick"
        MaxXIncs: noOfRegIncs, // max no of ticks on x-axis for given track
        LastXVal: lastX // the last x position value (miles)
    };
};
/**
 * Fill the elevation chart's background with white
 */
var renderBackground = function () {
    context.fillStyle = "White";
    context.fillRect(margin.left, margin.top, xMax, yMax);
};
/**
 * This function statement sets the labels for the x and y axis, based on
 * the global object 'data' properties 'labelFont', 'xLabel', and 'yLabel'
 * It then situates the labels in the canvas context
 */
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
    var tx = margin.left + (xMax / 2) - txtSize.width / 2;
    var ty = chartHeight - 16;
    context.fillStyle = 'Blue';
    context.fillText(data.xLabel, tx, ty);
    //Y-axis text
    context.save();
    context.rotate(-Math.PI / 2);
    context.font = labelFont;
    // specify position of text placement:
    tx = -1.4 * (yMax / 2);
    ty = 20;
    context.fillStyle = 'Blue';
    context.fillText(data.yLabel, tx, ty);
    context.restore();
};
/**
 * This function creates the chart 'grid' lines, tick marks and
 * corresponding tick mart text.
 */
var renderLinesAndLabels = function renderLinesAndLabels() {
    // Vertical scale: horizontal guide lines (arbitrary assignment of noOfGrids)
    var noOfGrids = 4;
    // find chart range to use for this range of y values:
    var chartNo = 0;
    for (var k = 0; k < chart_ranges.length; k++) {
        if (deltaY <= 0.85 * chart_ranges[k]) {
            chartNo = k;
            break;
        }
    }
    ratio = yMax / chart_ranges[chartNo];
    var gridCtr = chart_ranges[chartNo] / 2; // feet for center of grid space
    var gridSpacing = chart_ranges[chartNo] / noOfGrids; // feet between grids
    var spaceCtr = gridSpacing / 2;
    var dfactor = (chart_ranges[chartNo] < 500) ? 10 : 100;
    var adder = (median % dfactor === 0) ? 0 : spaceCtr;
    var midpt = Math.floor(median / dfactor) * dfactor + adder;
    var rgOffset = midpt - gridCtr; // grids assumed to otherwise start at 0
    rgMax = rgOffset + chart_ranges[chartNo];
    var yInc = yMax / noOfGrids; // no of pixels per grid
    var yPos = 0; // in pixels also
    var yVal; // data value, NOT in pixels
    var str_yVal; // string version of above
    var tx;
    context.font = (data.dataPointFont != null) ? data.dataPointFont : '10pt Calibri';
    context.fillStyle = 'Blue';
    // Y AXIS
    var lgx = margin.left;
    var txtSize;
    for (var i = 0; i < noOfGrids; i++) {
        yPos += (i === 0) ? margin.top : yInc;
        // gradient applied to y grid points
        var grad = context.createLinearGradient(lgx, yPos, lgx, yPos + yInc);
        grad.addColorStop(0, "#dfecdf");
        grad.addColorStop(1, "White");
        context.fillStyle = grad;
        context.fillRect(lgx, yPos, xMax, yInc);
        // y axis labels  
        yVal = rgMax - (i * gridSpacing);
        str_yVal = Thousands(yVal);
        txtSize = context.measureText(str_yVal);
        // position of y axis labels:
        tx = margin.left - ((txtSize.width >= 14) ? txtSize.width : 10) + 5;
        context.fillStyle = 'Blue';
        context.fillText(str_yVal, tx, yPos + 4);
    }
    // Want label at y=0 postion too:
    yPos += yInc;
    yVal = rgOffset;
    str_yVal = Thousands(yVal);
    txtSize = context.measureText(str_yVal);
    tx = margin.left - ((txtSize.width >= 14) ? txtSize.width : 10) + 5;
    context.fillText(str_yVal, tx, yPos + 4);
    /**
     *  X AXIS:
     *  When there are so many X-axis datapoints, there is a need to define a
     *  reasonable grid spacing and x-value readout. Those calculations are
     *  performed in 'getXInc()'
     */
    var xAxisData = getXInc();
    var xPos = margin.left; // "0" origin for x axis, in pixels
    context.fillStyle = 'Blue';
    // incremental "tick" miles on X-axis (.1, .5 or 1.0)
    var xInc = xAxisData.XaxisVal;
    // place x-axis labels just below x-axis horizontal line, ie.
    // from the chart top: top y margin + max y val allowed + 16px further down
    var ty = margin.top + yMax + 16;
    var txt = 0; // the x-axis tick label
    var remaining = 0; // distance remaining to plot after the last x-axis tick mark
    var hang; // leftover track after last regular incremental tick
    var lastTickTxtSize; // px of last regular tick mark label
    var lastPxTaken; // last regular tick pos + 1/2 label width
    var lastSize;
    var str_txt = '';
    context.textAlign = "center";
    // print out regularly spaced x-axis ticks
    for (var j = 0; j < xAxisData.MaxXIncs; j++) { // j=0 prints out origin
        txt = j * xInc; // next tick mile
        remaining = xAxisData.LastXVal - txt; // subtract before string conversion!
        str_txt = txt.toFixed(1); // yields string value (needed outside loop)
        context.fillText(str_txt, xPos, ty); // ty is constant here
        if (remaining >= 0 && remaining < xInc) {
            // time to quit!
            break;
        }
        xPos += xAxisData.XaxisPx;
    }
    // if there are miles "left over" after the last tick, and "hang" >= 25% of incr
    hang = remaining / xInc;
    if (hang >= 0.25) {
        // print an "end" label (not at the regular interval of xInc) if room exists
        // check the space left at 70% of remaining
        var nomLoc = 0.7 * remaining;
        nomLoc = parseFloat(nomLoc.toFixed(2)); // rd to 100th's
        var endLabel = parseFloat(str_txt) + nomLoc;
        var str_endLabel = xInc > 0.11 ? endLabel.toFixed(1) : endLabel.toFixed(2); /*
         * see if there is enough room after last regular tick text:
         *   the last tick is at xPos;
         *   half the text width extends beyond this limit, as text is centered;
         *   allow additional 6px of space between labels
         */
        lastTickTxtSize = context.measureText(str_txt).width;
        lastPxTaken = xPos + lastTickTxtSize / 2 + 6;
        lastSize = context.measureText(str_endLabel).width;
        // position of endLabel:
        var endLoc = parseFloat(str_endLabel) - parseFloat(str_txt);
        var str_endLoc = xPos + (endLoc / xInc) * xAxisData.XaxisPx - lastSize / 2;
        if (endLoc - lastPxTaken > 2) {
            endLoc += lastSize / 2;
            context.fillText(str_endLabel, str_endLoc, ty);
        }
    }
    //Vertical line
    drawLine(margin.left, margin.top, margin.left, margin.top + yMax, 'black', 2);
    //Horizontal Line
    drawLine(margin.left, margin.top + yMax, margin.left + xMax, margin.top + yMax, 'black', 2);
};
var drawLine = function drawLine(startX, startY, endX, endY, strokeStyle, lineWidth) {
    if (strokeStyle != null)
        context.strokeStyle = strokeStyle;
    if (lineWidth != null)
        context.lineWidth = lineWidth;
    context.beginPath();
    context.moveTo(startX, startY);
    context.lineTo(endX, endY);
    context.stroke();
    context.closePath();
};
var drawDot = function drawDot(ctxt, x, y) {
    ctxt.strokeStyle = 'DarkSlateGray';
    ctxt.beginPath();
    ctxt.arc(x, y, 3, 0, 2 * Math.PI);
    ctxt.stroke();
};
/**
 * This function will create the 'info box' as the user mouses over the chart. The
 * box is drawn on the canvas and displays the x & y coordinate values in miles/feet.
 * It is invoked in dynamicChart.js
 */
var infoBox = function infoBox(xloc, yloc, xval, yval, mapLink) {
    // NOTE: iframeWindow is a global established on the hikePageTemplate
    var mapFrame = document.getElementById('mapline');
    var mapFrameWin = mapFrame.contentWindow;
    if (mapFrameWin.mrkrSet) {
        mapFrameWin.chartMrkr.setMap(null);
    }
    mapFrameWin.drawMarker(mapLink);
    var miles = xval + ' miles';
    var yvalno = parseFloat(yval);
    var hflip = yvalno > vertTip ? true : false;
    var str_yval = Thousands(yvalno);
    var feet = str_yval + ' ft';
    var xvalno = parseFloat(xval);
    if (xvalno > tipPt) {
        xloc -= (xwidth + xshift);
    }
    else {
        xloc += xshift;
    }
    if (hflip) {
        yloc += yshift;
    }
    else {
        yloc -= (ywidth + yshift);
    }
    context.fillStyle = 'HoneyDew';
    context.fillRect(xloc, yloc, xwidth, ywidth);
    context.strokeStyle = 'DarkGray';
    context.lineWidth = 3;
    context.strokeRect(xloc, yloc, xwidth, ywidth);
    var txtx = xloc + 6;
    var txty = yloc + 16;
    context.font = data.dataPointFont;
    context.textAlign = "left";
    context.fillStyle = 'DarkGreen';
    context.fillText(feet, txtx, txty);
    txty += 18;
    context.fillStyle = 'Blue';
    context.fillText(miles, txtx, txty);
};
/**
 * This is the function that places lines between coordinates on the canvas.
 */
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
        if (i > 0 && type == renderType.lines) {
            var line_color = data.dataPoints[i].g === 0 ? 'DarkGreen' : 'Red';
            //Draw connecting lines
            drawLine(ptX, ptY, prevX, prevY, line_color, 2);
        }
        /**
         * LEAVE THIS CODE IN CASE POINTS ARE ADDED LATER...
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
};
/**
 * This simple function takes an integer, and if > 999 inserts commas as needed
 */
function Thousands(value) {
    var x = value;
    var newval = x.toFixed();
    var str_yRem;
    if (value > 999) { // add coma
        var yTh = Math.floor(value / 1000); // truncated to thousands
        var yRem = value - 1000 * yTh;
        str_yRem = yRem.toFixed();
        if (yRem === 0) {
            str_yRem = '000';
        }
        else if (yRem > 0 && yRem < 10) {
            str_yRem = '00' + yRem;
        }
        else if (yRem > 9 && yRem < 100) {
            str_yRem = '0' + yRem;
        }
        newval = yTh + ',' + str_yRem;
    }
    return newval;
}
