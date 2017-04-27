function drawMarker( mrkrLoc ) {
    chartMrkr = new google.maps.Marker({
        position: mrkrLoc,
        map: gmap
    });
    mrkrSet = true;
}

// create context for passing iframe variables to parent
setTimeout( function() {	
    parent.iframeWindow = window;
    }, 2000 );
