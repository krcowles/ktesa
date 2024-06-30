/// <reference path='./map.d.ts' />
declare var NM: NM[];
declare var appMode: string;
declare function positionFavToolTip(div: JQuery<HTMLElement>, like: JQuery<HTMLElement>): void;
declare function IdTableElements(bounds: string, zooms: boolean): void;
declare function formTbl(hikeobjs: NM[]): void;
/**
 * @fileoverview Set up a full page map showing the Favorites selected
 * by the user
 * 
 * @author Ken Cowles
 * @version 2.0 Typescripted, some type errors corrected
 * @version 3.0 Updated for compatibility with side table that shows previews
 * @version 3.1 Changed <a> links to open new tab
 * @version 3.2 Added link to page on track hover
 * @version 4.0 New GoogleMap marker type (AdvancedMarkerElement)
 */
var map: google.maps.Map;
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000', '#FFFF00']
var $fullScreenDiv: JQuery; // for google maps full screen mode
var $map = $('#map');
var mapEl: HTMLElement = <HTMLElement> $map.get(0);
var mapht: number;
var maxlat = 0;    // north
var maxlng = -180; // east
var minlat = 90;   // south
var minlng = 0;    // west
var nht = $('#nav').height() as number;
var lht = $('#logo').height() as number;
var navHt = nht + lht;
var zoom_level: number;
var zoomThresh = 13;

/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible.
 */
