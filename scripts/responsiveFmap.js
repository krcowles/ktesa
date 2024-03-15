"use strict"
/**
 * @fileoverview Set up a full page map showing the Favorites selected
 * by the user
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Responsive design intro (new menu, etc.)
 */

// Positioning of elements on page
//let title = $('#trail').text();
$('#ctr').text(title);
/**
 * Position the links button
 * 
 * @return {null}
 */
const links_btn = () => {
	let navht  = $('nav').height() + 16 // padding
	let logoht = $('#logo').height();
	let favtop = navht + logoht + 8 + 'px';
	let favlft = $('#map').width() - 240 + 'px';
	$('#favlist').css('top', favtop);
	$('#favlist').css('left', favlft);
};
links_btn();

// display 'no favs' modal when there are no favorites
var nofavs = new bootstrap.Modal(document.getElementById('nofavs'), {
    keyboard: false
});
if (tracks.length === 0) {
	nofavs.show();
}

var map;
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000', '#FFFF00']
var $fullScreenDiv; // for google maps full screen mode
var $map = $('#map');
var maxlat = 0;    // north
var maxlng = -180; // east
var minlat = 90;   // south
var minlng = 0;    // west
var mapTick = { // Custom tick mark for map tracks
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};
/**
 * This function positions the geosymbol in the bottom right corner of the map,
 * left of the google map zoom control 
 * 
 * @return {null}
 */
function locateGeoSym() {
	let winht = window.innerHeight - 64;
	let mapwd = $('#map').width() - 80;
	$('#geoCtrl').css({
		top: winht,
		left: mapwd
	});
	return;
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
	/**
	 * The only hikes on the favorites pages are 'normal' hikes, i.e.
	 * not clusters.
	 * 
	 * @param {object} hikeobj The hike object from mapJsData.php
	 * 
	 * @return {null}
	 */
	function AddHikeMarker(hikeobj) {
		let nmicon = getIcon(1);
		let marker = new google.maps.Marker({
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
		iwContent += '<a href="' + hikeobj.dirs + '">Directions</a></div>';
		var iw = new google.maps.InfoWindow({
				content: iwContent,
				maxWidth: 400
		});
		iw.addListener('closeclick', function() {
			marker.clicked = false;
		});
		marker.addListener( 'click', function() {
			let zoomLevel = map.getZoom();
			if (zoomLevel < 13) {
				map.setZoom(13);
			}
			map.setCenter(hikeobj.loc);
			iw.open(map, this);
			marker.clicked = true; // marker prototype property
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
	return;
}

// collect mouseover data for tracks and get link list of hikes for page
var trackdat  = [];
var pagelinks = '';
for (let i=0; i<tracks.length; i++) {
	trackdat[i] = '';
}
NM.forEach(function(hobj, indx) {
	trackdat[indx] = '<div id="iwNH">' + hobj.name + '<br />Length: ' +
		hobj.lgth + ' miles<br />Elev Chg: ' + hobj.elev +
		'<br />Difficulty: ' + hobj.diff + '</div>';
	pagelinks += '<a href="../pages/responsivePage.php?hikeIndx=' +
		hobj.indx + '">' + hobj.name + '</a><br />';
});
$('#favlinks').append(pagelinks);

// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
var geoOptions = { enableHighAccuracy: 'true' };
/**
 * This function will turn on tracks after tracks have been drawn;
 * 
 * @return {null}
 */
const enableTracks = () => {
	for (var m=0; m<allTheTracks.length; m++) {
		trkKeyStr = 'trk' + m;
		trkObj[trkKeyStr].setMap(map);
	}
	return;
}

// deferred wait for map to get initialized
$.when( mapdone ).then(drawTracks).then(function() {
	$fullScreenDiv = $map.children('div:first');
});

/**
 * Draw tracks for each of the favorites
 * 
 * @return {null}
 */
function drawTracks() {
	let trkcolor = 0;
	var promises = [];
	tracks.forEach(function(fname, indx) {
		if (fname !== '') {
			let trackdef = $.Deferred();
			promises.push(trackdef);
			let trkfile = '../json/' + fname;
			drawTrack(trkfile, colors[trkcolor++], indx, trackdef);
			if (trkcolor >= colors.length) {
				trkcolor = 0; // rollover colors when tracks exceeds colors size
			}
		}
	});
	$.when.apply($, promises).then(function() {
		if (allHikes.length === 1) {
			map.setCenter(NM[0].loc);
            map.setZoom(13);
		} else if (allHikes.length > 1) {
			let bounds = {north: maxlat, south: minlat, east: maxlng, west: minlng};
			map.fitBounds(bounds);
		}
	});
}
/**
 * This function draws one track
 * 
 * @param {string} jsonfile 
 * @param {string} color 
 * @param {number} ptr 
 * 
 * @return {null}
 */
function drawTrack(jsonfile, color, ptr, def) {
	$.ajax({
		dataType: "json",
		url: jsonfile,
		success: function(trackDat) {
			let json_track = trackDat['trk'];
			let track = new google.maps.Polyline({
				icons: [{
						icon: mapTick,
						offset: '0%',
						repeat: '15%' 
				}],
				path: json_track,
				geodesic: true,
				strokeColor: color,
				strokeOpacity: 1.0,
				strokeWeight: 3
			});
			track.setMap(map);
			// create the mouseover text:
			var iw = new google.maps.InfoWindow({
				content: trackdat[ptr]
			});
			track.addListener('mouseover', function(mo) {
				var trkPtr = mo.latLng;
				iw.setPosition(trkPtr);
				iw.open(map);
			});
			track.addListener('mouseout', function() {
				iw.close();
			});
			// establish map boundaries
			json_track.forEach(function(latlngpair) {
				if (latlngpair.lat > maxlat) {
					maxlat = latlngpair.lat;
				}
				if (latlngpair.lat < minlat) {
					minlat = latlngpair.lat;
				}
				if (latlngpair.lng <  minlng) {
					minlng = latlngpair.lng;
				}
				if (latlngpair.lng > maxlng) {
					maxlng = latlngpair.lng;
				}
			});
			def.resolve();
		},
		error: function(jqXHR, textStatus, errorThrown) {
			msg = 'Did not succeed in getting JSON data: ' + jsonfile +
				"\nError: " + textStatus;
			alert(msg);
			def.reject();
		}
	});
	return;
} // end drawTrack
// /////////////////////// END OF HIKE TRACK DRAWING /////////////////////

// ////////////////////////////  GEOLOCATION CODE //////////////////////////
/**
 * Locate the user on the map
 * 
 * @return {null}
 */
function setupLoc() {
	if (navigator.geolocation) {
		var obj = navigator
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
$(window).on('resize', function() {
	locateGeoSym();
	links_btn();
});
// //////////////////////////////////////////////////////////////
