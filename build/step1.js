$( function () { // when page is loaded...

if (typeof(Storage) != undefined) {
	var msg; //debug outputs
	var previousSaves;
	var buttonName, r1, r2;
	
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

	// MODAL WINDOW SETUP AND EVENT DEFINITION
	var $savePopup = $('#save-modal').detach();
	$('#saver').on('click', function() {
		modal.open({id: 'saver', content: $savePopup, width:400, height:200});
	});
	// DEBUG STUFF...
	$('#dbugr').on('click', function() {
		var a = window.localStorage.noOfSaves;
		var b = window.localStorage.oldName1;
		var c = window.localStorage.oldForm1;
		var d = window.localStorage.oldName2;
		var e = window.localStorage.oldForm2;
		var dout = '<p>Current storage data:</p> ';
		dout += '<p>Saves: ' + a + '</p><p> Item1: ' + b + ' / ' + c + '</p>';
		dout += '<p>Item2: ' + d + ' / ' + e + '</p>';
		$(this).prepend(dout);
	});
	$('#cleaner').on('click', function() {
		window.localStorage.noOfSaves = 0;
		window.localStorage.removeItem('oldName1');
		window.localStorage.removeItem('oldForm1');
		window.localStorage.removeItem('oldName2');
		window.localStorage.removeItem('oldForm2');
	});

	// Load previous saves and set event handlers
	previousSaves = parseFloat(window.localStorage.noOfSaves);
	if (previousSaves > 0) {
		$('#unsaver').text('Restore a previously saved form:');
		buttonName = window.localStorage.oldName1;
		r1 = '<p id="restore1" style="margin-top:3px;margin-bottom:0px;"><input type="radio" name="restores" value="r1" />' + buttonName +
			' [oldest]<br /></p>';
		$('#unsaver').append(r1);
		$('#restore1').on('click', function() {
			var restoredForm = window.localStorage.oldForm1;
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
		r2 = '<p id="restore2" style="margin-top:-1px;"><input id="restore2" type="radio" name="restores" value="r2" />' +
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
} else {
  	window.alert('Sorry - no local web storage: cannot save form data for later use');   
}

}); // end of page is loaded...