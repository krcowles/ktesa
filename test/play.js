// generic debug output var:
var msg; 

// ///////////////////////////// GLOBAL VARIABLES //////////////////////////////
var geoOptions = { enableHighAccuracy: true };
var watchOptions = { enableHighAccuracy: true };
var map;
var mapStartPos = {lat: 35.690183, lng: -106.013517};
var geoMarker;
var geoIcon = '../images/grnTarget.png';

// /////////////////////////////// BROWSER TYPE /////////////////////////////////
// determine the browser (non-mobile):
// NOTE: RE "i" modifier at end of pattern => case insensitive
function getBrowserInfo()
{
	var ua = navigator.userAgent, tem,
	M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
	if(/trident/i.test(M[1]))
	{
		tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
		return 'IE '+(tem[1] || '');
	}
	if(M[1]=== 'Chrome')
	{
		tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
		if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
	}
	M = M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
	if((tem= ua.match(/version\/(\d+)/i))!= null) 
		M.splice(1, 1, tem[1]);
	return M.join(' ');
}
mobile_browser = (navigator.userAgent.match(/\b(Android|Blackberry|IEMobile|iPhone|iPad|iPod|Opera Mini|webOS)\b/i) || (screen && screen.width && screen.height && (screen.width <= 480 || screen.height <= 480))) ? true : false;
/*
if (mobile_browser) {
	//window.alert('Mobile Browser');
	if ( !navigator.userAgent.match(/\b(IEMobile)\b/i ) ) {
		// IE phone: use this for sleep/awake:
		window.addEventListener('pageshow', function() {
			//window.alert('Awakened!');
		}, false);
	} else {
		window.attachEvent('pageshow', function() {
			//window.alert('Awake now');
		});
	}
} else {
	var browserInfo = getBrowserInfo();
	//window.alert(browserInfo);
}
*/

// //////////////////////////// CENTER MAP WHERE YOU ARE //////////////////////////
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

// //////////////////////////// GOOGLE MAP SETUP ////////////////////////////////
// Google Callback to establish the map
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
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});
}

// ///////////////////////////////// BUTTONS //////////////////////////////////
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

// ////////////////////////////// GEOLOCATION FUNCTIONS ////////////////////////////
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


// ///////////////////////////// TRACK SETUP /////////////////////////////////
// Since javascript uses 64bit double precision:
var dx = .000222;	// experimental latitude diff for 100ft stride
var dy = .000106;	// experimental longitude diff for 100ft stride
var hyp = 2*(dx*dx + dy*dy); // used as square of hypotenuse for determining min track distance
var trkPts = [];    // array of lat/lng objects compatible w/google maps

