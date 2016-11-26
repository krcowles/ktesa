$( function () { // when page is loaded...

// FUNCTION FOR RESTORING FORM DATA FROM A STRING
function stringToForm(formString, unfilledForm) {
	formObject = JSON.parse(formString);
	unfilledForm.find("input, select, textarea").each(function() {
		if (this.id) {
			id = this.id;
			elem = $(this); 
			if (elem.attr("type") == "checkbox" || elem.attr("type") == "radio" ) {
				elem.attr("checked", formObject[id]);
			} else {
				elem.val(formObject[id]);
			}
		}
	});
}

var msg; //debug outputs
var previousSaves;
var buttonName, r1, r2;

/*
if (typeof(Storage) != undefined) {
 
} else {
  	window.alert('Sorry - no local web storage: cannot save form data for later use');   
}
*/
	//stringToForm(formString, $("#myForm"));

// MODAL WINDOW SETUP AND EVENT DEFINITION
var $savePopup = $('#save-modal').detach();
$('#saver').on('click', function() {
	modal.open({id: 'saver', content: $savePopup, width:400, height:300});
});

previousSaves = parseFloat(window.localStorage.noOfSaves);
//msg = '<p>Entering routine with ' + previousSaves + ' previously saved forms</p>';
//$('#unsaver').append(msg);

if (previousSaves > 0) {
	$('#unsaver').text('Restore a previously saved form:');
	buttonName = window.localStorage.oldName1;
	r1 = '<p id="restore1"><input type="radio" name="restores" value="r1" />' + buttonName +
		' [oldest]<br /></p>';
	$('#unsaver').append(r1);
	$('#restore1').on('click', function() {
 		var restoredForm = window.localStorage.oldForm1	;
 		stringToForm(restoredForm, $('#theForm'));
 		previousSaves -= 1;
 		if (previousSaves === 0) {
 			$('#unsaver').text('Restore a previously saved form: (currently none)');
 		}
 		window.localStorage.noOfSaves = previousSaves;
 		window.localStorage.removeItem('oldName1');	
 		window.localStorage.removeItem('oldForm1');
 		$(this).remove();
	});
}
if (previousSaves > 1) {
	buttonName = window.localStorage.oldName2;
	r2 = '<p id="restore2"><input id="restore2" type="radio" name="restores" value="r2" />' +
		buttonName + '</p>';
	$('#unsaver').append(r2);
	$('#restore2').on('click', function() {
		var restoredForm = window.localStorage.oldForm2;
		stringToForm(restoredForm, $('#theForm'));
		previousSaves -= 1;
		if (previousSaves === 0) {
 			$('#unsaver').text('Restore a previously saved form: (currently none)');
 		}
		window.localStorage.noOfSaves = previousSaves;
		window.localStorage.removeItem('oldName2');
		window.localStorage.removeItem('oldForm2');
		$(this).remove();
	});
}

//msg = '<p>No of previous saves: ' + previousSaves + '</p>';
//$('#unsaver').prepend(msg);
//window.localStorage.noOfSaves = 0;
//var msg = '<p>Current number of saved items: ' + window.localStorage.noOfSaves + '</p>';
//$('#unsaver').after(msg);


//window.localStorage.removeItem('noOfSaves');




}); // end of page is loaded...