/**
 * @file This file creates and places the html for the side table, as well as providing
 *       a search bar capability synchronized to the side table.
 * @author Tom Sandberg
 * @author Ken Cowles
 * @version 4.0 Adds track highlighting
 */

// Items in the db that are not actual hikes (formerly Visitor Centers, now Clusters)
const VisitorCenters = ['Bandelier Index', 'Chaco Index', 'El Malpais Index',
    'Petroglyphs Index', 'El Morro Index', 'Rio Grande Nature Center Index'];

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
    for (let i=0; i<CL.length; i++) {
        if (VisitorCenters.includes(hikename)) { // VC's are now clusters
            let indx = VisitorCenters.indexOf(hikename);
            // there is a correspondence between VisitorCenters' index in CL index
            hilite_obj = {obj: CL[indx].hikes, type: 'cl'};
            infoWin(CL[indx].group, CL[indx].loc);
            found = true;
        }
        for (let j=0; j<CL[i].hikes.length; j++) {
            if (CL[i].hikes[j].name == hikename) {
                hilite_obj = {obj: CL[i].hikes[j], type: 'nm'};
                infoWin(CL[i].group, CL[i].loc);
                found = true;
                break;
            }
        }
        if (found) {
            break;
        }
    }
    if (!found) {
        for (let k=0; k<NM.length; k++) {
            if (NM[k].name == hikename) {
                hilite_obj = {obj: NM[k], type: 'nm'};
                infoWin(NM[k].name, NM[k].loc);
                found = true;
                break;
            }
        }
    }
    if (!found) {
        alert("This hike cannot be located in the list of hikes");
        infoWin_zoom = false;
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
    // clicking marker sets zoom
    for (let k=0; k<locaters.length; k++) {
        if (locaters[k].hikeid == hike) {
            let thismarker = locaters[k].pin;
            if (thismarker.clicked === false) {
                zoomLevel = map.getZoom();
                // clicking will set (prototype) marker.clicked = true
                marker_click = true; // the global in map zoom test
                google.maps.event.trigger(locaters[k].pin, 'click');
            } else {
                map.setCenter(loc);
            }
            break;
        }
    }
    return;
}

/**
 * This function emphasizes the hike track(s) that have been zoomed to;
 * NOTE: A javascript anomaly - passing in a single object in an array
 * results in the function receiving the object, but not as an array.
 * Hence a 'type' identifier is used here
 * 
 * @param {array} tracks The array of objects whose tracks wil be emphasized 
 * @param {string} type The identifier for single or multiple objects
 * 
 * @return {null}
 */
function highlightTracks() {
    if (!$.isEmptyObject(hilite_obj)) {
        if (hilite_obj.type === 'cl') { // object is an array of objects
            let cluster = hilite_obj.obj;
            cluster.forEach(function(track) {
                let polyno = track.indx;
                for (let k=0; k<drawnTracks.length; k++) {
                    if (drawnTracks[k].hike == polyno) {
                        let polyline = drawnTracks[k].track;
                        polyline.setOptions({
                            strokeWeight: 4,
                            strokeColor: '#FFFF00',
                            strokeOpacity: 1,
                            zIndex: 10
                        });
                        hilited.push(polyline);
                        break;
                    }
                }
            });
        } else { // mrkr === 'nm'; object is a single object
            let polyno = hilite_obj.obj.indx;
            for (let k=0; k<drawnTracks.length; k++) {
                if (drawnTracks[k].hike == polyno) {
                    let polyline = drawnTracks[k].track;
                    polyline.setOptions({
                        strokeWeight: 4,
                        strokeColor: '#FFFF00',
                        strokeOpacity: 1,
                        zIndex: 10
                    });
                    hilited.push(polyline);
                    break;
                }
            }
        }
        hilite_obj = {};
    }
}

/**
 * Undo any previous track highlighting
 * 
 * @return {null}
 */
function restoreTracks() {
    for (let n=0; n<hilited.length; n++) {
        hilited[n].setOptions({
            strokeOpacity: 0.60,
            strokeWeight: 3,
            zIndex: 1
        });
    }
    return;
}
/**
 * The side table includes all hikes on page load; on pan/zoom it will include only those
 * hikes within the map bounds. In the following code, the variables 'allHikes' and 
 * 'locations' are declared on home.php (and created by mapJsData.php):
 * allHikes:  an array of every hike in the database;
 * locations: a one-to-one correspondence to allHikes; an array of objects containing
 * the object type of the hike (CL, or NM) and its index in that array.
 * [CL, NM are arrays]
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
    if (obj.type === 'cl') {
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
 * Get the current list of user's favorites. Note that a deferred object is
 * defined so that the list can be retrieved prior to invoking the side table.
 */ 
