/// <reference path='./map.d.ts' />
/**
 * @fileoverview This routine initializes the google map to view the state
 *		of New Mexico, places markers on hike locations, and clusters the markers
 * 		together displaying the number of hikes in the group. It also draws hike
 * 		tracks when zoomed in, and continues when panned. The script relies on the
 * 		externally supplied lib 'markerclusterer.js'. A modification was added to 
 *      that file to add a 'cluster_click' boolean.
 * @author Ken Cowles
 * @version 3.0 Added Cluster Page compatibility (removes indexPageTemplate links)
 * @version 4.0 Typescripted, with some type errors corrected
 * @version 5.0 Reworked to synchronize with the new thumbnail loading process
 * @version 6.0 Change from old panel design to bootstrap navbar design
 * @version 7.0 Rework asynchronous map handlers to better control behavior; increase 
 * 				number of track colors available.
 */
const zoomThresh = 13;
var initialLoad  = true;

// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
var colors = [
	'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
var geoOpts: geoOptions = { enableHighAccuracy: true };

// need to be global:
var map: google.maps.Map;
var $fullScreenDiv: JQuery; // Google's hidden inner div when clicking on full screen mode
var $map: JQuery = $('#map');
var mapht: number;
// track vars
var drawnHikes: number[] = [];     // hike numbers which have had tracks created
var drawnTracks: HikeTrackObj[] = [];    // array of objects: {hike:hikeno , track:polyline}
var zoomedHikes: [number[], string[], string[]];
// globals to register when a zoom needs to call highlightTrack
var applyHighlighting: boolean = false;
var hiliteObj: Hilite_Obj = {};     // global object holding hike object & marker type
var hilited: google.maps.Polyline[] = [];
var zoom_level: number;
// map event handler globals for determining resulting action in handler
var panning         = false;
var clickedOnMarker = false;
var clickedOnGroup  = false;
// setting space for ktesaPanel
var panel = <number>$('#nav').height() + <number>$('#logo').height();

/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible.
 */
const initDivParms = () => {
	mapht = <number>$(window).height() - panel;
	$map.css('height', mapht + 'px');
	$('#adjustWidth').css('height', mapht + 'px');
	$('#sideTable').css('height', mapht + 'px');
	return;
}
initDivParms();

// Custom tick mark for map tracks
var mapTick = {
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Black',
    strokeWeight: 2
};

/**
 * This function places the geopositioning symbol in the lower right corner of the map
 */
function locateGeoSym() {
	var fromTop = panel + mapht - 84;
	var fromLft = <number>$('#map').width() - 120;
	$('#geoCtrl').css('top', fromTop);
	$('#geoCtrl').css('left', fromLft);
	return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);

var geoIcon:string = "../images/currentLoc.png";

/**
 * Use the arrays passed in to the home page by php: one for each type 
 * of marker to be displayed (Clustered, Normal):
 * 		CL Array: Clustered hike pages
 * 		NM Array: Normal hike pages
 * And one for creating tracks:
 * 		tracks Array: ordered list of json file names
 */
var locaters: MarkerIds = []; // global used to popup info window on map when hike is searched

/**
 * A simple function which correlates the number of hikes in a group to its icon
 */
const getIcon = (no_of_hikes:number) => {
	let icon = "../images/pins/hike" + no_of_hikes + ".png";
	return icon;
};

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
function initMap() {
	var clustererMarkerSet:google.maps.Marker[] = [];
	var nmCtr = {lat: 34.450, lng: -106.042};
	var json_style_array = <google.maps.MapTypeStyle[]>[
        { "featureType":"poi", "stylers":[{ "visibility": "off" }] }
    ];
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
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		styles: json_style_array
	});
    new google.maps.KmlLayer({
		url: "https://nmhikes.com/maps/NM_Borders.kml",
		map: map
	});

	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	CL.forEach(function(clobj) {
		AddClusterMarker(clobj.loc, clobj.group, clobj.hikes, clobj.page);
	});
	NM.forEach(function(nmobj) {
		AddHikeMarker(nmobj);
	});
	var iwContent:string;

	// Cluster Markers:
	function AddClusterMarker(location:GPS_Coord, group:string, clhikes:NM[], page:number) {
		let hikecnt = clhikes.length;
		let clicon = getIcon(hikecnt);
		let marker = new google.maps.Marker({
			position: location,
			map: map,
			icon: clicon,
			title: group
		});
		let srchmrkr:MarkerId = {hikeid: group, clicked: false, pin: marker};
		locaters.push(srchmrkr);
		let itemno = locaters.length -1;
		clustererMarkerSet.push(marker);

		iwContent = '<div id="iwCH">';
		let link: string;
		if (page > 0) {
			link = "hikePageTemplate.php?clus=y&hikeIndx=";	
			iwContent += '<br /><a href="' + link + page + '">' + group + '</a>';
		} else {
			iwContent += '<br />' + group;
		}
		link = "hikePageTemplate.php?hikeIndx=";
		clhikes.forEach(function(clobj) {
			iwContent += '<br /><a href="' + link + clobj.indx + '">' +
				clobj.name + '</a>';
			iwContent += ' Lgth: ' + clobj.lgth + ' miles; Elev Chg: ' + 
				clobj.elev + ' ft; Diff: ' + clobj.diff;
		});
		let iw = new google.maps.InfoWindow({
				content: iwContent,
				maxWidth: 600
		});
		iw.addListener('closeclick', function() {
			locaters[itemno].clicked = false;
		});
		marker.addListener( 'click', function() {
			clickedOnGroup = true;
			if (typeof loadSpreader !== "undefined") {
				clearInterval(loadSpreader);
				loadSpreader = void 0; // forces to "undefined"
			}
			// clicking on a marker changes the position, but not quite to the marker's loc
			map.setCenter(location);
			zoom_level = map.getZoom();
			if (zoom_level < zoomThresh) {
				map.setZoom(zoomThresh);
			}
			// hence, two center_change events will take place consecutively
			iw.open(map, this);
			locaters[itemno].clicked = true;
		});
	}

	// Normal Hike Markers
	function AddHikeMarker(hikeobj:NM) {
		let markerLoc = hikeobj.loc;
		let nmicon:string = getIcon(1);
		let utname = unTranslate(hikeobj.name);
		let marker = new google.maps.Marker({
		  position: markerLoc,
		  map: map,
		  icon: nmicon,
		  // 'title' is what is displayed on mouseover of the marker
		  title: utname   /// hikeobj.name
		});
		let srchmrkr:MarkerId = {hikeid: hikeobj.name, clicked: false, pin: marker};
		locaters.push(srchmrkr);
		let itemno = locaters.length -1;
		clustererMarkerSet.push(marker);
		// infoWin content: add data for this hike
		let iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
			+ hikeobj.indx + '">' + hikeobj.name + '</a><br />';
		iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
		iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
		iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
		iwContent += '<a href="' + hikeobj.dirs + '">Directions</a></div>';
		let iw = new google.maps.InfoWindow({
				content: iwContent,
				maxWidth: 400
		});
		iw.addListener('closeclick', function() {
			locaters[itemno].clicked = false;
		});
		marker.addListener( 'click', function() {
			clickedOnMarker = true;
			if (typeof loadSpreader !== "undefined") {
				clearInterval(loadSpreader);
				loadSpreader = void 0; // forces var to "undefined"
			}
			// clicking on a marker changes the position, but not quite to the marker's loc
			map.setCenter(markerLoc);
			zoom_level = map.getZoom();
			if (zoom_level < zoomThresh) {
				map.setZoom(zoomThresh);
			} 
			// hence, two center_change events will take place consecutively
			iw.open(map, this);
			locaters[itemno].clicked = true;
		});
	}

	// /////////////////////// Marker Grouping /////////////////////////
	let clusterer_opts: MarkerOpts = {
		imagePath: '../images/markerclusters/m',
		gridSize: 50,
		maxZoom: 12,
		averageCenter: true,
		zoomOnClick: true
	};
	new MarkerClusterer(map, clustererMarkerSet, clusterer_opts);

	// //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
	/**
     * NOTE: Loading the map on page load/reload causes an initial center_change AND
     * zoom_change event [with or without the markerclusterer.js and/or kml overlay
     * (NM Boundary on map)]; The 'center_change' occurs first.
     */

	/**
	 * PANNING: a 'center_change' event will obviously occur, so a variable called
	 * 'panning' is set to prevent the 'center_change' listener from acting. The 'center_change'
	 *  event will be triggered repeatedly but will not affect the pan.
	 */
	map.addListener('dragstart', function() {
		panning = true;
		if (typeof loadSpreader !== "undefined") {
			clearInterval(loadSpreader);
			loadSpreader = void 0; // forces var to "undefined"
		}
	});
	map.addListener('dragend', function() {
		var curr_zoom = map.getZoom();
		let zoomTracks = curr_zoom >= zoomThresh ? true : false;
		var newBds = String(map.getBounds());
		zoomedHikes = IdTableElements(newBds, zoomTracks);
		if (zoomTracks && zoomedHikes[0].length > 0) {
			$.when(
				zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])
			).then(function() {
				panning = false;
			});
		} else {
			panning = false;
		}
		
	});
	/**
	 * The 'center_changed' event is utilized as the key to all map event cases since:
	 * 1. A click on any marker will shift center. This is determined by the order of
	 *    code execution as defined in the marker listeners. [NOTE: even if the marker
	 *    were already 'dead center', the click would shift it out then back again];
	 * 2. A click on any clusterer (see markerclusterer.js) will shift center as a zoom 
	 *    will occur [else there would be no clusterers showing]
	 */
	map.addListener('center_changed', function() {
		if (panning) {
			return;
		} else {
			if (typeof loadSpreader !== "undefined") {
				clearInterval(loadSpreader);
				loadSpreader = void 0; // forces var to "undefined"
			}
			setIdleListener();
		}
	});
	/**
	 * Manual zoom will not change the center, but the side table needs to be regenerated;
	 * If the zoom is a post 'center_change' event, only flags are reset, no table generation.
	 */
	map.addListener('zoom_changed', function() {
		if (!initialLoad && !cluster_click && !clickedOnGroup && !clickedOnMarker) {
			// this is a manual zoom after page load/reload
			if (typeof loadSpreader !== "undefined") {
				clearInterval(loadSpreader);
				loadSpreader = void 0; // forces var to "undefined"
			}
			setIdleListener();
		} else {
			initialLoad     = false;
			cluster_click   = false;
			clickedOnGroup  = false;
			clickedOnMarker = false;
		}	
	});
	/**
	 * The time to update the side table and tracks is when any of the events has completed
	 * and the map has returned to an idle state. This function performs the idle ops,
	 * which include re-generating the side table for the new bounds, and if the zoom
	 * threshold is active, then draw any newly included tracks. Note that when done,
	 * the listener is removed.
	 */
	function setIdleListener() {
		var idle = google.maps.event.addListener(map, 'idle', function () {
			// wait for markerclusterer.js to draw clusters
			var curZoom = map.getZoom();
			var zoomTracks = curZoom >= zoomThresh ? true : false;	
			var perim = String(map.getBounds());
			zoomedHikes = IdTableElements(perim, zoomTracks);
			if (zoomTracks && zoomedHikes[0].length > 0) {
				$.when(
					zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])
				).then(function() {
					if (applyHighlighting) {
						restoreTracks();
						highlightTracks();
					}
					google.maps.event.removeListener(idle);
				});
			} else {
				google.maps.event.removeListener(idle);
			}			
		});
	}
}
// ////////////////////// END OF MAP INITIALIZATION  ///////////////////////

