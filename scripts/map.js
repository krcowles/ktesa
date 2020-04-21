// Hike Track Colors: red, blue, orange, purple, black, yellow
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000', '#FFFF00']
var geoOptions = { enableHighAccuracy: 'true' };

// need to be global:
var map;
var $fullScreenDiv; // Google's hidden inner div when clicking on full screen mode
var $map = $('#map');
var mapht;
// track vars
var drawnHikes = [];     // hike numbers which have had tracks created
var drawnTracks = [];    // track objects for drawnHikes
var drawnClusters = [];  // clusters which have had all hikes drawn

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

// Custom tick mark for map tracks
var mapTick = {
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


/**
 * Use the arrays passed in to the home page by php: one for each type 
 * of marker to be displayed (Visitor Ctr, Clustered, Normal):
 * 		VC Array: Visitor Center pages
 * 		CL Array: Clustered hike pages
 * 		NM Array: Normal hike pages
 * And one for creating tracks:
 * 		tracks Array: ordered list of json file names
 */
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
	VC.forEach(function(vcobj) {
		AddVCMarker(vcobj.loc, vcobj.name, vcobj.indx, vcobj.hikes);
	});
	CL.forEach(function(clobj) {
		AddClusterMarker(clobj.loc, clobj.group, clobj.hikes);
	});
	NM.forEach(function(nmobj) {
		AddHikeMarker(nmobj);
	});


	// Visitor Center Markers:
	function AddVCMarker(location, pinName, hikeindx, hikeobj) {
		let hikecnt = hikeobj.length;
		let vcicon = getIcon(hikecnt);
		let marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: vcicon,
		  title: pinName
		});
		marker.clicked = false; // not autoset by prototype
		let srchmrkr = {hikeid: pinName, pin: marker};
		locaters.push(srchmrkr);
		clustererMarkerSet.push(marker);
		
		// infoWindow content: add in all the hikes for this VC
		let website = '<a href="indexPageTemplate.php?hikeIndx=' + hikeindx +
			'">' + pinName + '</a>';
		iwContent = '<div id="iwVC">' + website;
		if (hikeobj.length > 0) { // array of associated hikes
			vLine3 = '<em>Hikes Originating from Visitor Center</em>';
			if(hikeobj.length === 1) {
				vLine3 = vLine3.replace('Hikes','Hike');
			}
			iwContent += '<br />' + vLine3;
			hikeobj.forEach(function(hike) {
				iwContent += 
					'<br /><a href="hikePageTemplate.php?hikeIndx=' +
					hike.indx + '">' + hike.name + '</a>';
				iwContent += ' Lgth: ' + hike.lgth + ' miles; Elev Chg: ';
				iwContent += hike.elev + ' ft; Diff: ' + hike.diff;
			});
		}
		let iw = new google.maps.InfoWindow({
				content: iwContent,
				maxWidth: 600
		});
		iw.addListener('closeclick', function() {
			marker.clicked = false;
		});
		marker.addListener( 'click', function() {
			map.setCenter(location);
			map.setZoom(13);
			iw.open(map, this);
			marker.clicked = true;
		});
	}

	// Clustered Markers:
	function AddClusterMarker(location, group, clhikes) {
		let hikecnt = clhikes.length;
		let clicon = getIcon(hikecnt);
		let marker = new google.maps.Marker({
			position: location,
			map: map,
			icon: clicon,
			title: group
		});
		marker.clicked = false;
		let srchmrkr = {hikeid: group, pin: marker};
		locaters.push(srchmrkr);
		clustererMarkerSet.push(marker);

		// infoWindow content: add in all the hikes for this group
		let iwContent = '<div id="iwCH">' + group;
		clhikes.forEach(function(clobj) {
			iwContent += '<br /><a href="hikePageTemplate.php?hikeIndx=' +
				clobj.indx + '">' + clobj.name + '</a>';
			iwContent += ' Lgth: ' + clobj.lgth + ' miles; Elev Chg: ' + 
				clobj.elev + ' ft; Diff: ' + clobj.diff;
		});
		let iw = new google.maps.InfoWindow({
				content: iwContent,
				maxWidth: 600
		});
		iw.addListener('closeclick', function() {
			marker.clicked = false;
		});
		marker.addListener( 'click', function() {
			map.setZoom(13);
			map.setCenter(location);
			iw.open(map, this);
			marker.clicked = true;
		});
	}

	// Normal Hike Markers
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
		var zoomTracks = false;
		var idle = google.maps.event.addListener(map, 'idle', function (e) {
			var curZoom = map.getZoom();
			var perim = String(map.getBounds());
			if ( curZoom > 12 ) {
				zoomTracks = true;
			} 
			var zoomedHikes = IdTableElements(perim, zoomTracks);
			if (zoomTracks && zoomedHikes.length > 0) {
				zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2]);
			}
			google.maps.event.removeListener(idle);
		});
	});
	
	map.addListener('dragend', function() {
		var curr_zoom = map.getZoom();
		let zoomTracks = true;
		if (curr_zoom < 13) {
			zoomTracks = false;
		}
		var newBds = String(map.getBounds());
		var zoomedHikes = IdTableElements(newBds, zoomTracks);
		if (zoomTracks && zoomedHikes.length > 0) {
			zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2]);
		}
	});
}  // end of initMap()
// ////////////////////// END OF MAP INITIALIZATION  ///////////////////////

