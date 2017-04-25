	function drawCircle( circCtr ) {
		chartMrkr = GV_Draw_Marker({
			lat: circCtr.lat,
			lon: circCtr.lon
		});
		circSet = true;
	}
	// create context for passing iframe variables to parent
	setTimeout( function() {	
		parent.iframeWindow = window;
		}, 2000 );
