/// <reference path="./map.d.ts" />
/**
 * @fileoverview This routine initializes the google map to view the state
 * of New Mexico, places markers on hike locations, and clusters the markers
 * together displaying the number of markers in the group. It also draws hike
 * tracks when zoomed in, and then also when panned. The script relies on the
 * externally supplied lib 'markerclusterer.js'. That lib was modified slightly
 * by adding a line specifying the state of boolean 'newBounds' to prevent
 * duplicate calls to form a side table (see Pan and Zoom handlers below).
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Responsive design intro (new menu, etc.)
 * @version 1.1 Typescripted
 * @version 2.0 Rework asynchronous map handlers per map.ts
 */

/**
 * INITIALIZATION OF PAGE & GLOBAL DEFINITIONS
 */
const zoomThresh = 13;  // Default zoom level for drawing tracks
// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
const colors = [
	'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
var geoOptions: geoOptions = { enableHighAccuracy: true };

//globals
var map: google.maps.Map;
var $fullScreenDiv: JQuery; // Google's hidden inner div when clicking on full screen mode
var $map: JQuery = $('#map');
var mapht: number;
// track vars
var drawnHikes: number[] = [];     // hike numbers which have had tracks created
var drawnTracks: HikeTrackObj[] = [];    // array of objects: {hike:hikeno , track:polyline}
var zoomedHikes: [number[], string[], string[]];
var zoomdone: JQuery.Deferred<void>;
// globals to register when a zoom needs to call highlightTrack
var applyHighlighting = false;
var hilite_obj: Hilite_Obj = {};     // global object holding hike object & marker type
var hilited: google.maps.Polyline[] = [];
var zoom_level: number;
// map event handler global used to prevent repeatitive event triggers when panning
var panning = false;

// Custom tick mark for map tracks
var mapTick = {
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};
var trail = "Welcome!";
$('#ctr').text(trail);
// position searchbar
let navheight = <number>$('nav').height();
let logoheight = <number>$('#logo').height();
let srchtop = navheight + 16 + logoheight + 14; // 16px padding on navbar
$('#search').css({
	top: srchtop,
	left: '40px'
});
/**
 * This function positions the geosymbol in the bottom right corner of the map,
 * left of the google map zoom control 
 */
function locateGeoSym() {
	let winht = window.innerHeight - 64;
	let mapwd = <number>$('#map').width() - 80;
	$('#geoCtrl').css({
		top: winht,
		left: mapwd
	});
	return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);

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
const getIcon = (no_of_hikes: number) => {
	let icon = "../images/pins/hike" + no_of_hikes + ".png";
	return icon;
};

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
function initMap() {
	google.maps.Marker.prototype.clicked = false;  // used in sideTables.js
	var clustererMarkerSet: google.maps.Marker[] = [];
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

	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	CL.forEach(function(clobj) {
		AddClusterMarker(clobj.loc, clobj.group, clobj.hikes, clobj.page);
	});
	NM.forEach(function(nmobj) {
		AddHikeMarker(nmobj);
	});

	// Cluster Markers:
	function AddClusterMarker(location: GPS_Coord, group: string,
		clhikes: NM[], page: number) {
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
		
		let iwContent = '<div id="iwCH">';
		if (page > 0) {
			let link = "hikePageTemplate.php?clus=y&hikeIndx=";	
			iwContent += '<br /><a href="' + link + page + '">' + group + '</a>';
		} else {
			iwContent += '<br />' + group;
		}
		let link = "responsivePage.php?hikeIndx=";
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
			marker.clicked = false;
		});
		marker.addListener( 'click', function() {
			zoom_level = map.getZoom();
			// newBounds is true if only a center change and no follow-on zoom
			window.newBounds = zoom_level >= zoomThresh ? true : false;
			map.setCenter(location);
			if (!window.newBounds) {
				map.setZoom(zoomThresh);
			}
			iw.open(map, this);
			marker.clicked = true; // marker prototype property
		});
	}

	// Normal Hike Markers
	function AddHikeMarker(hikeobj: NM) {
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
		var iwContent = '<div id="iwNH"><a href="responsivePage.php?hikeIndx='
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
			zoom_level = map.getZoom();
			map.setCenter(hikeobj.loc);
			// newBounds is true if only a center change with no follow-on zoom
			window.newBounds = zoom_level >= zoomThresh ? true : false;
			if (!window.newBounds) {
				map.setZoom(zoomThresh);
			}
			iw.open(map, this);
			marker.clicked = true; // marker prototype property
		});
	}


	// /////////////////////// Marker Grouping /////////////////////////
	new MarkerClusterer(map, clustererMarkerSet,
		{
			imagePath: '../images/markerclusters/m',
			gridSize: 50,
			maxZoom: 12,
			averageCenter: true,
			zoomOnClick: true
		});

	// //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
	/**
     * NOTE: Loading the map on page load/reload causes an initial 'center_changed'
	 * AND 'zoom_changed' event to occur. The 'center_changed' occurs first. Map event
	 * trigger code has been arranged to call setCenter before setZoom in each case,
	 * hence all map events (except for manual zoom) first trigger the 'center_changed'
	 * event. When the global variable 'window.newBounds' is false, associated activity (in this
	 * case, the drawing of tracks), will be determined by the 'zoom_changed' handler.
	 * When 'window.newBounds' is true, and 'center_changed' occurs, the associated
	 * activity will be handled by the 'center_changed' handler, as a 'zoom_changed'
	 * will not occur thereafter. NOTE: in each case, the setIdleListener is called
	 * only once, and that function determines whether or not to draw tracks.
     */

	/**
	 * PANNING: a 'center_changed' event will occur repeatedly, due to the very fast
	 * processing time of that event. For that reason, a variable called 'panning' is set
	 * to prevent the 'center_changed' listener from acting. When 'drag_end' occurs, 
	 * it will set 'panning' false, and let the setIdleListener function determine whether
	 * or not to execute associated activity (track drawing).
	 */
	 map.addListener('dragstart', function() {
		panning = true;
	});
	map.addListener('dragend', function() {
		setIdleListener(); // listener determines if tracks should be drawn
		panning = false;
	});

	map.addListener('center_changed', function() {
		if (panning) {
			return;
		} else {
			if (!window.newBounds) { // let idle listener determine track drawing
				setIdleListener(); 
			} // else zoom will handle it; setIdleListener will be called only once
		} 
	});
	map.addListener('zoom_changed', function() {
		setIdleListener();  // always
	});
	/**
	 * The time to update the tracks, if needed, is when any of the events has completed
	 * and the map has returned to an idle state.
	 */
	function setIdleListener() {
		var idle = google.maps.event.addListener(map, 'idle', function () {
			var curZoom = map.getZoom();
			if (curZoom >= zoomThresh) {	
				var perim = String(map.getBounds());
				zoomedHikes = tracksInBounds(perim);
				if (zoomedHikes[0].length > 0) {
					$.when(
						zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])
					).then(function() {
						if (applyHighlighting) {
							restoreTracks();
							highlightTracks();
						}
						google.maps.event.removeListener(idle);
					});
				}
			} else {
				google.maps.event.removeListener(idle);
			}			
		});
}
	
}
// ////////////////////// END OF MAP INITIALIZATION  ///////////////////////

