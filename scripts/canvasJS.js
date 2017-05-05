$( function() {  // wait until document is loaded...

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
var chartLoc = {};
var chart;

// Function to convert lats/lons to miles:
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
            if (i >= 23) {
                    var x = 'what';
            }
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
        $('#dbug').append(msg);
    }
});

// chart-making:
chart = new CanvasJS.Chart("chartline", {  // options object:
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
			if (iframeWindow.mrkrSet) {
				document.getElementById('mapline').contentWindow.chartMrkr.setMap(null);
			}
			document.getElementById('mapline').contentWindow.drawMarker(chartLoc);
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

// remove map circle when leaving chart
$('#chartline').on('mouseout', function() {
	document.getElementById('mapline').contentWindow.chartMrkr.setMap(null);
});


}); // end of page-loading wait statement