// ///////////////////////////  TRACK DRAWING  /////////////////////////////
/**
 * This file will create tracks for the input array of hike objects. If a track has
 * already been created, it will not be created again.
 * 
 * @param {array} hikenos The array of hike numbers within zoomed map bounds
 * @return {null}
 */
function zoom_track(hikenos, infoWins, clusters) {
	for (let i=0; i<hikenos.length; i++) {
		if (!drawnHikes.includes(hikenos[i])) {
			if (tracks[hikenos[i]] !== '') {
				let trackfile = '../json/' + tracks[hikenos[i]];
				let sgltrack = drawTrack(trackfile, infoWins[i], colors[0]);
				drawnHikes.push(hikenos[i]);
				let newtrack = {hike: hikenos[i], track: sgltrack};
				drawnTracks.push(newtrack);
			}
		}
	}
	for (let j=0; j<clusters.length; j++) {
		if (!drawnClusters.includes(clusters[j])) {
			drawClusters(clusters[j]);
			drawnClusters.push(clusters[j]);
		}
	}
}

/**
 * This function draws the track for the hike object
 * @param {string} json_filename: the name associated with the track file
 * @return {null}
 */
function drawTrack(json_filename, info_win, color) {
	var sgltrack;
	$.ajax({
		dataType: "json",
		url: json_filename,
		success: function(trackDat) {
			sgltrack = new google.maps.Polyline({
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
			sgltrack.setMap(map);
			// create the mouseover text:
			var iw = new google.maps.InfoWindow({
				content: info_win
			});
			sgltrack.addListener('mouseover', function(mo) {
				let trkPtr = mo.latLng;
				iw.setPosition(trkPtr);
				iw.open(map);
			});
			sgltrack.addListener('mouseout', function() {
				iw.close();
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			msg = 'Did not succeed in getting JSON data: ' + 
				json_filename;
			alert(msg);
			sgltrack = '';
		}
	});
	return sgltrack;
}

/**
 * This function draws all tracks for the incoming cluster object's hike object
 * 
 * @param {object} cluster_hikes The cluster object's hike object
 * @return {null}
 */
function drawClusters(cluster_index) {
	color = 0;
	hikesobj = CL[cluster_index].hikes;
	hikesobj.forEach(function(hikeobj) {
		if (tracks[hikeobj.indx] !== '') {
			var trkfile = '../json/' + tracks[hikeobj.indx];
			let iw = '<div id="iwCH">' + hikeobj.name + '<br />Length: ' +
				hikeobj.lgth + ' miles<br />Elev Chg: ' + hikeobj.elev +
				'<br />Difficulty: ' + hikeobj.diff + '</div>';
			drawTrack(trkfile, iw, colors[color++]);
			if (color > colors.length) { color = 0; } // recylce colors when large
		}
	});
}
// /////////////////////////  END TRACK DRAWING  ///////////////////////////

// //////////////////////////  GEOLOCATION CODE ////////////////////////////
function setupLoc() {
	if (navigator.geolocation) {
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
