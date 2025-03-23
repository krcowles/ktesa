"use strict";
$(function () {
    var hike = $('#ehno').text();
    var reload = "editDB.php?tab=2&hikeNo=" + hike;
    var nm_range = { north: 37, south: 31.3, west: -109.04, east: -103 };
    var $hboxes = $('.hpguse');
    var $mboxes = $('.mpguse');
    var $nolocs = $('.nomap');
    var $gpsdata = $('.picgps');
    var locmodal = new bootstrap.Modal(document.getElementById('photoloc'));
    $('#all').on('change', function () {
        if ($(this).prop('checked') === false) {
            $hboxes.each(function () {
                $(this).prop('checked', false);
            });
        }
        else {
            $hboxes.each(function () {
                $(this).prop('checked', true);
            });
        }
    });
    $('#mall').on('change', function () {
        if ($(this).prop('checked') === false) {
            $mboxes.each(function () {
                $(this).prop('checked', false);
            });
        }
        else {
            $mboxes.each(function () {
                $(this).prop('checked', true);
            });
        }
    });
    var saveGPS = function (cbid) {
        // to prevent retriggering of click:  (!!)
        $('#setloc').off('click').on('click', function () {
            var lat_entry = $('#piclat').val();
            var lng_entry = $('#piclng').val();
            if (lat_entry == '') {
                alert("Latitude data is missing...");
                return false;
            }
            if (lng_entry == '') {
                alert("Longitude data is missing...");
                return false;
            }
            var plat = parseFloat(lat_entry);
            var plng = parseFloat(lng_entry);
            if (plng > 0) {
                alert("Longitude must be a negative number");
                $('#piclng').val('');
                return false;
            }
            if (plat > nm_range.north || plat < nm_range.south
                || plng > nm_range.east || plng < nm_range.west) {
                var ans = confirm("The coordinates are outside of New Mexico: use anyway?");
                if (!ans) {
                    return false;
                }
            }
            var ajaxdata = { id: cbid, photolat: plat, photolng: plng };
            $.ajax({
                url: 'setLocations.php',
                method: 'post',
                data: ajaxdata,
                dataType: 'text',
                success: function (results) {
                    if (results == 'OK') {
                        locmodal.hide();
                        // change to mapping box is automatic via photoSelect.php
                        alert("Photo location updated");
                        window.open(reload, "_self");
                    }
                    else {
                        alert("Something went wrong: notify admin");
                    }
                },
                error: function () {
                    alert("Could not update location: notify admin");
                }
            });
            return;
        });
    };
    $.each($gpsdata, function (indx, gps) {
        $(gps).on('click', function () {
            gps.id = "g" + indx;
            var tsvid = $(gps).val();
            var $chkbox = $(gps);
            var $parent = $(gps).parent();
            var gpslat = $parent.children().eq(0).text();
            var gpslng = $parent.children().eq(1).text();
            $('#modtype').text('modify');
            $('#piclat').val(gpslat);
            $('#piclng').val(gpslng);
            locmodal.show();
            $chkbox.prop('checked', false);
            saveGPS(tsvid);
        });
    });
    $.each($nolocs, function (indx, pic) {
        $(pic).on('click', function () {
            pic.id = "nl" + indx;
            var tsvid = $(pic).val();
            var $chkbox = $(pic);
            $('#modtype').text("add");
            $('#piclat').val('');
            $('#piclng').val('');
            locmodal.show();
            $chkbox.prop('checked', false);
            saveGPS(tsvid);
            return;
        });
    });
});
