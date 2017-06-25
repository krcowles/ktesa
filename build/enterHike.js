$( function () { // when page is loaded...

var msgA;  // generic
var msgB;
var msgC;
var msgD;
var msgE;

/* Preload the GPS Maps & Data Section with the urls for uploaded files;
 * The user will still be required to fill in the remaining fields for these items:
 * note the addition of the 'required' attribute for the uploaded items;
 * Currently, only two items in each section are supported, but this can be
 * easily expanded by adding code here (and additional upload elements in the html)
 */
function preload(targfile,datasect) {
    var ptype = targfile.val();
    if ( ptype.indexOf('html') === -1 ) {
        var fname = '../gpx/' + ptype;
        $(datasect).val(fname);
    } else {
        var fname  = '../maps/' + ptype;
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
/* END OF page-creation type */

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
                $(box2).attr('placeholder',', by Author Name');
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

// PARTIALLY FILLED FORM-SAVING
if (typeof(Storage) !== undefined) {
    var msg; //debug outputs
    var previousSaves;
    var buttonName;

    // FUNCTION FOR RESTORING FORM DATA FROM A STRING
    function stringToForm(formString, unfilledForm) {
        formObject = JSON.parse(formString);
        $('input:text, input:radio, input:checkbox, select, textarea').each(function() {
            if (this.id) {
                id = this.id;
                elem = $(this); 
                if (elem.attr("type") == "checkbox" || elem.attr("type") == "radio" ) {
                    elem.prop("checked", formObject[id]);
                } else {
                    elem.val(formObject[id]);
                }
            }
        });
    }

    // MODAL WINDOW SETUP AND EVENT DEFINITION
    var $savePopup = $('#save-modal').detach();
    $('#saver').on('click', function() {
            modal.open({id: 'saver', content: $savePopup, width:400, height:200});
            /* NOTE: "id" key added as it was thought there would be other modal windows to process,
               and the routine (modal_setup.js) would need to know which type modal to produce */
    });

    // Load previous saves and set event handlers
    previousSaves = parseFloat(window.localStorage.noOfSaves);
    if (previousSaves > 0) {
        $('#unsaver').text('Restore a previously saved form:');
        buttonName = window.localStorage.oldName1;
        msg = '<label id="lbl1"><input id="save1" type="radio" name="restores" /> ' + 
                buttonName + '</label>';
        $('#rest1').prepend(msg);
        msg = '<label id="lblr1"><input id="kill1" type="radio" name="delRestore" /> Remove ' +
                buttonName + '</label>';
        $('#rem1').append(msg);
        $('#save1').on('click', function() {
            var restoredForm = window.localStorage.oldForm1;
            stringToForm(restoredForm, $('#hikeData'));
            window.alert("Form Restored - Please Re-enter files: they cannot be saved");
            $(this).attr('checked',false);
        });
        $('#kill1').on('click', function() {
            var currSaves = parseFloat(window.localStorage.noOfSaves);
            currSaves -= 1;
            if (currSaves === 0) {
                    $('#unsaver').text('Restore a previously saved form: (currently none)');
            }
            window.localStorage.noOfSaves = currSaves;
            window.localStorage.removeItem('oldName1');	
            window.localStorage.removeItem('oldForm1');
            $('#lbl1').remove();
            $('#lblr1').remove();
        });
    }
    if (previousSaves > 1) {
            buttonName = window.localStorage.oldName2;
            msg = '<label id="lbl2"><input id="save2" type="radio" name="restores" /> ' + 
                    buttonName + '</label>';
            $('#rest2').prepend(msg);
            msg = '<label id="lblr2"><input id="kill2" type="radio" name="delRestore" /> Remove ' +
                    buttonName + '</label>';
            $('#rem2').append(msg);
            $('#save2').on('click', function() {
                    var restoredForm = window.localStorage.oldForm2;
                    stringToForm(restoredForm, $('#hikeData'));
                    window.alert("File Resotred");
                    $(this).attr('checked',false);
            });
            $('#kill2').on('click', function() {
                    var savesNow = parseFloat(window.localStorage.noOfSaves);
                    savesNow -= 1;
                    if (savesNow === 0) {
                            $('#unsaver').text('Restore a previously saved form: (currently none)');
                    }
                    window.localStorage.noOfSaves = savesNow;
                    window.localStorage.removeItem('oldName2');
                    window.localStorage.removeItem('oldForm2');
                    $('#lbl2').remove();
                    $('#lblr2').remove();
            });
	}
        /*  DEBUG
        $('#dbugr').on('click', function() {
            var a = window.localStorage.noOfSaves;
            var b = window.localStorage.oldName1;
            var c = window.localStorage.oldForm1;
            var e = window.localStorage.oldName2;
            var f = window.localStorage.oldForm2;
            var dout = '<p>Current storage data:</p> ';
            dout += '<p>Saves: ' + a + '</p><p>Item1: ' + b  + '</p><p>' + c + '</p>';
            dout += '<p>Item2: ' + e + '</p><p>' + f + '</p>';
            $(this).prepend(dout);
        }); 
        $('#cleaner').on('click', function() {
            window.localStorage.noOfSaves = 0;
            $(this).append(window.localStorage.noOfSaves);
            window.localStorage.removeItem('oldName1');
            window.localStorage.removeItem('oldForm1');
            window.localStorage.removeItem('oldFile1');
            window.localStorage.removeItem('oldName2');
            window.localStorage.removeItem('oldForm2');
            window.localStorage.removeItem('oldFile2');
        });
        */

} else {
    window.alert('Sorry - no local web storage: cannot save form data for later use');   
}  // END OF FORM-SAVING

}); // end of page is loaded...