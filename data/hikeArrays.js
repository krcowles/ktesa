// colors are supplied in the hike arrays for tracks, when they exist
var lineColor = '#2974EB';
var trackColor = '#FF0000';
var altTrkClr1 = '#0000FF';
var altTrkClr2 = '#14613E';
var altTrkClr3 = '#000000';
var noTrk = '#000000';

// ***************************************************************************************
// -----------------------------------   IMPORTANT NOTE: --------------------------------
//	The hikeDataTbl.html file ***** MUST ***** list items in the ORDER SHOWN
//	below [as listed in arrays] in order for the correct elements to be listed
//	in the user table of hikes !!!!!!
//	-------------------------------------------------------------------------------------    
// ***************************************************************************************
                                     
// HIKE DATA ARRAYS:
//  1. 'Hike Name',
//  2. trailhead (or visitor center) latitude,
//  3. trailhead (or visitor center) longitude,
//  4. 'html source for page',
//  5. 'track json file' [may be '' if no file available]
//  6. if track is present: color variable defined above (N/A for ctrPinHikes)
//  7. clustered hikes -> 1 = represent group with marker, 0 = don't represent

// Visitor Center array:
var ctrPinHikes = [
	['Bandelier',35.779039,-106.270788,'Bandelier.html',''],
	['Chaco Canyon',36.030250,-107.91080,'Chaco.html',''],
	['El Malpais',34.970407,-107.810152,'ElMalpais.html',''],
	['Petroglyphs Natl Mon',35.138644,-106.711196,'Petroglyphs.html','']
];

// Hikes where trailheads overlap or are in very close proximity:
var clusterPinHikes = [
	// Bandelier hikes:
	['Ruins Trail',35.778943,-106.270838,'MainLoop.html','',noTrk,1],
	['Falls Trail',35.788735,-106.282079,'FallsTrail.html','',noTrk,0],
	['Frey Trail',35.779219,-106.285744,'Frey.html','',noTrk,0],
	['Frijolito Ruins',35.769573,-106.282433,'Frijolito.html','',noTrk,0],
	['Alcove House',35.764312,-106.273698,'AlcoveHouse.html','',noTrk,0],
	// Chaco Canyon hikes:
	['Pueblo Alto',36.068608,-107.959900,'PuebloAlto.html','palto.json',trackColor,1],
	['Kin Kletso',36.063864,-107.981315,'KinKletso.html','',noTrk,0],
	// Elena Gallegos hikes:
	['Pino Trail',35.163250, -106.470067,'Pino.html','pino.json',trackColor,1],
	['Domingo Baca',35.167093,-106.465502,'Domingo.html','baca.json',trackColor,0],
	// Manzanitas Trail hikes:
	['Tunnel Canyon',35.046562,-106.383088,'TunnelCanyon.html','tun.json',trackColor,1],
	['Birdhouse Ridge',35.055938,-106.388512,'Birdhouse.html','bird.json',trackColor,0],
	// Petroglyphs hikes:
	['Mesa Point Trail',35.161988,-106.718203,'MesaPoint.html','',noTrk,1],
	['Cliff Base Trail',35.165471,-106.729088,'CliffBase.html','',noTrk,0],
	['Macaw Trail',35.170242,-106.717243,'Macaw.html','',noTrk,0],
	// Big Tesuque Campground hikes:
	['Upper Tesuque',35.769508,-105.809155,'UpperTesuque.html','utes.json',altTrkClr1,1],
	['Middle Tesuque',35.738236,-105.779114,'MiddleTesuque.html','mtes.json',altTrkClr2,0],
	// Winsor Trailhead hikes:
	['Deception Pk',35.795845,-105.804605,'Deception.html','decp.json',trackColor,1],
	['Nambe Lake',35.818627,-105.797649,'Nambe.html','nambe.json',altTrkClr1,0],
	['La Vega',35.816873,-105.815796,'LaVega.html','vega.json',altTrkClr2,0],
	['Upper Rio En Medio',35.802801,-105.827387,'UpperRio.html','uriom.json',altTrkClr3,0],
	// Jemez East Fork trails
	['East Fork - Las Conchas',35.820818,-106.591123,'EForkConchas.html','efconchas.json',altTrkClr1,1],
	['East Fork - Battleship',35.825727,-106.599355,'EForkBattle.html','efbattle.json',trackColor,0],
	// Aspen Vista Trails
	['Aspen Vista',35.777433,-105.810933,'Aspen.html','aspen.json',altTrkClr3,1],
    ['Alamo Vista',35.777433,-105.810933,'AlamoVista.html','alamo.json',trackColor,0]
];

// All other hikes not covered by above:
var othrHikes = [
	['Three Rivers',33.419574,-105.987682,'ThreeRivers.html',''],
	['Corrales Acequia',35.249327,-106.607283,'Acequia.html','aceq.json'],
	['Agua Sarca',35.291533,-106.441050,'AguaSarca.html','sarca.json'],
	['Ancho Rapids',35.797000,-106.246417,'AnchoComb.html','ancho.json'],
	['Apache Canyon',35.629817,-105.858967,'ApacheCanyon.html','apache.json'],
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
	['Mesa Chijuilla',35.995233,-107.0827,'Chijuilla.html','chij.json'],
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
	['Traders Trail',36.323333,-105.70366666,'Traders.html','trader.json'],
	['Tsankawi Ruins',35.860416,-106.224682,'Tsankawi.html',''],
	['Canyon Trail',33.759012,-106.895278,'CanyonTrail.html',''],
	['Piedras Marcadas',35.188867,-106.686269,'Piedras.html',''],
	['Rinconada Canyon',35.126851,-106.724635,'Rinconada.html',''],
	['ABQ Volcanoes',35.13075,-106.7802667,'ABQvolcanoes.html','volc.json'],
	['Big Tubes',34.944733,-108.106983,'BigTubes.html','tubes.json'],
	['Ice Caves',34.99311,-108.080084,'IceCave.html',''],
	['El Calderon',34.9698,-108.00325,'ElCalderon.html','cald.json'],
	['Una Vida',336.064977,-107.969867,'UnaVida.html',''],
	['Hungo Pavi',36.049536,-107.93031,'HungoPavi.html',''],
	['Pueblo Bonito',36.059216,-107.958934,'Bonito.html',''],
	['Chimney Rock',36.330525,-106.47482,'ChimneyRock.html',''],
	['Kitchen Mesa',36.336353,-106.469007,'Kitchen.html',''],
	['Albuquerque Trail',34.793491,-106.372268,'ABQ.html',''],
	['July 4th Trail',34.790707,-106.382439,'July4.html',''],
	['San Ysidro Trials',35.568616666,-106.8122000,'SanYsidroTrials.html','sany.json'],
    ['Burnt Mesa',35.828253,-106.328961,'BurntMesa.html','Burnt_Mesa.json']
];