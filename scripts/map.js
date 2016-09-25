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

var msg;  // debug message string
var turnOnGeo = $('#geoSetting').text();

if ( turnOnGeo === 'ON' ) {
	$('#geoCtrl').css('display','block');
	$('#geoCtrl').on('click', setupLoc);
}

// icons for geolocation:
var smallGeo = '../images/starget.png';
var medGeo = '../images/grnTarget.png';
var lgGeo = '../images/ltarget.png';

var mobile_browser = (navigator.userAgent.match(/\b(Android|Blackberry|IEMobile|iPhone|iPad|iPod|Opera Mini|webOS)\b/i) || (screen && screen.width && screen.height && (screen.width <= 480 || screen.height <= 480))) ? true : false;
// icons depend on whether mobile or not (size factor for visibility)
// also text size for pop-ups - which doesn't seem to work!
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

// ANIMATED NEW HIKE MARKER: CURRENT WINNER IS: (marker type & array no)
var NewHikeType = 'C';
var NewHike = 22;
msg = 'Alamo Vista';
$('#winner').append(msg);
$('#winner').css('color','DarkGreen');
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
	// remember - the first 5 belong to the Bandelier marker...
	vcoffset = 5;
	for (var j=vcoffset; j<noOfCHikes; j++ ) {
		if ( clusterPinHikes[j][6] === 1 ) {
			loc = {lat: clusterPinHikes[j][1], lng: clusterPinHikes[j][2] };
			nme = clusterPinHikes[j][0];
			AddClusterMarker(loc, sym, nme, j);
		}
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
	// -- There's ALWAYS an exception: The Bandelier Visitor Center will show hikes which
	// begin from the center; Currently, Bandelier is the very first item in the list [0];
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
			map.setCenter(location);
			var markerId = this.getTitle();
			for (var p=0; p<noOfVCs; p++ ) {
				if ( markerId === ctrPinHikes[p][0] ) {
					var vcIndex = p;
					break;
				}
			}
			var iwContent = mkContent(VC_TYPE, vcIndex);
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
		if ( NewHikeType == 'C' && NewHike == indx ) {
			allMarkers[marker].setAnimation(google.maps.Animation.BOUNCE);
			setTimeout(function(){ 
				allMarkers[marker].setAnimation(null); 
				$('#anbox').css('display','none'); }, 6000);
		}
		// info window content: add in all the hikes for this group
		allMarkers[marker].addListener( 'click', function() {
			map.setCenter(location);
			var tblIndx = noOfVCs + indx + 1;
			// get "cluster group"
			var ctype =  $('tbody tr').eq(tblIndx).data('cluster');
			var iwContent = '<div id="iwCH">';  // one 1 directions link needed
			var gDirs = $('tbody tr').eq(tblIndx).find('td:nth-child(9)').html();
			for (var q=indx; q<noOfCHikes; q++ ) {
				iwContent += mkContent(CH_TYPE, q);
				if ( q == noOfCHikes -1 ) {
					break;
				}
				tblIndx = noOfVCs + q + 1;
				if ( $('tbody tr').eq(tblIndx+1).data('cluster') != ctype ) {
					break;
				}	
			}
			iwContent += '<strong>Common Trailhead Directions:</strong> ' + gDirs + '</div>';
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 600
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
			map.setCenter(location);
			var markerId = this.getTitle();
			for (var r=0; r<noOfOthr; r++) {
				if ( markerId === othrHikes[r][0] ) {
					var ohIndex = r;
					break;
				}
			}
			var iwContent = mkContent(OH_TYPE, ohIndex);
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
				if ( elementNo === 0 ) {  // the ONE exception (so far!)
					hPg = '<a href="' + hPg + '" target="_blank">About Bandelier + Hike Table</a>';
					noOfVCHikes = 5;
					locInTbl = 5;  // this cluster-group (A) starts at the 5th row in the table
					var popup = '<div id="iwVC"><p>Visitor Center: ' +
							 hPg + '<br>' + '<em>Hikes Available from Visitor Center:</em>';
					for ( k=0; k<noOfVCHikes; k++ ) {
						var vchike = clusterPinHikes[k][0];
						var vcpg = clusterPinHikes[k][3];
						vcpg = '<a href="' + vcpg + '" target="_blank">Website</a>';
						var vclgth = $('tbody tr').eq(locInTbl+k).find('td:nth-child(5)').html();
						var vcelev = $('tbody tr').eq(locInTbl+k).find('td:nth-child(6)').html();
						var vcdiff = $('tbody tr').eq(locInTbl+k).find('td:nth-child(7)').html();
						popup += '<br>Hike:' + vchike + '; Lgth:' + vclgth + '; Elev Chg:' + vcelev +
							'; Diff:' + vcdiff + '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' +
							vcpg;
					}
					popup += '</div>';
				} else {
					hPg = '<a href="' + hPg + '" target="_blank">Hike Index Pg</a>';
					var hDir = $('tbody tr').eq(elementNo+1).find('td:nth-child(9)').html();
					var popup = '<div id="iwVC"><p>Visitor Center for<br>Park: ' + hName + '<br>' +
						hPg + '<br>' + 'Directions: ' + hDir + '</p></div>';
				}
				break;
			case CH_TYPE:
				var hName = clusterPinHikes[elementNo][0];
				var hPg = clusterPinHikes[elementNo][3];
				hPg = '<a href="' + hPg + '" target="_blank">Website</a>';
				elementNo += noOfVCs + 1;
				var hLgth = $('tbody tr').eq(elementNo).find('td:nth-child(5)').text();
				var hElev = $('tbody tr').eq(elementNo).find('td:nth-child(6)').text();
				var hDiff = $('tbody tr').eq(elementNo).find('td:nth-child(7)').text();
				var popup = 'Hike: ' + hName + '; Lgth: ' + hLgth + '; Elev Chg: ' +
						hElev + '; Diff: ' + hDiff + 
						'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' +
						hPg + '<br>';
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
					hPg + '<br>Directions: ' + hDir + '</div>';
				break;
			default:
				break;
		}
		return (popup);
	}

	// PAN AND ZOOM HANDLERS:
	map.addListener('zoom_changed', function() {
		var curZoom = map.getZoom();
		if (useTbl) {
			var perim = String(map.getBounds());
			IdTableElements(perim);
		}
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
// use pre-defined directional arrow ['mapTick'] for tracks

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
	