var listdone = new $.Deferred();
var favlist = [];
var ftbl = '../pages/getFavorites.php';
var fdat = {userid: userid};  // userid specified in getLogin.js
$.ajax({
    url: ftbl,
    method: 'post',
    data: fdat,
    dataType: 'text',
    success: function(flist) {
        favlist = JSON.parse(flist);
        listdone.resolve();
    },
    error: function(jqXHR, textStatus, errorThrown) {
        var newDoc = document.open();
        newDoc.write(jqXHR.responseText);
        newDoc.close();
    }
});

/**
 * The html 'wrapper' for each item included in the side table
 */
var tblItemHtml;
// one tableItem div for each side table hike
tblItemHtml = '<div class="tableItem"><div class="tip">Add to Favorites</div>';
// the div holding the favorites icon and the zoom-to-map icon
tblItemHtml += '<div class="icons">';
tblItemHtml += '<img class="like" src="../images/favoritesYellow.png" alt="favorites icon" />';
tblItemHtml += '<br /><img class="zoomers" src="../images/mapZoom.png" alt="zoom symbol" />';
tblItemHtml += '<span class="zpop">Zoom to Hike</span>';
tblItemHtml += '</div>';
// the div holding the hike-specific data
tblItemHtml += '<div class="content">';
$.when(listdone).then(function() {
    formTbl(sideTbl); // initial page load
});


/**
 * The DOM elements for the side table are created and attached in this function
 * 
 * @param {array} indxArray This array of objects will be used to create the side table
 * @returns {null}         
 */
function formTbl(indxArray) {
    $('#sideTable').empty();
    if (allHikes.length === 0) {
        let no_table = '<div class="tableItem" style="text-align:center;font-size:' +
            '20px;color:brown;padding-top:32px;">No favorites selected</div>';
        $('#sideTable').append(no_table);
    } else {
        $.each(indxArray, function(i, obj) {
            let hno = obj.indx;
            var tbl;
            if (favlist.includes(hno) || favlist.includes(hno.toString())) {
                tbl = tblItemHtml.replace('Yellow', 'Red');
            } else {
                tbl = tblItemHtml;
            }   
            let lnk = '<a href="hikePageTemplate.php?hikeIndx=' + obj.indx + 
                '">' + obj.name + '</a>';
            tbl += lnk;
            tbl += '<br /><span class="subtxt">Rating: ' + obj.diff + ' / '
                + obj.lgth + ' miles';
            tbl += '</span><br /><span class="subtxt">Elev Change: ';
            tbl += obj.elev + ' feet</span><p id="sidelat" style="display:none">';
            tbl += obj.lat  + '</p><p id="sidelng" style="display:none">';
            tbl += obj.lng + '</p></div></div>';
            $('#sideTable').append(tbl);
        });
        enableFavorites();
        enableZoom();
        return;
    }
}

/**
 * This function will track events on the favorites icons
 * 
 * @returns {null} Favorites' text change in the DOM & event setting takes place in this function
 */
