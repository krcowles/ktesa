// generic var for outputting debug messages
var msg;

// detect when the map is ready for hike track inputting
var mapRdy = false;

// Determine which page is calling this script: for full page map, no tables displayed
var useTbl = $('title').text() == 'Hike Map' ? false : true;

if ( useTbl ) {
	// Table html wrapper, to be populated with rows later in script (see 'var outHike'):
	
	// when row-finding is enabled, use the next 2 lines:
	//var tblHtml = '<table class="msortable" onMouseOver="javascript:findPinFromRow(event);"'
	//tblHtml += ' onMouseOut="javascript:undoMarker();">';
	var tblHtml = '<table class="msortable">';
	tblHtml += $('table').html();
	var inx = tblHtml.indexOf('<tbody') + 8;
	tblHtml = tblHtml.substring(0,inx);
	var endTbl = ' </tbody> </table>';
	endTbl += ' <div> <p id="metric" class="dressing">Click here for metric units</p> </div>';

	// ///////////////////////  TABLE FUNCTION DECLARATIONS /////////////////////////
	// Establish the compare method (object) for table sorts:
	var compare = {
		std: function(a,b) {	// standard sorting - literal
			if ( a < b ) {
				return -1;
			} else {
				return a > b ? 1 : 0;
			}
		},
		lan: function(a,b) {    // "Like A Number": extract numeric portion for sort
			// commas allowed in numbers, so;
			var indx = a.indexOf(',');
			if ( indx < 0 ) {
				a = parseFloat(a);
			} else {
				noPart1 = parseFloat(a);
				msg = a.substring(indx + 1, indx + 4);
				noPart2 = msg.valueOf();
				a = noPart1 + noPart2;
			}
			indx = b.indexOf(',');
			if ( indx < 0 ) {
				b = parseFloat(b);
			} else {
				noPart1 = parseFloat(b);
				msg = b.substring(indx + 1, indx + 4);
				noPart2 = msg.valueOf();
				b = noPart1 + noPart2;
			}
			return a - b;
		} 
	};  // end of COMPARE object
	
	// Create the html for the table, and add sort definitions and metric conversion
	function formTbl ( noOfRows, tblRowsArray ) {
		// HTML CREATION:
		var thisTbl = tblHtml + ' <tr>';
		var indxRow;
		for (var m=0; m<noOfRows; m++) {
			indxRow = tblRowsArray[m];
			thisTbl += $tblRows.eq(indxRow).html();
			thisTbl += ' </tr> ';
		}
		thisTbl += endTbl;
		$('#usrTbl').html(thisTbl);
		$('#metric').css('display','block');
		// ADD SORT FUNCTIONALITY ANEW FOR EACH CREATION OF TABLE:
		$('.msortable').each(function() {
			var $table = $(this); 
			var $tbody = $table.find('tbody');
			var $controls = $table.find('th'); // store all headers
			var trows = $tbody.find('tr').toArray();  // array of rows

			$controls.on('click', function() {
				var $header = $(this);
				var order = $header.data('sort');
				var column;
	
				// IF defined for selected column, toggle ascending/descending class
				if ( $header.is('.ascending') || $header.is('.descending') ) {
					$header.toggleClass('ascending descending');
					$tbody.append(trows.reverse());
				} else {
				// NOT DEFINED - add 'ascending' to current; remove remaining headers' classes
					$header.addClass('ascending');
					$header.siblings().removeClass('ascending descending');
					if ( compare.hasOwnProperty(order) ) {
						column = $controls.index(this);  // index into the row array's data
						trows.sort(function(a,b) {
							a = $(a).find('td').eq(column).text();
							b = $(b).find('td').eq(column).text();
							return compare[order](a,b);
						});
						$tbody.append(trows);
					} // end if-compare
				} // end else
			}); // end on.click
		}); // end '.msortable each' loop
		// ADD METRIC CONVERSION ANEW FOR EACH CREATION OF TABLE:
		$('#metric').on('click', function() {
			// table locators:
			var $etable = $('table');
			var $etbody = $etable.find('tbody');
			var $erows = $etbody.find('tr');
			var state = this.textContent;
			// conversion variables:
			var tmpUnits;
			var tmpConv;
			var newDist;
			var newElev;
			var dist;
			var elev;
			// determine which state to convert from
			var mindx = state.indexOf('metric');
			if ( mindx < 0 ) { // currently metric; convert TO English
				newDist = 'miles';
				newElev = 'ft';
				state = state.replace('English','metric');
				dist = 0.6214;
				elev = 3.278;
			} else { // currently English; convert TO metric
				newDist = 'kms';
				newElev = 'm';
				state = state.replace('metric','English');
				dist = 1.61;
				elev = 0.305;
			}
			$('#metric').text(state); // new data element text
			$erows.each( function() {
				// index 3 is column w/distance units (miles/kms)
				// ASSUMPTION: always less than 1,000 miles or kms!
				tmpUnits = $(this).find('td').eq(3).text();
				tmpConv = parseFloat(tmpUnits);
				tmpConv = dist * tmpConv;
				var indxLoc = tmpUnits.substring(0,2);
				if ( indxLoc === '0*' ) {
					tmpUnits = '0* ' + newDist;
				} else {
					tmpUnits = tmpConv.toFixed(1);
					tmpUnits = tmpUnits + ' ' + newDist;
				}
				$(this).find('td').eq(3).text(tmpUnits);
				// index 4 is column w/elevation units (ft/m)
				tmpUnits = $(this).find('td').eq(4).text();
				// need to worry about commas...
				mindx = tmpUnits.indexOf(',');
				if ( mindx < 0 ) {
					tmpConv = parseFloat(tmpUnits);
				} else {
					noPart1 = parseFloat(tmpUnits);
					noPart2 = tmpUnits.substring(mindx + 1,mindx + 4);
					noPart2 = noPart2.valueOf();
					tmpConv = noPart1 + noPart2;
				}
				tmpConv = dist * tmpConv;
				indxLoc = tmpUnits.substring(0,2);
				if ( indxLoc === '0*' ) {
					tmpUnits = '0* ' + newElev;
				} else {
					tmpUnits = tmpConv.toFixed(0);
					tmpUnits = tmpUnits + ' ' + newElev;
				}
				$(this).find('td').eq(4).text(tmpUnits);
	
			});  // end 'each erow'	
		}); // end of click on metric
	}  // end of FORMTBL function

	// ROW-FINDING FUNCTIONS FOR mouseover TABLE... [not currently enabled]
	/*
	function findPinFromRow(eventArg) {
		if ( !eventArg ) {
			eventArg = window.event;
		}
		// IE browsers:
		if ( eventArg.srcElement ) {
			getRowNo(eventArg.srcElement);
		} else if ( eventArg.target ) {
			getRowNo(eventArg.target)
		}
	}
	function getRowNo(El) {
		if ( El.nodeName == "TD" ) {
			El = El.parentNode;
			msg = '<p>Now El is ' + El.nodeName + '; row indx is ' + El.rowIndex;
			var cellDat = El.cells[1].textContent;
			msg += 'w/Cell data = ' + cellDat + '</p>';
			$('#dbug').append(msg);
		} else return;
	}
	function undoMarker() {
		msg = '<p>Mouse out of row...</p>';
		//$('#features').append(msg);
	}
	// END OF ROW-FINDING FUNCTIONS
	*/
	
}  // end of useTbl test

