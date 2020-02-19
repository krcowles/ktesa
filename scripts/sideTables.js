var tstcnt = 0;
/**
 * Searchbar Functionality (html datalist element)
 */
$('#searchbar').val('');
$('#searchbar').on('input', function(ev) {
    var $input = $(this),
       val = $input.val(),
       list = $input.attr('list'),
       match = $('#'+list + ' option').filter(function() {
           return ($(this).val() === val);
       });
    if(match.length > 0) {
        var found = false;
        // Is this hike associated with a Visitor Center?
        VC.forEach(function(ctr) {
            // is this a visitor center?
            if (ctr.name == val) {
                infoWin(ctr.name, ctr.loc);
                found = true;
                return;
            } else {
                // is it one of the VC's hikes?
                ctr.hikes.forEach(function(atvc) {
                    if (atvc.name == val) {
                        infoWin(ctr.name, ctr.loc);
                        found = true;
                        return;
                    }
                });
            }
        });
        if (!found) {
            CL.forEach(function(clus) {
                clus.hikes.forEach(function(hike) {
                    if (hike.name == val) {
                        infoWin(clus.group, clus.loc);
                        found = true;
                        return;
                    }
                });
            });
            if (!found) {
                NM.forEach(function(hike) {
                    if (hike.name == val) {
                        infoWin(hike.name, hike.loc);
                        found = true;
                        return;
                    }
                });
            }
            if (!found) {
                alert("This hike cannot be located in the list of hikes");
            }
        }
    } // do nothing if not a match
});
const infoWin = (name, location) => {
    // find the marker associated with the input parameters and popup its info window
    map.setCenter(location);
    map.setZoom(13);
    $.each(locaters, function(indx, value) {
        if (value.hikeid == name) {
            google.maps.event.trigger(value.pin, 'click');
            return;
        }
    });
}


const idHike = (indx, obj) => {
    if (obj.type === 'vc') {
        let vcobj = VC[obj.group];
        let vchikes = vcobj.hikes;
        for (let k=0; k<vchikes.length; k++) {
            if (vchikes[k].indx === indx) {         
                return vchikes[k];
            }
        }
    } else if (obj.type === 'cl') {
        let clobj = CL[obj.group];
        let clhikes = clobj.hikes;
        for (let m=0; m<clhikes.length; m++) {
            if (clhikes[m].indx === indx) {
                return clhikes[m];
            }
        }
    } else if (obj.type === 'nm') {
       let hikeobj = NM[obj.group];
       return hikeobj;
    }
}

// html for items included in side table
var tblItemHtml; // this will hold an html "wrapper" for items id'd for inclusion by new bounds
tblItemHtml = '<div class="tableItem"><div class="tip">Add to Favorites</div>';
//tblItemHtml += '<img class="like" src="../images/like.png" alt="favorites icon" />';
tblItemHtml += '<div class="content">';

/**
 * Create the html for the side table
 */
const formTbl = (indxArray) => {
    $('#sideTable').empty();
    $.each(indxArray, function(i, obj) {
        var tbl = tblItemHtml;
        var lnk = '<a href="hikePageTemplate.php?hikeIndx=' + obj.indx + 
            '">' + obj.name + '</a>';
        tbl += lnk;
        tbl += '<br /><span class="subtxt">Rating: ' + obj.diff + ' / '
            + obj.lgth + ' miles';
        tbl += '</span><br /><span class="subtxt">Elev Change: ';
        tbl += obj.elev + ' feet</span><p id="sidelat" style="display:none">';
        tbl += obj.lat  + '</p><p id="sidelng" style="display:none">';
        tbl += obj.lng + '</p></div></div>';
        $('#sideTable').append(tbl);
        // tooltips for 'favorites'
        /*
        $('.like').each(function() {
            var pos = $(this).offset();
            var $txtspan = $(this).parent().children().eq(0); // div holding tooltip
            $(this).on('mouseover', function() {
                var left = pos.left - 124 + 'px'; // width of tip is 120px
                var top = pos.top + 14 + 'px';
                $txtspan[0].style.top = top;
                $txtspan[0].style.left = left;
                $txtspan[0].style.display = 'block';
            });
            $(this).on('mouseout', function() {
                $txtspan[0].style.display = 'none';
            });
        });
        */
    });
}  // end of formTbl() function

/*
 * The side table includes all hikes on page load
 * The variables 'allHikes' and 'locations' are declared on home.php (created by mapJsData.php)
 */
var sideTbl = new Array();
var arrayCnt = 0;
for ( var i=0; i<allHikes.length; i++ ) {
    var hobj = locations[i];  // declared on home.php
    var data = idHike(allHikes[i], hobj); // the specific hike object
    sideTbl.push(data);
}
formTbl(sideTbl);

/**
 * Function to find elements within current bounds and display them in a table
 */
const IdTableElements = (boundsStr) => {
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
    /* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
    var hikearr = [];
    VC.forEach(function(ctr) {
        ctr.hikes.forEach(function(hike) {
            if (hike.lng <= east && hike.lng >= west && hike.lat <= north && hike.lat >= south) {
                let hikeindx = allHikes.indexOf(hike.indx);
                let hikeobj = locations[hikeindx];
                let data = idHike(allHikes[hikeindx], hikeobj);
                hikearr.push(data);
            }
        });
    });
    CL.forEach(function(clus) {
        clus.hikes.forEach(function(hike) {
            if (hike.lng <= east && hike.lng >= west && hike.lat <= north && hike.lat >= south) {
                let hikeindx = allHikes.indexOf(hike.indx);
                let hikeobj = locations[hikeindx];
                let data = idHike(allHikes[hikeindx], hikeobj);
                hikearr.push(data);
            }
        });
    });
    NM.forEach(function(hike) {
        let lat = hike.loc.lat;
        let lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
            let hikeindx = allHikes.indexOf(hike.indx);
            let hikeobj = locations[hikeindx];
            let data = idHike(allHikes[hikeindx], hikeobj);
            hikearr.push(data);
        }
    });
    if ( hikearr.length === 0 ) {
        $('#sideTable').empty();
        var nohikes = '<p style="padding-left:12px;font-size:18px;">' +
            'There are no hikes in the viewing area</p>';
        $('#sideTable').html(nohikes);
    } else {
        formTbl(hikearr);
    }
}
