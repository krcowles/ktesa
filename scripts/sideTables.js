/**
 * @file This file creates and places the html for the side table, as well as providing
 *       a search bar capability synchronized to the side table.
 * @author Tom Sandberg
 * @author Ken Cowles
 * @version 2.0 [Introduces 'zoom to hike' in map for side table items]
 */

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
        popupHikeName(val);
    }
});

/**
 * This function [coupled with infoWin()] 'clicks' the infoWin
 * for the corresponding hike
 * 
 * @param {string} hikename The name of the hike to be 'popped up'
 * @return {null}
 */
function popupHikeName(hikename) {
    var found = false;
    // Is this hike associated with a Visitor Center?
    VC.forEach(function(ctr) {
        // is this a visitor center?
        if (ctr.name == hikename) {
            infoWin(ctr.name, ctr.loc);
            found = true;
            return;
        } else {
            // is it one of the VC's hikes?
            ctr.hikes.forEach(function(atvc) {
                if (atvc.name == hikename) {
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
                if (hike.name == hikename) {
                    infoWin(clus.group, clus.loc);
                    found = true;
                    return;
                }
            });
        });
        if (!found) {
            NM.forEach(function(hike) {
                if (hike.name == hikename) {
                    let loc = {lat: hike.lat, lng: hike.lng};
                    infoWin(hike.name);
                    found = true;
                    return;
                }
            });
        }
        if (!found) {
            alert("This hike cannot be located in the list of hikes");
        }
    }
}

/**
 * This function will click the argument's infoWindow
 * 
 * @param {string} hike The name of the hike whose infoWindow will be clicked
 * @returns {null}
 */
function infoWin(hike, loc) {
    // find the marker associated with the input parameters and popup its info window
    map.setZoom(13);
    $.each(locaters, function(indx, value) {
        if (value.hikeid == hike) {
            let thismarker = value.pin;
            if (thismarker.clicked === false) {
                // clicking will set marker.clicked = true
                google.maps.event.trigger(value.pin, 'click');
            } else {
                map.setCenter(loc);
            }
            return;
        }
    });
};

/**
 * The side table includes all hikes on page load; on pan/zoom it will include only those
 * hikes within the map bounds. In the following code, the variables 'allHikes' and 
 * 'locations' are declared on home.php (and created by mapJsData.php):
 * allHikes:  an array of every hike in the database;
 * locations: a one-to-one correspondence to allHikes; an array of objects containing
 * the object type of the hike (VC, CL, or NM) and its index in that array.
 * [VC, CL, NM are all arrays]
 */
var sideTbl = new Array();
for ( var i=0; i<allHikes.length; i++ ) {
    var groupObj = locations[i];
    var hikeObj = idHike(allHikes[i], groupObj); // retrieve the specific hike object
    sideTbl.push(hikeObj);
}

/**
 * The following function returns the appropriate hike object based on the incoming
 * object (obj) and the desired hike number (indx) in that object. Note that Type VC
 * and CL hikes can have an array of hikes in their corresponding objects.
 * 
 * @param {integer} indx This is the hike number (indxNo in database)
 * @param {object}  obj  This is the object holding the hike's object type (VC, CL, NM)
 * @returns {object}     The desired hike object
 */
function idHike(indx, obj) {
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

/**
 * The html 'wrapper' for each item included in the side table
 */
var tblItemHtml;
tblItemHtml = '<div class="tableItem"><div class="tip">Add to Favorites</div>';
//tblItemHtml += '<img class="like" src="../images/like.png" alt="favorites icon" />';
tblItemHtml += '<div class="content">';
formTbl(sideTbl); // initial page load

/**
 * The DOM elements for the side table are created and attached in this function
 * 
 * @param {array} indxArray This array of objects will be used to create the side table
 * @returns {null}         
 */
function formTbl(indxArray) {
    $('#sideTable').empty();
    $.each(indxArray, function(i, obj) {
        var tbl = tblItemHtml;
        var lnk = '<a href="hikePageTemplate.php?hikeIndx=' + obj.indx + 
            '">' + obj.name + '</a>';
        tbl += lnk;
        tbl += '<img style="position:relative;left:20px;top:6px;" class="zoomers" ' +
            'src="../images/mapZoom.png" alt="zoom symbol" />';
        tbl += '<span class="zpop">Zoom to Hike</span>';
        tbl += '<br /><span class="subtxt">Rating: ' + obj.diff + ' / '
            + obj.lgth + ' miles';
        tbl += '</span><br /><span class="subtxt">Elev Change: ';
        tbl += obj.elev + ' feet</span><p id="sidelat" style="display:none">';
        tbl += obj.lat  + '</p><p id="sidelng" style="display:none">';
        tbl += obj.lng + '</p></div></div>';
        $('#sideTable').append(tbl);
    });
    enableZoom();
    return;
}

/**
 * This function will zoom to the correct map location for the corresponding
 * hike, and popup its infoWin. It also displays a tooltip on mouseover.
 * 
 * @return {null}
 */
function enableZoom() {
    let $mags = $('.zoomers');
    $mags.each(function() {
        $(this).css('cursor', 'pointer');
        $(this).on('click', function() {
            let hikename = $(this).prev().text();
            popupHikeName(hikename);
        });
        $(this).on('mouseover', function() {
            let zpos = $(this).offset();
            let hpos = zpos.left - 42;
            let vpos = zpos.top + 24;
            $(this).next().css('left', hpos);
            $(this).next().css('top', vpos);
            $(this).next().css('display', 'block');
        });
        $(this).on('mouseout', function() {
            $(this).next().css('display', 'none');
        });
    });
}

/**
 * A function to find elements within current map bounds and display them in
 * the side table. This is invoked by either a pan or a zoom on the map (see
 * map.js for listeners)
 * 
 * @param {string} boundsStr The string from google maps holding the new map bounds
 * @returns {null}
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
