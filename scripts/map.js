// generic var for outputting debug messages
var msg;

// generics for setting up multiple markers
var trailHead;
var markerLoc;

// Table structure, to be populated with rows later in script (see 'var outHike'):
var tblHtml = '<table class="msortable" onMouseOver="javascript:findPinFromRow(event);"'
tblHtml += ' onMouseOut="javascript:undoMarker();">';
tblHtml += $('table').html();
var inx = tblHtml.indexOf('<tbody') + 8;
tblHtml = tblHtml.substring(0,inx);
var endTbl = ' </tbody> </table>';
endTbl += ' <div> <p id="metric" class="dressing">Click here for metric units</p> </div>';


/* ******************** FUNCTION DECLARATIONS / DEFINITIONS ****************** */
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

// ROW-FINDING FUNCTIONS FOR mouseover TABLE...
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
/* ***************************************************************************** */
			
			
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
	['Pueblo Alto',36.068608,-107.959900,'PuebloAlto.html'],
	['Kin Kletso',36.063864,-107.981315,'KinKletso.html'],
	// El Malpais hikes:
	['Big Tubes',34.944733,-108.106983,'BigTubes.html'],
	['Ice Caves',34.99311,-108.080084,'IceCave.html'],
	['El Calderon',34.9698,-108.00325,'ElCalderon.html'],
	// Elena Gallegos hikes:
	['Pino Trail',35.160419, -106.463184,'Pino.html'],
	['Domingo Baca',35.167093,-106.465502,'Domingo.html'],
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
	['Cliff Base Trail',35.165471,-106.729088,'CliffBase.html'],
	['Macaw Trail',35.170242,-106.717243,'Macaw.html'],
	['Rinconada Canyon',35.126851,-106.724635,'Rinconada.html'],
	['ABQ Volcanoes',35.13075,-106.7802667,'ABQVolcanoes.html'],
	// Big Tesuque Campground hikes:
	['Upper Tesuque',35.764427,-105.769501,'UpperTesuque.html'],
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


/* **************************** MAIN MAP CALL ************************** */
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
	
