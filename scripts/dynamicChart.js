$( function() {  // wait until document is loaded...

function setChartDims() {
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
    if (chartHeight < 100) {
        $('#chartline').height(100);
        canvasEl.height = 100;
    } else {
        $('#chartline').height(chartHeight);
        canvasEl.height = chartHeight;
    }
    canvasEl.width = chartWidth;
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
    }
}
// account for building new page - files not stored in main yet
var trackfile = $('#chartline').data('gpx');
if ( trackfile.indexOf('tmp/') === -1 ) {
    trackfile = '../gpx/' + trackfile;
}
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

/* This section of code reads in the GPX file (ajax) capturing the latitudes
 * and longitudes, calculating the distances between points via fct 'distance',
 * and storing the results in the array 'elevs'.
 */
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
$.ajax({
    dataType: "xml",  // xml document object can readily be handled by jQuery
    url: trackfile,
    success: function(trackDat) {
        var $trackpts = $("trkpt",trackDat);
        var hikelgth = 0;  // distance between pts, in miles
        var dataPtObj;
        $trackpts.each( function() {
            var tag = parseFloat($(this).attr('lat'));
            lats.push(tag);
            tag =parseFloat( $(this).attr('lon'));
            lngs.push(tag);
            var $ele = $(this).children().eq(0);
            tag = parseFloat($ele.text()) * 3.2808;
            elevs.push(tag);
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
var hikeDat = [{ x: 0, y: 5150 },
    { x: 0, y: 5150 },
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
// test code for eliminating duplication in array
var newArray = [];
var naIndx;
for (var n=0; n<hikeDat.length; n++) {
    if (n === 0) {
        newArray[0] = hikeDat[0];
        naIndx = 1;
    } else {
        if (hikeDat[n].x !== hikeDat[n-1].x) {
            newArray[naIndx] = hikeDat[n];
            naIndx++;
        }
    }
}
msg = "New array length is " + newArray.length;
//window.alert(msg);
// render the chart using predefined objects
var waitForDat = setInterval( function() {
    if (ajaxDone) {
        var chartData = defineData();
        ChartObj.render('grph', chartData);
        crossHairs();
        clearInterval(waitForDat);
    }
}, 200);

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