function enableFavorites() {
    positionFavTooltips();
    $('.like').each(function() {
        $(this).unbind('click').bind('click', function() {
            // get this div's hikeno
            let href = $(this).parent().next().children().eq(0).attr('href');
            let digitpos = href.indexOf('=') + 1;
            var hikeno = href.substr(digitpos);
            var ajaxdata = {id: userid, no: hikeno};
            var isrc = $(this).attr('src');
            var newsrc;
            if (isrc.indexOf('Yellow') !== -1) { // currently a not favorite
                ajaxdata.action = 'add';
                newsrc = isrc.replace('Yellow', 'Red');
                var $txtspan = $(this).parent().prev();
                $txtspan.text('Unmark');
                $.ajax({
                    url: "markFavorites.php",
                    method: "post",
                    data: ajaxdata,
                    dataType: "text",
                    success: function() {
                        favlist.push(parseInt(hikeno));
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        var newDoc = document.open("text/html", "replace");
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            } else { // currently a favorite
                ajaxdata.action = 'delete';
                newsrc = isrc.replace('Red', 'Yellow');
                var $txtspan = $(this).parent().prev();
                $txtspan.text('Add to Favorites');
                $.ajax({
                    url: "markFavorites.php",
                    method: "post",
                    data: ajaxdata,
                    dataType: "text",
                    success: function(json_results) {
                        let key = favlist.indexOf(parseInt(hikeno));
                        favlist.splice(key, 1); 
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        var newDoc = document.open("text/html", "replace");
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            }
            $(this).attr('src', newsrc);
        });
    });
    return;
};

/**
 * This function is required in order to reposition the like popups after
 * resizing
 * 
 * @return {null}
 */
function positionFavTooltips() {
    $('.like').each(function() {
        var $txtspan = $(this).parent().parent().children().eq(0); // div holding tooltip
        if (this.src.indexOf('Yellow') === -1) {
            $txtspan[0].innerHTML = 'Unmark Favorite';
        }
        $(this).on('mouseover', function() {
            let pos = $(this).offset();
            let left = pos.left - 128 + 'px'; // width of tip is 120px
            let top = pos.top + 'px';
            $txtspan[0].style.top = top;
            $txtspan[0].style.left = left;
            $txtspan[0].style.display = 'block';
        });
        $(this).on('mouseout', function() {
            $txtspan[0].style.display = 'none';
        });
    });
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
            let hikename = $(this).parent().next().children().eq(0).text();
            popupHikeName(hikename);
        });
        $(this).on('mouseover', function() {
            let zpos = $(this).offset();
            let hpos = zpos.left - 108;
            let vpos = zpos.top;
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
 * map.js for listeners).
 * This function also returns a set of hikenumbers for making tracks when the map
 * zoom >= 13. Clusters are 'segregated' so that the entire set of hikes in the
 * cluster can be drawn, each with a unique color.
 * 
 * @param {string} boundsStr The string from google maps holding the new map bounds
 * @param {boolean} zoom Indicates whether or not map is zoomed > 12.
 * @returns {array}  if map zoom > 12, arrays of hike numbers (and their
 *                   corresponding info window text) within bounds: clusters return
 *                   only the index number into the CL array.
 */
const IdTableElements = (boundsStr, zoom) => {
    var singles = [];        // individual hike nos
    var trackColors = [];    // for clusters, tracks get unique colors
    var hikeInfoWins = [];   // info window content for each hikeno in singles
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
    var max_color = colors.length - 1;
    CL.forEach(function(clus, clindx) {
        var color = 0;
        clus.hikes.forEach(function(hike) {
            let lat = hike.loc.lat;
            let lng = hike.loc.lng;
            if (lng <= east && lng >= west && lat <= north && lat >= south) {
                let hikeindx = allHikes.indexOf(hike.indx);   
                let hikeobj = locations[hikeindx];
                let data = idHike(allHikes[hikeindx], hikeobj);
                hikearr.push(data);
                if (zoom) {
                    let cliw = '<div id="iwCH">' + hike.name + '<br />Length: ' +
                        hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                        '<br />Difficulty: ' + hike.diff + '</div>';
                    singles.push(hike.indx);
                    hikeInfoWins.push(cliw);
                    trackColors.push(colors[color++]);
                    if (color > max_color) { // rotate through colors
                        color = 0;
                    }
                }
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
            if (zoom) {
                let nmiw = '<div id="iwNH">' + hike.name + '<br />Length: ' +
                    hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                    '<br />Difficulty: ' + hike.diff + '</div>';
                singles.push(hike.indx);
                hikeInfoWins.push(nmiw);
                trackColors.push(colors[0]);
            }
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
    return new Array(singles, hikeInfoWins, trackColors);
 
}

var grabber = document.getElementById('adjustWidth');
grabber.addEventListener('mousedown', changeWidth, false);
/**
 * Function to change div widths when mousedown on 'grabber' (#adjustWidth)
 * Thie function adds a mousemove listener to track the mouse location
 * 
 * @param {DOMevent} ev The DOM event associated with mousedown
 * @return {null}
 */
function changeWidth(ev) {
    ev.preventDefault(); // prevents selecting other elements while mousedown
    document.addEventListener('mousemove', widthSizer, false);
}

/**
 * The function is called by the mousemove event listener. It is necessary
 * not to use anonymous functions here as those listeners cannot be removed.
 * When the mouse moves, a listener is add to detect when the mouse is released.
 * 
 * @param {DOMevent} evt 
 * @return {null}
 */
function widthSizer(evt) {
    document.addEventListener('mouseup', stopMoving, false);
    let viewport = window.innerWidth;
    let sideWidth = viewport - evt.clientX - 3;
    $('#map').width(evt.clientX);
    $('#sideTable').width(sideWidth);
    positionFavTooltips();
    locateGeoSym();
}

/**
 * This function removes both the mousemove listener and the mouseup listener
 * so that widthSizer ceases to function, and the mousdedown can be re-invoked
 */
function stopMoving() {
    document.removeEventListener('mousemove', widthSizer, false);
    document.removeEventListener('mouseup', stopMoving, false);
}