// colors
var lineColor = '#2974EB';
var trackColor = '#FF0000';
var altTrkClr1 = '#0000FF';
var altTrkClr2 = '#14613E';
//var altTrkClr3 = '#AAAAAA';
var noTrk = '#000000';
			
// -------------------------------   IMPORTANT NOTE: ----------------------------
//	The index.html table ***** MUST ***** list items in the
//	order shown below [as listed in arrays] in order for the correct elements to be listed
//	in the user table of hikes
//	-----------------------------------------------------------------------------                                         */
// HIKE DATA ARRAYS:
// [ 'Name', lat, lng, html source for page, GPX file string (if present, .json file name) ]
var ctrPinHikes = [
	['Bandelier',35.779039,-106.270788,'Bandelier.html',''],
	['Chaco Canyon',36.030250,-107.91080,'Chaco.html',''],
	['El Malpais',34.970407,-107.810152,'ElMalpais.html',''],
	['Petroglyphs Natl Mon',35.138644,-106.711196,'Petroglyphs.html','']
];
// NOTE: clusterPinHikes have an added field of "trackColor" to differentiate overlaps
var clusterPinHikes = [
	// Bandelier hikes:
	['Ruins Trail',35.793670,-106.273155,'MainLoop.html','',noTrk],
	['Falls Trail',35.788735,-106.282079,'FallsTrail.html','',noTrk],
	['Frey Trail',35.779219,-106.285744,'Frey.html','',noTrk],
	['Frijolito Ruins',35.769573,-106.282433,'Frijolito.html','',noTrk],
	['Alcove House',35.764312,-106.273698,'AlcoveHouse.html','',noTrk],
	['Tsankawi Ruins',35.860416,-106.224682,'Tsankawi.html','',noTrk],
	// Bosque del Apache hikes:
	['Canyon Trail',33.759012,-106.895278,'CanyonTrail.html','',noTrk],
	// Chaco Canyon hikes:
	['Una Vida',36.033331,-107.911942,'UnaVida.html','',noTrk],
	['Hungo Pavi',36.049536,-107.93031,'HungoPavi.html','',noTrk],
	['Pueblo Bonito',36.059216,-107.958934,'Bonito.html','',noTrk],
	['Pueblo Alto',36.068608,-107.959900,'PuebloAlto.html','palto.json',trackColor],
	['Kin Kletso',36.063864,-107.981315,'KinKletso.html','',noTrk],
	// El Malpais hikes:
	['Big Tubes',34.944733,-108.106983,'BigTubes.html','tubes.json',trackColor],
	['Ice Caves',34.99311,-108.080084,'IceCave.html','',noTrk],
	['El Calderon',34.9698,-108.00325,'ElCalderon.html','cald.json',trackColor],
	// Elena Gallegos hikes:
	['Pino Trail',35.160419, -106.463184,'Pino.html','pino.json',trackColor],
	['Domingo Baca',35.167093,-106.465502,'Domingo.html','baca.json',trackColor],
	// Ghost Ranch hikes:
	['Chimney Rock',36.330525,-106.47482,'ChimneyRock.html','',noTrk],
	['Kitchen Mesa',36.336353,-106.469007,'Kitchen.html','',noTrk],
	// Manzanitas Trail hikes:
	['Tunnel Canyon',35.055938,-106.371517,'TunnelCanyon.html','tun.json',trackColor],
	['Birdhouse Ridge',35.055938,-106.388512,'Birdhouse.html','bird.json',trackColor],
	// Manzanos hikes:
	['Albuquerque Trail',34.793491,-106.372268,'ABQ.html','',noTrk],
	['July 4th Trail',34.790707,-106.382439,'July4.html','',noTrk],
	// Petroglyphs hikes:
	['Piedras Marcadas',35.188867,-106.686269,'Piedras.html','',noTrk],
	['Mesa Point Trail',35.160629,-106.716645,'MesaPoint.html','',noTrk],
	['Cliff Base Trail',35.165471,-106.729088,'CliffBase.html','',noTrk],
	['Macaw Trail',35.170242,-106.717243,'Macaw.html','',noTrk],
	['Rinconada Canyon',35.126851,-106.724635,'Rinconada.html','',noTrk],
	['ABQ Volcanoes',35.13075,-106.7802667,'ABQVolcanoes.html','volc.json',trackColor],
	// Big Tesuque Campground hikes:
	['Upper Tesuque',35.764427,-105.769501,'UpperTesuque.html','utes.json',altTrkClr1],
	['Middle Tesuque',35.738236,-105.779114,'MiddleTesuque.html','mtes.json',altTrkClr2],
	// Winsor Trailhead hikes:
	['Deception Pk',35.807036,-105.783577,'Deception.html','decp.json',trackColor],
	['Nambe Lake',35.818627,-105.797649,'Nambe.html','nambe.json',altTrkClr1],
	['La Vega',35.816873,-105.815796,'LaVega.html','vega.json',altTrkClr2],
	['Upper Rio En Medio',35.802801,-105.827387,'UpperRio.html','uriom.json',trackColor]
];
var othrHikes = [
	['Three Rivers',33.419574,-105.987682,'ThreeRivers.html',''],
	['Corrales Acequia',35.249327,-106.607283,'Acequia.html','aceq.json'],
	['Agua Sarca',35.291533,-106.441050,'AguaSarca.html','sarca.json'],
	['Ancho Rapids',35.797000,-106.246417,'AnchoComb.html','ancho.json'],
	['Apache Canyon',35.629817,-105.858967,'ApacheCanyon.html','apache.json'],
	['Aspen Vista',35.777433,-105.810933,'Aspen.html','aspen.json'],
	['Atalaya Mtn',35.670450,-105.900667,'Atalaya.html','atalaya.json'],
	['Battleship Rock',35.828099,-106.641862,'Battleship.html',''],
	['Borrego/Bear Wallow',35.7462,-105.8342667,'Borrego.html','borrego.json'],
	['Buckman Mesa',35.835833,-106.161033,'Buckman.html','buckman.json'],
	['Cabezon Pk',35.597,-107.1053833,'Cabezon.html','czon.json'],
	['Cerrillos Hills',35.444819,-106.122029,'Cerrillos.html',''],
	['Chamisa Trail',35.728417,-105.86597,'Chamisa.html','cham.json'],
	['Chavez Canyon',36.367385,-106.677235,'ChavezCanyon.html',''],
	['Coyote Call',35.848167,-106.465383,'CoyoteCall.html','ccall.json'],
	['Dale Ball North',35.71075,-105.899467,'DBallNorth.html','dbnorth.json'],
	['Del Agua',35.277,-106.4840333,'DelAguaHike.html','del.json'],
	['Diablo Canyon',35.8046,-106.1362333,'DiabloComb.html','diablo.json'],
	['El Morro',35.038224,-108.348783,'ElMorro.html',''],
	['Ft Bayard Tree',32.782028,-108.147333,'FtBayard.html',''],
	['Hyde Park Circle',35.730717,-105.8371,'HydePk.html','hyde.json'],
	['Josephs Mine',36.305933,-106.05142,'OjoCaliente.html',''],
	['La Bajada',35.551633,-106.23655,'LaBajada.html','baj.json'],
	['La Luz',35.219667,-106.4810167,'LaLuz.html','luz.json'],
	['La Vista Verde',36.341432,-105.736461,'VistaVerde.html',''],
	['Las Conchas Trail',35.814841,-106.533158,'Conchas.html','conch.json'],
	['Mesa Chijuilla',35.995233,-107.0827,'Chijuilla.html',''],
	['Mesa de Cuba',36.010603,-106.980625,'MesaCuba.html',''],
	['Nature Conservancy',35.68701,-105.89697,'Conservancy.html',''],
	['Ojito Wilderness',35.495067,-106.921767,'Ojito.html','ojito.json'],
	['Pinabete Tank',35.771583,-106.19055,'Pinabete.html','ptank.json'],
	['Purgatory Chasm',33.032667,-108.1536667,'Purgatory.html','purg.json'],
	['Pyramid Rock',35.542743,-108.613801,'PyramidRock.html',''],
	['Red Dot - Blue Dot',35.809767,-106.200917,'RedBlueComb.html','rbdot.json'],
	['San Lorenzo Canyon',34.239571,-107.026899,'SanLorenzo.html',''],
	['Strip Mine Trail',35.30015,-106.4804667,'StripMine.html','smine.json'],
	['Sun Mountain',35.65675,-105.92095,'SunMountain.html','sun.json'],
	['Tent Rocks',35.661033,-106.416106,'TentRocks.html',''],
	['Tesuque-Lower',35.759783,-105.845917,'LowerTesuque.html','ltes.json'],
	['Catwalks',33.37781,-108.839842,'Catwalks.html',''],
	['Tetilla Peak',35.602683,-106.19663,'Tetilla.html','tet.json'],
	['Valle Grande',35.857077,-106.491058,'ValleGrandeInSnow.html','vgrand.json'],
	['Viewpoint Loop',35.264798,-105.33362,'Villanueva.html',''],
	['Williams Lake',36.572704,-105.436408,'WilliamsLake.html',''],
	['Traders Trail',36.323333,-105.70366666,'Traders.html','trader.json']
];

