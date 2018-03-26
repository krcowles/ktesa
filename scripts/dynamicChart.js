$( function() {  // wait until document is loaded...

var trackfile = $('#chartline').data('gpx');
var lats = [];
var lngs = [];
var elevs = [];  // elevations, in ft.
var rows = [];
var xval;
var yval;
var emax;  // maximum value found for elevation
var emin;  // minimum value found for evlevatiom
var msg;
var ajaxDone = false;
var resizeFlag = true;
var fullWidth; // page width on load
var chartHeight; // chart height on load
var chartWidth; // chart width on load
var lmarg = $('#sidePanel').css('margin-left');
var pnlMarg = lmarg.substr(0, lmarg.length-2); // remove 'px' at end
/* 
 * This next section of code reads in the GPX file (ajax) capturing the latitudes
 * and longitudes, calculating the distances between points via fct 'distance',
 * and storing the results in the array 'elevs'.
 */
$.ajax({
    dataType: "xml",  // xml document object can readily be handled by jQuery
    url: trackfile,
    success: function(trackDat) {
        var $trackpts = $("trkpt", trackDat);
        if ($trackpts.length === 0) {
            // try as rtepts instead of trkpts
            $trackpts = $("rtept", trackDat)
        }
        var hikelgth = 0;  // distance between pts, in miles
        var dataPtObj;
        $trackpts.each( function() {
            var tag = parseFloat($(this).attr('lat'));
            lats.push(tag);
            tag =parseFloat( $(this).attr('lon'));
            lngs.push(tag);
            //var $ele = $(this).children().eq(0);
            var $ele = $(this).find('ele').text();
            if ( $ele.length ) { 
                tag = parseFloat($ele) * 3.2808;
                elevs.push(tag);
            } else {   // some GPX files contain trkpts w/no ele tag
                // remove entries for trkpts that have no elevation:
                lats.pop();
                lngs.pop();
            }
        });
        // form the array of datapoint objects for the chart:
        rows[0] = { x: 0, y: elevs[0] };
    	emax = 0;
        emin = 20000;
        for (var i=0; i<lats.length-1; i++) {
            hikelgth += distance(lats[i],lngs[i],lats[i+1],lngs[i+1],"M");
            if (elevs[i+1] > emax) { emax = elevs[i+1]; }
            if (elevs[i+1] < emin) { emin = elevs[i+1]; }
            dataPtObj = { x: hikelgth, y: elevs[i+1] };
            rows.push(dataPtObj);
        }
        // set y axis range values:
        // NOTE: this algorithm works for elevs above 1,000ft (untested below that)
        var Cmin = Math.floor(emin/100);
        var Cmax = Math.ceil(emax/100);
        if ( (emin - 100 * Cmin) < 40 ) {
            emin = Cmin - 0.5;
        } else {
            emin = Cmin;
        }
        if ( (100 * Cmax - emax) < 40 ) {
            emax = Cmax + 0.5;
        } else {
            emax = Cmax;
        }
        emax *= 100;
        emin *= 100;
        ajaxDone = true;
    },
    error: function() {
        msg = '<p>Did not succeed in getting XML data: ' + trackfile + '</p>';
        alert(msg);
    }
});
/* This section of code renders the graph itself based on the data obtained above */
var canvasEl = document.getElementById('grph');
setChartDims();
var coords = {};  // data points by which to mark the track
var indxOfPt;
var prevCHairs = false;
var imageData;
// render the chart using predefined objects
var waitForDat = setInterval( function() {
    if (ajaxDone) {
        drawChart();
        clearInterval(waitForDat);
    }
}, 200);
// Hide side panel
$('#hide').on('click', function() {
    $('#sidePanel').css('display', 'none');
    $('#chartline').width(fullWidth);
    canvasEl.width = fullWidth;
    // redraw the chart
    drawChart();
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
    drawChart();
    $('iframe').width(chartWidth);
    $('#unhide').css('display','none');
});
/*
 * FUNCTION DECLARATIONS:
 */
function drawChart() {
    var chartData = defineData();
    ChartObj.render('grph', chartData);
    crossHairs();
}
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
}
function defineData() {
    // data object for the chart:
    var dataDef = { title: "",
        minY: emin,
        maxY: emax,
        xLabel: 'Distance (miles)', 
        yLabel: 'Elev. (ft)',
        labelFont: '10pt Arial', 
        dataPointFont: '8pt Arial',
        renderTypes: [ChartObj.renderType.lines, ChartObj.renderType.points],
        dataPoints: rows
    };
    return dataDef;
}
function crossHairs() {
    canvasEl.onmousemove = function (e) {
        var loc = window2canvas(canvasEl, e.clientX, e.clientY);
        coords = dataReadout(loc);
        if (!prevCHairs) {
            imageData = context.getImageData(0,0,canvasEl.width,canvasEl.height);
            prevCHairs = true;
        } else {
            context.putImageData(imageData, 0, 0);
        }
        var mapObj = { lat: lats[indxOfPt], lng: lngs[indxOfPt] };
        drawLine(coords.px,margin.top,coords.px,margin.top+yMax,'Tomato',1);
        drawLine(margin.left,coords.py,margin.left+xMax,coords.py);
        infoBox(coords.px,coords.py,coords.x.toFixed(2),coords.y.toFixed(),mapObj);
    };
    canvasEl.onmouseout = function (e) {
        context.putImageData(imageData,0,0);
        prevCHairs = false;
        document.getElementById('mapline').contentWindow.chartMrkr.setMap(null);
    }
}
function distance(lat1, lon1, lat2, lon2, unit) {
    if (lat1 === lat2 && lon1 === lon2) { return 0; }
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1-lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist);
    dist = dist * 180/Math.PI;
    dist = dist * 60 * 1.1515;
    if (unit === "K") { dist = dist * 1.609344; }
    if (unit === "N") { dist = dist * 0.8684; }  // else result is in miles "M"
    return dist;
}
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
function dataReadout(mousePos) {
    var xDat = 0;
    var yDat = 0;
    if (mousePos.x > margin.left) {
        var chartPos = mousePos.x - margin.left;
        var chartY = mousePos.y - margin.top;
        var lastEl = rows.length - 1;
        var maxMile = rows[lastEl].x;
        var unitsPerPixel = maxMile/xMax;
        var xDat = chartPos * unitsPerPixel;
        var bounds = findNeighbors(xDat);
        if (bounds.u === bounds.l) {
            yDat = rows[bounds.u].y;
            indxOfPt = bounds.u;
        } else {
            var higher = rows[bounds.u].x;
            var lower = rows[bounds.l].x;
            var extrap = (xDat - lower)/(higher - lower);
            if (extrap >= 0.5) {
                xDat = higher;
                yDat = rows[bounds.u].y;
                indxOfPt = bounds.u;
            } else {
                xDat = lower;
                yDat = rows[bounds.l].y;
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
}
function findNeighbors(xDataPt) {
    for (var k=0; k<rows.length; k++) {
        if (rows[k].x === xDataPt) {
            upper = k;
            lower = k;
            break;
        } else {
            if (xDataPt < rows[k].x) {
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
// Redraw when there is a window resize
$(window).resize( function() {
    if (resizeFlag) {
        prevCHairs = false;
        resizeFlag = false;
        setTimeout( function() {
            canvasEl.onmousemove = null;
            setChartDims();
            var chartData = defineData();
            ChartObj.render('grph', chartData);
            crossHairs();
            resizeFlag = true; 
            //var msg = 'New width: ' + canvasEl.width + ', height: ' + canvasEl.height;
            //window.alert(msg);
        }, 300);      
    }  
});

}); // end of page-loading wait statement
