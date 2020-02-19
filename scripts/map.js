// Hike Track Colors: red, blue, orange, purple, black, yellow
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000', '#FFFF00']

// need to be global:
var map;
var allTheTracks = [];
var tracksDone = false;

// Google's hidden inner div when clicking on full screen mode
var $fullScreenDiv;
var $map = $('#map');
// Maximize map height & side table div
var mapht = $(window).height() - $('#panel').height();
$map.css('height', mapht + 'px');
$('#sideTable').css('height', mapht + 'px');
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

var mobile_browser = (navigator.userAgent.match(/\b(Android|Blackberry|IEMobile|iPhone|iPad|iPod|Opera Mini|webOS)\b/i) || (screen && screen.width && screen.height && (screen.width <= 480 || screen.height <= 480))) ? true : false;
// icons depend on whether mobile or not (size factor for visibility)
// also text size for pop-ups - which doesn't seem to work!
if ( mobile_browser ) {
    var geoIcon = lgGeo;
    var ctrIcon = '../images/yellow64.png';
    var clusterIcon = '../images/blue64.png';
    var hikeIcon = '../images/pink64.png';
    $('#iwVC').css('font-size','400%');
    $('#iwCH').css('font-size','400%');
    $('#iwOH').css('font-size','400%');
} else {
    //var geoIcon = medGeo;
    var ctrIcon = '../images/yellow.png';
    var clusterIcon = '../images/bluepin.png';
	var hikeIcon = '../images/redpin.png';
} 

