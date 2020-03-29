$.when(mapdone).then(function() {
    if (allHikes.length !== 0) {
        if (allHikes.length === 1) {
            map.setCenter(NM[0].loc);
            map.setZoom(13);
        } else {
            var maxlat = 0;    // north
            var maxlng = -180; // east
            var minlat = 90;   // south
            var minlng = 0;    // west
            NM.forEach(function(userfav) {
                    if (userfav.loc.lat > maxlat) {
                        maxlat = userfav.loc.lat;
                    }
                    if (userfav.loc.lat < minlat) {
                        minlat = userfav.loc.lat;
                    }
                    if (userfav.loc.lng <  minlng) {
                        minlng = userfav.loc.lng;
                    }
                    if (userfav.loc.lng > maxlng) {
                        maxlng = userfav.loc.lng;
                    }
            });
            let bounds = {north: maxlat, south: minlat, east: maxlng, west: minlng};
            map.fitBounds(bounds);
        }
    }
});
