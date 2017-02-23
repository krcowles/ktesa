// TRACK COLORS 
var lineColor = '#2974EB';  // apparently a google api function also assigns to this name
const trackClr1 = '#FF0000';
const trackClr2 = '#0000FF';
const trackClr3 = '#F88C00';
const trackClr4 = '#884998';
// constants for readability during marker creation
const VC_TYPE = 0; 
const CH_TYPE = 1;
const NH_TYPE = 2;


var map;  // needs to be global!
var mapRdy = false; // flag for map initialized & ready to draw tracks
var mapTick = {   // custom tick-mark symbol for tracks
	path: 'M 0,0 -5,11 0,8 5,11 Z',
	fillcolor: 'Red',
	fillOpacity: 0.8,
	scale: 1,
	strokeColor: 'Red',
	strokeWeight: 2
};

var msg;  // debug message string
var turnOnGeo = $('#geoSetting').text(); // get the setting from the html, compliments php

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
	var ctrIcon = '../images/yellow64.png';
	var clusterIcon = '../images/blue64.png';
	var hikeIcon = '../images/pink64.png';
	$('#iwVC').css('font-size','400%');
	$('#iwCH').css('font-size','400%');
	$('#iwOH').css('font-size','400%');
} else {
	var geoIcon = medGeo;
	var ctrIcon = '../images/yellow.png';
	var clusterIcon = '../images/bluepin.png';
	var hikeIcon = '../images/redpin.png';
} 

/* Animated "New Hike" Marker:
	- Place the box on the left-hand side of the map, below the map-style drop-down 
	  far enough down to ensure clearance when drop-down is selected
	- Assume that the last hike in the table is the new hike */
// Determine & set box position:
var winWidth = $(window).width();
var mapWidth = $('#map').width();  // same as container size (currently 960)
if (winWidth < mapWidth) {
	$('#newHikeBox').css('left',12);
} else {
	var newHikeBoxLeft = Math.floor( (winWidth - mapWidth)/2 ) + 12;
	$('#newHikeBox').css('left',newHikeBoxLeft);
}
// Determine last hike name & hike type:
var $hikeRows = $('#refTbl tbody tr');
var lastHikeIndx = $hikeRows.length - 1; // offset 1 for header row
var $lastHikeRow = $hikeRows.eq(lastHikeIndx).find('td');
var newHikeName = $lastHikeRow.eq(1).text();
if ($hikeRows.eq(lastHikeIndx).hasClass('clustered')) {
	// in this case, animate the cluster group marker
	newHikeName = $hikeRows.eq(lastHikeIndx).data('tool');
}
newHikeName = newHikeName.replace('Index','Visitor Center');
$('#winner').append(newHikeName);
$('#winner').css('color','DarkGreen');
$('#newHikeBox').css('display','block');

/* Create the hike arrays to be used in marker and info window creation */
// get node lists for each marker type:
var allVs = [];
var allCs = [];
var allNs = [];
var allXs = [];  // this array will hold the special-case "At VC" hikes
// NOTE: "At VC" hikes are ignored for purposes of creating separate markers
$hikeRows.each( function() {
	if ( $(this).hasClass('indxd') ) {
		allVs.push(this);
	} else if ( $(this).hasClass('clustered') ) {
		allCs.push(this);
	} else if ( $(this).hasClass('vchike') ) {
		allXs.push(this);
	} else if ( $(this).hasClass('normal') ) {
		allNs.push(this);
	}  // anything not caught in this trap is an anomaly!!
});

