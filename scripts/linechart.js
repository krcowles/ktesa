// eventually, the file name will be supplied by the calling routine...
var trackfile = 'Apache_Canyon.GPX';

var rows = []; // global holding dataset for chart

// Function to convert lats/lons to miles:
function distance(lat1, lon1, lat2, lon2, unit) {
	var radlat1 = Math.PI * lat1/180
	var radlat2 = Math.PI * lat2/180
	var theta = lon1-lon2
	var radtheta = Math.PI * theta/180
	var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
	dist = Math.acos(dist)
	dist = dist * 180/Math.PI
	dist = dist * 60 * 1.1515
	if (unit=="K") { dist = dist * 1.609344 }
	if (unit=="N") { dist = dist * 0.8684 }  // else result is in miles "M"
	return dist
}

// asynchronous load & processing of the gpx file:
$.ajax({
	dataType: "xml",  // xml document object can be handled by jQuery
	url: trackfile,
	success: function(trackDat) {
		var $trackpts = $("trkpt",trackDat);
		var lats = [];
		var lngs = [];
		var eles = [];
		var hikelgth = 0;
		var datapt = [];
		$trackpts.each( function() {
			var tag = parseFloat($(this).attr('lat'));
			lats.push(tag);
			tag =parseFloat( $(this).attr('lon'));
			lngs.push(tag);
			var $ele = $(this).children().eq(0);
			tag = parseFloat($ele.text()) * 3.2808;
			eles.push(tag);
		});
		// Convert lats/lons to distances in miles;
		datapt[0] = hikelgth;
		datapt[1] = eles[0];
		rows.push(datapt);
		datapt = [];
		for (var i=0; i<lats.length-1; i++) {
			hikelgth += distance(lats[i],lngs[i],lats[i+1],lngs[i+1],"M");
			datapt[0] = hikelgth;
			datapt[1] = eles[i+1];
			rows.push(datapt);
			datapt = [];
		}
	},
	error: function() {
		msg = '<p>Did not succeed in getting XML data: ' + trackfile + '</p>';
		$('#dbug').append(msg);
	}
});

// setting up & displaying the line chart
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawCrosshairs);

function drawCrosshairs() {
      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'Ft:');

	  for (var n=0; n<rows.length; n++) {
		  data.addRows([
				rows[n]
		  ]);
      }

      var options = {
        hAxis: {
          title: 'Distance (Miles)'
        },
        vAxis: {
          title: 'Elevation (Ft.)'
        },
        colors: ['#a52714'],
        backgroundColor: '#FFEBCD',
        crosshair: {
          color: '#AAAAAA',
          trigger: 'both'
        }
      };

	  // instantiate:
      var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
      chart.draw(data, options);
      chart.setSelection([{row: 500, column: 1}]);
      
      // when chart pts are clicked:
	  function dataRetriever() {
	  	var clickdat = chart.getSelection()[0];
	  	if (clickdat) {
		  var value = data.getValue(clickdat.row, clickdat.column);
		  //var miles = data.getDistinctValues(clickdat.column);
		  window.alert('The user selected ' + value);
		}

	  }
      google.visualization.events.addListener(chart, 'select', dataRetriever);
}