// ///////////////////////////  TRACK DRAWING  /////////////////////////////
/**
 * This file will create tracks for the input arrays of hike objects and clusters.
 * If a track has already been created, it will not be created again.
 */
function zoom_track(hikenos:number[], infoWins:string[], trackcolors:string[]) {
	var promises:JQueryDeferred<void>[] = [];
	for (let i=0,j=0; i<hikenos.length; i++,j++) {
		if (!drawnHikes.includes(hikenos[i])) {
			if (tracks[hikenos[i]] !== '') {
				let sgldef:JQueryDeferred<void> = $.Deferred<void>();
				promises.push(sgldef);
				let trackfile = '../json/' + tracks[hikenos[i]];
				drawnHikes.push(hikenos[i]);
				if (j === trackcolors.length) {
					j = 0;  // rollover colors when # of tracks > # of colors
				}
				drawTrack(trackfile, infoWins[i], trackcolors[j], hikenos[i], sgldef);
			}
		}
	}
	return $.when.apply($, promises);
}

/**
 * This function draws the track for the hike object
 */
function drawTrack(json_filename:string, info_win:string, color:string,
		hikeno:number, deferred:JQueryDeferred<void>) {
	let sgltrack:google.maps.Polyline;
	mapTick.fillcolor = color;
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
				strokeOpacity: .6,
				strokeWeight: 3,
				zIndex: 1
			});
			sgltrack.setMap(map);
			// create the mouseover text:
			let iw = new google.maps.InfoWindow({
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
			let newtrack:HikeTrackObj = {hike: hikeno, track: sgltrack};
			drawnTracks.push(newtrack);
			deferred.resolve();
		},
		error: function() {
			let msg:string = 'Did not succeed in getting track data: ' + 
				json_filename;
			alert(msg);
			let usererr = "User couldn't retrieve json file: " +
				json_filename;
			let errobj = {err: usererr};
			$.post('../php/ajaxError.php', errobj);
			deferred.reject();
		}
	});
	return;
}

// /////////////////////////  END TRACK DRAWING  ///////////////////////////

// //////////////////////////  GEOLOCATION CODE ////////////////////////////
/**
 * Drop the geolocation symbol on the user's current location
 */
function setupLoc() {
		navigator.geolocation.getCurrentPosition(success, error, geoOpts);
		function success(_pos:any) {
			var geoPos = _pos.coords;
			var geoLat = geoPos.latitude;
			var geoLng = geoPos.longitude;
			var newWPos = {lat: geoLat, lng: geoLng };
			new google.maps.Marker({
				position: newWPos,
				map: map,
				icon: geoIcon,
			});
			map.setCenter(newWPos);
			var currzoom = map.getZoom();
			if (currzoom < zoomThresh) {
				map.setZoom(zoomThresh);
			}
		}
		function error(eobj: GeoErrorObject) {
			let msg = 'Error retrieving position; Code: ' + eobj.code;
			window.alert(msg);
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
		let $icon = $(this);
        let $tooldiv = $icon.parent().prev();
        positionFavToolTip($tooldiv, $icon);
	});
	google.maps.event.trigger(map, "resize");
});

// //////////////////////////////////////////////////////////////