/* INSIDE the initMap function, the map listener is defined, and depending on whether
   or not there is a table present, a definition is added for "dragend" */

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
	var clustersUsed = '';
	var animateMe;
	
	// Loop through marker definitions and call marker-creator fcts: 1st, visitor centers:
	sym = ctrIcon;
	$(allVs).each( function() {
		var thisVorgs = [];
		var vlat = parseFloat($(this).data('lat'));
		var vlon = parseFloat($(this).data('lon'));
		loc = {lat: vlat, lng: vlon};
		// identify the originating hikes, as they will not have individual markers...
		var orgDat = $(this).data('org-hikes');
		// orgDat looks like a string to the debugger, but not the browser! so:
		var orgHikes = String(orgDat);
		if (orgHikes !== '') {
			if (orgHikes.indexOf(".") === -1) { // no "." means only one hike is listed
				thisVorgs.push(orgHikes);
			} else {
				var orgHikeArray = orgHikes.split("."); // for multiple hike listings
				for (j=0; j<orgHikeArray.length; j++) {
					thisVorgs.push(orgHikeArray[j]);
				}
			}
		} // if emtpy string, thisVorgs will be an empty array (0 elements)
		var $dataCells = $(this).find('td');
		var $link = $dataCells.eq(3).find('a');
		var vpage = $link.attr('href');
		var $dlink = $dataCells.eq(8).find('a');
		var dirLink = $dlink.attr('href');
		nme = $dataCells.eq(1).text();
		nme = nme.replace('Index','Visitor Center');
		animateMe = nme == newHikeName ? true : false;
		AddVCMarker(loc, sym, nme, animateMe, vpage, dirLink, thisVorgs);
	});
	// Now, the "clustered" hikes: Add one and only one cluster marker per group
	sym =clusterIcon;
	$(allCs).each( function() {
		var chikeArray;
		var clusterGrp = $(this).data('cluster'); // must be a single char
		var cindx;
		if ( clustersUsed.indexOf(clusterGrp) === -1 ) { // skip over other members in group
			// a new group has been encountered
			chikeArray = [];
			clustersUsed += clusterGrp; // update "Used"
			// collect the indices for all hikes in this group
			for (n=0; n<allCs.length; n++) {
				if ($(allCs[n]).data('cluster') == clusterGrp) {
					cindx = $(allCs[n]).data('indx');
					chikeArray.push(cindx);
				}
			}
			// proceed with def's for other arguments
			var clat = parseFloat($(this).data('lat'));
			var clon = parseFloat($(this).data('lon'));
			nme = $(this).data('tool');
			loc = {lat: clat, lng: clon};
			animateMe = nme == newHikeName ? true : false;
			var hikeId = $(this).data('indx');
			var $dataCells = $(this).find('td');
			var $plink = $dataCells.eq(3).find('a');
			cpage = $plink.attr('href');
			var $dlink = $dataCells.eq(8).find('a');
			var dirLink = $dlink.attr('href');
			AddClusterMarker(loc, sym, nme, animateMe, cpage, dirLink, chikeArray);
		}
	});
	// Finally, the remaining hike markers
	sym = hikeIcon;
	$(allNs).each( function() { // by def, no vchikes here
		var nlat = parseFloat($(this).data('lat'));
		var nlon = parseFloat($(this).data('lon'));
		loc = {lat: nlat, lng: nlon};
		var hikeNo = $(this).data('indx');
		var $dataCells = $(this).find('td');
		nme = $dataCells.eq(1).text();
		$plink = $dataCells.eq(3).find('a');
		npage = $plink.attr('href');
		$dlink = $dataCells.eq(8).find('a');
		dirLink = $dlink.attr('href');
		animateMe = nme == newHikeName ? true : false;
		AddHikeMarker(loc, sym, nme, animateMe, npage, dirLink, hikeNo);
	});
	
	/* the actual functions to create the markers & setup info windows */
	// Visitor Center Markers:
	function AddVCMarker(location, iconType, pinName, bounce, website, dirs, orgHikes) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		// animated marker if new hike is a visitor center
		if ( bounce ) {
			marker.setAnimation(google.maps.Animation.BOUNCE);
			setTimeout(function(){ 
				marker.setAnimation(null); 
				$('#newHikeBox').css('display','none'); }, 6000);
		}
		// add info window functionality
		marker.addListener( 'click', function() {
			map.setCenter(location);
			var iwContent;
			vLine1 = '<div id="iwVC">' + pinName;
			vLine2 = '<a href="' + website + 
					'" target="_blank">Park Information and Hike Index</a>';  // web link
			iwContent = vLine1 + '<br />' + vLine2;
			if (orgHikes.length > 0) { // orgHikes is an array parameter passed in
				vLine3 = '<em>Hikes Originating from Visitor Center</em>';
				if(orgHikes.length === 1) {
					vLine3 = vLine3.replace('Hikes','Hike');
				}
				iwContent += '<br />' + vLine3;
				for (v=0; v<orgHikes.length; v++) {
					iwContent += coreHikeData(VC_TYPE, orgHikes[v]);
				}
			}
			iwContent += '<br /><a href="' + dirs + '" target="_blank">Directions</a></div>';
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 400
			});
			iw.open(map, this);
		});
	} // end function AddVCMarker
	// Clustered Trailhead Markers:
	function AddClusterMarker(location, iconType, pinName, bounce, website, dirs, hikes) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		if ( bounce ) {
			marker.setAnimation(google.maps.Animation.BOUNCE);
			setTimeout(function(){ 
				marker.setAnimation(null); 
				$('#newHikeBox').css('display','none'); }, 6000);
		}
		// info window content: add in all the hikes for this group
		marker.addListener( 'click', function() {
			map.setCenter(location);
			var iwContent;
			var cline1 = '<div id="iwCH">' + pinName + '<br />';
			var cline2 = '<em>Hikes in this area</em>';
			iwContent = cline1 + cline2;
			for (m=0; m<hikes.length; m++) {
				iwContent += coreHikeData(CH_TYPE, hikes[m]);
			}
			iwContent += '<br /><a href="' + dirs + '" target="_blank">Directions</a></div>';
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 600
			});
			iw.open(map, this);
		});
	} // end AddClusterMarker
	function AddHikeMarker(location, iconType, pinName, bounce, website, dirs, hike) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		if ( bounce ) {
			marker.setAnimation(google.maps.Animation.BOUNCE);
			setTimeout(function(){ 
				marker.setAnimation(null); 
				$('#newHikeBox').css('display','none'); }, 6000);
		}
		marker.addListener( 'click', function() {
			map.setCenter(location);
			var iwContent = '<div id="NH">Hike: ' + pinName + '<br />';
			var $nData = coreHikeData(NH_TYPE, hike);
			iwContent += 'Length: ' + $nData.eq(4).text() + '<br />';
			iwContent += 'Elevation Change: ' + $nData.eq(5).text() + '<br />';
			iwContent += 'Difficulty: ' + $nData.eq(6).text() + '<br />';
			var $plink = $nData.eq(3).find('a');
			iwContent += '<a href="' + $plink.attr('href') + '" target="_blank">Website</a><br />';
			var $dlink = $nData.eq(8).find('a');
			iwContent += '<a href="' + $dlink.attr('href') + '" target="_blank">Directions</a></div>';
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 400
			});
			iw.open(map, this);
		});
	}
	
	// /////////////////////  CORE HIKE DATA FOR INFO WINDOW //////////////////////
	function coreHikeData(markerType, hikeNo) {
		var $hikeData;
		var hikeLocated = false;
		if (markerType === VC_TYPE) {
			$(allXs).each( function() {
				if ( $(this).data('indx') == hikeNo ) {
					hikeLocated = true;
					$hikeData = $(this).find('td');
					return true;
				}
			});
		}
		if (markerType === NH_TYPE) {
			$(allNs).each( function() {
				if ( $(this).data('indx') == hikeNo ) {
					hikeLocated = true;
					$hikeData = $(this).find('td');
					return true;
				}
			});
		}
		if (markerType === CH_TYPE) {
			$(allCs).each( function() {
				if ( $(this).data('indx') == hikeNo ) {
					hikeLocated = true;
					$hikeData = $(this).find('td');
					return true;
				}
			});
		}
		if ( !hikeLocated ) {
			window.alert('Could not find hike in index table!')
		}
		if (markerType === NH_TYPE) {
			return $hikeData;
		}
		var iwDat = '<br />' + $hikeData.eq(1).text() + '; ';
		iwDat += 'Lgth: ' + $hikeData.eq(4).text() + '; ';
		iwDat += 'Elev Chg: ' + $hikeData.eq(5).text() + '; ';
		iwDat += 'Diff: ' + $hikeData.eq(6).text() + '<br />';
		var $plink = $hikeData.eq(3).find('a');
		iwDat += '<a href="' + $plink.attr('href') + '" target="_blank">Website</a>';
		return iwDat;
	} // end function coreHikeData
	
	// //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
	map.addListener('zoom_changed', function() {
		var curZoom = map.getZoom();
		if (useTbl) {
			var perim = String(map.getBounds());
			IdTableElements(perim);
		}
		if ( curZoom > 12 ) {
			for (var m=0; m<allTheTracks.length; m++) {
				trkKeyStr = 'trk' + m;
				//var db = 'trkName' + m;
				//msg = '<p>Track: ' + trkObj[db] + '</p>';
				//$('#dbug').append(msg);
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
var trkObj = { trk0: {}, trkName0: 'name' };
var trkKeyNo = 0;
var trkKeyStr;
var allTheTracks = [];
var trackColor;

var trackForm = setInterval(startTracks,40);
function startTracks() {
	if ( mapRdy ) {
		clearInterval(trackForm);
		drawTracks();
	}
}

function ClusterGroups( clusId ) {
	this.id = clusId;
	this.cnt = 1;
	this.color = 1;
}
function idClusters() {
	var cId;
	var cObj;
	var csUsed = '';
	var cTracks = [];
	for (j=0; j<allCs.length; j++) {
		cId = $(allCs[j]).data('cluster');
		if (csUsed.indexOf(cId) == -1) {
			// new group
			cObj = new ClusterGroups(cId);
			cTracks.push(cObj);
		} else {
			// this group already exists
			for (k=0; k<ctracks.length; k++) {
				if (cTracks[k].id == cId) {
					cTracks[k].cnt++;
					break;
				}
			}
		}
	}  // end of for loop
	return cTracks;
}
// NO GPX files for Visitor Centers, so start with cluster hikes:
function drawTracks() {
	var clusGrp;
	var clusters = idClusters();
	var trackFile;
	var cindx;
	var handle;
	var hikeId;
	var colorId;
	var cGrpNo;
	for (i=0; i<allCs.length; i++) {
		cGrpNo = -1;
		clusGrp = $(allCs[i]).data('cluster');
		trackFile = $(allCs[i]).data('track');
		hikeId = $(allCs[i]).data('indx');
		if (trackFile !== '') {
			cindx = trackFile.indexOf('.json');
			handle = trackFile.substring(0,cindx);
			trkKeyStr = 'trkName' + trkKeyNo;
			trkObj[trkKeyStr] = handle;
			trackFile = '../json/' + trackFile;
			// find the corresponding object
			for (k=0; k<clusters.length; k++) {
				if (clusGrp == clusters[k].id) {
					colorId = clusters[k].color;
					cGrpNo = k;
					switch (colorId) {
						case 1:
							trackColor = trackClr1;
							break;
						case 2:
							trackColor = trackClr2;
							break;
						case 3: 
							trackColor = trackClr3;
							break;
						case 4:
							trackColor = trackClr4;
							break;
						default:
							trackColor = '#000000';
							break;
					}
					break;
				}
			}
		}	
		sglTrack(trackFile,CH_TYPE,trackColor,hikeId);
		if (cGrpNo !== -1) {
			clusters[cGrpNo].color++;
		}
	}  // end of cluster drawing
	for (j=0; j<allNs.length; j++) {
		trackFile = $(allNs[j]).data('track');
		hikeId = $(allNs[j]).data('indx');
		if (trackFile !== '') {
			cindx = trackFile.indexOf('.json');
			handle = trackFile.substring(0,cindx);
			trkKeyStr = 'trkName' + trkKeyNo;
			trkObj[trkKeyStr] = handle;
			trackFile = '../json/' + trackFile;
		}
		sglTrack(trackFile,NH_TYPE,trackClr1,hikeId);
	}
	// may need one for allXs ????
}  // END FUNCTION DrawTracks
function sglTrack(trkUrl,trkType,trkColor,hikeNo) {
	if (trkUrl === '') {
		return;
	}
	$.ajax({
		dataType: "json",
		url: trkUrl,
		success: function(trackDat) {
			var newTrack = trackDat;
			var mdiv;
			var $trkRow;
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
			// when loaded, all tracks are off (not set)
		    allTheTracks.push(trkKeyStr);
			// create the mouseover text:
			if ( trkType === CH_TYPE ) {
				
				mdiv = '<div id="iwCH">';
				$(allCs).each( function() {
					if ( $(this).data('indx') == hikeNo ) {
						$trkRow = $(this).find('td');
						return;
					}
				});
			} else {
				mdiv = '<div id="iwNH">';
				$(allNs).each( function() {
					var hIndx = $(this).data('indx');
					if ( $(this).data('indx') == hikeNo ) {
						$trkRow = $(this).find('td');
						return;
					}
				});
			}
			var hName = $trkRow.eq(1).text();
			var hLgth = $trkRow.eq(4).text();
			var hElev = $trkRow.eq(5).text();
			var hDiff = $trkRow.eq(6).text();
			var iwContent = mdiv + hName + '<br />Length: ' +
				hLgth + '<br />Elev Chg: ' + hElev + '<br />Difficulty: ' + hDiff + '</div>'; 
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
		},
		error: function() {
			msg = '<p>Did not succeed in getting JSON data: ' + trkUrl + '</p>';
			$('#dbug').append(msg);
		}
	});
} // end of function sglTrack
// /////////////////////// END OF HIKE TRACK DRAWING /////////////////////


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
	