const initDivParms = () => {
	var wht = $(window).height() as number;
	mapht = wht - navHt;
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

/**
 * This function simply locates the geolocation symbol on the page
 */
function locateGeoSym() {
	var winht = navHt + mapht - 80;
	var mapwd = $('#map').width() as number - 120;
	$('#geoCtrl').css('top', winht);
	$('#geoCtrl').css('left', mapwd);
	return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);

// icons for geolocation:
var smallGeo = '../images/starget.png';
var medGeo = '../images/purpleTarget.png';
var lgGeo = '../images/ltarget.png';

var locaters: MarkerIds = []; // global used to popup info window on map when hike is searched
formTbl(NM);
/**
 * Create the NM hikes marker data array (there are no cluster markers
 * on this page). The array is mapped into markers for the markerClusterer
 */
const getIcon = (no_of_hikes:number) => {
	let icon = "../images/pins/hike" + no_of_hikes + ".png";
	return icon;
};
const nm_marker_data = [] as Marker_Data[];
NM.forEach(function(hikeobj) {
	var mrkr_loc = hikeobj.loc;
	var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
			+ hikeobj.indx + '" target="_blank">' + hikeobj.name + '</a><br />';
		iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
		iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
		iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
		iwContent += '<a href="' + hikeobj.dirs + '">Directions</a></div>';
	const nm_icon = document.createElement("IMG") as HTMLImageElement;
	nm_icon.src = "../images/pins/greennm.png";
	var nm_title = hikeobj.name;
	var nm_marker = {position: mrkr_loc, iw_content: iwContent,
			icon: nm_icon, title: nm_title};
	nm_marker_data.push(nm_marker)
});

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
var mapdone = $.Deferred();
/**
 * The google maps callback function to initialize the map
 * 
 * @return {null}
 */
function initMap():void {
	var nmCtr = {lat: 34.450, lng: -106.042};
	map = new google.maps.Map(mapEl, {
		center: nmCtr,
		zoom: 7,
		mapId: "39681f98dcd429f8",  // vector map; all styling
		// optional settings:
		isFractionalZoomEnabled: true,
		zoomControl: true,
		scaleControl: true,
		fullscreenControl: true,
		streetViewControl: false,
		rotateControl: false,
	});
	new google.maps.KmlLayer({
		url: "https://nmhikes.com/maps/NM_Borders.kml",
		map: map
	});
	mapdone.resolve();

	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	const infoWindow = new google.maps.InfoWindow({
		content: "",
		disableAutoPan: true,
		maxWidth: 400
	});
	const markers = nm_marker_data.map((mrkr_data: Marker_Data) => { // create array of markers
		const position = mrkr_data.position as GPS_Coord;
		const nm_icon = document.createElement("IMG") as HTMLImageElement;
		nm_icon.src = "../images/pins/rednm.png";
		const nm_title = mrkr_data.title;
		// THE MARKER:
		const marker = new google.maps.marker.AdvancedMarkerElement({
		  position,
		  content: nm_icon,
		  title: nm_title
		});
		// MARKER SEARCH
		const srchmrkr: MarkerId = {hikeid: mrkr_data.title, clicked: false, pin: marker};
		locaters.push(srchmrkr);
		const itemno = locaters.length -1;
		// CLICK ON MARKER:
		marker.addListener("click", () => {
			zoom_level = map.getZoom() as number;
			// newBounds is true if only a center change with no follow-on zoom
			// this statement must precede the setCenter cmd.
			window.newBounds = zoom_level >= zoomThresh ? true : false;
			map.setCenter(mrkr_data.position);
			if (!window.newBounds) {
				map.setZoom(zoomThresh);
			}
			locaters[itemno].clicked = true;
			infoWindow.setContent(mrkr_data.iw_content);
			infoWindow.open(map, marker);
		});
		// INFO WINDOW CLOSE:
		infoWindow.addListener('closeclick', function() {
			locaters[itemno].clicked = false;
		});
		return marker;
	});
	// /////////////////////// Marker Grouping /////////////////////////
	let clusterer_opts: MarkerOpts = {
		gridSize: 50,
		maxZoom: 12,
		averageCenter: true,
		zoomOnClick: true
	};
	new markerClusterer.MarkerClusterer({ markers, map, clusterer_opts });

	// IdTableElements must be called in order to initiate the side table creation
	var idle = google.maps.event.addListener(map, 'idle', function () {
		var perim = String(map.getBounds());
		IdTableElements(perim, true);  // kicks off 'formTbl'
	});
	map.addListener('bounds_changed', function() {
		google.maps.event.trigger(map, 'resize');
	});
	return;
}  // end of initMap()
// ////////////////////// END OF MAP INITIALIZATION  /////////////////////////////

// collect mouseover data for tracks; initialize arrow holding info
var trackdat: string[] = [];
for (let i=0; i<tracks.length; i++) {
	trackdat[i] = '';
}
NM.forEach(function(hobj, indx) {
	trackdat[indx] = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx=' + 
		hobj.indx + '" target="_blank">' + hobj.name + '</a><br />Length: ' +
		hobj.lgth + ' miles<br />Elev Chg: ' + hobj.elev +
		'<br />Difficulty: ' + hobj.diff + '</div>';
});

// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile: string; // name of the JSON file to be read in
var geoOptions: geoOptions = { enableHighAccuracy: true };

// deferred wait for map to get initialized
$.when( mapdone ).then(drawTracks).then(function() {
	$fullScreenDiv = $map.children('div:first');
});

/**
 * Draw tracks for each of the favorites
 */
function drawTracks() {
	let trkcolor = 0;
	var promises:JQueryDeferred<void>[] = [];
	tracks.forEach(function(fname, indx) {
		if (fname !== '') {
			let trackdef:JQueryDeferred<void> = $.Deferred();
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
 */
function drawTrack(jsonfile: string, color: string, ptr: number, def: JQuery.Deferred<void>) {
	$.ajax({
		dataType: "json",
		url: jsonfile,
		success: function(trackDat) {
			var json_track = trackDat['trk']
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
		error: function(_jqXHR, _textStatus, _errorThrown) {
			if (appMode === 'development') {
				var newDoc = document.open();
				newDoc.write(_jqXHR.responseText);
				newDoc.close();
			}
			else { // production
				var msg = "An error has occurred: " +
					"We apologize for any inconvenience\n" +
					"The webmaster has been notified; please try again later";
				alert(msg);
				var ajaxerr = `Trying to access json file: ${jsonfile};\n` +
					`Error text: ${_textStatus}; Error: ${_errorThrown}\n` +
					`jqXHR: ${_jqXHR.responseText}`;
				var errobj = { err: ajaxerr };
				$.post('../php/ajaxError.php', errobj);
			}
			def.reject();
		}
	});
	return;
} // end drawTrack
// /////////////////////// END OF HIKE TRACK DRAWING /////////////////////

// ////////////////////////////  GEOLOCATION CODE //////////////////////////
/**
 * Locate the user on the map
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
			new google.maps.Marker({
				position: newWPos,
				map: map,
				icon: "../images/currentLoc.png"
			});
			map.setCenter(newWPos);
			var currzoom = map.getZoom() as number;
			if (currzoom < 13) {
				map.setZoom(13);
			}
		} // end of watchSuccess function
		function error(eobj: any) {
			let msg = '<p>Error in get position call: code ' + eobj.code + '</p>';
			window.alert(msg);
		}
	} else {
		window.alert('Geolocation not supported on this browser');
	}
}
// //////////////////////  MAP FULL SCREEN DETECT  //////////////////////
$(document).on(
	'webkitfullscreenchange mozfullscreenchange fullscreenchange',
	function() {
		let thisMapDoc = <MapDoc>document;
		var isFullScreen:boolean = thisMapDoc.fullScreen ||
			thisMapDoc.mozFullScreen ||
			thisMapDoc.webkitIsFullScreen;
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
	let newWinWidth = window.innerWidth;
	let mapWidth = Math.round(0.72 * newWinWidth);
	let tblWidth = newWinWidth - (mapWidth + 3); // 3px = adjustWidth
	initDivParms();
	$map.css('width', mapWidth + 'px');
	$('#sideTable').css('width', tblWidth + 'px');
	locateGeoSym();
	$('.like').each(function() {
		// apparently don't need positionFavToolTip for fav page
	});
	google.maps.event.trigger(map, "resize");
});
// //////////////////////////////////////////////////////////////
