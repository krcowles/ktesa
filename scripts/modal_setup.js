// modal object definition
var modal = (function() {
    // Local/private to "modal"
    var $window = $(window);
    var $modal = $('<div class="modal" style="background-color:white;"/>'); 
                    // NOTE: "/>" is a means of self-closing the div
    var $content = $('<div class="modal-content"/>');
    var $close = $('<button role="button" class="modal-close">Close</button>');

    $modal.append($content);
    $modal.append($close);
    $close.on('click', function(e) {
        e.preventDefault();
        modal.close();
    });
	
    /* FUNCTION TO CONVERT FORM DATA INTO STRING FOR SAVING IN LOCAL STORAGE */
    function form2String(filledForm) {
        formObject = new Object
        $savers = $('input:text, input:radio, input:checkbox, select, textarea');
        $savers.each(function() {
            if (this.id) {
                elem = $(this);
                if (elem.attr("type") == 'checkbox' || elem.attr("type") == 'radio') {
                    formObject[this.id] = elem.prop("checked");
                } else {
                    formObject[this.id] = elem.val();
                }
            }
        });
        formString = JSON.stringify(formObject);
        return formString;
    }
    /* FUNCTION TO CONVERT STRING BACK INTO FORM DATA */
    function string2Form(formString, unfilledForm) {
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

    // THE WORKHORSE TO SAVE THE FORM
    function formSaver() {
        var msg;
        var prevName;
        var curDiv;
        var closeModal = true;
        var saveName = $('#savetxt').val(); // name of saved data...
        if (saveName === '') {
            window.alert("Please enter an identifier in text box!");
            $(this).attr('checked',false);
        } else {
            // there is a name to use for the save:
            if (currentSaves >= 2) {
                msg = 'ALREADY SAVED 2: Nothing new will be saved';
                window.alert(msg);
                modal.close();
            } 
            // therefore, current no of saves is 0 or 1:
            formString = form2String($('#hikeData')); // saved form data
            var currentSaves = parseFloat(window.localStorage.noOfSaves);
            /* There is an existing correspondence between divs & saved data:
             * <rest1> is associated w/localStorage.oldName1,
             * <rest2> is associated w/localStorage.oldName2
             */
            if (currentSaves === 1) {
                // Already have one stored: must be EITHER rest1 or rest2
                curDiv = $('#rest1').text();
                if (curDiv === '')	{
                    // rest1 is empty, so the -existing- button is in rest2
                    prevName = window.localStorage.oldName2;
                } else { // existing button is in rest1
                    prevName = window.localStorage.oldName1;
                }
                if (prevName == saveName) {
                    msg = 'Choose a different identifier - this was previously saved';
                    window.alert(msg);
                    $('#edit').prop('checked',false);
                    $('#closeit').prop('checked',false);
                    closeModal = false;
                } else {
                    if ( curDiv !== '' ) {  // current button in rest1, so add rest2
                        window.localStorage.oldName2 = saveName;
                        window.localStorage.oldForm2 = formString;
                        window.localStorage.noOfSaves = 2;
                        msg = '<label id="lbl2"><input id="save2" type="radio" name="restores" /> ' + 
                                saveName + '</label>';
                        $('#rest2').prepend(msg);
                        msg = '<label id="lblr2"><input id="kill2" type="radio" name="delRestore" /> Remove ' +
                                saveName + '</label>';
                        $('#rem2').append(msg);
                        $('#save2').on('click', function() {
                            var restoredForm = window.localStorage.oldForm2;
                            string2Form(restoredForm, $('#hikeData'));
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
                        modal.close();
                        return;
                    }  // end of lbl2 creation
                }  // end of DUPLICATE NAME test					
            } // end of no of saves = 1
            /*
             * NOTE: the above case has ONLY supplied a button in the second row
             *  when first row is already consumed. Otherwise, it has done no
             *  insertions.
             */
            if (closeModal === true) {
                // Either no of saves = 0, or an existing save is in rest2
                currentSaves++;  // either it moved from 0 to 1 if prev saves = 0, or
                                 // it moved from 1 to 2 if prev saves = 1
                window.localStorage.oldName1 = saveName;
                window.localStorage.oldForm1 = formString;
                window.localStorage.noOfSaves = currentSaves;
                $('#unsaver').text('Restore a previously saved form: ');
                msg = '<label id="lbl1"><input id="save1" type="radio" name="restores" /> ' + 
                        saveName + '</label>';
                $('#rest1').prepend(msg);
                msg = '<label id="lblr1"><input id="kill1" type="radio" name="delRestore" /> Remove ' +
                        saveName + '</label>';
                $('#rem1').append(msg);
                $('#save1').on('click', function() {
                    var restoredForm = window.localStorage.oldForm1;
                    string2Form(restoredForm, $('#hikeData'));
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
                modal.close();
            }  // end of create lbl1 and close
        }  // end of testing contents of 'saveName'
    } // end of function formSaver()

    // public
    return {   // returns object methods:
        center: function() {
            var top = Math.max($window.height() - $modal.outerHeight(), 0) / 2;
            var left = Math.max($window.width() - $modal.outerWidth(), 0) / 2;
            $modal.css({
                    top: top + $window.scrollTop(),
                    left: left + $window.scrollLeft()
            });
        },
        open: function(settings) {
            $content.empty().append(settings.content.html());
            $modal.css({
                width: settings.width || auto,
                height: settings.height || auto,
                border: '3px solid',
                padding: '8px'
            }).appendTo('body');
            modal.center();
            $(window).on('resize', modal.center);
            if (settings.id === 'saver') {
                $close.detach();
                $('#edit').on('click', formSaver);
                $('#dontsave').on('click', function() {
                    modal.close();
                });
            }
        },
        close: function() {
            $content.empty();
            $modal.detach();
            $(window).off('resize', modal.center);
        }
    };
}());  // modal is an IIFE