$( function () { // when page is loaded...

var msgA;  // generic
var msgB;
var msgC;

/* Preload the GPS Maps & Data Section with the urls for uploaded files;
 * The user will still be required to fill in the remaining fields for these items:
 * note the addition of the 'required' attribute for the uploaded items;
 * Currently, only two items in each section are supported, but this can be
 * easily expanded by adding code here (and additional upload elements in the html)
 */
function preload(targfile,datasect) {
    var fullPath = targfile.val();
    var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
    var filename = fullPath.substring(startIndex);
    if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
        filename = filename.substring(1);
    }
    // This provides C:\fakepath as a lead-in: strip off the filename:
    if ( filename.indexOf('html') === -1 ) {
        var fname = '../gpx/' + filename;
        $(datasect).val(fname);
    } else {
        var fname  = '../maps/' + filename;
        $(datasect).val(fname);
    }
}
$('#pmap').change( function() { 
    preload($(this),'#ur1');
    $('#lt1').attr('required',true);
    $('#ct1').attr('required',true);
});
$('#pgpx').change( function() {
    preload($(this),'#ur2');
    $('#lt2').attr('required',true);
    $('#ct2').attr('required',true);
});
$('#amap').change( function() {
    preload($(this),'#ur5');
    $('#lt5').attr('required',true);
    $('#ct5').attr('required',true);
});
$('#agpx').change( function() {
    preload($(this),'#ur6');
    $('#lt6').attr('required',true);
    $('#ct6').attr('requried',true);
});

// Start with no display of lat/lng inputs - only used for Index Page Creation
$('#latlng').css('display','none');

// When adding photo urls, populate the first two in the 'Other URL's' section
$('#curl1').change( function() {
    var ph1 = $(this).val();
    if ($(this).val() === '') {
        $('#url1').val('');
    } else {
        $('#url1').val(ph1);
    }
})
$('#curl2').change( function() {
    var ph2 = $(this).val();
    if ($(this).val() === '') {
        $('#url2').val('');
    } else {
        $('#url2').val(ph2);
    }
})

/* Setting the target action for the submit button, based on whether the submission is
 * for a new hike page, or a new index pg;
 * NOTE: the "pageType" radio buttons are not within the <form> element
 */
$('input[name="pageType"]').click( function() {
    if($('input:radio[name=pageType]:checked').val() == "vcenter") {
        useIndexPg();
        $('input[name="mstyle"][value="center"]').prop('checked',true);
    } else {
        useStdPg();
        $('input[name="mstyle"][value="center"]').prop('checked',false);
    }
});
$('input[name="mstyle"]').click( function() {
    if($('input:radio[name=mstyle]:checked').val() == "center") {
        useIndexPg();
        $('input[name="pageType"][value="standard"]').prop('checked',false);
        $('input[name="pageType"][value="vcenter"]').prop('checked',true);
    } else {
        useStdPg();
        $('input[name="pageType"][value="standard"]').prop('checked',true);
        $('input[name="pageType"][value="vcenter"]').prop('checked',false);
    }
    if ($('input:radio[name=mstyle]:checked').val() == 'ctrhike') {
        $('#newvch').css('display','block');
    } else {
        $('#newvch').css('display','none');
    }
    if ($('input:radio[name=mstyle]:checked').val() == 'cluster') {
        $('#newcl').css('display','block');
    } else {
        $('#newcl').css('display','none');
    }
});
function useIndexPg() {
    pageSelector = "displayIndexPg.php";
    msgA = "Index Page Name (Include 'Index' at end of descriptor): ";
    $('.notVC').css('color','Gray');
    msgB = "Provide image for Index Page: ";
    msgC = "Visitor Center/Park Map  [ image size around 700px x 450px ]: ";
    $('#l_add1').css('color','Black');
    $('#pgTitleText').text(msgA);
    $('#spImg').text(msgB);
    $('.indxFile').css('display','none');
    $('#latlng').css('display','block');
    $('#refdat').css('display','none');
    $('#l_add1').css('color','Brown');
    $('#l_add1').text(msgC);
    $('.honly').css('display','none');
}
function useStdPg() {
    pageSelector = "validateHike.php";
    msgA = "Hike Name (As it will appear in the table & window tab): ";
    $('.notVC').css('color','Black');
    msgB = "Additional Images - optional";
    msgC = "Other image (pop-up captions not provided at this time): &nbsp;";
    $('.indxFile').css('display','block');
    $('#latlng').css('display','none');
    $('#refdat').css('display','block');
    $('#l_add1').css('color','Black');
    $('#pgTitleText').text(msgA);
    $('#spImg').text(msgB);
    $('#l_add1').html(msgC);
    $('.honly').css('display','block');
}
var dwidth = Math.floor($(document).width());
dwidth -= 170;
$('#saver').css('left',dwidth+'px');
$(window).resize( function() {
    var wwidth = Math.floor($(document).width());
    wwidth -= 170;
    $('#saver').css('left',wwidth+'px');
});
/* END OF page-creation type */

/* 
 * Load any data from database via php
 */
var dbhno = $('#dbhno').text();
var dbhnm = $('#dbhnm').text();
var dbloc = $('#dbloc').text();  // locale
$('#area').val(dbloc);
var dblog = $('#dblog').text();  // logistics (type)
$('#type').val(dblog);
var dbmrk = $('#dbmrk').text();  // marker style
$('#vc').prop('checked',false);
$('#vch').prop('checked',false);
$('#ch').prop('checked',false);
$('#othr').prop('checked',false);
if (dbmrk == 'At VC') {
    $('#vch').prop('checked',true);
    var dbcst = $('#dbcst').text();
    $('#newvch').css('display','block');
    $('#nvch').val(dbcst);
}
if (dbmrk == 'Cluster') {
    $('#ch').prop('checked',true);
    var dbcgr = $('#dbcgr').text();
    $('#newcl').css('display','block');
    $('#nclus').val(dbcgr);
}
var dbdif = $('#dbdif').text();  // difficulty
$('#ease').val(dbdif);
var dbexp = $('#dbexp').text();  // exposure
$('#sunny').prop('checked',false);
$('#partly').prop('checked',false);
$('#shady').prop('checked',false);
if(dbexp == 'Full sun') {
    $('#sunny').prop('checked',true);
} else if(dbexp == 'Mixed sun/shade') {
    $('#partly').prop('checked',true);
} else if(dbexp == 'Good shade') {
    $('#shady').prop('checked',true);
}
// References: (up to 6)
var dbrt1 = $('#dbrt1').text();
if (dbrt1 !== '') {
    $('#href1').val(dbrt1);  // there should always be at least one ref...
}
var dbrt2 = $('#dbrt2').text();
if (dbrt2 !== '') {
    $('#href2').val(dbrt2);
}
var dbrt3 = $('#dbrt3').text();
if (dbrt3 !== '') {
    $('#href3').val(dbrt3);
}
var dbrt4 = $('#dbrt4').text();
if (dbrt4 !== '') {
    $('#href4').val(dbrt4);
}
var dbrt5 = $('#dbrt5').text();
if (dbrt5 !== '') {
    $('#href5').val(dbrt5);
}
var dbrt6 = $('#dbrt6').text();
if (dbrt6 !== '') {
    $('#href6').val(dbrt6);
}
// Proposed and Actual GPS Maps & Data:

/*
 * END OF DATA PRELOADING FROM DATABASE
 */

// add placeholder attribute when input text is book/author
$reftags = $('select[id^="href"]');
$reftags.each( function() {
    $(this).change( function() {
        var selId = this.id;
        var elNo = parseInt(selId.substring(4,5));
        var elStr = "ABCDEFGH".substring(elNo-1,elNo);
        var box1 = '#rit' + elStr + '1';
        var box2 = '#rit' + elStr + '2';
        if ($(this).val() === 'b') {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Book Title');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Author Name');
            }
        } else if ($(this).val() !== 'n') {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','URL');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Clickable text');
            }
        } else {
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Enter Text Here');
            } 
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','THIS BOX IGNORED');
            }
        }
    });
});
// Hide or display the part of the form used to enter pictures
$('#nopics').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $('#picopt').css('display','block');
    } else {
        $('#picopt').css('display','none');
    }
});

}); // end of page is loaded...