// generic var for outputting debug messages
var msg;
// generics for setting up multiple markers
var trailHead;
var markerLoc;
var pgUrl;

// Table structure, to be populated with rows later in script (see 'var outHike'):
var tblHtml = '<table class="msortable">';
tblHtml += $('table').html();
var inx = tblHtml.indexOf('<tbody') + 8;
tblHtml = tblHtml.substring(0,inx);
var endTbl = ' </tbody> </table>';

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
};  // end of object declaration

// -------------------------------   IMPORTANT NOTE: ----------------------------
//	The index.html table MUST list items in the
//	order shown below in order for the correct elements to be listed
//	in the user table of hikes
//	-----------------------------------------------------------------------------                                         */

// THE FOLLOWING HIKE SITES ARE ALWAYS SHOWN IN THE TABLE IF IN BOUNDS
// When zoom <= 10, show these markers; When zoom > 10, don't show markers
var ctrPinHikes = [
	['Bandelier',35.779039,-106.270788,'Bandelier.html'],
	//['Bosque del Apache',33.805197,-106.891603,''],
	['Chaco Canyon',36.030250,-107.91080,'Chaco.html'],
	['El Malpais',34.970407,-107.810152,'ElMalpais.html'],
	//['Elena Gallegos',35.163181,-106.470118,''],
	//['Ghost Ranch',36.330975,-106.472760,''],
	//['Manzanitas Mtn Trails',35.046561,-106.383116,''],
	//['Manzanos Mtn Trails',34.791913,-106.381613,''],
	['Petroglyphs Natl Mon',35.138644,-106.711196,'Petroglyphs.html']
	//['Big Tesuque Camp',35.769502,-105.809310,''],
	//['Winsor Trailhead',35.795537,-105.804860,'']
];
// Always shoiw markers:
var clusterPinHikes = [
	// Bandelier hikes:
	['Ruins Trail',35.793670,-106.273155,'MainLoop.html'],
	['Falls Trail',35.788735,-106.282079,'FallsTrail.html'],
	['Frey Trail',35.779219,-106.285744,'Frey.html'],
	['Frijolito Ruins',35.769573,-106.282433,'Frijolito.html'],
	['Alcove House',35.764312,-106.273698,'AlcoveHouse.html'],
	['Tsankawi Ruins',35.860416,-106.224682,'Tsankawi.html'],
	// Bosque del Apache hikes:
	['Canyon Trail',33.759012,-106.895278,'CanyonTrail.html'],
	// Chaco Canyon hikes:
	['Una Vida',36.033331,-107.911942,'UnaVida.html'],
	['Hungo Pavi',36.049536,-107.93031,'HungoPavi.html'],
	['Pueblo Bonito',36.059216,-107.958934,'Bonito.html'],
	['Pueblo Alto',36.065393,-107.968054,'PuebloAlto.html'],
	['Kin Kletso',36.064890,-107.969792,'KinKletso.html'],
	// El Malpais hikes:
	['Big Tubes',34.944733,-108.106983,'BigTubes.html'],
	['Ice Caves',34.99311,-108.080084,'IceCave.html'],
	['El Calderon',34.9698,-108.00325,'ElCalderon.html'],
	// Elena Gallegos hikes:
	['Pino Trail',35.163732, -106.468270,'Pino.html'],
	['Domingo Baca',35.166117,-106.467717,'Domgingo.html'],
	// Ghost Ranch hikes:
	['Chimney Rock',36.330525,-106.47482,'ChimneyRock.html'],
	['Kitchen Mesa',36.336353,-106.469007,'Kitchen.html'],
	// Manzanitas Trail hikes:
	['Tunnel Canyon',35.055938,-106.371517,'TunnelCanyon.html'],
	['Birdhouse Ridge',35.055938,-106.388512,'Birdhouse.html'],
	// Manzanos hikes:
	['Albuquerque Trail',34.793491,-106.372268,'ABQ.html'],
	['July 4th Trail',34.790707,-106.382439,'July4.html'],
	// Petroglyphs hikes:
	['Piedras Marcadas',35.188867,-106.686269,'Piedras.html'],
	['Mesa Point Trail',35.160629,-106.716645,'MesaPoint.html'],
	['Cliff Base Trail',35.162105,-106.718386,'CliffBase.html'],
	['Macaw Trail',35.162157,-106.718032,'Macaw.html'],
	['Rinconada Canyon',35.126851,-106.724635,'Rinconada.html'],
	['ABQ Volcanoes',35.13075,-106.7802667,'ABQVolcanoes.html'],
	// Big Tesuque Campground hikes:
	['Upper Tesuque',35.764427,-105.769501,'UpperTesugue.html'],
	['Middle Tesuque',35.738236,-105.779114,'MiddleTesuque.html'],
	// Winsor Trailhead hikes:
	['Deception Pk',35.807036,-105.783577,'Deception.html'],
	['Nambe Lake',35.818627,-105.797649,'Nambe.html'],
	['La Vega',35.816873,-105.815796,'LaVega.html'],
	['Upper Rio En Medio',35.802801,-105.827387,'UpperRio.html']
];
// Always show markers:
var othrHikes = [
	['Three Rivers',33.419574,-105.987682,'ThreeRivers.html'],
	['Corrales Acequia',35.249327,-106.607283,'Acequia.html'],
	['Agua Sarca',35.291533,-106.441050,'AguaSarca.html'],
	['Ancho Rapids',35.797000,-106.246417,'AnchoComb.html'],
	['Apache Canyon',35.629817,-105.858967,'ApacheCanyon.html'],
	['Aspen Vista',35.777433,-105.810933,'Aspen.html'],
	['Atalaya Mtn',35.670450,-105.900667,'Atalaya.html'],
	['Battleship Rock',35.828099,-106.641862,'Battleship.html'],
	['Borrego/Bear Wallow',35.7462,-105.8342667,'Borrego.html'],
	['Buckman Mesa',35.835833,-106.161033,'Buckman.html'],
	['Cabezon Pk',35.597,-107.1053833,'Cabezon.html'],
	['Cerrillos Hills',35.444819,-106.122029,'Cerrillos.html'],
	['Chamisa Trail',35.728417,-105.86597,'Chamisa.html'],
	['Chavez Canyon',36.367385,-106.677235,'ChavezCanyon.html'],
	['Coyote Call',35.848167,-106.465383,'CoyoteCall.html'],
	['Dale Ball North',35.71075,-105.899467,'DBallNorth.html'],
	['Del Agua',35.277,-106.4840333,'DelAguaHike.html'],
	['Diablo Canyon',35.8046,-106.1362333,'DiabloComb.html'],
	['El Morro',35.038224,-108.348783,'ElMorro.html'],
	['Ft Bayard Tree',32.782028,-108.147333,'FtBayard.html'],
	['Hyde Park Circle',35.730717,-105.8371,'HydePk.html'],
	['Josephs Mine',36.305933,-106.05142,'OjoCaliente.html'],
	['La Bajada',35.551633,-106.23655,'LaBajada.html'],
	['La Luz',35.219667,-106.4810167,'LaLuz.html'],
	['La Vista Verde',36.341432,-105.736461,'VistaVerde.html'],
	['Las Conchas Trail',35.814841,-106.533158,'Conchas.html'],
	['Mesa Chijuilla',35.995233,-107.0827,'Chijuilla.html'],
	['Mesa de Cuba',36.010603,-106.980625,'MesaCuba.html'],
	['Nature Conservancy',35.68701,-105.89697,'Conservancy.html'],
	['Ojito Wilderness',35.495067,-106.921767,'Ojito.html'],
	['Pinabete Tank',35.771583,-106.19055,'Pinabete.html'],
	['Purgatory Chasm',33.032667,-108.1536667,'Purgatory.html'],
	['Pyramid Rock',35.542743,-108.613801,'PyramidRock.html'],
	['Red Dot - Blue Dot',35.809767,-106.200917,'RedBlueComb.html'],
	['San Lorenzo Canyon',34.239571,-107.026899,'SanLorenzo.html'],
	['Strip Mine Trail',35.30015,-106.4804667,'StripMine.html'],
	['Sun Mountain',35.65675,-105.92095,'SunMountain.html'],
	['Tent Rocks',35.661033,-106.416106,'TentRocks.html'],
	['Tesuque-Lower',35.759783,-105.845917,'LowerTesuque.html'],
	['Catwalks',33.37781,-108.839842,'Catwalks.html'],
	['Tetilla Peak',35.602683,-106.19663,'Tetilla.html'],
	['Valle Grande',35.857077,-106.491058,'ValleGrandeInSnow.html'],
	['Viewpoint Loop',35.264798,-105.33362,'Villanueva.html'],
	['Williams Lake',36.572704,-105.436408,'WilliamsLake.html']
];

