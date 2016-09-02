var map;  // needs to be global!
var mapRdy = false; // flag for map initialized & ready to draw tracks
var mapTick = {   // custom tick-mark symbol for tracks
	path: 'M 0,0 -5,11 0,8 5,11 Z',
	fillcolor: 'DarkBlue',
	fillOpacity: 0.8,
	scale: 1,
	strokeColor: 'DarkBlue',
	strokeWeight: 2
};

var turnOnGeo = localStorage.getItem('geoLoc');

if ( turnOnGeo === 'true' ) {
	$('#geoCtrl').css('display','block');
	$('#geoCtrl').on('click', setupLoc);
}

// icons for geolocation:
var smallGeo = '../images/starget.png';
var medGeo = '../images/grnTarget.png';
var lgGeo = '../images/ltarget.png';

var mobile_browser = (navigator.userAgent.match(/\b(Android|Blackberry|IEMobile|iPhone|iPad|iPod|Opera Mini|webOS)\b/i) || (screen && screen.width && screen.height && (screen.width <= 480 || screen.height <= 480))) ? true : false;
// icons depend on whether mobile or not (size factor for visibility)
// also text size for pop-ups
if ( mobile_browser ) {
	var geoIcon = lgGeo;
	var ctrIcon = '../images/green64.png';
	var clusterIcon = '../images/blue64.png';
	var hikeIcon = '../images/pink64.png';
	$('#iwVC').css('font-size','400%');
	$('#iwCH').css('font-size','400%');
	$('#iwOH').css('font-size','400%');
} else {
	var geoIcon = medGeo;
	var ctrIcon = '../images/greenpin.png';
	var clusterIcon = '../images/bluepin.png';
	var hikeIcon = '../images/redpin.png';
} 

// INSIDE the initMap function, the listener is defined, and depending on whether
// or not there is a table present, a definition is added for "dragend"


// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
// THE MAP CALLBACK FUNCTION:

