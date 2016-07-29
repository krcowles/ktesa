// generic debug output var:
var msg; 

var geoOptions = { enableHighAccuracy: true };
var watchOptions = { enableHighAccuracy: true };
var map;
var mapStartPos = {lat: 35.690183, lng: -106.013517};
var geoMarker;
var geoIcon = '../images/grnTarget.png';

// IIFE:
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
			geoMarker = new google.maps.Marker({
				position: mapStartPos,
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

var rateObj = { key1: 'val1' }; // somewhat elaborate method to avoid repeated usage
// of interval timer names, in case it causes confusion...
var keyVal = 1;
var rateObjKey;

var watchObj = { key1: 'val1' };
var wkeyVal = 1;
var watchObjKey;

var intType = false;
var watType = false;

function intervals() {
	if ( watType ) {
		watType = false;
		$('#wtch').css('background-color','white');
		navigator.geolocation.clearWatch(watchObj[watchObjKey]);
		}
	intType = true;
	$('#int').css('background-color','pink');
	rateObjKey = 'key' + keyVal++;
	rateObj[rateObjKey] = setInterval(getLoc, 10000);
}

function wtch() {
	if ( intType ) {
		clearInterval(rateObj[rateObjKey]);
		$('#int').css('background-color','white');
		intType = false;
	}
	$('#wtch').css('background-color','pink');
	watType = true;
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
		//msg = '<p>New position obtained</p>';
		//$('#dbug').append(msg);
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