/* ************************** MANY MARKER DEFINITIONS ************************** */
/* ***************************************************************************** */

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
		var Bpg = 'pages/' + BandLoc[3];
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
		var msgOut = ChacoMrkr.getTitle();
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
	// LOTS OF CODE, but no more execution than the generic loop which was useless in
	// terms of assigning listener functions: always defaulted to last loop value :-(
	// CLUSTER PIN HIKES:
	var RuinsDat = clusterPinHikes[0];
	var Ruins = new google.maps.Marker({
		position: {lat: RuinsDat[1], lng: RuinsDat[2] },
		map: map,
		icon: clusterIcon,
		title: RuinsDat[0]
	});
	Ruins.addListener('click', function() {
		var pgUrl = 'pages/' + RuinsDat[3];
		window.open(pgUrl,'_blank');
	});

	var FallsDat = clusterPinHikes[1];
	var Falls = new google.maps.Marker({
		position: {lat: FallsDat[1], lng: FallsDat[2] },
		map: map,
		icon: clusterIcon,
		title: FallsDat[0]
	});
	Falls.addListener('click', function() {
		var pgUrl = 'pages/' + FallsDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var FreyDat = clusterPinHikes[2];
	var Frey = new google.maps.Marker({
		position: {lat: FreyDat[1], lng: FreyDat[2] },
		map: map,
		icon: clusterIcon,
		title: FreyDat[0]
	});
	Frey.addListener('click', function() {
		var pgUrl = 'pages/' + FreyDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var FrijDat = clusterPinHikes[3];
	var Frij = new google.maps.Marker({
		position: {lat: FrijDat[1], lng: FrijDat[2] },
		map: map,
		icon: clusterIcon,
		title: FrijDat[0]
	});
	Frij.addListener('click', function() {
		var pgUrl = 'pages/' + FrijDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AlcDat = clusterPinHikes[4];
	var Alc = new google.maps.Marker({
		position: {lat: AlcDat[1], lng: AlcDat[2] },
		map: map,
		icon: clusterIcon,
		title: AlcDat[0]
	});
	Alc.addListener('click', function() {
		var pgUrl = 'pages/' + AlcDat[3];
		window.open(pgUrl,'_blank');
	}); 
	
	var TsanDat = clusterPinHikes[5];
	var Tsan = new google.maps.Marker({
		position: {lat: TsanDat[1], lng: TsanDat[2] },
		map: map,
		icon: clusterIcon,
		title: TsanDat[0]
	});
	Tsan.addListener('click', function() {
		var pgUrl = 'pages/' + TsanDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CanyDat = clusterPinHikes[6];
	var Cany = new google.maps.Marker({
		position: {lat: CanyDat[1], lng: CanyDat[2] },
		map: map,
		icon: clusterIcon,
		title: CanyDat[0]
	});
	Cany.addListener('click', function() {
		var pgUrl = 'pages/' + CanyDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var UnaDat = clusterPinHikes[7];
	var Una = new google.maps.Marker({
		position: {lat: UnaDat[1], lng: UnaDat[2] },
		map: map,
		icon: clusterIcon,
		title: UnaDat[0]
	});
	Una.addListener('click', function() {
		var pgUrl = 'pages/' + UnaDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var HungoDat = clusterPinHikes[8];
	var Hungo = new google.maps.Marker({
		position: {lat: HungoDat[1], lng: HungoDat[2] },
		map: map,
		icon: clusterIcon,
		title: HungoDat[0]
	});
	Hungo.addListener('click', function() {
		var pgUrl = 'pages/' + HungoDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BonDat = clusterPinHikes[9];
	var Bon = new google.maps.Marker({
		position: {lat: BonDat[1], lng: BonDat[2] },
		map: map,
		icon: clusterIcon,
		title: BonDat[0]
	});
	Bon.addListener('click', function() {
		var pgUrl = 'pages/' + BonDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AltoDat = clusterPinHikes[10];
	var Alto = new google.maps.Marker({
		position: {lat: AltoDat[1], lng: AltoDat[2] },
		map: map,
		icon: clusterIcon,
		title: AltoDat[0]
	});
	Alto.addListener('click', function() {
		var pgUrl = 'pages/' + AltoDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var KletDat = clusterPinHikes[11];
	var Klet = new google.maps.Marker({
		position: {lat: KletDat[1], lng: KletDat[2] },
		map: map,
		icon: clusterIcon,
		title: KletDat[0]
	});
	Klet.addListener('click', function() {
		var pgUrl = 'pages/' + KletDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var TubeDat = clusterPinHikes[12];
	var Tube = new google.maps.Marker({
		position: {lat: TubeDat[1], lng: TubeDat[2] },
		map: map,
		icon: clusterIcon,
		title: TubeDat[0]
	});
	Tube.addListener('click', function() {
		var pgUrl = 'pages/' + TubeDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var IceDat = clusterPinHikes[13];
	var Ice = new google.maps.Marker({
		position: {lat: IceDat[1], lng: IceDat[2] },
		map: map,
		icon: clusterIcon,
		title: IceDat[0]
	});
	Ice.addListener('click', function() {
		var pgUrl = 'pages/' + IceDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CaldDat = clusterPinHikes[14];
	var Cald = new google.maps.Marker({
		position: {lat: CaldDat[1], lng: CaldDat[2] },
		map: map,
		icon: clusterIcon,
		title: CaldDat[0]
	});
	Cald.addListener('click', function() {
		var pgUrl = 'pages/' + CaldDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var PinoDat = clusterPinHikes[15];
	var Pino = new google.maps.Marker({
		position: {lat: PinoDat[1], lng: PinoDat[2] },
		map: map,
		icon: clusterIcon,
		title: PinoDat[0]
	});
	Pino.addListener('click', function() {
		var pgUrl = 'pages/' + PinoDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var DomDat = clusterPinHikes[16];
	var Dom = new google.maps.Marker({
		position: {lat: DomDat[1], lng: DomDat[2] },
		map: map,
		icon: clusterIcon,
		title: DomDat[0]
	});
	Dom.addListener('click', function() {
		var pgUrl = 'pages/' + DomDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ChimDat = clusterPinHikes[17];
	var Chim = new google.maps.Marker({
		position: {lat: ChimDat[1], lng: ChimDat[2] },
		map: map,
		icon: clusterIcon,
		title: ChimDat[0]
	});
	Chim.addListener('click', function() {
		var pgUrl = 'pages/' + ChimDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var KitchDat = clusterPinHikes[18];
	var Kitch = new google.maps.Marker({
		position: {lat: KitchDat[1], lng: KitchDat[2] },
		map: map,
		icon: clusterIcon,
		title: KitchDat[0]
	});
	Kitch.addListener('click', function() {
		var pgUrl = 'pages/' + KitchDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var TunDat = clusterPinHikes[19];
	var Tun = new google.maps.Marker({
		position: {lat: TunDat[1], lng: TunDat[2] },
		map: map,
		icon: clusterIcon,
		title: TunDat[0]
	});
	Tun.addListener('click', function() {
		var pgUrl = 'pages/' + TunDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BhseDat = clusterPinHikes[20];
	var Bhse = new google.maps.Marker({
		position: {lat: BhseDat[1], lng: BhseDat[2] },
		map: map,
		icon: clusterIcon,
		title: BhseDat[0]
	});
	Bhse.addListener('click', function() {
		var pgUrl = 'pages/' + BhseDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AlbDat = clusterPinHikes[21];
	var Alb = new google.maps.Marker({
		position: {lat: AlbDat[1], lng: AlbDat[2] },
		map: map,
		icon: clusterIcon,
		title: AlbDat[0]
	});
	Alb.addListener('click', function() {
		var pgUrl = 'pages/' + AlbDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var J4Dat = clusterPinHikes[22];
	var J4 = new google.maps.Marker({
		position: {lat: J4Dat[1], lng: J4Dat[2] },
		map: map,
		icon: clusterIcon,
		title: J4Dat[0]
	});
	J4.addListener('click', function() {
		var pgUrl = 'pages/' + J4Dat[3];
		window.open(pgUrl,'_blank');
	});
	
	var PiedDat = clusterPinHikes[23];
	var Pied = new google.maps.Marker({
		position: {lat: PiedDat[1], lng: PiedDat[2] },
		map: map,
		icon: clusterIcon,
		title: PiedDat[0]
	});
	Pied.addListener('click', function() {
		var pgUrl = 'pages/' + PiedDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var MPtDat = clusterPinHikes[24];
	var MPt = new google.maps.Marker({
		position: {lat: MPtDat[1], lng: MPtDat[2] },
		map: map,
		icon: clusterIcon,
		title: MPtDat[0]
	});
	MPt.addListener('click', function() {
		var pgUrl = 'pages/' + MPtDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CliffDat = clusterPinHikes[25];
	var Cliff = new google.maps.Marker({
		position: {lat: CliffDat[1], lng: CliffDat[2] },
		map: map,
		icon: clusterIcon,
		title: CliffDat[0]
	});
	Cliff.addListener('click', function() {
		var pgUrl = 'pages/' + CliffDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var MacaDat = clusterPinHikes[26];
	var Maca = new google.maps.Marker({
		position: {lat: MacaDat[1], lng: MacaDat[2] },
		map: map,
		icon: clusterIcon,
		title: MacaDat[0]
	});
	Maca.addListener('click', function() {
		var pgUrl = 'pages/' + MacaDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var RincDat = clusterPinHikes[27];
	var Rinc = new google.maps.Marker({
		position: {lat: RincDat[1], lng: RincDat[2] },
		map: map,
		icon: clusterIcon,
		title: RincDat[0]
	});
	Rinc.addListener('click', function() {
		var pgUrl = 'pages/' + RincDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var VolcDat = clusterPinHikes[28];
	var Volc = new google.maps.Marker({
		position: {lat: VolcDat[1], lng: VolcDat[2] },
		map: map,
		icon: clusterIcon,
		title: VolcDat[0]
	});
	Volc.addListener('click', function() {
		var pgUrl = 'pages/' + VolcDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var UTesDat = clusterPinHikes[29];
	var UTes = new google.maps.Marker({
		position: {lat: UTesDat[1], lng: UTesDat[2] },
		map: map,
		icon: clusterIcon,
		title: UTesDat[0]
	});
	UTes.addListener('click', function() {
		var pgUrl = 'pages/' + UTesDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var MTesDat = clusterPinHikes[30];
	var MTes = new google.maps.Marker({
		position: {lat: MTesDat[1], lng: MTesDat[2] },
		map: map,
		icon: clusterIcon,
		title: MTesDat[0]
	});
	MTes.addListener('click', function() {
		var pgUrl = 'pages/' + MTesDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var DecDat = clusterPinHikes[31];
	var Dec = new google.maps.Marker({
		position: {lat: DecDat[1], lng: DecDat[2] },
		map: map,
		icon: clusterIcon,
		title: DecDat[0]
	});
	Dec.addListener('click', function() {
		var pgUrl = 'pages/' + DecDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var NambDat = clusterPinHikes[32];
	var Namb = new google.maps.Marker({
		position: {lat: NambDat[1], lng: NambDat[2] },
		map: map,
		icon: clusterIcon,
		title: NambDat[0]
	});
	Namb.addListener('click', function() {
		var pgUrl = 'pages/' + NambDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var LaVDat = clusterPinHikes[33];
	var LaV = new google.maps.Marker({
		position: {lat: LaVDat[1], lng: LaVDat[2] },
		map: map,
		icon: clusterIcon,
		title: LaVDat[0]
	});
	LaV.addListener('click', function() {
		var pgUrl = 'pages/' + LaVDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var URioDat = clusterPinHikes[34];
	var URio = new google.maps.Marker({
		position: {lat: URioDat[1], lng: URioDat[2] },
		map: map,
		icon: clusterIcon,
		title: URioDat[0]
	});
	URio.addListener('click', function() {
		var pgUrl = 'pages/' + URioDat[3];
		window.open(pgUrl,'_blank');
	});
	
	// ALL THE "SINGLETON", NON-OVERLAPPING HIKE MARKERS:
	var ThreeDat = othrHikes[0];
	var Three = new google.maps.Marker({
		position: {lat: ThreeDat[1], lng: ThreeDat[2] },
		map: map,
		icon: hikeIcon,
		title: ThreeDat[0]
	});
	Three.addListener('click', function() {
		var pgUrl = 'pages/' + ThreeDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AceqDat = othrHikes[1];
	var Aceq = new google.maps.Marker({
		position: {lat: AceqDat[1], lng: AceqDat[2] },
		map: map,
		icon: hikeIcon,
		title: AceqDat[0]
	});
	Aceq.addListener('click', function() {
		var pgUrl = 'pages/' + AceqDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var SarcDat = othrHikes[2];
	var Sarca = new google.maps.Marker({
		position: {lat: SarcDat[1], lng: SarcDat[2] },
		map: map,
		icon: hikeIcon,
		title: SarcDat[0]
	});
	Sarca.addListener('click', function() {
		var pgUrl = 'pages/' + SarcDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AnchoDat = othrHikes[3];
	var Ancho = new google.maps.Marker({
		position: {lat: AnchoDat[1], lng: AnchoDat[2] },
		map: map,
		icon: hikeIcon,
		title: AnchoDat[0]
	});
	Ancho.addListener('click', function() {
		var pgUrl = 'pages/' + AnchoDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ApachDat = othrHikes[4];
	var Apache = new google.maps.Marker({
		position: {lat: ApachDat[1], lng: ApachDat[2] },
		map: map,
		icon: hikeIcon,
		title: ApachDat[0]
	});
	Apache.addListener('click', function() {
		var pgUrl = 'pages/' + ApachDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AspDat = othrHikes[5];
	var Aspen = new google.maps.Marker({
		position: {lat: AspDat[1], lng: AspDat[2] },
		map: map,
		icon: hikeIcon,
		title: AspDat[0]
	});
	Aspen.addListener('click', function() {
		var pgUrl = 'pages/' + AspDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var AtaDat = othrHikes[6];
	var Atal = new google.maps.Marker({
		position: {lat: AtaDat[1], lng: AtaDat[2] },
		map: map,
		icon: hikeIcon,
		title: AtaDat[0]
	});
	Atal.addListener('click', function() {
		var pgUrl = 'pages/' + AtaDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BattDat = othrHikes[7];
	var Battle = new google.maps.Marker({
		position: {lat: BattDat[1], lng: BattDat[2] },
		map: map,
		icon: hikeIcon,
		title: BattDat[0]
	});
	Battle.addListener('click', function() {
		var pgUrl = 'pages/' + BattDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BorrDat = othrHikes[8];
	var Borreg = new google.maps.Marker({
		position: {lat: BorrDat[1], lng: BorrDat[2] },
		map: map,
		icon: hikeIcon,
		title: BorrDat[0]
	});
	Borreg.addListener('click', function() {
		var pgUrl = 'pages/' + BorrDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BuckDat = othrHikes[9];
	var Buck = new google.maps.Marker({
		position: {lat: BuckDat[1], lng: BuckDat[2] },
		map: map,
		icon: hikeIcon,
		title: BuckDat[0]
	});
	Buck.addListener('click', function() {
		var pgUrl = 'pages/' + BuckDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CabDat = othrHikes[10];
	var Cabzon = new google.maps.Marker({
		position: {lat: CabDat[1], lng: CabDat[2] },
		map: map,
		icon: hikeIcon,
		title: CabDat[0]
	});
	Cabzon.addListener('click', function() {
		var pgUrl = 'pages/' + CabDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CerrDat = othrHikes[11];
	var Ceril = new google.maps.Marker({
		position: {lat: CerrDat[1], lng: CerrDat[2] },
		map: map,
		icon: hikeIcon,
		title: CerrDat[0]
	});
	Ceril.addListener('click', function() {
		var pgUrl = 'pages/' + CerrDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ChamDat = othrHikes[12];
	var Chami = new google.maps.Marker({
		position: {lat: ChamDat[1], lng: ChamDat[2] },
		map: map,
		icon: hikeIcon,
		title: ChamDat[0]
	});
	Chami.addListener('click', function() {
		var pgUrl = 'pages/' + ChamDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ChavDat = othrHikes[13];
	var Chavez = new google.maps.Marker({
		position: {lat: ChavDat[1], lng: ChavDat[2] },
		map: map,
		icon: hikeIcon,
		title: ChavDat[0]
	});
	Chavez.addListener('click', function() {
		var pgUrl = 'pages/' + ChavDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CoyDat = othrHikes[14];
	var Coyo = new google.maps.Marker({
		position: {lat: CoyDat[1], lng: CoyDat[2] },
		map: map,
		icon: hikeIcon,
		title: CoyDat[0]
	});
	Coyo.addListener('click', function() {
		var pgUrl = 'pages/' + CoyDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var DaleDat = othrHikes[15];
	var DBallN = new google.maps.Marker({
		position: {lat: DaleDat[1], lng: DaleDat[2] },
		map: map,
		icon: hikeIcon,
		title: DaleDat[0]
	});
	DBallN.addListener('click', function() {
		var pgUrl = 'pages/' + DaleDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var DelDat = othrHikes[16];
	var DelAg = new google.maps.Marker({
		position: {lat: DelDat[1], lng: DelDat[2] },
		map: map,
		icon: hikeIcon,
		title: DelDat[0]
	});
	DelAg.addListener('click', function() {
		var pgUrl = 'pages/' + DelDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var DiabDat = othrHikes[17];
	var Diablo = new google.maps.Marker({
		position: {lat: DiabDat[1], lng: DiabDat[2] },
		map: map,
		icon: hikeIcon,
		title: DiabDat[0]
	});
	Diablo.addListener('click', function() {
		var pgUrl = 'pages/' + DiabDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ElmoDat = othrHikes[18];
	var ElMor = new google.maps.Marker({
		position: {lat: ElmoDat[1], lng: ElmoDat[2] },
		map: map,
		icon: hikeIcon,
		title: ElmoDat[0]
	});
	ElMor.addListener('click', function() {
		var pgUrl = 'pages/' + ElmoDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var FtBDat = othrHikes[19];
	var Bayrd = new google.maps.Marker({
		position: {lat: FtBDat[1], lng: FtBDat[2] },
		map: map,
		icon: hikeIcon,
		title: FtBDat[0]
	});
	Bayrd.addListener('click', function() {
		var pgUrl = 'pages/' + FtBDat[3];
		window.open(pgUrl,'_blank');
	});

	var HydeDat = othrHikes[20];
	var HydePk = new google.maps.Marker({
		position: {lat: HydeDat[1], lng: HydeDat[2] },
		map: map,
		icon: hikeIcon,
		title: HydeDat[0]
	});
	HydePk.addListener('click', function() {
		var pgUrl = 'pages/' + HydeDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var JosDat = othrHikes[21];
	var JMine = new google.maps.Marker({
		position: {lat: JosDat[1], lng: JosDat[2] },
		map: map,
		icon: hikeIcon,
		title: JosDat[0]
	});
	JMine.addListener('click', function() {
		var pgUrl = 'pages/' + JosDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BajDat = othrHikes[22];
	var Bajada = new google.maps.Marker({
		position: {lat: BajDat[1], lng: BajDat[2] },
		map: map,
		icon: hikeIcon,
		title: BajDat[0]
	});
	Bajada.addListener('click', function() {
		var pgUrl = 'pages/' + BajDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var LuzDat = othrHikes[23];
	var LaLuz = new google.maps.Marker({
		position: {lat: LuzDat[1], lng:LuzDat[2] },
		map: map,
		icon: hikeIcon,
		title: LuzDat[0]
	});
	LaLuz.addListener('click', function() {
		var pgUrl = 'pages/' + LuzDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var VerdeDat = othrHikes[24];
	var Verde = new google.maps.Marker({
		position: {lat: VerdeDat[1], lng: VerdeDat[2] },
		map: map,
		icon: hikeIcon,
		title: VerdeDat[0]
	});
	Verde.addListener('click', function() {
		var pgUrl = 'pages/' + VerdeDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ConchDat = othrHikes[25];
	var Conchas = new google.maps.Marker({
		position: {lat: ConchDat[1], lng: ConchDat[2] },
		map: map,
		icon: hikeIcon,
		title: ConchDat[0]
	});
	Conchas.addListener('click', function() {
		var pgUrl = 'pages/' + ConchDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var ChijDat = othrHikes[26];
	var Chiju = new google.maps.Marker({
		position: {lat: ChijDat[1], lng: ChijDat[2] },
		map: map,
		icon: hikeIcon,
		title: ChijDat[0]
	});
	Chiju.addListener('click', function() {
		var pgUrl = 'pages/' + ChijDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CubDat = othrHikes[27];
	var MCuba = new google.maps.Marker({
		position: {lat: CubDat[1], lng: CubDat[2] },
		map: map,
		icon: hikeIcon,
		title: CubDat[0]
	});
	MCuba.addListener('click', function() {
		var pgUrl = 'pages/' + CubDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var NatDat = othrHikes[28];
	var Conser = new google.maps.Marker({
		position: {lat: NatDat[1], lng: NatDat[2] },
		map: map,
		icon: hikeIcon,
		title: NatDat[0]
	});
	Conser.addListener('click', function() {
		var pgUrl = 'pages/' + NatDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var OjDat = othrHikes[29];
	var Ojito = new google.maps.Marker({
		position: {lat: OjDat[1], lng: OjDat[2] },
		map: map,
		icon: hikeIcon,
		title: OjDat[0]
	});
	Ojito.addListener('click', function() {
		var pgUrl = 'pages/' + OjDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var BeteDat = othrHikes[30];
	var Pinab = new google.maps.Marker({
		position: {lat: BeteDat[1], lng: BeteDat[2] },
		map: map,
		icon: hikeIcon,
		title: BeteDat[0]
	});
	Pinab.addListener('click', function() {
		var pgUrl = 'pages/' + BeteDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var PurgDat = othrHikes[31];
	var Purga = new google.maps.Marker({
		position: {lat: PurgDat[1], lng: PurgDat[2] },
		map: map,
		icon: hikeIcon,
		title: PurgDat[0]
	});
	Purga.addListener('click', function() {
		var pgUrl = 'pages/' + PurgDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var PyrDat = othrHikes[32];
	var Pymid = new google.maps.Marker({
		position: {lat: PyrDat[1], lng: PyrDat[2] },
		map: map,
		icon: hikeIcon,
		title: PyrDat[0]
	});
	Pymid.addListener('click', function() {
		var pgUrl = 'pages/' + PyrDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var RedDat = othrHikes[33];
	var RedBlue = new google.maps.Marker({
		position: {lat: RedDat[1], lng: RedDat[2] },
		map: map,
		icon: hikeIcon,
		title: RedDat[0]
	});
	RedBlue.addListener('click', function() {
		var pgUrl = 'pages/' + RedDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var LorenDat = othrHikes[34];
	var Lorenzo = new google.maps.Marker({
		position: {lat: LorenDat[1], lng: LorenDat[2] },
		map: map,
		icon: hikeIcon,
		title: LorenDat[0]
	});
	Lorenzo.addListener('click', function() {
		var pgUrl = 'pages/' + LorenDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var StripDat = othrHikes[35];
	var SMine = new google.maps.Marker({
		position: {lat: StripDat[1], lng: StripDat[2] },
		map: map,
		icon: hikeIcon,
		title: StripDat[0]
	});
	SMine.addListener('click', function() {
		var pgUrl = 'pages/' + StripDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var SunMDat = othrHikes[36];
	var SunMtn = new google.maps.Marker({
		position: {lat: SunMDat[1], lng: SunMDat[2] },
		map: map,
		icon: hikeIcon,
		title: SunMDat[0]
	});
	SunMtn.addListener('click', function() {
		var pgUrl = 'pages/' + SunMDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var TentDat = othrHikes[37];
	var Kashe = new google.maps.Marker({
		position: {lat: TentDat[1], lng: TentDat[2] },
		map: map,
		icon: hikeIcon,
		title: TentDat[0]
	});
	Kashe.addListener('click', function() {
		var pgUrl = 'pages/' + TentDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var LTesDat = othrHikes[38];
	var LowTes = new google.maps.Marker({
		position: {lat: LTesDat[1], lng: LTesDat[2] },
		map: map,
		icon: hikeIcon,
		title: LTesDat[0]
	});
	LowTes.addListener('click', function() {
		var pgUrl = 'pages/' + LTesDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var CatDat = othrHikes[39];
	var CWalks = new google.maps.Marker({
		position: {lat: CatDat[1], lng: CatDat[2] },
		map: map,
		icon: hikeIcon,
		title: CatDat[0]
	});
	CWalks.addListener('click', function() {
		var pgUrl = 'pages/' + CatDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var TetDat = othrHikes[40];
	var Tetilla = new google.maps.Marker({
		position: {lat: TetDat[1], lng: TetDat[2] },
		map: map,
		icon: hikeIcon,
		title: TetDat[0]
	});
	Tetilla.addListener('click', function() {
		var pgUrl = 'pages/' + TetDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var VGDat = othrHikes[41];
	var VGrande = new google.maps.Marker({
		position: {lat: VGDat[1], lng: VGDat[2] },
		map: map,
		icon: hikeIcon,
		title: VGDat[0]
	});
	VGrande.addListener('click', function() {
		var pgUrl = 'pages/' + VGDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var VwPtDat = othrHikes[42];
	var ViewPt = new google.maps.Marker({
		position: {lat: VwPtDat[1], lng: VwPtDat[2] },
		map: map,
		icon: hikeIcon,
		title: VwPtDat[0]
	});
	ViewPt.addListener('click', function() {
		var pgUrl = 'pages/' + VwPtDat[3];
		window.open(pgUrl,'_blank');
	});
	
	var WillDat = othrHikes[43];
	var WilLake = new google.maps.Marker({
		position: {lat: WillDat[1], lng: WillDat[2] },
		map: map,
		icon: hikeIcon,
		title: WillDat[0]
	});
	WilLake.addListener('click', function() {
		var pgUrl = 'pages/' + WillDat[3];
		window.open(pgUrl,'_blank');
	});
/* ***************************************************************************** */
/* ***************************************************************************** */

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
	var KinAltoLoc = {lat: 36.064977, lng: -107.969867 };
	var KinAltMrkrLocs = [
		{lat: 36.063864, lng: -107.981315 },
		KinAltoLoc,
		{lat: 36.068608, lng: -107.959900 }
	];
	var KinAltLines = new google.maps.Polyline({
		path: KinAltMrkrLocs,
		geodesic: false,
		strokeColor: '#FF0000',
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
        strokeColor: '#FF0000',
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
		strokeColor: '#FF0000',
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
		strokeColor: '#FF0000',
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	mmtLines.setMap(null);
	/* END OF POLYLINES CREATION */
/* ***************************************************************************** */

/* ************************** PAN & ZOOM HANDLERS ****************************** */
	map.addListener('zoom_changed', function() {
		var curZoom = map.getZoom();
		var perim = String(map.getBounds());
		IdTableElements(perim);
		if ( curZoom > 10 ) {
			Blines.setMap(map);
			KinAltLines.setMap(map);
			SkiLines.setMap(map);
			egLines.setMap(map);
			tesLines.setMap(map);
			CliffMacLines.setMap(map);
			mmtLines.setMap(map);
			BandMrkr.setMap(null);
			ChacoMrkr.setMap(null);
			ElMalMrkr.setMap(null);
			PetroMrkr.setMap(null);
		} else {
			Blines.setMap(null);
			KinAltLines.setMap(null);
			SkiLines.setMap(null);
			egLines.setMap(null);
			tesLines.setMap(null);
			CliffMacLines.setMap(null);
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
			}); // end of click on metric */
		}  //END ELSE [outHike]
	} // END: IdTableElements() FUNCTION

}  // end of initMap()