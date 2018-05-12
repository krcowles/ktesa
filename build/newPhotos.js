var passData = [];
var includes = [];
// includes member = 0 implies not a selection
$('.allPhotos').each( function() {
    includes.push(0);
});
var noOfPix = includes.length;
var allpix = picdata; // array of photo data created by php
if (noOfPix !== allpix.length) {
    alert("Mismatch no of photos from saved data (php)...");
}
/* 
 * create an object to add to the passData before passing to server
 * the prinary use is to pass info like hike no and user that is not
 * otherwise a part of the picture data but is needed by the php script
 */
var processdat = {
    folder: '999',
    pic: hikeno,
    desc: usrid,
    alb: '',
    org: '',
    thumb: '',
    nsize: '',
    pHt: '',
    pWd: '',
    taken: '',
    lat: '',
    lng: '',
    gpsdate: '',
    gpstime: ''
};
$('#addall').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $ckboxes.each( function(indx) {
            $(this).prop('checked',false);
            includes[indx] = 0;
        });
    } else {
        $ckboxes.each( function(indx) {
            $(this).prop('checked',true);
            includes[indx] = 1;
        });
    }
    updateSelections();
});
var $ckboxes = $('.ckbox');
$ckboxes.each( function() {
    $(this).on('click', function() { 
        $('#addall').prop('checked', false); 
        for (var i=0; i<phTitles.length; i++) {
            if ($(this).val() == phTitles[i]) {
                if ($(this).prop('checked')) {
                    includes[i] = 1;
                } else {
                    includes[i] = 0;
                }
            }
        }
        updateSelections();
    });
});

function updateSelections() {
    passData = []; // ensure clean starting point
    // filter out selections:
    for (var k=0; k<allpix.length; k++) {
        if (includes[k] === 1) {
            passData.push(allpix[k]);
        }
    }
    // passData is an array of objects; add an object to the array to pass addtl info:
    passData.push(processdat);
}

$('#load').on('click', function() {
    var serverData = JSON.stringify(passData);
    $.ajax({
        type: "POST",
        url: "storeNewPhotos.php",
        dataType: 'text',
        data: { 'info' : serverData },
        success: function(result) {
            var output = result;
            if (output == 'Success') {
                window.open("editDB.php?hno=" + hikeno + "&usr=" + usrid);
            } else {
                alert("Photos not saved: \n" + output);
            }
            window.close();
        },
        error: function(jq, errmsg, stat) {
            alert("Attempt to save photos failed: " + errmsg + "; " + stat);
        }
    });
});
