$( function() {  // wait until document is loaded...

var trackfile = 'Apache_Canyon.GPX';

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
var chartLoc = {};

// Function to convert lats/lons to miles:
function distance(lat1, lon1, lat2, lon2, unit) {
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
	
// asynchronous load & processing of the gpx file:
$.ajax({
    dataType: "xml",  // xml document object can be handled by jQuery
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
        // datapoint = { y: elevation }
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
        var delta = emax - emin;
        if (delta > 500) {
        	var adder = 1;
        } else {
        	var adder = 2;
        }
        emax = 100 * (Math.round(emax/100) + adder);
        emin = 100 * (Math.floor(emin/100) - adder);
        ajaxDone = true;
    },
    error: function() {
        msg = '<p>Did not succeed in getting XML data: ' + trackfile + '</p>';
        $('#dbug').append(msg);
    }
});

// chart-making:
var chart = new CanvasJS.Chart("chartContainer", {  // options object:
	toolTip: {
		borderThickness: 2,
		backgroundColor: 'White',
		contentFormatter: function(e) { 
			var content = "";
			var ypt = e.entries[0].dataPoint.y;
			var yout = '<span style="color:DarkBlue;">' + Math.round(ypt) + ' ft</span>';
			content += yout;
			var xpt = e.entries[0].dataPoint.x;
			var xout = '<br /><span style="color:DimGray;">' + Math.round(xpt * 100)/100 + ' miles</span>';
			content += xout;
			var indx = 0;
			for (var k=0; k<rows.length; k++) {
				if (rows[k].x === xpt) {
					indx = k;
					break;
				}
			}
			chartLoc = { lat: lats[indx], lng: lngs[indx] };
			if (iframeWindow.circSet) {
				document.getElementById('gpsvmap').contentWindow.chartMrkr.setMap(null);
			}
			document.getElementById('gpsvmap').contentWindow.drawCircle(chartLoc);
			return content;	
		}
	},
	data: [       // only 1 array element: elevation dataseries 
		{
			type: "line",	
			cursor: "crosshair",
			dataPoints: [ ]
		}
	],
	axisY: {
		title: "Elevation (ft)",
		suffix: " ft.",
		interlacedColor: "AliceBlue"
	},
	axisX: {
		title: "Distance (miles)"
	}
});

var drawit = setInterval( function() {
	if (ajaxDone) {
		chart.options.data[0].dataPoints = rows;
		chart.options.axisY.minimum = emin;
		chart.options.axisY.maximum = emax;
		chart.render();
		clearInterval(drawit);
	}
}, 50);

// remove map symbol when leaving chart
$('#chartContainer').on('mouseout', function() {
	document.getElementById('gpsvmap').contentWindow.chartMrkr.setMap(null);
});


}); // end of page-loading wait statement