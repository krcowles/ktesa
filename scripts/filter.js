/**
 * @fileoverview This module setups & executes the filtering capability
 * @author Tom Sandberg
 * @author Ken Cowles
 */
var mapHikes = []; // required by map-drawing code
// the filter 'miles' spinner:
var spinner = $('#spinner').spinner({
    min: 1,
    max: 50,
    page: 5,
    value: 5
});
spinner.spinner("value", 5);
var hikePgClick = true;
positionMain();

/**
 * This function will place position elements on the page on page
 * load and during window resize
 * @return {null}
 */
function positionMain() {
    let winwidth = $(window).innerWidth();
    let tblwidth = $('.sortable').width();
    let margs = Math.floor((winwidth - tblwidth)/2) + "px";
    $('#tblfilter').css('margin-left', margs);
    $('#tblfilter').css('margin-right', margs);
    $('#filtnote').css('margin-left', margs);
    $('#filtnote').css('margin-right', margs);
    $('#mapnote').css('margin-left', margs);
    let btnwidth = $('#map').width();
    let delta =Math.floor(parseInt(margs) - btnwidth) - 24;
    $('#map').css('left', delta);
    return;
}
/**
 * This function displays the 'area' html with select box when the 'loc'
 * radio button is clicked.
 * @return {null}
 */
function displayAreaFilter() {
    $('#selloc').removeClass('hidden');
    $('#selloc').addClass('inline');
    $('#loclbl').removeClass('normal');
    $('#loclbl').addClass('hilite');
    return;
}
/**
 * This function hides the 'area' html (including radio button & select box)
 * @return {null}
 */
function hideAreaFilter() {
    $('#selloc').removeClass('inline');
    $('#selloc').addClass('hidden');
    $('#loclbl').removeClass('hilite');
    $('#loclbl').addClass('normal');
    return;
}
/**
 * This function displays the 'hike' html with input box when the 'hike'
 * radio button is clicked
 * @return {null}
 */
function displayHikeFilter() {
    $('#selhike').removeClass('hidden');
    $('#selhike').addClass('inline');
    $('#hikelbl').removeClass('normal');
    $('#hikelbl').addClass('hilite');
    return;
}
/** 
 * This function hides the 'hike' html & input box
 * @return {null}
 */
function hideHikeFilter() {
    $('#selhike').removeClass('inline');
    $('#selhike').addClass('hidden');
    $('#hikelbl').removeClass('hilite');
    $('#hikelbl').addClass('normal');
    return;
}
/**
 * This function will execute when the user selects a hike link from the table.
 * If 'hikePgClick' is true, the link will operate from the as expected
 * i.e. it will go to the hike page. When 'hikePgClic' is false, the link
 * will place the hike name into the filter's hike html box for use in filtering.
 * @return {null}
 */
function hikeSelect() {
    hikePgClick = false;
    $('a').on('click', function(ev) {
        if ($('#hike').prop('checked')) {
            ev.preventDefault();
            var thishike = $(this).text();
            $('#link').val(thishike);
            $('#link').focus();
            var currpos = $('#link').scrollTop();
            // different values for table only and map+table pages:
            var scrolloff = -40;
            if (typeof $('#map') !== 'undefined') {
                var mapoff = $('#map').height();
                scrolloff += mapoff;
            }
            $(window).scrollTop(currpos + scrolloff);
        }
    });
}

/**
 * This function establishes the location of filter options and their
 * execution capability.
 * @return {null}
 */
