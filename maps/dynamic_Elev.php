				function drawCircle( circCtr ) {
				chartMrkr = new google.maps.Circle({
					strokeColor: '#FF0000',
					strokeOpacity: 0.8,
					strokeWeight: 3,
					fillColor: '#FF0000',
					fillOpacity: 0.35,
					center: circCtr,
					map: gmap,
					radius: 40
				});
				circSet = true;
			}
			// create context for passing iframe variables to parent
			setTimeout( function() {	
				parent.iframeWindow = window;
				}, 2000 );
