// generic debug output var:
var msg; 

var geoOptions = { enableHighAccuracy: true };
var watchOptions = { enableHighAccuracy: true };
var map;
var mapStartPos = {lat: 35.690183, lng: -106.013517};
var geoMarker;
var geoIcon = '../images/grnTarget.png';

// IIFE: Set to current location
(function() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(success, fail, geoOptions);
		function fail(PositionError) {
			switch (PositionError.code) {
				case 1:
					msg = 'GEOLOCATION: Permission denied';
					break;
				case 2:
					msg = 'GEOLOCATION: Unavailable';
					break;
				case 3:
					msg = 'GEOLOCATION: Request timed out';
					break;
			}
			window.alert(msg);
		}  // end of FAIL function
		function success(Position) {
			var cLat = Position.coords.latitude;
			var cLng = Position.coords.longitude;
			var myLoc = new google.maps.LatLng(cLat, cLng);
			var startPos = map.setCenter(myLoc);
			geoMarker = new google.maps.Marker({
				position: myLoc,
				map: map,
				icon: geoIcon,
				size: new google.maps.Size(24,24),
				origin: new google.maps.Point(0, 0),
				// The anchor for this image is the center (12, 12).
				anchor: new google.maps.Point(12, 12)
			});
			
		} // end of SUCCESS function
	} else {
		window.alert('Geolocation not supported on this browser');
	}
}());

// Callback to establish the map
function initMap() {
	var mapDiv = document.getElementById('tstMap');
	map = new google.maps.Map(mapDiv, {
		center: mapStartPos,
		zoom: 18,
		// optional settings:
		zoomControl: true,
		scaleControl: true,
		mapTypeControl: true,
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
			mapTypeIds: [
				// only two of these show, don't know why...
				google.maps.MapTypeId.ROADMAP,
				google.maps.MapTypeId.TERRAIN,
				google.maps.MapTypeId.HYBRID,
				google.maps.MapTypeId.SATELLITE
			]
		},
		fullscreenControl: true,
		streetViewControl: false,
		rotateControl: false,
		mapTypeId: google.maps.MapTypeId.HYBRID
	});
}

// All the "button" behaviors, colors, including interval updates & watchPosition method
var rateObj = { key1: 'val1' }; // somewhat elaborate method to avoid repeated usage
// of interval timer names, in case it causes confusion...
var keyVal = 1;
var rateObjKey;

var watchObj = { key1: 'val1' };
var wkeyVal = 1;
var watchObjKey;

var intType = false;
var watType = false;

$('#ion').on('click', function() {
	$('#istat').text('ON');
	$('#istat').css('color','Green');
	$('#istat').css('font-weight','bold');
	if ( watType ) {
		$('#wstat').text('OFF');
		$('#wstat').css('color','Red');
		$('#wstat').css('font-weight','normal');
		watType = false;
		navigator.geolocation.clearWatch(watchObj[watchObjKey]);
	}
	if ( !intType ) {
		intType = true;
		rateObjKey = 'key' + keyVal++;
		rateObj[rateObjKey] = setInterval(getLoc, 10000);;
	}
});
$('#ioff').on('click', function() {
	$('#istat').text('OFF');
	$('#istat').css('color','Red');
	$('#istat').css('font-weight','normal');
	if ( intType ) {
		intType = false;
		clearInterval(rateObj[rateObjKey]);
	}
});

$('#won').on('click', function() {
	$('#wstat').text('ON');
	$('#wstat').css('color','Green');
	$('#wstat').css('font-weight','bold');
	if ( intType ) {
		intType = false;
		$('#istat').text('OFF');
		$('#istat').css('color','Red');
		$('#istat').css('font-weight','normal');
		clearInterval(rateObj[rateObjKey]);
	}
	if ( !watType ) {
		watType = true;
		wtch();	
	}
});
$('#woff').on('click', function() {
	$('#wstat').text('OFF');
	$('#wstat').css('color','Red');
	$('#wstat').css('font-weight','normal');
	if ( watType ) {
		navigator.geolocation.clearWatch(watchObj[watchObjKey]);
	}
});

var locCount = 0;
function wtch() {
	watchObjKey = 'key' + wkeyVal++;
	watchObj[watchObjKey] = navigator.geolocation.watchPosition(watchSuccess, watchError, watchOptions);
	function watchSuccess(pos) {
		var watchPos = pos.coords;
		var wLat = watchPos.latitude;
		var wLng = watchPos.longitude;
		var newWPos = {lat: wLat, lng: wLng };
		geoMarker.setMap(null);
		geoMarker = null;
		geoMarker = new google.maps.Marker({
			position: newWPos,
			map: map,
			icon: geoIcon,
			size: new google.maps.Size(24,24),
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(12, 12)
		});
		trackDraw(wLat, wLng);
	} // end of watchSuccess function
	function watchError(eobj) {
		msg = '<p>Error in watch call: code ' + eobj.code + '</p>';
		window.alert(msg);
	}
}

function getLoc() {
	navigator.geolocation.getCurrentPosition(updateSuccess, updateFail, geoOptions);
	function updateFail(PositionError) {
		switch (PositionError.code) {
			case 1:
				msg = 'GEOLOCATION: Permission denied';
				break;
			case 2:
				msg = 'GEOLOCATION: Unavailable';
				break;
			case 3:
				msg = 'GEOLOCATION: Request timed out';
				break;
		}
		window.alert(msg);
	}  // end of FAIL function
	function updateSuccess(newPosition) {
		geoMarker.setMap(null);
		geoMarker = null;
		var newLat = newPosition.coords.latitude;
		var newLng = newPosition.coords.longitude;
		var newLoc = new google.maps.LatLng(newLat, newLng);
		geoMarker = new google.maps.Marker({
			position: newLoc,
			map: map,
			icon: geoIcon,
			size: new google.maps.Size(24,24),
			origin: new google.maps.Point(0, 0),
			// The anchor for this image is the center (12, 12).
			anchor: new google.maps.Point(12, 12)
		});
	} // end of SUCCESS function
	
}  // end of getLoc function

// Since javascript uses 64bit double precision:
var dx = .000222;	// experimental latitude diff for 100ft stride
var dy = .000106;	// experimental longitude diff for 100ft stride
var hyp = 2*(dx*dx + dy*dy); // used as square of hypotenuse for determining min track distance
var trkPts = [];

//var fake1 = trackDraw( 35.000000, -106.000000 );
//var fake2 = trackDraw( 35.000620, -106.00620 );
// Attempt to draw tracking lines

function trackDraw( trkLat, trkLng ) {
	if ( locCount === 0 ) {
		var firstPt = { lat: trkLat, lng: trkLng };
		trkPts.push(firstPt);
		locCount++;
		msg = '<p>First pt [' + locCount + '] pushed: lat ' + trkLat + '; lng ' + trkLng + '</p>';
		$('#dbug').append(msg);
	} else {  // all the rest of the points
		var lastPt = trkPts[locCount-1];
		var tstLat = trkLat - lastPt['lat'];
		var tstLng = trkLng - lastPt['lng'];
		var tstHyp = tstLat*tstLat + tstLng*tstLng;
		if ( tstHyp >= hyp ) {  // we have a winner...
			msg = '<p>point ' + (locCount + 1) + ' saved: lat ' + trkLat + '; lng ' + trakLng + '<p>';
			$('#dbug').append(msg);
			var newPt = { lat: trkLat, lng: trkLng };
			trkPts.push(newPt);
			locCount++
		}
	}
}