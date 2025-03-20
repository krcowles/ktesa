interface RangeCoords {
    north: number;
    south: number;
    west: number;
    east: number;
}
$(function () { // when page is loaded...
 
var hike = $('#ehno').text();
var reload = "editDB.php?tab=2&hikeNo=" + hike;
var nm_range = {north: 37, south: 31.3, west: -109.04, east: -103} as RangeCoords;
var $hboxes  = $('.hpguse');
var $mboxes  = $('.mpguse');
var $nolocs  = $('.nomap');
var locmodal = new bootstrap.Modal(document.getElementById('photoloc') as HTMLElement);

$('#all').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $hboxes.each( function() {
                $(this).prop('checked',false);
        });
    } else {
        $hboxes.each( function() {
                $(this).prop('checked',true);
        });
    }
});
$('#mall').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $mboxes.each( function() {
                $(this).prop('checked',false);
        });
    } else {
        $mboxes.each( function() {
                $(this).prop('checked',true);
        });
    }
});
$.each($nolocs, function(indx, pic) {
    $(pic).on('click', function() {
        pic.id = "nl" + indx;
        var tsvid = $(pic).val() as string;
        var $chkbox = $(pic);
        locmodal.show();
        // to prevent retriggering of click:  (!!)
        $('#setloc').off('click').on('click', function() {
            var lat_entry = $('#piclat').val() as string;
            var lng_entry = $('#piclng').val() as string;
            if (lat_entry == '' ) {
                alert("Latitude data is missing...");
                //locmodal.hide();
                $chkbox.prop('checked', false);
                return false;
            }
            if (lng_entry == '') {
                alert("Longitude data is missing...");
                //locmodal.hide();
                $chkbox.prop('checked', false);
                return false;
            }
            var plat = parseFloat(lat_entry);
            var plng = parseFloat(lng_entry);
            if (plng > 0) {
                alert("Longitude must be a negative number");
                $('#piclng').val('');
                //locmodal.hide();
                $chkbox.prop('checked', false);
                return false;
            }
            if (plat > nm_range.north || plat < nm_range.south 
                    || plng > nm_range.east || plng < nm_range.west) {
                var ans = confirm("The coordinates are outside of New Mexico: use anyway?");
                if (!ans) {
                    //locmodal.hide()
                    $chkbox.prop('checked', false);
                    return false;
                }
            }   
            var ajaxdata = {id: tsvid, photolat: plat, photolng: plng};
            $.ajax({
                url: 'setLocations.php',
                method: 'post',
                data: ajaxdata,
                dataType: 'text',
                success: function(results) {
                    if (results == 'OK') {
                        locmodal.hide();
                        // change to mapping box is automatic via photoSelect.php
                        alert("Photo location updated");
                        window.open(reload, "_self");
                    } else {
                        alert("Something went wrong: notify admin")
                    }
                },
                error: function() {
                    alert("Could not update location: notify admin")
                }
            });
            return;
        });
        return;
    });
});

});


