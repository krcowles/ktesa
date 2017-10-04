$( function() {  // wait until document is loaded...

var init = $('#regusrs').css('display');
alert("INIT: " + init);
$('#auxfrm').submit( function() {
    var keyval = this.regkey.value;
    if (keyval == '1948') {
        $('#regusrs').css('display','block');
        $('#masters').css('display','none');
        $('#creator').on('click', function() {
            var createUrl = 'build/newHike.php';
            window.open(createUrl, target="_blank");
        });
        $('#editor').on('click', function() {
            var editUrl = 'build/hikeEditor.php';
            window.open(editUrl, target="_blank");
        });
        $('.hide').on('click', function() {
            $("input[type='password']").val('');
            $('#regusrs').css('display','none');
            
        });
    } else if (keyval == '000ktesa9') {
        $('#regusrs').css('display','none');
        $('#masters').css('display','block');
        $('#indxpg').on('click', function() {
            var indxurl = 'build/indexEditor.php';
            window.open(indxurl, target="_blank");
        });  
        $('#admin').on('click', function() {
            var admintools = 'admin/admintools.php';
            window.open(admintools,"_blank");
        });
        $('.hide').on('click', function() {
            $("input[type='password']").val('');
            $('#masters').css('display','none');
        });
    } else {
        $("input[type='password']").val('');
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