function initMap() {
	// NOW TO THE MAP!!
	var nmCtr = {lat: 34.450, lng: -106.042};

	var mapDiv = document.getElementById('map');
	map = new google.maps.Map(mapDiv, {
		center: nmCtr,
		zoom: 7,
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
				google.maps.MapTypeId.SATELLITE,
				google.maps.MapTypeId.HYBRID
			]
		},
		fullscreenControl: true,
		streetViewControl: false,
		rotateControl: false,
		mapTypeId: google.maps.MapTypeId.TERRAIN
	});
	mapRdy = true;

	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	var loc; // google lat/lng object
	var sym; // type of icon to display for marker
	var nme; // name of hike (for 'tooltip' type title of marker
	var mrkrIndx = 0; // index number for object below to create marker obj references
	var allMarkers = { marker0: {}, title0: '' }; // object to hold marker references
	var noOfVCs = ctrPinHikes.length;
	var noOfCHikes = clusterPinHikes.length;
	var noOfOthr = othrHikes.length;
	var VC_TYPE = 0; // constants for readability during "make content" function: mkContent()
	var CH_TYPE = 1;
	var OH_TYPE = 2;
	
	// Loop through marker creation: 1st, visitor centers:
	sym = ctrIcon;
	for (var i=0; i<noOfVCs; i++) {
		loc = {lat: ctrPinHikes[i][1], lng: ctrPinHikes[i][2] };
		nme = ctrPinHikes[i][0];
		AddVCMarker(loc, sym, nme, i);
	}
	// Now, the "clustered" hikes:
	sym =clusterIcon;
	for (var j=0; j<noOfCHikes; j++ ) {
		loc = {lat: clusterPinHikes[j][1], lng: clusterPinHikes[j][2] };
		nme = clusterPinHikes[j][0];
		AddClusterMarker(loc, sym, nme, j);
	}
	// Finally, the remaining hike markers
	sym = hikeIcon;
	for (var k=0; k<noOfOthr; k++) {
		loc = {lat: othrHikes[k][1], lng: othrHikes[k][2] };
		nme = othrHikes[k][0];
		AddHikeMarker(loc, sym, nme, k);
	}
	
	// the actual functions to create the markers & setup info windows
	// Visitor Center Markers: 
	function AddVCMarker(location, iconType, pinName,indx) {
		// save marker reference
		var title = 'title' + mrkrIndx;
		var marker = 'marker' + mrkrIndx;
		mrkrIndx++ ;
		allMarkers[title] = pinName;
		allMarkers[marker] = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		allMarkers[marker].addListener( 'click', function() {
			var markerId = this.getTitle();
			for (p=0; p<noOfVCs; p++ ) {
				if ( markerId === ctrPinHikes[p][0] ) {
					var vcIndex = p;
					break;
				}
			}
			iwContent = mkContent(VC_TYPE, vcIndex);
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 400
			});
			iw.open(map, this);
		});
	}
	// Clustered Trailhead Markers:
	function AddClusterMarker(location, iconType, pinName, indx) {
		// save marker reference
		var title = 'title' + mrkrIndx;
		var marker = 'marker' + mrkrIndx;
		mrkrIndx++ ;
		allMarkers[title] = pinName;
		allMarkers[marker] = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		allMarkers[marker].addListener( 'click', function() {
			var markerId = this.getTitle();
			for (q=0; q<noOfCHikes; q++ ) {
				if ( markerId === clusterPinHikes[q][0] ) {
					var chIndex = q;
					break;
				}
			}
			iwContent = mkContent(CH_TYPE, chIndex);
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 400
			});
			iw.open(map, this);
		});
	}
	function AddHikeMarker(location, iconType, pinName, indx) {
		// save marker reference
		var title = 'title' + mrkrIndx;
		var marker = 'marker' + mrkrIndx;
		mrkrIndx++ ;
		allMarkers[title] = pinName;
		allMarkers[marker] = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		allMarkers[marker].addListener( 'click', function() {
			var markerId = this.getTitle();
			for (r=0; r<noOfOthr; r++) {
				if ( markerId === othrHikes[r][0] ) {
					var ohIndex = r;
					break;
				}
			}
			iwContent = mkContent(OH_TYPE, ohIndex);
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 400
			});
			iw.open(map, this);
		});
	}
	
	// /////////////////////////   INFO WINDOW CREATION   /////////////////////////
	function mkContent(markerType, elementNo) {
		// construct content for info window:
		switch (markerType) {
			case VC_TYPE:
				var hName = ctrPinHikes[elementNo][0];
				var hPg = ctrPinHikes[elementNo][3];
				hPg = '<a href="' + hPg + '" target="_blank">Hike Index Pg</a>';
				var hDir = $('tbody tr').eq(elementNo+1).find('td:nth-child(9)').html();
				var popup = '<div id="iwVC"><p>Visitor Center for<br>Park: ' + hName + '<br>' +
					hPg + '<br>' + hDir + '</p></div>';
				break;
			case CH_TYPE:
				var hName = clusterPinHikes[elementNo][0];
				var hPg = clusterPinHikes[elementNo][3];
				hPg = '<a href="' + hPg + '" target="_blank">Website</a>';
				elementNo += noOfVCs + 1; // nth row in table
				var hDir = $('tbody tr').eq(elementNo).find('td:nth-child(9)').html();
				var hLgth = $('tbody tr').eq(elementNo).find('td:nth-child(5)').text();
				var hElev = $('tbody tr').eq(elementNo).find('td:nth-child(6)').text();
				var hDiff = $('tbody tr').eq(elementNo).find('td:nth-child(7)').text();
				var popup = '<div id="iwCH">Hike: ' + hName + '<br>Difficulty: ' +
					hDiff + '<br>Length: ' + hLgth + '<br>Elev Chg: ' + hElev + '<br>' + 
					hPg + '<br>' + hDir + '</div>';
				break;
			case OH_TYPE:
				var hName = othrHikes[elementNo][0];
				var hPg = othrHikes[elementNo][3];
				hPg = '<a href="' + hPg + '" target="_blank">Website</a>';
				elementNo += noOfVCs + noOfCHikes + 1; // nth row in table
				var hDir = $('tbody tr').eq(elementNo).find('td:nth-child(9)').html();
				var hLgth = $('tbody tr').eq(elementNo).find('td:nth-child(5)').text();
				var hElev = $('tbody tr').eq(elementNo).find('td:nth-child(6)').text();
				var hDiff = $('tbody tr').eq(elementNo).find('td:nth-child(7)').text();
				var popup = '<div id="iwOH">Hike: ' + hName + '<br>Difficulty: ' +
					hDiff + '<br>Length: ' + hLgth + '<br>Elev Chg: ' + hElev + '<br>' + 
					hPg + '<br>' + hDir + '</div>';
				break;
			default:
				break;
		}
		return (popup);
	}
	
	// Establish polylines for areas where trailhead has more than 1 hike
	// BANDELIER:
	var BandCtr = {lat: 35.778943, lng: -106.270838 };	
	var BandHikeMrkrLocs = [ 
		{lat: 35.793670, lng: -106.273155 },
		BandCtr,
		{lat: 35.788735, lng: -106.282079 },
		BandCtr,
		{lat: 35.779219, lng: -106.285744 },
		BandCtr,
		{lat: 35.769573, lng: -106.282433 },
		BandCtr,
		{lat: 35.764312, lng: -106.273698 }
	];
	var Blines = new google.maps.Polyline({
		path: BandHikeMrkrLocs,
        geodesic: false,
        strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	Blines.setMap(null);
	var KinAltoLoc = {lat: 36.064977, lng: -107.969867 };
	var KinAltMrkrLocs = [
		{lat: 36.063864, lng: -107.981315 },
		KinAltoLoc,
		{lat: 36.068608, lng: -107.959900 }
	];
	var KinAltLines = new google.maps.Polyline({
		path: KinAltMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	KinAltLines.setMap(null);
	// SANTA FE SKI AREA (Winsor Trailhead):
	var SkiCtr = {lat: 35.795845, lng: -105.804605 };
	var SkiMrkrLocs = [
		{lat: 35.807036, lng: -105.783577 },
		SkiCtr,
		{lat: 35.818627, lng: -105.797649 },
		SkiCtr,
		{lat: 35.816873, lng: -105.815796 },
		SkiCtr,
		{lat: 35.802801, lng: -105.827387 }
	];
	var SkiLines = new google.maps.Polyline({
		path: SkiMrkrLocs,
        geodesic: false,
        strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	SkiLines.setMap(null);
	// ELENA GALLEGOS: PINO & DOMINGO BACA:
	var eg = {lat:35.163250, lng: -106.470067 };
	var egMrkrLocs = [
		{lat: 35.160419, lng: -106.463184 },
		eg,
		{lat: 35.167093, lng: -106.465502}
	];
	var egLines = new google.maps.Polyline({
		path: egMrkrLocs,
        geodesic: false,
        strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	egLines.setMap(null);
	// BIG TESUQUE:
	var tes = {lat: 35.769508, lng: -105.809155 };
	var tesMrkrLocs = [
		{lat: 35.764427, lng: -105.769501 },
		tes,
		{lat: 35.738236, lng: -105.779114 }
	];
	var tesLines = new google.maps.Polyline({
		path: tesMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	tesLines.setMap(null);
	// PETROGLYPHS: BOCA NEGRA
	var CliffCtr = {lat: 35.161988, lng: -106.718203 };
	var CliffMacMrkrLocs = [
		{lat: 35.165471, lng: -106.729088 },
		CliffCtr,
		{lat: 35.170242, lng: -106.717243 }
	];
	var CliffMacLines = new google.maps.Polyline({
		path: CliffMacMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	CliffMacLines.setMap(null);
	// MANZANITAS MTN TRAILS:
	var mmt = {lat: 35.046562, lng: -106.383088 };
	var bhse = {lat: 35.055938, lng: -106.388512 };
	var tunl = {lat: 35.055938, lng: -106.371517 };
	var mmtMrkrLocs = [ bhse, mmt, tunl ];
	var mmtLines = new google.maps.Polyline({
		path: mmtMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	mmtLines.setMap(null);
	// EAST FORK TRAILS:
	var efth = {lat: 35.820818, lng: -106.591123 };
	var eflc = {lat: 35.827885, lng: -106.580129 };
	var efbs = {lat: 35.825727, lng: -106.599355 };
	var efpath = [ efbs, efth, eflc ];
	var eftrails = new google.maps.Polyline({
		path: efpath,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	eftrails.setMap(null);	
	// END OF POLYLINES CREATION
	
	// PAN AND ZOOM HANDLERS:
	map.addListener('zoom_changed', function() {
		var curZoom = map.getZoom();
		if (useTbl) {
			var perim = String(map.getBounds());
			IdTableElements(perim);
		}
		if ( curZoom > 10 ) {
			Blines.setMap(map);
			KinAltLines.setMap(map);
			SkiLines.setMap(map);
			egLines.setMap(map);
			tesLines.setMap(map);
			CliffMacLines.setMap(map);
			eftrails.setMap(map);
			mmtLines.setMap(map);
			for (var m=0; m<allTheTracks.length; m++) {
				trkKeyStr = 'trk' + m;
				trkObj[trkKeyStr].setMap(map);
			}

		} else {
			Blines.setMap(null);
			KinAltLines.setMap(null);
			SkiLines.setMap(null);
			egLines.setMap(null);
			tesLines.setMap(null);
			CliffMacLines.setMap(null);
			mmtLines.setMap(null);
			eftrails.setMap(null);
			for (var n=0; n<allTheTracks.length; n++) {
				trkKeyStr = 'trk' + n;
				trkObj[trkKeyStr].setMap(null);
			}
		}
	});
	
	if( useTbl) {
		map.addListener('dragend', function() {
			var newBds = String(map.getBounds());
			IdTableElements(newBds);
		});
	}
	
}  // end of initMap()
// ////////////////////// END OF MAP INITIALIZATION  /////////////////////////////


// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
//var newTrack; // used repeatedly to assign incoming JSON data
// the following is not used yet, but intended to allow individual turn on/off of tracks
var allTheTracks = []; // array of all existing track objects
var trkObj = { trk0: {}, trkName0: 'name' };
var trkKeyNo = 0;
var trkKeyStr;
var clusterCnt = 0; // number of clusterPinHikes processed
var othrCnt = 0; // number of othrHikes processed
// use pre-defined directional arrow for tracks

var trackForm = setInterval(startTracks,40);

function startTracks() {
	if ( mapRdy ) {
		clearInterval(trackForm);
		drawTracks(clusterCnt, othrCnt);
	}
}

function sglTrack(trkUrl,trkType,trkColor,indx) {
	$.ajax({
		dataType: "json",
		url: trkUrl,
		success: function(trackDat) {
			var newTrack = trackDat;
			trkKeyStr = 'trk' + trkKeyNo;	
			trkObj[trkKeyStr] = new google.maps.Polyline({
				icons: [{
					icon: mapTick,
					offset: '0%',
					repeat: '15%' 
				}],
				path: newTrack,
				geodesic: true,
				strokeColor: trkColor,
				strokeOpacity: 1.0,
				strokeWeight: 3
			});
			//trkObj['trk'].setMap(map);
			allTheTracks.push(trkKeyStr);
			if ( trkType ) {
				var hName = othrHikes[indx][0];
				var hPg = othrHikes[indx][3];
				indx += ctrPinHikes.length + clusterPinHikes.length;
			} else {
				var hName = clusterPinHikes[indx][0];
				var hPg = clusterPinHikes[indx][3];
				indx += ctrPinHikes.length;
			}
			var hLgth = $('tbody tr').eq(indx).find('td:nth-child(5)').text();
			var hElev = $('tbody tr').eq(indx).find('td:nth-child(6)').text();
			var hDiff = $('tbody tr').eq(indx).find('td:nth-child(7)').text();
			var iwContent = '<div id="iwOH">Hike: ' + hName + '<br>Difficulty: ' +
				hDiff + '<br>Length: ' + hLgth + '<br>Elev Chg: ' + hElev + '<br><a href="pages/' + 
				hPg + '" target="_blank">Website</a></div>'; 
			var iw = new google.maps.InfoWindow({
				content: iwContent
			});
			trkObj[trkKeyStr].addListener('mouseover', function(mo) {
				var trkPtr = mo.latLng;
				iw.setPosition(trkPtr);
				iw.open(map);
			});
			trkObj[trkKeyStr].addListener('mouseout', function() {
				iw.close();
			});
			trkKeyNo++;
			if ( trkType == 0 ) {
				drawTracks(clusterCnt++,othrCnt);
			} else {
				drawTracks(clusterCnt,othrCnt++);
			}
		},
		error: function() {
			msg = '<p>Did not succeed in getting JSON data: ' + trkUrl + '</p>';
			$('#dbug').append(msg);
		}
	});
}

// NO GPX files for Visitor Centers, so start with cluster hikes:
function drawTracks(cluster,othr) {
	if ( cluster < clusterPinHikes.length ) {
		if ( clusterPinHikes[cluster][4] ) {
			trackFile = clusterPinHikes[cluster][4];
			var cindx = trackFile.indexOf('.json');
			var handle = trackFile.substring(0,cindx);
			trkKeyStr = 'trkName' + trkKeyNo;
			trkObj[trkKeyStr] = handle;
			trackFile = '../json/' + trackFile;
			clusColor = clusterPinHikes[cluster][5];
			sglTrack(trackFile,0,clusColor,cluster);
		} else {
			drawTracks(clusterCnt++,othrCnt);
		}
	} else {  // End of clusterHike test
		if ( othr < othrHikes.length ) {
			if ( othrHikes[othr][4] ) {
				trackFile = othrHikes[othr][4];
				var oindx = trackFile.indexOf('.json');
				var handle = trackFile.substring(0,oindx);
				trkKeyStr = 'trkName' + trkKeyNo;
				trkObj[trkKeyStr] = handle;
				trackFile = '../json/' + trackFile;
				sglTrack(trackFile,1,trackColor,othr);
			} else {
				drawTracks(clusterCnt,othrCnt++);
			}
		}  // End of othrHikes segment
	}  // End of whole test
}  // END FUNCTION
// /////////////////////// END OF HIKING TRACK DRAWING /////////////////////


// ////////////////////////////  GEOLOCATION CODE //////////////////////////
var geoOptions = { enableHighAccuracy: 'true' };

if ( turnOnGeo === 'true' ) {
	var geoTmr = setInterval(turnOnGeoLoc,100);
}

function turnOnGeoLoc() {
	if ( mapRdy ) {
		clearInterval(geoTmr);
		setupLoc();
	}
}

function setupLoc() {
	if (Modernizr.geolocation) {
		var myGeoLoc = navigator.geolocation.getCurrentPosition(success, error, geoOptions);
		function success(pos) {
			var geoPos = pos.coords;
			var geoLat = geoPos.latitude;
			var geoLng = geoPos.longitude;
			var newWPos = {lat: geoLat, lng: geoLng };
			geoMarker = new google.maps.Marker({
				position: newWPos,
				map: map,
				icon: geoIcon,
				size: new google.maps.Size(24,24),
				origin: new google.maps.Point(0, 0),
				anchor: new google.maps.Point(12, 12)
			});
		} // end of watchSuccess function
		function error(eobj) {
			msg = '<p>Error in get position call: code ' + eobj.code + '</p>';
			window.alert(msg);
		}
	} else {
		window.alert('Geolocation not supported on this browser');
	}
}
// //////////////////////////////////////////////////////////////
	
