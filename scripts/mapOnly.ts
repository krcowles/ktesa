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
 * @version 3.0 Support for New GoogleMap marker type (AdvancedMarkerElement)
 */

/**
 * INITIALIZATION OF PAGE & GLOBAL DEFINITIONS
 */
const hike_mrkr_icon = "../images/blue_nobg.png";
// <a href="https://www.flaticon.com/free-icons/marker" title="marker icons">Marker icons created by Vector Stall - Flaticon</a>
const clus_mrkr_icon = "../images/star8.png";
const initialValue = 0;
const zoomThresh = 13;  // Default zoom level for drawing tracks
// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
const colors = [
	'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
var geoOptions: geoOptions = { enableHighAccuracy: true };
var markers: google.maps.marker.AdvancedMarkerElement[];
var appMode = $('#appMode').text() as string;
var map: google.maps.Map;
var $fullScreenDiv: JQuery; // Google's hidden inner div when clicking on full screen mode
var $map: JQuery = $('#map');
var mapEl: HTMLElement = <HTMLElement> $map.get(0);
var mapht: number;
// track vars
var drawnHikes: number[] = [];     // hike numbers which have had tracks created
var drawnTracks: HikeTrackObj[] = [];    // array of objects: {hike:hikeno , track:polyline}
var zoomedHikes: [number[], string[], string[]];
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
// 16px padding on navbar, 42px to eliminate interference with maptype
let srchtop = navheight + 16 + logoheight + 14 + 42;
$('#search').css({
	top: srchtop,
	left: '40px'
});
/**
 * This function positions the geosymbol in the bottom right corner of the map,
 * left of the google map zoom control 
 */
function locateGeoSym() {
	let winht = window.innerHeight - 90;
	let mapwd = <number>$('#map').width() - 120;
	$('#geoCtrl').css({
		top: winht,
		left: mapwd
	});
	return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);

var locaters: MarkerIds = []; // global used to popup info window on map when hike is searched

/**
 * Collect the number of hikes associated with a clusterer for labelling purposes
 */
const makeClusterLabel = (markers: CustomAdvancedMarker[]) => {
    var total: number[] = [];
    markers.forEach(function(mrkr) {
        total.push(Number(mrkr.hikes));
    });
    var hike_total = total.reduce(
        (accumulator, currentValue) => accumulator + currentValue, 
        initialValue
    );
    return  hike_total;
};
/**
 * Create a DOM element containing a marker or clusterer icon with a mrkr_cnt div showing
 * the number of hikes associated with it
 */
const build_content = (glyph: string, count: number) => {
	var gtop;
    var glft;
    var gsize;
    var gpadding = "0 3px 0 3px";
    if (glyph === hike_mrkr_icon) { // single marker or cluster marker
        gtop = "10px";
        glft = "13px";
        gsize = "11px";
    } else {
        gtop = "10px";
        if (count < 10) {
            glft = "12px";
            gsize = "11px;"
        } else if (count < 100) {
            glft = "10px";
            gsize = "10px;"
            gpadding = "0 2px 0 2px";
        } else {
            gtop = "11px";
            glft = "9px";
            gsize = "9px";
            gpadding = "0 2px 0 2px";
        }
    }
    var content = document.createElement("div");
    var icon = document.createElement("img");
    var mrkr_cnt = document.createElement("div");
    var mrkr_txt = document.createTextNode(String(count));
    mrkr_cnt.style.background = "white";
    mrkr_cnt.style.position = "absolute";
    mrkr_cnt.style.top = gtop;
    mrkr_cnt.style.left= glft;
    mrkr_cnt.style.fontSize = gsize;
    mrkr_cnt.style.padding = gpadding;
    mrkr_cnt.style.borderRadius = "6px";
    mrkr_cnt.appendChild(mrkr_txt);
    icon.style.zIndex = "900";
    icon.src = glyph;
    content.appendChild(icon);
    content.appendChild(mrkr_cnt);
    return content;
};
/**
 * Use the arrays passed in to the home page by php: one for each type 
 * of marker to be displayed (Clustered, Normal):
 * 		CL Array: Clustered hike pages
 * 		NM Array: Normal hike pages
 * And one for creating tracks:
 * 		tracks Array: ordered list of json file names
 */
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
	const infoWindow = new google.maps.InfoWindow({
		content: "",
		disableAutoPan: true,
		maxWidth: 400
	});
	// ///////////////////////////   MARKER CREATION   ////////////////////////////
	const nm_markers = nm_marker_data.map((mrkr_data: NM_Marker_Data) => { // create array of markers
		const position = mrkr_data.position as GPS_Coord;
		const nm_title = mrkr_data.title;
		// THE MARKER:
		const marker = new google.maps.marker.AdvancedMarkerElement({
			position: position,
			map: map,
			content: build_content(hike_mrkr_icon, 1),
			title: nm_title
		}) as CustomAdvancedMarker;
		marker.hikes = 1;
		// MARKER SEARCH
		const srchmrkr: MarkerId = {
			hikeid: mrkr_data.title, 
			clicked: false,
			pin: marker
		};
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
	const cl_markers = cl_marker_data.map((mrkr_data: CL_Marker_Data) => {
		const position = mrkr_data.position as GPS_Coord;
		const cl_title = mrkr_data.title;
		const hike_count = mrkr_data.hikecnt;
		// THE MARKER:
		const marker = new google.maps.marker.AdvancedMarkerElement({
			position: position,
			map: map,
		  	content: build_content(hike_mrkr_icon, hike_count),
		  	title: cl_title,
			gmpClickable: true
		}) as CustomAdvancedMarker;
		marker.hikes = hike_count;
		// MARKER SEARCH:
		const srchmrkr: MarkerId = {
			hikeid: mrkr_data.title,
			clicked: false,
			pin: marker
		};
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
	const markers = [...nm_markers, ...cl_markers];
	const renderer = { // must be an object, here with key 'render'
        /**
         * render( CLUSTER, stats, map) where CLUSTER 'Accessors' are bounds, count, position
         * and 'cluster' contains various properties, including _position, and markers[]
         */
        render: function (cluster: ClustererForRender) {
            var marker_label = makeClusterLabel(cluster.markers);
            return new google.maps.marker.AdvancedMarkerElement({
                position: cluster._position,
                map: map,
                content: build_content(clus_mrkr_icon, marker_label),
                title: "Cluster"
            });
        }
    };
	// Add a marker clusterer to manage the markers.
	new markerClusterer.MarkerClusterer({
        markers: markers,
        map: map,
        algorithmOptions: {maxZoom: 12}, // no apparent effect...
        renderer: renderer
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
			var curZoom = map.getZoom() as number;
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
				path: trackDat.trk,
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
			let newtrack = {hike: hikeno, track: sgltrack};
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
		var currzoom = map.getZoom() as number;
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
