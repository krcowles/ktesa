// jQuery UI widget:
var spinner = $('#spinner').spinner({
    min: 1,
    max: 50,
    page: 5,
    value: 5
});
spinner.spinner("value", 5);

// Used for both table only and map+table pages
function filterSetup() {
    // positioning:
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
            $('#selloc').removeClass('hidden');
            $('#selloc').addClass('inline');
            $('#loclbl').removeClass('normal');
            $('#loclbl').addClass('hilite');
        } else {
            $('#selloc').removeClass('inline');
            $('#selloc').addClass('hidden');
            $('#loclbl').removeClass('hilite');
            $('#loclbl').addClass('normal');
            $(this).prop('checked', false);
        }
        $('#hike').prop('checked', false);
        if ($('#selhike').hasClass('inline')) {
            $('#selhike').removeClass('inline');
            $('#selhike').addClass('hidden');
            $('#hikelbl').removeClass('hilite');
            $('#hikelbl').addClass('normal');
        }
    });
    $('#hike').on('click', function() {
        if ($('#selhike').hasClass('hidden')) {
            $('#selhike').removeClass('hidden');
            $('#selhike').addClass('inline');
            $('#hikelbl').removeClass('normal');
            $('#hikelbl').addClass('hilite');
        } else {
            $('#selhike').removeClass('inline');
            $('#selhike').addClass('hidden');
            $('#hikelbl').removeClass('hilite');
            $('#hikelbl').addClass('normal');
            $(this).prop('checked', false);
        }
        if ($('#selloc').hasClass('inline')) {
            $('#selloc').removeClass('inline');
            $('#selloc').addClass('hidden');
            $('#loclbl').removeClass('hilite');
            $('#loclbl').addClass('normal');
        }
        $('#loc').prop('checked', false);
    });
    $('a').on('click', function(ev) {
        if ($('#hike').prop('checked')) {
            ev.preventDefault();
            var thishike = $(this).text();
            $('#link').val(thishike);
            $('#link').focus();
            var currpos = $('#link').scrollTop();
            $(window).scrollTop(currpos - 40);
        }
    });
    // actual filtering of hikes:
    var ajaxdone = false;
    var coords = {};
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
            var arealoc = $('#area').find(":selected").text();
            if (!ajaxdone) {
                var locWait = setInterval( function() {
                    if (ajaxdone) {
                        clearInterval(locWait);
                        filterList(epsilon, coords);
                        ajaxdone = false;
                        return;
                    }
                }, 10);
            }
            getAreaCoords(arealoc);
        } else if ($('#hike').prop('checked')) {
            var hikeloc = $('#link').val().trim();
            if (hikeloc !== '') {
                coords = getHikeCoords(hikeloc);
                filterList(epsilon, coords);
            } else {
                alert("You have not selected a hike");
                return;
            }
        } else {
            alert("You must select either area or hike");
            return;
        }
    });
    function getAreaCoords(area) {
        $.ajax( {
            url: '../json/areas.json',
            dataType: 'json',
            success: function(json_dat) {
                var all_locs = json_dat.areas;
                for (var j=0; j<all_locs.length; j++) {
                    if (all_locs[j].loc == area) {
                        coords = {"lat": all_locs[j].lat, "lng": all_locs[j].lng};
                        break;
                    }
                }
                ajaxdone = true;
            },
            error: function() {
                alert("Unable to retrieve areas.json in json directory");
            }
        });
    }
    function getHikeCoords(hike) {
        coords = {lat:35.99998,lng:-106.00001};
        if (pg === 'tbl') {
            var $tblrows = $('.sortable tbody tr');
        } else {
            var $tblrows = $('.msortable tbody tr');
        }
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