// ///////////////////////////  TRACK DRAWING  /////////////////////////////
/**
 * When there is a pan or zoom, identify tracks that should be displayed
 */
function tracksInBounds(boundsStr: string):[number[],string[],string[]] {
	var singles: number[] = [];        // individual hike nos
	var hikeInfoWins: string[] = [];   // info window content for each hikeno in singles
    var trackColors: string[] = [];    // for clusters, tracks get unique colors
	var max_color = colors.length - 1; // cycle through colors in var
    // Define north, south, east,west
    var beginA = boundsStr.indexOf('((') + 2;
    var leftParm = boundsStr.substring(beginA,boundsStr.length);
    var beginB = leftParm.indexOf('(') + 1;
    var rightParm = leftParm.substring(beginB,leftParm.length);
    var south = parseFloat(leftParm);
    var north = parseFloat(rightParm);
    var westIndx = leftParm.indexOf(',') + 1;
    var westStr = leftParm.substring(westIndx,leftParm.length);
    var west = parseFloat(westStr);
    var eastIndx = rightParm.indexOf(',') + 1;
    var eastStr = rightParm.substring(eastIndx,rightParm.length);
    var east = parseFloat(eastStr);

	CL.forEach(function(clus) {
        var color = 0;
        var link = "responsivePage.php?hikeIndx=";
        if (clus.page > 0) { // then this is a 'Cluster Page'
            link = "responsivePage.php?clus=y&hikeIndx=";
        }
        clus.hikes.forEach(function(hike) {
            let lat = hike.loc.lat;
            let lng = hike.loc.lng;
            if (lng <= east && lng >= west && lat <= north && lat >= south) {
				let cliw = '<div id="iwCH"><a href="' + link + hike.indx + 
					'" target="_blank">' + hike.name + '</a><br />Length: ' +
					hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
					'<br />Difficulty: ' + hike.diff + '</div>';
				singles.push(hike.indx);
				hikeInfoWins.push(cliw);
				trackColors.push(colors[color++]);
				if (color > max_color) { // rotate through colors
					color = 0;
				}
            }
        });
    });
    NM.forEach(function(hike) {
        let lat = hike.loc.lat;
        let lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
			let nmiw = '<div id="iwNH"><a href="responsivePage.php?hikeIndx=' +
				hike.indx + '" target="_blank">' + hike.name + '</a><br />Length: ' +
				hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
				'<br />Difficulty: ' + hike.diff + '</div>';
			singles.push(hike.indx);
			hikeInfoWins.push(nmiw);
			trackColors.push(colors[0]);
		}
    });
    return [singles, hikeInfoWins, trackColors];
 
}
/**
 * This file will create tracks for the input arrays of hike objects and clusters.
 * If a track has already been created, it will not be created again.
 */
