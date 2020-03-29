// need to be global:
var map;
var allTheTracks = [];
var tracksDone = false;

// Google's hidden inner div when clicking on full screen mode
var $fullScreenDiv;
var $map = $('#map');
// Maximize map height & side table div
var mapht = $(window).height() - $('#panel').height();

/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible.
 * 
 * @return {null}
 */
const initDivParms = () => {
	mapht = $(window).height() - $('#panel').height();
	$map.css('height', mapht + 'px');
	$('#adjustWidth').css('height', mapht + 'px');
	$('#sideTable').css('height', mapht + 'px');
}
initDivParms();

var mapTick = {   // custom tick-mark symbol for tracks
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};

function locateGeoSym() {
	var winht = $('#panel').height() + mapht - 100;
	var mapwd = $('#map').width() - 120;
	$('#geoCtrl').css('top', winht);
	$('#geoCtrl').css('left', mapwd);
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);

// icons for geolocation:
var smallGeo = '../images/starget.png';
var medGeo = '../images/purpleTarget.png';
var lgGeo = '../images/ltarget.png';

var locaters = []; // global used to popup info window on map when hike is searched
const getIcon = (no_of_hikes) => {
	let icon = "../images/pins/hike" + no_of_hikes + ".png";
	return icon;
};

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
var mapdone = new $.Deferred();
function initMap() {
	google.maps.Marker.prototype.clicked = false;  // used in sideTables.js
	var clustererMarkerSet = [];
	var nmCtr = {lat: 34.450, lng: -106.042};
	map = new google.maps.Map($map.get(0), {
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
	mapdone.resolve();

	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	NM.forEach(function(nmobj) {
		AddHikeMarker(nmobj);
	});
	// Normal Hike Markers
	function AddHikeMarker(hikeobj) {
		let nmicon = getIcon(1);
		var marker = new google.maps.Marker({
		  position: hikeobj.loc,
		  map: map,
		  icon: nmicon,
		  title: hikeobj.name
		});
		marker.clicked = false;
		let srchmrkr = {hikeid: hikeobj.name, pin: marker};
		locaters.push(srchmrkr);
		clustererMarkerSet.push(marker);

		// infoWin content: add data for this hike
		var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
			+ hikeobj.indx + '">' + hikeobj.name + '</a><br />';
		iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
		iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
		iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
		iwContent += '<a href="' + hikeobj.dir + '">Directions</a></div>';
		var iw = new google.maps.InfoWindow({
				content: iwContent,
				maxWidth: 400
		});
		iw.addListener('closeclick', function() {
			marker.clicked = false;
		});
		marker.addListener( 'click', function() {
			map.setCenter(hikeobj.loc);
			map.setZoom(13);
			iw.open(map, this);
			marker.clicked = true;
		});
	}


	// /////////////////////// Marker Grouping /////////////////////////
	var markerCluster = new MarkerClusterer(map, clustererMarkerSet,
		{
			imagePath: '../images/markerclusters/m',
			gridSize: 50,
			maxZoom: 12,
			averageCenter: true,
			zoomOnClick: true
		});

	// //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
	map.addListener('zoom_changed', function() {
		var idle = google.maps.event.addListener(map, 'idle', function (e) {
			var curZoom = map.getZoom();
			var perim = String(map.getBounds());
			if ( curZoom > 12 ) {
				for (var m=0; m<allTheTracks.length; m++) {
					trkKeyStr = 'trk' + m;
					trkObj[trkKeyStr].setMap(map);
				}
			} else {
				for (var n=0; n<allTheTracks.length; n++) {
					trkKeyStr = 'trk' + n;
					trkObj[trkKeyStr].setMap(null);
				}
			}
			//IdTableElements(perim);
			google.maps.event.removeListener(idle);
		});
	});
	
	map.addListener('dragend', function() {
		var newBds = String(map.getBounds());
		IdTableElements(newBds);
	});
}  // end of initMap()
// ////////////////////// END OF MAP INITIALIZATION  /////////////////////////////

// collect mouseover data for tracks
var trackdat = [];
for (let i=0; i<tracks.length; i++) {
	trackdat[i] = '';
}
NM.forEach(function(hobj) {
	if (tracks[hobj.indx] !== '') {
		trackdat[hobj.indx] = '<div id="iwNH">' + hobj.name + '<br />Length: ' +
			hobj.lgth + ' miles<br />Elev Chg: ' + hobj.elev +
			'<br />Difficulty: ' + hobj.diff + '</div>';
	}
});

// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
var trkObj = { trk0: {}, trkName0: 'name' };
var trkKeyNo = 0;
var trkKeyStr;
var i,j,k;
var geoOptions = { enableHighAccuracy: 'true' };

// deferred wait for map to get initialized
$.when( mapdone ).then(drawTracks).then(function() {
	$fullScreenDiv = $map.children('div:first');
});

function drawTracks() {
	let color = '#FF0000'
	tracks.forEach(function(fname, indx) {
		if (fname !== '') {
			var trkfile = '../json/' + fname;
			drawTrack(trkfile, color, indx);
		}
	});
	tracksDone = true;
}
function drawTrack(jsonfile, color, ptr) {
	$.ajax({
		dataType: "json",
		url: jsonfile,
		success: function(trackDat) {
			trkKeyStr = 'trk' + trkKeyNo;	
			trkObj[trkKeyStr] = new google.maps.Polyline({
				icons: [{
						icon: mapTick,
						offset: '0%',
						repeat: '15%' 
				}],
				path: trackDat,
				geodesic: true,
				strokeColor: color,
				strokeOpacity: 1.0,
				strokeWeight: 3
			});
			//trkObj[trkKeyStr].setMap(map);
			// when loaded, all tracks are off (not set)
			allTheTracks.push(trkKeyStr);
			// create the mouseover text:
			var iw = new google.maps.InfoWindow({
				content: trackdat[ptr]
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
		},
		error: function(jqXHR, textStatus, errorThrown) {
			msg = 'Did not succeed in getting JSON data: ' + jsonfile;
			alert("Indx " + ptr);
		}
	});
} // end drawTrack
// /////////////////////// END OF HIKE TRACK DRAWING /////////////////////

// ////////////////////////////  GEOLOCATION CODE //////////////////////////
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
			map.setCenter(newWPos);
			var currzoom = map.getZoom();
			if (currzoom < 13) {
				map.setZoom(13);
			}
		} // end of watchSuccess function
		function error(eobj) {
			msg = '<p>Error in get position call: code ' + eobj.code + '</p>';
			window.alert(msg);
		}
	} else {
		window.alert('Geolocation not supported on this browser');
	}
}
// //////////////////////  MAP FULL SCREEN DETECT  //////////////////////
$(document).bind(
	'webkitfullscreenchange mozfullscreenchange fullscreenchange',
	function() {
		var isFullScreen = document.fullScreen ||
			document.mozFullScreen ||
			document.webkitIsFullScreen;
		if (isFullScreen) {
			console.log('fullScreen!');
			var $gicon = $('#geoCtrl').detach();
			var $nhbox = $('#newHikeBox').detach()
			$gicon.appendTo($fullScreenDiv);
			$nhbox.appendTo($fullScreenDiv);
		} else {
			console.log('NO fullScreen!');
		}
});

// //////////////////////  WINDOW RESIZE EVENT  //////////////////////
$(window).resize(function() {
	let newWinWidth = window.innerWidth;
	let mapWidth = Math.round(0.72 * newWinWidth);
	let tblWidth = newWinWidth - (mapWidth + 3); // 3px = adjustWidth
	initDivParms();
	$map.css('width', mapWidth + 'px');
	$('#sideTable').css('width', tblWidth + 'px');
	locateGeoSym();
	positionFavTooltips();
});
// //////////////////////////////////////////////////////////////
