/// <reference path='./map.d.ts' />
interface CustomMarker extends google.maps.Marker {
	hikes?: number;
}
interface CustomAdvancedMarker extends google.maps.marker.AdvancedMarkerElement {
	hikes?: number;
}
interface ClustererForRender extends CustomMarker{
	markers: google.maps.Marker[];
	_position: GPS_Coord;
}
/**
 * @fileoverview This routine initializes the google map to view the state
 *		of New Mexico, places markers on hike locations, and clusters the markers
 * 		together, displaying the number of hikes in each cluster. It also draws hike
 * 		tracks when zoomed in, and afterwards when panned. The script relies on the
 * 		externally supplied lib 'markerclusterer.js'. That lib was modified slightly
 *      by adding a line specifying the state of boolean 'newBounds' to prevent
 *      duplicate calls to form a side table (see Pan and Zoom handlers below).
 * @author Ken Cowles
 *
 * @version 8.0 Major mods to improve side table formation when multiple map events occur
 * @version 9.0 New GoogleMap marker type (AdvancedMarkerElement)
 */

var markers: google.maps.marker.AdvancedMarkerElement[];
const initialValue = 0;
const zoomThresh = 13;  // Default zoom level for drawing tracks
// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
const colors = [
	'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
const geoOpts: geoOptions = { enableHighAccuracy: true };

// global vars:
var appMode = $('#appMode').text() as string;
var map: google.maps.Map;
var $fullScreenDiv: JQuery; // Google's hidden inner div when clicking on full screen mode
var $map: JQuery = $('#map');
var mapEl: HTMLElement = <HTMLElement> $map.get(0);
var mapht: number;
// track vars
var drawnHikes: number[] = [];     // hike numbers which have had tracks created
var drawnTracks: HikeTrackObj[] = [];    // array of objects: {hike:hikeno , track:polyline}
var zoomedHikes: [NM[], number[], string[], string[]];
// globals to register when a zoom needs to call highlightTrack
var applyHighlighting: boolean = false;
var hiliteObj: Hilite_Obj = {};     // global object holding hike object & marker type
var hilited: google.maps.Polyline[] = [];
var zoom_level: number;
var first_load = true;
/**
 *  'panning' global is used to prevent repetitive event triggers when panning
 *  'kill_table' informs any current side table formation to 'abort'
 */
var panning = false;
var kill_table = false;

/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible; 'panel' is also used
 * in locateGeoSymbol().
 */
var panel = <number>$('#nav').height() + <number>$('#logo').height();
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
 * Create the NM hikes marker data array and also the CL hikes marker data array
 * The arrays are mapped into markers for the markerClusterer
 */
const makeClusterLabel = (markers: CustomMarker[]) => {
    var total: number[] = [];
    markers.forEach(function(mrkr) {
        total.push(Number(mrkr.hikes));
    });
    var hike_total = total.reduce(
        (accumulator, currentValue) => accumulator + currentValue, 
        initialValue
    );
    return  String(hike_total);
}
const build_content = (count: number) => {
    const content = document.createElement("div");
    const icon = document.createElement("img");
    const mrkr_cnt = document.createElement("div");
    const mrkr_txt = document.createTextNode(String(count));
    mrkr_cnt.appendChild(mrkr_txt);
    icon.style.zIndex = "900";
    icon.src = "../images/pins/hiker_pin.png";
    content.appendChild(icon);
    content.appendChild(mrkr_cnt);
    return content;
};
const nm_marker_data = [] as NM_Marker_Data[];
NM.forEach(function(hikeobj) {
	var mrkr_loc = hikeobj.loc;
	var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
			+ hikeobj.indx + '" target="_blank">' + hikeobj.name + '</a><br />';
	iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
	iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
	iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
	iwContent += '<a href="' + hikeobj.dirs + '">Directions</a></div>';
	var nm_title = hikeobj.name;
	var nm_marker = {position: mrkr_loc, iw_content: iwContent, title: nm_title};
	nm_marker_data.push(nm_marker)
});
const cl_marker_data = [] as CL_Marker_Data[];
CL.forEach(function(clobj) {
	const mrkr_loc = clobj.loc;
	const hikecnt = clobj.hikes.length;
	let iwContent = '<div id="iwCH">';
	var link: string;
	if (clobj.page > 0) {
		link = "hikePageTemplate.php?clus=y&hikeIndx=";	
		iwContent += '<a href="' + link + clobj.page + '">' + 
			clobj.group + '</a>';
	} else {
		iwContent += clobj.group + "<br/>";
	}
	link = "hikePageTemplate.php?hikeIndx=";
	clobj.hikes.forEach(function(clobj) {
		iwContent += '<br/><a href="' + link + clobj.indx + '" target="_blank">' +
			clobj.name + '</a>';
		iwContent += ' Lgth: ' + clobj.lgth + ' miles; Elev Chg: ' + 
			clobj.elev + ' ft; Diff: ' + clobj.diff;
	});
	var cl_marker = {position: mrkr_loc, iw_content: iwContent,
		title: clobj.group, hikecnt: hikecnt};
	cl_marker_data.push(cl_marker);
});

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
function initMap() {
	//const clustererMarkerSet:google.maps.marker.AdvancedMarkerElement[] = [];
	const nmCtr = {lat: 34.450, lng: -106.042};
	var options = {
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
	};
	map = new google.maps.Map(mapEl, options);
		
    new google.maps.KmlLayer({
		url: "https://nmhikes.com/maps/NM_Borders.kml",
		map: map
	});

	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	const infoWindow = new google.maps.InfoWindow({
		content: "",
		disableAutoPan: true,
		maxWidth: 400
	});
	const nm_markers = nm_marker_data.map((mrkr_data: NM_Marker_Data) => { // create array of markers
		const position = mrkr_data.position as GPS_Coord;
		const nm_title = mrkr_data.title;
		// THE MARKER:
		const marker = new google.maps.marker.AdvancedMarkerElement({
		  position: position,
		  map: map,
		  content: build_content(1),
		  title: nm_title,
		  gmpClickable: true
		}) as CustomAdvancedMarker;
		marker.hikes = 1;
		// MARKER SEARCH
		const srchmrkr: MarkerId = {hikeid: mrkr_data.title, clicked: false, pin: marker};
		locaters.push(srchmrkr);
		const itemno = locaters.length -1;
		// CLICK ON MARKER:
		marker.addListener("click", (): void => {
			zoom_level = map.getZoom() as number;
			// newBounds is true if only a center change with no follow-on zoom
			// this statement must precede the setCenter cmd.
			window.newBounds = zoom_level >= zoomThresh ? true : false;
			map.setCenter(mrkr_data.position);
			if (!window.newBounds) {
				map.setZoom(zoomThresh);
			}
			let this_mrkr = locaters[itemno];
			this_mrkr.clicked = true;
			infoWindow.setContent(mrkr_data.iw_content);
			infoWindow.open(map, marker);
		});
		// INFO WINDOW CLOSE:
		infoWindow.addListener('closeclick', function() {
			locaters[itemno].clicked = false;
		});
		return marker;
	});
	const cl_markers = cl_marker_data.map((mrkr_data: CL_Marker_Data) => {
		const position = mrkr_data.position as GPS_Coord;
		const cl_title = mrkr_data.title;
		const hike_count = mrkr_data.hikecnt;
		// THE MARKER:
		const marker = new google.maps.marker.AdvancedMarkerElement({
			position: position,
			map: map,
		  	content: build_content(hike_count),
		  	title: cl_title,
			gmpClickable: true
		}) as CustomAdvancedMarker;
		marker.hikes = hike_count;
		// MARKER SEARCH:
		const srchmrkr: MarkerId = {hikeid: mrkr_data.title, clicked: false, pin: marker};
		locaters.push(srchmrkr);
		const itemno = locaters.length -1;
		// CLICK ON MARKER:
		marker.addListener("click", () => {
			zoom_level = map.getZoom() as number;
			// newBounds is true if only a center change and no follow-on zoom
			window.newBounds = zoom_level >= zoomThresh ? true : false;
			map.setCenter(position);
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
	markers = [...nm_markers, ...cl_markers];
	const renderer = { // must be an object, here with key 'render'
        /**
         * render( CLUSTER, stats, map) where CLUSTER 'Accessors' are bounds, count, position
         * and 'cluster' contains various properties, including _position, and markers[]
         */
        render: (cluster: ClustererForRender) => {
            const marker_label = makeClusterLabel(cluster.markers);
            return new google.maps.Marker({
                label: { 
                    text: marker_label,
                    color: "white", 
                    fontSize: "10px" },
                position: cluster._position,
                // adjust zIndex to be above other markers
                zIndex: 50 //Number(google.maps.Marker.MAX_ZINDEX), // + count,
            })
        }
    };
	// Add a marker clusterer to manage the markers.
	// Add a marker clusterer to manage the markers (maxZoom doesn't seem to hold)
    new markerClusterer.MarkerClusterer({
        markers: markers,
        map: map,
        algorithmOptions: {maxZoom: 13},
        renderer: renderer
    });
	// /////////////////////// Marker Grouping /////////////////////////
	
	//new markerClusterer.MarkerClusterer({mapMarkers, map});
	//new MarkerClusterer(map, clustererMarkerSet, clusterer_opts);

	// //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
	/**
     * NOTE: Loading the map on page load/reload causes an initial center_change AND
     * zoom_change event [with or without the markerclusterer.js and/or kml overlay
     * (NM Boundary on map)]; The 'center_change' occurs first. All map event trigger
	 * code in this script has been arranged to call setCenter() before setZoom().
	 * The 'first_load' condition invokes a simplified 'idle' listener
     */

	/**
	 * PANNING: a 'center_change' event will obviously occur, so a variable called
	 * 'panning' is set to prevent the 'center_change' listener from repeatedly
	 * responding as the pan progresses.
	 */
	map.addListener('dragstart', function() {
		kill_table = true;
		panning = true;
	});
	map.addListener('dragend', function() {
		setIdleListener('de'); // Drag End...
	});
	/**
	 * The goal is to create a side table once and only once per user-initiated
	 * map event. If a follow-on event occurs while the side table is still under
	 * construction, it will be aborted and started anew with the new bounds.
	 *                    ---- Other considerations ----
	 * When there is only a center change (not resulting from a pan event,
	 * which is handled separately), form the side table. This will happen, e.g., when
	 * the map is already zoomed in to zoomThresh level (or greater). When a zoom is to
	 * follow the center change, then let only the zoom form the side table in order 
	 * to reduce invocations of side table formation.
	 * 
	 * 1. Since page load/reload triggers a center_change & zoom, the var "newBounds"
	 *    is set false on initialization to prevent the load from invoking both
	 *    center_change and zoom invocations of the side table.
	 * 2. When completing a search in the searchbar, the "newBounds" may be set to
	 *    indicate that only a center change is occurring.
	 * 3. A click on any clusterer (see markerclusterer.js) will shift center via 
	 *    'map.fitBounds' - the bounds which were established by the clusterer and
	 *    assigned during creation, and when zoomOnClick option is 'true'. This
	 *    seems to register two consecutive 'center change/zoom's the first time
	 *    a cluster is clicked, but only one 'center change/zoom' thereafter. The
	 *    3rd party software has been modified to set the var "newBounds" false so
	 *    that only the zoom event controls the side table formation.
	 * 4. A click on any marker will shift center. This is determined by the order of
	 *    code execution as defined in the marker listeners. [NOTE: even if the marker
	 *    were already 'dead center', the click would shift it out then back again];
	 *    Note that when the zoom is already at zoomThresh or greater, the marker
	 *    click will not be followed by a zoom.
	 * 
	 * Lastly, the setIdleListener function has an argument to indicate the event
	 * invoking the function, but only the 'pan' event requires it. It was originally
	 * used to understand event synchronization.
	 */
	map.addListener('center_changed', function() {
		if (panning) {  // when panning, simply wait for the dragend event
			return;
		} else {
			if (!first_load) {
				kill_table = true;
			}
			if (window.newBounds) {
				// if a center change only, initiate side table formation
				setIdleListener('cc'); // Center Change
				window.newBounds = false;
			}
		}
	});
	/**
	 * Zoom change will always initiate the side table formation. 
	 */
	map.addListener('zoom_changed', function() {
		if (!first_load) {
			kill_table = true;
		}
		setIdleListener('zm'); // ZooM
	});
	/**
	 * NOTE: 'idle' does not mean the map is displayed!
	 * 
	 * The time to update the side table and tracks is when any of the events has
	 * completed and the map has returned to an idle state. This idle listener
	 * executes the idle ops, which include re-generating the side table for the
	 * new bounds, and if the zoom threshold is active, draw any newly included tracks.
	 */
	function setIdleListener(event_type: string) {
		if (first_load) {
			var init_idle = google.maps.event.addListener(map, 'idle', function () {
				// first load always has zoom < zoomThresh
				kill_table = false;
				first_load = false;
				var bounds = String(map.getBounds());
				var hike_result = IdTableElements(bounds, false);
				formTbl(hike_result[0]);
				google.maps.event.removeListener(init_idle);
			});
		} else {
			console.log('Idle');
			var idle = google.maps.event.addListener(map, 'idle', async function () {
				var curZoom = map.getZoom() as number;
				var zoomTracks = curZoom >= zoomThresh ? true : false;	
				var perim = String(map.getBounds());
				// in case of intervening map event:
				kill_table = false;
				zoomedHikes = IdTableElements(perim, zoomTracks);
				await formTbl(zoomedHikes[0]);
				if (zoomTracks && zoomedHikes[1].length > 0) {
					$.when(
						zoom_track(zoomedHikes[1], zoomedHikes[2], zoomedHikes[3])
					).then(function() {
						if (event_type === 'de') {
							panning = false;
						} else {
							if (applyHighlighting) {
								restoreTracks();
								highlightTracks();
							}
						}
						google.maps.event.removeListener(idle);
					});
				} else {
					if (event_type === 'de') {
						panning = false;
					}
					google.maps.event.removeListener(idle);
				}			
			});
		}
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
			// All hikes should have a json file
			let sgldef:JQueryDeferred<void> = $.Deferred<void>();
			promises.push(sgldef);
			let trackfile = "../json/pmn" + hikenos[i] + "_1.json";
			drawnHikes.push(hikenos[i]);
			if (j === trackcolors.length) {
				j = 0;  // rollover colors when # of tracks > # of colors
			}
			drawTrack(trackfile, infoWins[i], trackcolors[j], hikenos[i], sgldef);

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
		success: function(trackDat:JsonFile) {
			let track_data = trackDat["trk"];
			for (let j=0; j<track_data.length; j++) {
				let org_json = track_data[j];
				delete org_json["ele"];
			}
			sgltrack = new google.maps.Polyline({
				icons: [{
					icon: mapTick,
					offset: '0%',
					repeat: '15%' 
				}],
				path: track_data,
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
			sgltrack.addListener('mouseover', function(mo: any) {
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
		error: function(_jqXHR, _textStatus, _errorThrown) {
			if (appMode === 'development') {
				var newDoc = document.open();
				newDoc.write(_jqXHR.responseText);
				newDoc.close();
			}
			else { // production
				let msg:string = 'Did not succeed in getting track data: ' + 
					json_filename + "\nWe apologize for any inconvenience\n" +
					"The webmaster has been notified; please try again later";
				alert(msg);
				var ajaxerr = "Trying to access " + json_filename + 
					";\nError text: " + _textStatus + "; Error: " +
					_errorThrown + ";\njqXHR: " + _jqXHR.responseText;
				var errobj = { err: ajaxerr };
				$.post('../php/ajaxError.php', errobj);
			}
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
			new google.maps.marker.AdvancedMarkerElement({
				position: newWPos,
				map: map
			});
			var currzoom = map.getZoom() as number;
			window.newBounds = currzoom >= zoomThresh ? true : false;
			map.setCenter(newWPos);
			if (!window.newBounds) {
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