function zoom_track(hikenos: number[], infoWins: string[], trackcolors: string[]) {
	var promises: JQuery.Deferred<void>[] = [];
	for (let i=0,j=0; i<hikenos.length; i++,j++) {
		if (!drawnHikes.includes(hikenos[i])) {
			if (tracks[hikenos[i]] !== '') {
				let trackDef = $.Deferred();
				promises.push(trackDef);
				let trackfile = '../json/' + tracks[hikenos[i]];
				drawnHikes.push(hikenos[i]);
				if (j === trackcolors.length) {
					j = 0;  // rollover colors when # of tracks > # of colors
				}
				drawTrack(trackfile, infoWins[i], trackcolors[j], hikenos[i], trackDef);
			}
		}
	}
	return $.when.apply($, promises);
}

/**
 * This function draws the track for the hike object
 */
function drawTrack(json_filename: string, info_win: string, color:string,
	hikeno: number, deferred: JQueryDeferred<void>) {
	let sgltrack: google.maps.Polyline;
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
			let newtrack = {hike: hikeno, track: sgltrack};
			drawnTracks.push(newtrack);
			deferred.resolve();
		},
		error: function() {
			let msg = 'Did not succeed in getting track data: ' + 
				json_filename;
			alert(msg);
			deferred.reject();
		}
	});
	return;
}
/**
 * This function emphasizes the hike track(s) when the user searches for
 * a hike with the search bar, or zooms to it via the zoom icon in the 
 * side table. If the track has not been drawn yet, it is drawn.
 * NOTE: A javascript anomaly - passing in a single object in an array
 * results in the function receiving the object, but not as an array.
 * Hence a 'type' identifier is used here
 */
function highlightTracks() {
    if (!$.isEmptyObject(hilite_obj)) {
        if (hilite_obj.type === 'cl') { // object is an array of objects
            // wait for tracks to be drawn, if not already...
            let cluster = <Normals>hilite_obj.obj;
            cluster.forEach(function(track) {
                let polyno = track.indx;
                for (let k=0; k<drawnTracks.length; k++) {
                    if (drawnTracks[k].hike == polyno) {
                        let polyline = drawnTracks[k].track;
                        polyline.setOptions({
                            strokeWeight: 4,
                            strokeColor: '#FFFF00',
                            strokeOpacity: 1,
                            zIndex: 10
                        });
                        hilited.push(polyline);
                        break;
                    }
                }
            });
        } else { // mrkr === 'nm'; object is a single object
            // wait for tracks to be drawn, if not already...
            let nmobj = <NM>hilite_obj.obj;
			let polyno = nmobj.indx;
            for (let k=0; k<drawnTracks.length; k++) {
                if (drawnTracks[k].hike == polyno) {
                    let polyline = drawnTracks[k].track;
                    polyline.setOptions({
                        strokeWeight: 4,
                        strokeColor: '#FFFF00',
                        strokeOpacity: 1,
                        zIndex: 10
                    });
                    hilited.push(polyline);
                    break;
                }
            }
        }
        hilite_obj = {};
    }
}

/**
 * Undo any previous track highlighting
 */
function restoreTracks() {
    for (let n=0; n<hilited.length; n++) {
        hilited[n].setOptions({
            strokeOpacity: 0.60,
            strokeWeight: 3,
            zIndex: 1
        });
    }
    return;
}
// /////////////////////////  END TRACK DRAWING  ///////////////////////////

// //////////////////////////  GEOLOCATION CODE ////////////////////////////
function setupLoc() {
	navigator.geolocation.getCurrentPosition(success, error, geoOptions);
	function success(pos: any) {
		var geoPos = pos.coords;
		var geoLat = geoPos.latitude;
		var geoLng = geoPos.longitude;
		var newWPos = {lat: geoLat, lng: geoLng };
		new google.maps.Marker({
			position: newWPos,
			map: map,
			icon: "../images/currentLoc.png"
		});
		var currzoom = map.getZoom();
		window.newBounds = currzoom >= zoomThresh ? true : false;
		map.setCenter(newWPos);
		if (!window.newBounds) {
			map.setZoom(zoomThresh);
		}
	} // end of watchSuccess function
	function error(eobj: GeoErrorObject) {
		let msg = 'Error retrieving position; Code: ' + eobj.code;
		window.alert(msg);
	}
}

// //////////////////////  MAP FULL SCREEN DETECT  //////////////////////
$(document).bind(
	'webkitfullscreenchange mozfullscreenchange fullscreenchange',
	function() {
		let thisMapDoc = <MapDoc>document;
		var isFullScreen: boolean = thisMapDoc.fullScreen ||
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
	locateGeoSym();
});

// //////////////////////////////////////////////////////////////