msg = '<p>Push x.20</p>';
$('#dbug').append(msg);

// icon defs: need prefix when calling from full map page
var prefix = useTbl ? '' : '../';
var ctrIcon = prefix + 'images/greenpin.png';
var clusterIcon = prefix + 'images/bluepin.png';
var hikeIcon = prefix + 'images/redpin.png';
// icons for geolocation:
var smallGeo = prefix + 'images/starget.png';
var medGeo = prefix + 'images/mtarget.png';
var lgGeo = prefix + 'images/ltarget.png';

// Display whole table when index.html page loads
if ( useTbl ) {
	var $tblRows = $('.sortable tbody tr');
	var iCnt = $tblRows.length;
	var mCnt = ctrPinHikes.length + clusterPinHikes.length + othrHikes.length;
	if ( mCnt != iCnt ) {
		window.alert('Index table row count does not match script: investigate!');
	}
	var fullTbl = new Array();
	for ( var x=0; x<mCnt; x++ ) {
		// every row will be used, so create a sequential array:
		fullTbl[x] = x;
	}
	formTbl( mCnt, fullTbl );
}
var pgLnk = useTbl ? 'pages/' : '../pages/';

// There are three separate arrays for markers, based on their characteristic:
//	1) Visitor Center / Index Pages; 2) "Cluster Hikes" (trailheads overlap or are very
//  close togther; 3) all other hikes
var vcMarkers = [];
var clusterMarkers = [];
var hikeMarkers = [];

// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
// THE MAP CALLBACK FUNCTION:
var map;  // needs to be global!
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

	// /////////////   THE HEART OF ALL MARKER CREATION!!   ///////////////
	function AddVCMarker(location, iconType, pinName, hikePg) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		vcMarkers.push(marker);
		marker.addListener('click', function() {
			window.open(hikePg,'_blank');
		});
	}
	function AddClusterMarker(location, iconType, pinName, hikePg) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		clusterMarkers.push(marker);
		marker.addListener('click', function() {
			window.open(hikePg,'_blank');
		});
	}
	function AddHikeMarker(location, iconType, pinName, hikePg) {
		var marker = new google.maps.Marker({
		  position: location,
		  map: map,
		  icon: iconType,
		  title: pinName
		});
		hikeMarkers.push(marker);
		marker.addListener('click', function() {
			window.open(hikePg,'_blank');
		});
	}
	var loc;
	var sym;
	var nme;
	var hpg;
	
	// Create all the markers: 1st, visitor centers:
	var noOfVCs = ctrPinHikes.length
	sym = ctrIcon;
	for (var i=0; i<noOfVCs; i++) {
		loc = {lat: ctrPinHikes[i][1], lng: ctrPinHikes[i][2] };
		nme = ctrPinHikes[i][0];
		hpg = pgLnk + ctrPinHikes[i][3];
		AddVCMarker(loc, sym, nme, hpg);
	}
	// Now, the "clustered" hikes:
	var noOfCHikes = clusterPinHikes.length;
	sym =clusterIcon;
	for (var j=0; j<noOfCHikes; j++ ) {
		loc = {lat: clusterPinHikes[j][1], lng: clusterPinHikes[j][2] };
		nme = clusterPinHikes[j][0];
		hpg = pgLnk + clusterPinHikes[j][3];
		AddClusterMarker(loc, sym, nme, hpg); // could add color id here...
	}
	// Finally, the remaining "normal" hike markers
	var noOfSolo = othrHikes.length;
	sym = hikeIcon;
	for (var k=0; k<noOfSolo; k++) {
		loc = {lat: othrHikes[k][1], lng: othrHikes[k][2] };
		nme = othrHikes[k][0];
		hpg = pgLnk + othrHikes[k][3];
		AddHikeMarker(loc, sym, nme, hpg);
	}

	// Establish polylines for areas where trailhead has more than 1 hike
	// BANDELIER:
	var BandCtr = {lat: 35.778943, lng: -106.270838 };	
	var BandHikeMrkrLocs = [ 
		{lat: 35.793670, lng: -106.273155 },
		BandCtr,
		{lat: 35.788735, lng: -106.282079 },
		BandCtr,
		{lat: 35.779219, lng: -106.285744 },
		BandCtr,
		{lat: 35.769573, lng: -106.282433 },
		BandCtr,
		{lat: 35.764312, lng: -106.273698 }
	];
	var Blines = new google.maps.Polyline({
		path: BandHikeMrkrLocs,
        geodesic: false,
        strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	Blines.setMap(null);
	var KinAltoLoc = {lat: 36.064977, lng: -107.969867 };
	var KinAltMrkrLocs = [
		{lat: 36.063864, lng: -107.981315 },
		KinAltoLoc,
		{lat: 36.068608, lng: -107.959900 }
	];
	var KinAltLines = new google.maps.Polyline({
		path: KinAltMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	KinAltLines.setMap(null);
	// SANTA FE SKI AREA (Winsor Trailhead):
	var SkiCtr = {lat: 35.795845, lng: -105.804605 };
	var SkiMrkrLocs = [
		{lat: 35.807036, lng: -105.783577 },
		SkiCtr,
		{lat: 35.818627, lng: -105.797649 },
		SkiCtr,
		{lat: 35.816873, lng: -105.815796 },
		SkiCtr,
		{lat: 35.802801, lng: -105.827387 }
	];
	var SkiLines = new google.maps.Polyline({
		path: SkiMrkrLocs,
        geodesic: false,
        strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	SkiLines.setMap(null);
	// ELENA GALLEGOS: PINO & DOMINGO BACA:
	var eg = {lat:35.163250, lng: -106.470067 };
	var egMrkrLocs = [
		{lat: 35.160419, lng: -106.463184 },
		eg,
		{lat: 35.167093, lng: -106.465502}
	];
	var egLines = new google.maps.Polyline({
		path: egMrkrLocs,
        geodesic: false,
        strokeColor: lineColor,
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	egLines.setMap(null);
	// BIG TESUQUE:
	var tes = {lat: 35.769508, lng: -105.809155 };
	var tesMrkrLocs = [
		{lat: 35.764427, lng: -105.769501 },
		tes,
		{lat: 35.738236, lng: -105.779114 }
	];
	var tesLines = new google.maps.Polyline({
		path: tesMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	tesLines.setMap(null);
	// PETROGLYPHS: BOCA NEGRA
	var CliffCtr = {lat: 35.161988, lng: -106.718203 };
	var CliffMacMrkrLocs = [
		{lat: 35.165471, lng: -106.729088 },
		CliffCtr,
		{lat: 35.170242, lng: -106.717243 }
	];
	var CliffMacLines = new google.maps.Polyline({
		path: CliffMacMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	CliffMacLines.setMap(null);
	// MANZANITAS MTN TRAILS:
	var mmt = {lat: 35.046562, lng: -106.383088 };
	var bhse = {lat: 35.055938, lng: -106.388512 };
	var tunl = {lat: 35.055938, lng: -106.371517 };
	var mmtMrkrLocs = [ bhse, mmt, tunl ];
	var mmtLines = new google.maps.Polyline({
		path: mmtMrkrLocs,
		geodesic: false,
		strokeColor: lineColor,
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	mmtLines.setMap(null);
	// END OF POLYLINES CREATION
	
	// PAN AND ZOOM HANDLERS:
	map.addListener('zoom_changed', function() {
		var curZoom = map.getZoom();
		if (useTbl) {
			var perim = String(map.getBounds());
			IdTableElements(perim);
		}
		if ( curZoom > 10 ) {
			Blines.setMap(map);
			KinAltLines.setMap(map);
			SkiLines.setMap(map);
			egLines.setMap(map);
			tesLines.setMap(map);
			CliffMacLines.setMap(map);
			mmtLines.setMap(map);
			for (var m=0; m<ctrPinHikes.length; m++) {
				vcMarkers[m].setMap(null);
			}
		} else {
			Blines.setMap(null);
			KinAltLines.setMap(null);
			SkiLines.setMap(null);
			egLines.setMap(null);
			tesLines.setMap(null);
			CliffMacLines.setMap(null);
			mmtLines.setMap(null);
			for (var n=0; n<ctrPinHikes.length; n++) {
				vcMarkers[n].setMap(map);
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


// //////////////////////// DYNAMIC TABLE SIZING  ////////////////////////////////
// Function to find elements within current bounds and display them in a table
function IdTableElements(boundsStr) {
	// ESTABLISH CURRENT VIEWPORT BOUNDS:
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
	var hikeSet = new Array();
	var tblEl = new Array(); // holds the index into the row number array: tblRows
	var pinLat;
	var pinLng;
	// REMOVE previous table:
	$('div #usrTbl').replaceWith('<div id="usrTbl"></div>');
	/* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
	// First, check to see if any ctrPinHikes are within the viewport;
	// if so, include them in the table
	var n = 0; //
	var rowCnt = 0;
	for (j=0; j<ctrPinHikes.length; j++) {
		hikeSet = ctrPinHikes[j];
		pinLat = parseFloat(hikeSet[1]);
		pinLng = parseFloat(hikeSet[2]);
		if( pinLng <= east && pinLng >= west && pinLat <= north && pinLat >= south ) {
			tblEl[n] = j;
			n++;
			rowCnt ++;
		}
	}
	// now look for clusterPinHikes
	for (k=0; k<clusterPinHikes.length; k++) {
		hikeSet = clusterPinHikes[k];
		pinLat = parseFloat(hikeSet[1]);
		pinLng = parseFloat(hikeSet[2]);
		if( pinLng <= east && pinLng >= west && pinLat <= north && pinLat >= south ) {
			tblEl[n] = ctrPinHikes.length + k;
			n++;
			rowCnt++;
		}
	}
	// and lastly, othrHikes
	for (l=0; l<othrHikes.length; l++) {
		hikeSet = othrHikes[l];
		pinLat = parseFloat(hikeSet[1]);
		pinLng = parseFloat(hikeSet[2]);
		if( pinLng <= east && pinLng >= west && pinLat <= north && pinLat >= south ) {
			tblEl[n] = ctrPinHikes.length + clusterPinHikes.length + l;
			n++;
			rowCnt++;
		}
	}
	
	if ( rowCnt === 0 ) {
		msg = '<p>NO hikes in this area</p>';;
		$('#usrTbl').append(msg);
	} else {
		formTbl( rowCnt, tblEl );
	}
} // END: IdTableElements() FUNCTION
// //////////////////////// END OF DYNAMIC TABLE SIZING /////////////////////

// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
var newTrack; // used repeatedly to assign incoming JSON data
// the following is not used yet, but intended to allow individual turn on/off of tracks
var allTheTracks = []; // array of all existing track object references [trkObj's]
var trkObj = { trk: 'ref', trkName: 'trkname' };
var clusterCnt = 0; // number of clusterPinHikes processed
var othrCnt = 0; // number of othrHikes processed

var trackForm = setInterval(startTracks,40);

function startTracks() {
	if ( mapRdy ) {
		clearInterval(trackForm);
		drawTracks(clusterCnt, othrCnt);
	}
}

function sglTrack(trkUrl,trkType,trkColor) {
	$.ajax({
		dataType: "json",
		url: trkUrl,
		success: function(trackDat) {
			newTrack = trackDat;
			trkObj['trk'] = new google.maps.Polyline({
				path: newTrack,
				geodesic: true,
				strokeColor: trkColor,
				strokeOpacity: 1.0,
				strokeWeight: 2
			});
			trkObj['trk'].setMap(map);
			allTheTracks.push(trkObj);
			if ( trkType == 0 ) {
				drawTracks(clusterCnt++,othrCnt);
			} else {
				drawTracks(clusterCnt,othrCnt++);
			}
		},
		error: function() {
			msg = '<p>Did not succeed in getting JSON data</p>';
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
			trkObj['trkName'] = trackFile.substring(0,cindx);
			trackFile = 'json/' + trackFile;
			clusColor = clusterPinHikes[cluster][5];
			sglTrack(trackFile,0,clusColor);
		} else {
			drawTracks(clusterCnt++,othrCnt);
		}
	} else {  // End of clusterHike test
		if ( othr < othrHikes.length ) {
			if ( othrHikes[othr][4] ) {
				trackFile = othrHikes[othr][4];
				var oindx = trackFile.indexOf('.json');
				trkObj['trkName'] = trackFile.substring(0,oindx);
				trackFile = 'json/' + trackFile;
				sglTrack(trackFile,1,trackColor);
			} else {
				drawTracks(clusterCnt,othrCnt++);
			}
		}  else {
			msg = '<p>Total number of tracks loaded: ' + allTheTracks.length + '</p>';
			$('#dbug').append(msg);
		} // End of othrHike test
	}  // End of whole test
}  // END FUNCTION
// /////////////////////// END OF HIKING TRACK DRAWING /////////////////////


// ////////////////////////////  GEOLOCATION CODE //////////////////////////
/*
var geoMark;			// the geolocation marker object
var geoStat = false;    // false if geolocation is off, true if it is enabled
var firstCall = true;   // invoke geolocation 'firstCall' function only once, first time turned on
					    // after that, simply turn interval update on or off;
var clickEnabled = false; // when click updates are enabled...
var clickOn;			// var for setting listener when On-Click update is active
var buttonEna = false;  // when update buttons are enabled...
var intTarg; 			// variable used to access key value in rateObj
var chgCnt = 1;			// used in an effort to NOT reuse variables for setInterval
var rateObj = { 'key1': 'int1' };
var geoOptions = { enableHighAccuracy: true };
		  
function updateOnClick() {
	if ( buttonEna ) {
		clearInterval(rateObj[intTarg]);
		$('#firstRate').css('background-color','White');
		$('#secondRate').css('background-color','White');
		$('#clicker').css('background-color','#cfe2cf');
		if ( !clickEnabled ) {
			clickEnabled = true;
			clickOn = geoMark.addListener('click', getLoc);
		} else {
			clickEnabled = false;
			$('#clicker').css('background-color','white');
			google.maps.event.removeListener(clickOn);
		}
	} 
}
function refreshFct1( newRate ) {
	if ( buttonEna ) {
		if ( clickEnabled ) {
			$('#clicker').css('background-color','White');
			//google.maps.event.removeListener(clickOn);
			clickEnabled = false;	
		}
		$('#secondRate').css('background-color','White');
		$('#firstRate').css('background-color','#cfe2cf');
		clearInterval(rateObj[intTarg]);	
		refreshRate( newRate );
	}
}
function refreshFct2( newRate ) {
	if ( buttonEna ) {
		if ( clickEnabled ) {
			$('#clicker').css('background-color','White');
			//google.maps.event.removeListener(clickOn);
			clickEnabled = false;	
		}
		$('#firstRate').css('background-color','White');
		$('#secondRate').css('background-color','#cfe2cf');	
		clearInterval(rateObj[intTarg]);
		refreshRate( newRate );
	}
}
function refreshRate( milliSeconds ) {
	clearInterval(rateObj[intTarg]);
	intTarg = 'key' + chgCnt++;
	rateObj[intTarg] = setInterval(getLoc, milliSeconds);
}

function geoLoc() {  // this is called when 'Enable: Turn On/Off' button is clicked
	if ( firstCall ) {
		firstCall = false;
		getLoc();
	} // end of firstCall test
	// now turn on/off the interval timer to do position updates: (and update panel colors)
	if ( !geoStat ) {   
		$('#geoExtras').css('background-color','DarkSeaGreen');
		$('#firstRate').css('background-color','White');
		$('#secondRate').css('background-color','White');
		$('#clicker').css('background-color','White');
		$('.sw').addClass('friendly');
		geoStat = true;
		$('#toggle').val('Turn Off');
		buttonEna = true;
		intTarg = 'key' + chgCnt++;
		rateObj[intTarg] = setInterval(getLoc, 10000); // this is the default refresh value
		if( geoMark ) {
			geoMark.setMap(map);
		}
	} else {
		clearInterval(rateObj[intTarg]);
		$('.sw').removeClass('friendly');
		$('#geoExtras').css('background-color','LightGray');
		$('#firstRate').css('background-color','LightGray');
		$('#secondRate').css('background-color','LightGray');
		$('#clicker').css('background-color','LightGray');
		geoStat = false;
		buttonEna = false;
		$('#toggle').val('Turn On');
		if( geoMark ) {
			geoMark.setMap(null);
		}
	} // end of geoStat testing	
} // end of GEOLOC function (called by button-click)

function getLoc() {
	if (Modernizr.geolocation) {
		navigator.geolocation.getCurrentPosition(success, fail, geoOptions);
		function fail(PositionError) {
			switch (PositionError.code) {
				case 1:
					msg = 'GEOLOCATION: Permission denied';
					break;
				case 2:
					msg = 'GEOLOCATION: Unavailable';
					break;
				case 3:
					msg = 'GEOLOCATION: Request timed out';
					break;
			}
			window.alert(msg);
		}  // end of FAIL function
		function success(Position) {
			if ( geoMark ) {
				geoMark.setMap(null);
				geoMark = null;
			}
			var cLat = Position.coords.latitude;
			var cLng = Position.coords.longitude;
			var myLoc = new google.maps.LatLng(cLat, cLng);
			geoMark = new google.maps.Marker({
				position: myLoc,
				map: map,
				icon: lgGeo,
				size: new google.maps.Size(32, 32),
				// The origin for this image is (0, 0)
				origin: new google.maps.Point(0, 0),
				// The anchor for this image is the center (16, 16).
				anchor: new google.maps.Point(16, 16)
			});
		} // end of SUCCESS function
	} else {
		window.alert('Geolocation not supported on this browser');
	}
}
*/

// CURRENT USAGE:
var watchOptions = { enableHighAccuracy: true };
var geoIcon = medGeo;

var geoTmr = setInterval(turnOnGeoLoc,100);

function turnOnGeoLoc() {
	if ( mapRdy ) {
		clearInterval(geoTmr);
		setupLoc();
	}
}

function setupLoc() {
	if (Modernizr.geolocation) {
		var myGeoLoc = navigator.geolocation.watchPosition(watchSuccess, watchError, watchOptions);
		function watchSuccess(pos) {
			var watchPos = pos.coords;
			var wLat = watchPos.latitude;
			var wLng = watchPos.longitude;
			var newWPos = {lat: wLat, lng: wLng };
			if ( geoMarker ) {
				geoMarker.setMap(null);
				geoMarker = null;
			}
			geoMarker = new google.maps.Marker({
				position: newWPos,
				map: map,
				icon: geoIcon,
				size: new google.maps.Size(24,24),
				origin: new google.maps.Point(0, 0),
				anchor: new google.maps.Point(12, 12)
			});
		} // end of watchSuccess function
		function watchError(eobj) {
			msg = '<p>Error in watch call: code ' + eobj.code + '</p>';
			window.alert(msg);
		}
	} else {
		window.alert('Geolocation not supported on this browser');
	}
}
// //////////////////////////////////////////////////////////////
	