// place legend link on map (w/draggable header);
function dragElement(elmnt) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    if (document.getElementById(elmnt.id + "header")) {
      // if present, the header is where you move the DIV from:
      document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
    } else {
      // otherwise, move the DIV from anywhere inside the DIV:
      elmnt.onmousedown = dragMouseDown;
    }
    function dragMouseDown(e) {
      e = e || window.event;
      e.preventDefault();
      // get the mouse cursor position at startup:
      pos3 = e.clientX;
      pos4 = e.clientY;
      document.onmouseup = closeDragElement;
      // call a function whenever the cursor moves:
      document.onmousemove = elementDrag;
    }
    function elementDrag(e) {
      e = e || window.event;
      e.preventDefault();
      // calculate the new cursor position:
      pos1 = pos3 - e.clientX;
      pos2 = pos4 - e.clientY;
      pos3 = e.clientX;
      pos4 = e.clientY;
      // set the element's new position:
      elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
      elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }
    function closeDragElement() {
      // stop moving when mouse button is released:
      document.onmouseup = null;
      document.onmousemove = null;
    }
}
var linkpos = $('#map').width() - 180 + 'px';
var lgndpos = $('#map').width() - 260 + 'px';
$('#legend').css('left', linkpos);
$('#legend').css('top', '94px');
$('#legend').css('z-index', '1000');
$('#maplegend').css('left', lgndpos);
$('#maplegend').css('top', '108px');
$('#legend').on('click', function(ev) {
	ev.preventDefault();
	$('#maplegend').css('display', 'block');
	var dragit = document.getElementById('maplegend');
	dragElement(dragit);
});
$('#can').on('click', function(ev) {
	ev.preventDefault();
	$('#maplegend').css('display', 'none');
});

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

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
var mapdone = new $.Deferred();
function initMap() {
	var clusterMarkerSet = [];
	var nmCtr = {lat: 34.450, lng: -106.042};
	//var mapDiv = document.getElementById('map');
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
		AddVCMarker(vcobj.loc, ctrIcon, vcobj.name, vcobj.indx, vcobj.hikes);
	});
	CL.forEach(function(clobj) {
		AddClusterMarker(clobj.loc, clusterIcon, clobj.group, clobj.hikes);
	});
	NM.forEach(function(nmobj) {
		AddHikeMarker(hikeIcon, nmobj);
	});


	// Visitor Center Markers:
	function AddVCMarker(location, iconType, pinName, hikeindx, hikeobj) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		var srchmrkr = {hikeid: pinName, pin: marker};
		locaters.push(srchmrkr);
		clusterMarkerSet.push(marker);
		// add info window functionality
		marker.addListener( 'click', function() {
			map.setCenter(location);
			var website = '<a href="indexPageTemplate.php?hikeIndx=' + hikeindx +
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
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 600
			});
			iw.open(map, this);
		});
		// add in locaters for the hikes associated with VC's:
		
	}

	// Clustered Markers:
	function AddClusterMarker(location, iconType, group, clhikes) {
		var marker = new google.maps.Marker({
			position: location,
			map: map,
			icon: iconType,
			title: group
		});
		var srchmrkr = {hikeid: group, pin: marker};
		locaters.push(srchmrkr);
		clusterMarkerSet.push(marker);
		// info window content: add in all the hikes for this group
		marker.addListener( 'click', function() {
			map.setCenter(location);
			var iwContent = '<div id="iwCH">' + group;
			clhikes.forEach(function(clobj) {
				iwContent += '<br /><a href="hikePageTemplate.php?hikeIndx=' +
					clobj.indx + '">' + clobj.name + '</a>';
				iwContent += ' Lgth: ' + clobj.lgth + ' miles; Elev Chg: ' + 
					clobj.elev + ' ft; Diff: ' + clobj.diff;
			});
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 600
			});
			iw.open(map, this);
		});
	}

	// Normal Hike Markers
	function AddHikeMarker(iconType, hikeobj) {
		var marker = new google.maps.Marker({
		  position: hikeobj.loc,
		  map: map,
		  icon: iconType,
		  title: hikeobj.name
		});
		marker.addListener( 'click', function() {
			map.setCenter(hikeobj.loc);
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
			iw.open(map, this);
		});
		var srchmrkr = {hikeid: hikeobj.name, pin: marker};
		locaters.push(srchmrkr);
		clusterMarkerSet.push(marker);
	}


	// /////////////////////// Marker Grouping /////////////////////////
	var markerCluster = new MarkerClusterer(map, clusterMarkerSet,
		{
			imagePath: '../images/markerclusters/m',
			gridSize: 50,
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
			IdTableElements(perim);
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
VC.forEach(function(vc) {
	vc.hikes.forEach(function(hobj) {
		if (tracks[hobj.indx] !== '') {
			trackdat[hobj.indx] = '<div id="iwXH">' + hobj.name + '<br />Length: ' +
				hobj.lgth + ' miles<br />Elev Chg: ' + hobj.elev +
				'<br />Difficulty: ' + hobj.diff + '</div>';
		}
	});
});
CL.forEach(function(clus) {
	clus.hikes.forEach(function(hobj) {
		if (tracks[hobj.indx] !== '') {
			trackdat[hobj.indx] = '<div id="iwCH">' + hobj.name + '<br />Length: ' +
				hobj.lgth + ' miles<br />Elev Chg: ' + hobj.elev +
				'<br />Difficulty: ' + hobj.diff + '</div>';
		}
	});
});
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
	var color;
	// Clusters first, as they require multiple colors
	CL.forEach(function(clobj) {
		color = 0;
		clobj.hikes.forEach(function(hikeobj) {
			// hike indx nos. start at 1, arrays start at 0, so:
			if (tracks[hikeobj.indx] !== '') {
				var trkfile = '../json/' + tracks[hikeobj.indx];
				drawTrack(trkfile, colors[color], hikeobj.indx);
				// empty corresponding indx in array so that it won't get drawn again
				tracks[hikeobj.indx] = '';
				color++;
				if (color > 5) { color = 0; } // only 6 colors for now
			}
		});
	});
	color = colors[0];
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
	mapht = $(window).height() - $('#panel').height();
	$('#map').css('height', mapht + 'px');
	$('#sideTable').css('height', mapht + 'px');
	locateGeoSym();
});

// //////////////////////////////////////////////////////////////