function filterSetup() {
    setupCheckboxes();
    
    // displaying of features:
    $('#showfilter').on('click', function() {
        $('#dispopts').toggle();
    });
    $('#loc').on('click', function() {
        if ($('#selloc').hasClass('hidden')) {
            displayAreaFilter();
        } else {
            hideAreaFilter();
            $(this).prop('checked', false);
        }
        if ($('#selhike').hasClass('inline')) {
            hideHikeFilter();
            $('a').off('click');
            hikePgClick = true;
        }
        $('#hike').prop('checked', false);
    });
    $('#hike').on('click', function() {
        if ($('#selhike').hasClass('hidden')) {
            displayHikeFilter();
            hikeSelect();
        } else {
            hideHikeFilter();
            $(this).prop('checked', false);
            $('a').off('click');
            hikePgClick = true;
        }
        if ($('#selloc').hasClass('inline')) {
            hideAreaFilter();
        }
        $('#loc').prop('checked', false);
    });
    
    var coords = {}; // coordinates of location from which to calculate radius
    // in case of page refresh:
    $('#loc').prop('checked', false);
    $('#hike').prop('checked', false);

    // when applying the filter:
    $('#apply').on('click', function() {
        var epsilon = $('#spinner').spinner('value');
        if (!($('#results').css('display') === 'none')) {
            $('#ftable').html('<tbody></tbody>');
        }
        if ($('#loc').prop('checked')) {
            var area = $('#area').find(":selected").text();
            $.ajax({ // returns array of location centers on success
                url:      '../json/areas.json',
                dataType: 'json',
                success: function(json_data) {
                    var areaLocCenters = json_data.areas;
                    for (var j=0; j<areaLocCenters.length; j++) {
                        if (areaLocCenters[j].loc == area) {
                            coords = {
                                "lat": areaLocCenters[j].lat, 
                                "lng": areaLocCenters[j].lng
                            };
                            break;
                        }
                    }
                    filterList(epsilon, coords);
                },
                error: function() {
                    alert("Unable to retrieve areas.json in json directory");
                    return false;
                }
            });
            if (!hikePgClick) {  // restore normal hike page clicking
                $('a').off('click');
            }
        } else if ($('#hike').prop('checked')) {
            var hikeloc = $('#link').val().trim();
            if (hikeloc !== '') {
                coords = getHikeCoords(hikeloc);
                filterList(epsilon, coords);
                $('a').off('click');
                hikePgClick = true;
            } else {
                alert("You have not selected a hike");
                return;
            }
        } else {
            alert("You must select either area or hike");
            return;
        }
    });
    /**
     * This function extracts latitude/longitude form the table for the
     * target hike name.
     * @param {string} hike name of the target hike 
     * @return {object} the latitude/longitude found
     */
    function getHikeCoords(hike) {
        var $tblrows = $('.sortable tbody tr');
        var coords = {};
        $tblrows.each(function() {
            let hikeLinkText = $(this).find('td').eq(1).children().eq(0).text();
            if (hikeLinkText === hike) {
                var hlat = $(this).data('lat');
                var hlon = $(this).data('lon');
                coords = {lat: hlat, lng: hlon};
                return;
            }
        });
        if (coords.length === 0) {
            alert("Hike not found - try new link");
        } else {
            return coords;
        }
    }
    /**
     * This function creates the rows for the results table based on the 
     * filter parameters (radius from center pt, center pt). After creating
     * the table, it displays the results
     * @param {number} radius the radius of the search
     * @param {object} geo The center point latitude/longitude
     * @return {null}
     */
    function filterList(radius, geo) {
        tblHtml = $('.sortable').html();
        var bdystrt = tblHtml.indexOf('<tbody>');
        tblHtml = tblHtml.substr(0, bdystrt);
        $('#ftable').append(tblHtml);
        $tblrows = $('.sortable tbody tr').each( function() {
            var hikelat = $(this).data('lat');
            var hikelng = $(this).data('lon');
            var distance = radialDist(hikelat, hikelng, geo.lat, geo.lng, 'M');
            if (distance <= radius) {
                //var hikename = $(this).find('td').eq(0).text();
                //alert(hikename);

                // create clone, else node is removed from big table!
                var $clone = $(this).clone();
                $('#ftable tbody').append($clone);
            }
        });
        $('#results').show();
        setupCheckboxes();
        $('#refTbl').hide();
        return;
    }
    /**
     * This function will return the radial distance between two lat/lngs
     * @param {number} lat1 first latitude
     * @param {number} lon1 first longitude
     * @param {number} lat2 second latitude
     * @param {number} lon2 second longitude
     * @param {string} unit which units to use: miles or kilometers
     * @return {number} the radial distance in the selected units
     */
    function radialDist(lat1, lon1, lat2, lon2, unit) {
        if (lat1 === lat2 && lon1 === lon2) { return 0; }
        var radlat1 = Math.PI * lat1/180;
        var radlat2 = Math.PI * lat2/180;
        var theta = lon1-lon2;
        var radtheta = Math.PI * theta/180;
        var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        dist = Math.acos(dist);
        dist = dist * 180/Math.PI;
        dist = dist * 60 * 1.1515;
        if (unit === "K") { dist = dist * 1.609344; }
        if (unit === "N") { dist = dist * 0.8684; }  // else result is in miles "M"
        return dist;
    }
    $('#redo').on('click', function() {
        $('#ftable').html('<tbody></tbody');
        $('#results').hide();
        if ($('#loc').prop('checked')) {
            $('#loc').click();
            // jQ sees click prop off after above event processing and turns it on, so
            $('#loc').prop('checked', false);
        }
        if ($('#hike').prop('checked')) {
            $('#hike').click();
            $('#hike').prop('checked', false);
            $('#link').val('');
        }
        $('#refTbl').show();
    });
}

/**
 * This function collects all the checkboxes (esp required after presenting a new
 * table of results). Each checkbox will add to or delete from the global var
 * mapHikes. That var is used to determine which hikes to draw on a single map.
 * @return {null}
 */
function setupCheckboxes() {
    $('input[type=checkbox]').on('change', function() {
        let gpx = $(this).parent().data('track');
        if($(this).is(':checked')) {
            mapHikes.push(gpx);
        } else {
            if (mapHikes.length > 0) {
                let indx = mapHikes.indexOf(gpx);
                if (indx > -1) {
                    mapHikes.splice(indx, 1);
                }
            }
        }
    });
}

// draw the map
$('#map').on('click', function() {
    if (mapHikes.length === 0) {
        alert("No hike checkboxes have been checked");
        return;
    }
	var query = '';
	mapHikes.forEach(function(track) {
		query += "m[]=" + track + "&";
	});
	query = query.substring(0, query.length-1);
	window.open("../php/multiMap.php?" + query);
});

$(window).resize(positionMain);