// TRACK COLORS 
var lineColor = '#2974EB';  // apparently a google api function also assigns to this name
var trackClr1 = '#FF0000';
var trackClr2 = '#0000FF';
var trackClr3 = '#F88C00';
var trackClr4 = '#884998';
// constants for readability during marker creation
var VC_TYPE = 0; 
var CH_TYPE = 1;
var NH_TYPE = 2;
var XH_TYPE = 3;

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
var geoleftOffset = 120; // px offset from right edge of window
var geotopOffset = 100; // px offset from bottom of window
var turnOnGeo = $('#geoSetting').text(); // get the setting from the html, compliments php

if ( turnOnGeo.trim() === 'ON' ) {
	// starting position in window:
	var winht = $('#map').innerHeight() - geotopOffset;
	var winwd = $(window).innerWidth() - geoleftOffset;
	$('#geoCtrl').css('top', winht);
	$('#geoCtrl').css('left', winwd);
	// enable click:
    $('#geoCtrl').on('click', setupLoc);
}

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
    var geoIcon = medGeo;
    var ctrIcon = '../images/yellow.png';
    var clusterIcon = '../images/bluepin.png';
    var hikeIcon = '../images/redpin.png';
} 
/* Create the hike arrays to be used in marker and info window creation */
// get node lists for each marker type:
var allVs = [];
var allCs = [];
var allNs = [];
var allXs = [];  // this array will hold the special-case "At VC" hikes
// NOTE: "At VC" hikes are ignored for purposes of creating separate markers
// $hikeRows is defined in hikeBox.js
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
// need column indices from table for certain items:
var hike_hdr;
var lgth_hdr;
var elev_hdr;
var diff_hdr;
var dir_hdr;
var $tblhdrs = $('table thead');
var $hdrs = $tblhdrs.eq(0).find('th');
$hdrs.each( function(indx) {
	if ($(this).text() === 'Hike/Trail Name') {
		hike_hdr = indx;
	}
	if ($(this).text() === 'Length') {
		lgth_hdr = indx;
	}
	if ($(this).text() === 'Elev Chg') {
		elev_hdr = indx;
	}
	if ($(this).text() === 'Difficulty') {
		diff_hdr = indx;
	}
	if ($(this).text() === 'By Car') {
		dir_hdr = indx;
	}
});

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
function initMap() {
	var clusterMarkerSet = [];
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
	// Loop through marker definitions and call marker-creator fcts: 
	// 1st, visitor centers:
	sym = ctrIcon;
	$(allVs).each( function() {
		var thisVorgs = [];
		var vlat = parseFloat($(this).data('lat'));
		var vlon = parseFloat($(this).data('lon'));
		var hno = parseInt($(this).data('indx'));
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
		var $link = $dataCells.eq(hike_hdr).find('a');
		var vpage = $link.attr('href');
		var $dlink = $dataCells.eq(dir_hdr).find('a');
		var dirLink = $dlink.attr('href');
		nme = $dataCells.eq(hike_hdr).text();
		nme = nme.replace('Index','Visitor Center');
		if (nme == newHikeName) {
			latest = hno;
			newloc = loc;
		}
		AddVCMarker(loc, sym, nme, vpage, dirLink, thisVorgs, hno);
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
			loc = {lat: clat, lng: clon};
			var hno = parseInt($(this).data('indx'));
			nme = $(this).data('tool');
			if (nme == newHikeName) {
				latest = hno;
				newloc = loc;
			}
			var hikeId = $(this).data('indx');
			var $dataCells = $(this).find('td');
			var $plink = $dataCells.eq(hike_hdr).find('a');
			cpage = $plink.attr('href');
			var $dlink = $dataCells.eq(dir_hdr).find('a');
			var dirLink = $dlink.attr('href');
			AddClusterMarker(loc, sym, nme, cpage, dirLink, chikeArray, hno);
		}
	});
	// Finally, the remaining hike markers
	sym = hikeIcon;
	$(allNs).each( function() { // by def, no vchikes here
		var nlat = parseFloat($(this).data('lat'));
		var nlon = parseFloat($(this).data('lon'));
		loc = {lat: nlat, lng: nlon};
		var hno = $(this).data('indx');
		var $dataCells = $(this).find('td');
		nme = $dataCells.eq(hike_hdr).text();
		if (nme == newHikeName) {
			latest = hno;
			newloc = loc;
		}
		$plink = $dataCells.eq(hike_hdr).find('a');
		npage = $plink.attr('href');
		$dlink = $dataCells.eq(dir_hdr).find('a');
		dirLink = $dlink.attr('href');
		AddHikeMarker(loc, sym, nme, npage, dirLink, hno);
	});
	/* the actual functions to create the markers & setup info windows */
	// Visitor Center Markers:
	function AddVCMarker(location, iconType, pinName, website, dirs, orgHikes, mrkrno) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		clusterMarkerSet.push(marker);
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
	function AddClusterMarker(location, iconType, pinName, website, dirs, hikes, mrkrno) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		clusterMarkerSet.push(marker);
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
			//iwContent += '<br /><a href="' + dirs + '" target="_blank">Directions</a></div>';
			var iw = new google.maps.InfoWindow({
					content: iwContent,
					maxWidth: 600
			});
			iw.open(map, this);
		});
	} // end AddClusterMarker
	function AddHikeMarker(location, iconType, pinName, website, dirs, hike, mrkrno) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		clusterMarkerSet.push(marker);
		marker.addListener( 'click', function() {
			map.setCenter(location);
			var iwContent = '<div id="NH">Hike: ' + pinName + '<br />';
			var $nData = coreHikeData(NH_TYPE, hike);
			iwContent += 'Length: ' + $nData.eq(lgth_hdr).text() + '<br />';
			iwContent += 'Elevation Change: ' + $nData.eq(elev_hdr).text() + '<br />';
			iwContent += 'Difficulty: ' + $nData.eq(diff_hdr).text() + '<br />';
			var $plink = $nData.eq(hike_hdr).find('a');
			iwContent += '<a href="' + $plink.attr('href') + '" target="_blank">Website</a><br />';
			var $dlink = $nData.eq(dir_hdr).find('a');
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
		var iwDat = '<br />' + $hikeData.eq(hike_hdr).text() + '; ';
		iwDat += 'Lgth: ' + $hikeData.eq(lgth_hdr).text() + '; ';
		iwDat += 'Elev Chg: ' + $hikeData.eq(elev_hdr).text() + '; ';
		iwDat += 'Diff: ' + $hikeData.eq(diff_hdr).text() + '<br />';
		var $plink = $hikeData.eq(hike_hdr).find('a');
		iwDat += '<a href="' + $plink.attr('href') + '" target="_blank">Website</a>';
		return iwDat;
	} // end function coreHikeData

	// /////////////////////// Marker Grouping /////////////////////////
	var markerCluster = new MarkerClusterer(map, clusterMarkerSet,
		{
			imagePath: '../images/markerclusters/m',
			gridSize: 50,
			averageCenter: true,
			zoomOnClick: false
		});

	// //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
	map.addListener('zoom_changed', function() {
		var idle = google.maps.event.addListener(map, 'idle', function (e) {
			var curZoom = map.getZoom();
			if (typeof(useTbl) !== 'undefined' && useTbl) {
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
			google.maps.event.removeListener(idle);
		});
	});
	
	if (typeof(useTbl) !== 'undefined' && useTbl) {
		map.addListener('dragend', function() {
			var newBds = String(map.getBounds());
			IdTableElements(newBds);
		});
	}
	$('#newhike').on('click', function(ev) {
		ev.preventDefault();
		map.setCenter(newloc);
		map.setZoom(13);
	});
	
}  // end of initMap()
// ////////////////////// END OF MAP INITIALIZATION  /////////////////////////////

// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
var trkObj = { trk0: {}, trkName0: 'name' };
var trkKeyNo = 0;
var trkKeyStr;
var allTheTracks = [];
var trackColor;
var i,j,k;

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
			for (k=0; k<cTracks.length; k++) {
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
    var coll = '';
    var lastUsed = trackClr1;
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
    for (k=0; k<allXs.length; k++) {
        trackFile = $(allXs[k]).data('track');
        hikeId = $(allXs[k]).data('indx');
        if (trackFile !== '') {
            var thiscoll = $(allXs[k]).data('vc');
            if (thiscoll === coll) {
                var clrid = parseInt(lastUsed);
                clrid++;
                switch (colorId) {
                    case 2:
                        lastUsed = trackClr2;
                        break;
                    case 3: 
                        lastUsed = trackClr3;
                        break;
                    case 4:
                        lastUsed = trackClr4;
                        break;
                    default:
                        lastUsed = '#000000';
                        break;
                    }
            } else {
                coll = thiscoll;
                lastUsed = trackClr1;
            }
            cindx = trackFile.indexOf('.json');
            handle = trackFile.substring(0,cindx);
            trkKeyStr = 'trkName' + trkKeyNo;
            trkObj[trkKeyStr] = handle;
            trackFile = '../json/' + trackFile;
        }
        sglTrack(trackFile,XH_TYPE,lastUsed,hikeId);
    }
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
            } else if (trkType === XH_TYPE) {
                mdiv = '<div id="iwXH">';
                $(allXs).each( function() {
                    if ( $(this).data('indx') == hikeNo ) {
                        $trkRow = $(this).find('td');
                        return;
                    }
                });
                
            } else {
                // must be NH_TYPE: verify types called in drawTracks()
                mdiv = '<div id="iwNH">';
                $(allNs).each( function() {
                    var hIndx = $(this).data('indx');
                    if ( $(this).data('indx') == hikeNo ) {
                        $trkRow = $(this).find('td');
                        return;
                    }
                });
            }
            var hName = $trkRow.eq(hike_hdr).text();
            var hLgth = $trkRow.eq(lgth_hdr).text();
            var hElev = $trkRow.eq(elev_hdr).text();
            var hDiff = $trkRow.eq(diff_hdr).text();
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
$(window).resize( function() {
	var winht = $('#map').innerHeight() - geotopOffset;
	var winwd = $(window).innerWidth() - geoleftOffset;
	$('#geoCtrl').css('top', winht);
	$('#geoCtrl').css('left', winwd);
});
// //////////////////////////////////////////////////////////////
