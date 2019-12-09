// the main page search bar:
$('#searchbar').val('');
$('#searchbar').on('input', function(ev) {
    var $input = $(this),
       val = $input.val();
       list = $input.attr('list'),
       match = $('#'+list + ' option').filter(function() {
           return ($(this).val() === val);
       });
    if(match.length > 0) {
        // find the hike and zoom in...
        $('table tbody tr').each(function() {
            if ($(this).children().eq(0).children().eq(0).text().trim() == val) {
                var lat = parseFloat($(this).data('lat'));
                var lng = parseFloat($(this).data('lon'));
                var srchloc = {lat: lat, lng: lng};
                var hikepage = $(this).children().eq(0).children().eq(0).attr('href');
                var $opts = $('#srch').detach();
                var def = new $.Deferred();
                modal.open({
                    id: 'srchopt',
                    height: '76px',
                    width: '164px',
                    content: $opts,
                    hike: val,
                    loc: srchloc,
                    page: hikepage,
                    deferred: def
                });
            $.when( def ).then(function() {
                $('#modals').append($opts);
            });
                return false; // as this will happen for each table...
            }
        });
    } // do nothing if not a match
});

// filter 'miles' spinner:
var spinner = $('#spinner').spinner({
    min: 1,
    max: 50,
    page: 5,
    value: 5
});
spinner.spinner("value", 5);
var hikePgClick = true;

// Used for both table only and map+table pages
function displayAreaFilter() {
    $('#selloc').removeClass('hidden');
    $('#selloc').addClass('inline');
    $('#loclbl').removeClass('normal');
    $('#loclbl').addClass('hilite');
}
function hideAreaFilter() {
    $('#selloc').removeClass('inline');
    $('#selloc').addClass('hidden');
    $('#loclbl').removeClass('hilite');
    $('#loclbl').addClass('normal');
}
function displayHikeFilter() {
    $('#selhike').removeClass('hidden');
    $('#selhike').addClass('inline');
    $('#hikelbl').removeClass('normal');
    $('#hikelbl').addClass('hilite');
}
function hideHikeFilter() {
    $('#selhike').removeClass('inline');
    $('#selhike').addClass('hidden');
    $('#hikelbl').removeClass('hilite');
    $('#hikelbl').addClass('normal');
}
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
function filterSetup() {
    // positioning: (tables are different widths on these pages)
    var winwidth = $(window).innerWidth();
    if (pg === 'tbl') {
        var tblwidth = $('.sortable').width();
    } else {
        var tblwidth = $('.msortable').width();
    }
    var margs = Math.floor((winwidth - tblwidth)/2) + "px";
    $('#tblfilter').css('margin-left', margs);
    $('#tblfilter').css('margin-right', margs);
    $('#filtnote').css('margin-left', margs);
    $('#filtnote').css('margin-right', margs);
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
    function getHikeCoords(hike) {
        var $tblrows = $('.sortable tbody tr');
        var coords = {};
        $tblrows.each(function() {
            if ($(this).find('td').eq(0).text() === hike) {
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
    function filterList(radius, geo) {
        //alert("Hikes within " + radius + " miles of " + geo.lat + ", " + geo.lng);
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
    }
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
    });
}