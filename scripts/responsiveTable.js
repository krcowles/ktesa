"use strict";
/// <reference types="bootstrap" />
/**
 * @fileoverview Management of options button
 *
 * @author Ken Cowles
 *
 * @version 1.0 First release of responsive design
 * @version 1.1 Typescripted
 */
$(function () {
    /**
     * This function merely positions the options drop-down wrt/table
     */
    var optsPosition = function () {
        var tblwidth = $('table').width();
        $('#floater').width(tblwidth);
        return;
    };
    optsPosition();
    $(window).on('resize', function () {
        optsPosition();
    });
    $('#areas').hide();
    var title = $('#trail').text();
    $('#ctr').text(title);
    // table parms
    var $tbody = $('table').find('tbody');
    var rows = $tbody.find('tr').toArray();
    // js modal
    var near_modal = new bootstrap.Modal(document.getElementById('near'), {
        keyboard: false
    });
    // dropdown selections:
    $('#all').on('click', function (ev) {
        ev.preventDefault();
        $('#areas').hide();
        $('tbody').empty();
        $('tbody').append(rows);
    });
    $('#reg').on('click', function (ev) {
        ev.preventDefault();
        $('#areas').show();
    });
    $('#cls').on('click', function (ev) {
        ev.preventDefault();
        $('#areas').hide();
        near_modal.show();
    });
    // setup area links
    var $loc_list = $('#alist').find('li a');
    $loc_list.each(function () {
        $(this).on('click', function (ev) {
            ev.preventDefault();
            var lochikes = [];
            var locarea = $(this).text();
            var hikeset = regions[locarea]; // regions are objects whose key is a locale (string)
            for (var i = 0; i < rows.length; i++) {
                var item = $(rows[i]).data('indx');
                var hikeno = parseInt(item);
                for (var j = 0; j < hikeset.length; j++) {
                    if (hikeno == hikeset[j]) {
                        lochikes.push(rows[i]);
                    }
                }
            }
            $('tbody').empty();
            $('tbody').append(lochikes);
        });
    });
    // show radius hikes
    $('#show').on('click', function (ev) {
        ev.preventDefault();
        var retval = true;
        var locale = $('#regions').val();
        var miles_str = $('#miles').val();
        var miles_no = parseFloat(miles_str);
        if (locale == "") {
            alert("You have not selected a region");
            retval = false;
        }
        else if (miles_str == '' || isNaN(miles_no)) {
            alert("Please enter a numerical value for miles");
            retval = false;
        }
        else {
            // proceed with list of hikes...
            $.ajax({
                url: '../json/areas.json',
                dataType: 'json',
                success: function (json_data) {
                    var areaLocCenters = json_data.areas;
                    var coords = { lat: 0, lng: 0 };
                    for (var j = 0; j < areaLocCenters.length; j++) {
                        if (areaLocCenters[j].loc == locale) {
                            coords = {
                                "lat": areaLocCenters[j].lat,
                                "lng": areaLocCenters[j].lng
                            };
                            break;
                        }
                    }
                    filterHikes(miles_no, coords);
                },
                error: function () {
                    alert("Sorry, we can't find the coordinates\nThe admin " +
                        "has been notified");
                    var err = "Mobile access of areas.json failed";
                    var errobj = { err: err };
                    $.post('../php/ajaxError.php', errobj);
                    return false;
                }
            });
        }
        return retval;
    });
    /**
     * This function creates the rows for the results table based on the
     * filter parameters (radius from center pt, center pt). After creating
     * the table, it displays the results
     */
    function filterHikes(radius, geo) {
        var nearby = [];
        for (var j = 0; j < rows.length; j++) {
            var lat = $(rows[j]).data('lat');
            var lng = $(rows[j]).data('lon');
            var distance = radialDist(lat, lng, geo.lat, geo.lng, 'M');
            if (distance <= radius) {
                nearby.push(rows[j]);
            }
        }
        $('tbody').empty();
        $('tbody').append(nearby);
    }
    /**
     * This function will return the radial distance between two lat/lngs
     */
    function radialDist(lat1, lon1, lat2, lon2, unit) {
        if (lat1 === lat2 && lon1 === lon2) {
            return 0;
        }
        var radlat1 = Math.PI * lat1 / 180;
        var radlat2 = Math.PI * lat2 / 180;
        var theta = lon1 - lon2;
        var radtheta = Math.PI * theta / 180;
        var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        dist = Math.acos(dist);
        dist = dist * 180 / Math.PI;
        dist = dist * 60 * 1.1515;
        if (unit === "K") {
            dist = dist * 1.609344;
        }
        if (unit === "N") {
            dist = dist * 0.8684;
        } // else result is in miles "M"
        return dist;
    }
});
