$( function() {  // wait until document is loaded...

$('#auxfrm').submit( function() {
	var keyval = this.regkey.value;
	if (keyval == '1948') {
		$('#experts').css('display','block');
		$('#editor').on('click', function() {
			var editUrl = 'build/hikeEditor.php';
			window.open(editUrl, target="_blank");
		});
		$('#indxpg').on('click', function() {
			var indxurl = 'build/indexEditor.php';
			window.open(indxurl, target="_blank");
		});
		$('#creator').on('click', function() {
			var createUrl = 'build/newHike.php';
			window.open(createUrl, target="_blank");
		});
                $('#admin').on('click', function() {
                    var adminUrl = 'admin/admintools.php';
                    window.open(adminUrl,target="_blank");
                });
		$('#hide').on('click', function() {
			$("input[type='password']").val('');
			$('#experts').css('display','none');
		});
	} else {
		window.alert("Key not recognized");
	}
	return false;
});
// IF KEY SUCCESSFUL:


$('#turnon').on('click', function() {
	$('#more').css('display','block');
	$(this).css('display','none');
});
$('#turnoff').on('click', function() {
	$('#more').css('display','none');
	$('#turnon').css('display','block');
});

}); // end of page-loading wait statement