// icon defs:
var ctrIcon = 'images/greenpin.png';
var clusterIcon = 'images/bluepin.png';
var hikeIcon = 'images/redpin.png';
var $tblRows = $('.sortable tbody tr');
var iCnt = $tblRows.length;
var mCnt = ctrPinHikes.length + clusterPinHikes.length + othrHikes.length;
if ( mCnt != iCnt ) {
	window.alert('Index table row count does not match script: investigate!');
}

// THE MAP CALLBACK FUNCTION:
function initMap() {
	
	// NOW TO THE MAP!!
	var nmCtr = {lat: 34.450, lng: -106.042};
	
	var mapDiv = document.getElementById('map');
	var map = new google.maps.Map(mapDiv, {
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
	
	// Establish the markers for the "Index Page" hikes [aka: ctrPinHikes] are individually
	// named, so that they can be turned off during zoom in [by name].
	var BandLoc = ctrPinHikes[0];
	var BandMrkr = new google.maps.Marker({
		position: {lat: BandLoc[1], lng: BandLoc[2] },
		map: map,
		icon: ctrIcon,
		title: BandLoc[0]
	});
	BandMrkr.addListener('click', function() {
		var Bpg = 'pages/' + BandLoc[0];
		window.open(Bpg,'_blank');
	});
	var ChacoLoc = ctrPinHikes[1];
	var ChacoMrkr = new google.maps.Marker({
		position: {lat: ChacoLoc[1], lng: ChacoLoc[2] },
		map: map,
		icon: ctrIcon,
		title: ChacoLoc[0]
	});
	ChacoMrkr.addListener('click', function() {
		var Cpg = 'pages/' + ChacoLoc[3];
		window.open(Cpg,'_blank');
	});
	var ElMalLoc = ctrPinHikes[2];
	var ElMalMrkr = new google.maps.Marker({
		position: {lat: ElMalLoc[1], lng: ElMalLoc[2] },
		map: map,
		icon: ctrIcon,
		title: ElMalLoc[0]
	});
	ElMalMrkr.addListener('click', function() {
		Epg = 'pages/' + ElMalLoc[3];
		window.open(Epg,'_blank');
	});
	var PetroLoc = ctrPinHikes[3];
	var PetroMrkr = new google.maps.Marker({
		position: {lat: PetroLoc[1], lng: PetroLoc[2] },
		map: map,
		icon: ctrIcon,
		title: PetroLoc[0]
	});
	PetroMrkr.addListener('click', function() {
		var Ppg = 'pages/' + PetroLoc[3];
		window.open(Ppg,'_blank');
	});
	// generic markers (always on):
	var clusterPin;
	for ( var i=0; i<clusterPinHikes.length; i++ ) {
		trailHead = clusterPinHikes[i];
		markerLoc = {lat: trailHead[1], lng: trailHead[2]};
		pgUrl = 'pages/' + trailHead[3];
		clusterPin = new google.maps.Marker( {
			position: markerLoc,
			map: map,
			icon: clusterIcon,
			title: trailHead[0]
		});
		clusterPin.addListener('click', function() {
			window.open(pgUrl,'_blank');
		});
	}
	var othrPin;
	for ( var i=0; i<othrHikes.length; i++ ) {
		trailHead = othrHikes[i];
		markerLoc = {lat: trailHead[1], lng: trailHead[2]};
		pgUrl = 'pages/' + trailHead[3];
		othrPin = new google.maps.Marker( {
			position: markerLoc,
			map: map,
			icon: hikeIcon,
			title: trailHead[0]
		});
		othrPin.addListener('click', function() {
			window.open(pgUrl,'_blank');
		});
	}	

	/* Establish polylines for areas where trailhead has more than 1 hike */
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
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	Blines.setMap(null);
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
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 2
	});
	SkiLines.setMap(null);
	// ELENA GALLEGOS: PINO & DOMINGO BACA:
	var eg = {lat:35.163250, lng: -106.470067 };
	var egMrkrLocs = [
		{lat: 35.163732, lng: -106.468270 },
		eg,
		{lat: 35.166117, lng: -106.467717 }
	];
	var egLines = new google.maps.Polyline({
		path: egMrkrLocs,
        geodesic: false,
        strokeColor: '#FF0000',
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
		strokeColor: '#FF0000',
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	tesLines.setMap(null);
	// MANZANITAS MTN TRAILS:
	var mmt = {lat: 35.046562, lng: -106.383088 };
	var bhse = {lat: 35.055938, lng: -106.388512 };
	var tunl = {lat: 35.055938, lng: -106.371517 };
	var mmtMrkrLocs = [ bhse, mmt, tunl ];
	var mmtLines = new google.maps.Polyline({
		path: mmtMrkrLocs,
		geodesic: false,
		strokeColor: '#FF0000',
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	mmtLines.setMap(null);
	/* END OF POLYLINES CREATION */

	map.addListener('zoom_changed', function() {
		var curZoom = map.getZoom();
		var perim = String(map.getBounds());
		IdTableElements(perim);
		if ( curZoom > 10 ) {
			Blines.setMap(map);
			SkiLines.setMap(map);
			egLines.setMap(map);
			tesLines.setMap(map);
			mmtLines.setMap(map);
			BandMrkr.setMap(null);
			ChacoMrkr.setMap(null);
			ElMalMrkr.setMap(null);
			PetroMrkr.setMap(null);
		} else {
			Blines.setMap(null);
			SkiLines.setMap(null);
			egLines.setMap(null);
			tesLines.setMap(null);
			mmtLines.setMap(null);
			BandMrkr.setMap(map);
			ChacoMrkr.setMap(map);
			ElMalMrkr.setMap(map);
			PetroMrkr.setMap(map);
		}
	});
	
	map.addListener('dragend', function() {
		var newBds = String(map.getBounds());
		IdTableElements(newBds);
	});
	
	
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
		$('div #wholeTbl').replaceWith('<div id="wholeTbl"></div>');
		
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
		
		var outHike = new Array();
		if ( rowCnt === 0 ) {
			msg = '<p>NO hikes in this area</p>';;
			$('#usrTbl').append(msg);
		} else {
			var thisTbl = tblHtml + ' <tr>';
			var indxRow;
			for (m=0; m<rowCnt; m++) {
				indxRow = tblEl[m];
				thisTbl += $tblRows.eq(indxRow).html();
				thisTbl += ' </tr> ';
			}
			thisTbl += endTbl;
			$('#usrTbl').html(thisTbl);
			$('#metric').css('display','block');
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
		}
	}

}  // end of initMap()