// Function to DOWNLOAD the file of segments
function download(strData, strFileName, strMimeType) {
    var D = document,
        A = arguments,
        a = D.createElement("a"),
        d = A[0],  // 1st arg = strData (text data to send)
        n = A[1],  // 2nd arg = strFileName (name to send to computer)
        t = A[2] || "text/plain";

    //build download link:
    // NOTE: I swapped out 'escape' (deprecated) and replaced it with 'encodeURI'
    a.href = "data:" + strMimeType + "charset=utf-8," + encodeURI(strData);
	if ('download' in a) { //FF20, CH19
			a.setAttribute("download", n);
			a.innerHTML = "downloading...";
			D.body.appendChild(a);
			setTimeout(function() {
				var e = D.createEvent("MouseEvents");
				e.initMouseEvent("click", true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
				a.dispatchEvent(e);
				D.body.removeChild(a);
			}, 66);
			return true;
	}; // end if('download' in a)

    //do iframe dataURL download: (older W3)
    var f = D.createElement("iframe");
    D.body.appendChild(f);
    f.src = "data:" + (A[2] ? A[2] : "application/octet-stream") + (window.btoa ? ";base64" : "") + "," + (window.btoa ? window.btoa : encodeURI)(strData);
    setTimeout(function() {
        D.body.removeChild(f);
    }, 333);
    return true;
}

//setTimeout( fakeit, 2000 );
function fakeit() {
	var fake1 = trackDraw( 35.6903502, -106.0135077 );
	var fake2 = trackDraw( 35.6885545, -106.0142811 );
	var fake3 = trackDraw( 35.6893862, -106.0142786);
	var fake4 = trackDraw( 35.6903876, -106.0136131 );
	var fake5 = trackDraw( 35.6905510, -106.0131575 );
	var fake6 = trackDraw( 35.6903234, -106.0134661 );
}

// Attempt to draw tracking lines
var trackLine;
function trackDraw( trkLat, trkLng ) {
	if ( locCount === 0 ) {
		var firstPt = { lat: trkLat, lng: trkLng };
		trkPts.push(firstPt);
		locCount++;
		var firstDate = new Date();
		var timeStamp = 'Month:Day/Hr:Min:Sec ';
		msg = '<p>Timestamp format is ' + timeStamp + '</p>';
		$('#dbug').append(msg);
		var firstBase = firstDate.getMonth() + 1;
		firstBase += ':' + firstDate.getDate() + '/' + firstDate.getHours();
		firstBase += ':' + firstDate.getMinutes() + ':' + firstDate.getSeconds();
		msg = '<p>' + firstBase + '; First pt [' + locCount + '] pushed: lat ' + trkLat + '; lng ' + trkLng + '</p>';
		$('#dbug').append(msg);
	} else {  // all the rest of the points
		var lastPt = trkPts[locCount-1];
		var tstLat = trkLat - lastPt['lat'];
		var tstLng = trkLng - lastPt['lng'];
		var tstHyp = tstLat*tstLat + tstLng*tstLng;
		if ( tstHyp >= hyp ) {  // we have a qualified point
			var nxtDate = new Date();
			var nxtStamp = nxtDate.getMonth() + 1;
			nxtStamp += ':' + nxtDate.getDate() + '/' + nxtDate.getHours();
			nxtStamp += ':' + nxtDate.getMinutes() + ':' + nxtDate.getSeconds();
			msg = '<p>' + nxtStamp + '; point ' + (locCount + 1) + ' saved: lat ' + trkLat + '; lng ' + trkLng + '<p>';
			$('#dbug').append(msg);
			var newPt = { lat: trkLat, lng: trkLng };
			trkPts.push(newPt);
			locCount++;
			// create first piece (locCount = 2) then "add" to it as you go
			if ( locCount === 2 ) {  // create the first line, don't delete it before doing so
				var thisLine = [ lastPt, newPt ];
				trackLine = new google.maps.Polyline({
					path: thisLine,
					map: map,
					geodesic: true,
					strokeColor: '#FF0000',
					strokeOpacity: 1.0,
					strokeWeight: 3
				});
			} else {  // remaining points
				trackLine.setMap(null);  // is this required? test later....
				trackLine = null;
				trackLine = new google.maps.Polyline({
					path: trkPts,
					map: map,
					geodesic: true,
					strokeColor: '#FF0000',
					strokeOpacity: 1.0,
					strokeWeight: 3 
				});
				if ( locCount === 6 ) {
					var dataStr = '[ ';
					for ( var j=0; j<6; j++ ) {
						dataStr += '{lat: '
						dataStr += trkPts[j]['lat'];
						dataStr += ',lng: ' + trkPts[j]['lng'];
						dataStr += ' }, ';
					}
					var lastComma = dataStr.lastIndexOf(',');
					dataStr = dataStr.substring(0,lastComma);
					dataStr += ' ];';
					download(dataStr,'GPSpoints.txt','text/plain');
				} // end of LOCCOUNT = 6
			} // end ELSE of IF LOCCOUNT === 2
			
		} // end of IF - GOOD DATA POINT
	}
}