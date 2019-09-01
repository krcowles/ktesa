$(function () { // when page is loaded...

$('#form').validate({
    rules: {
        password: {
            minlength: 8,
        },
        confirm_password: {
            minlength: 8,
            equalTo: "#passwd"
        }
    },
    messages: {
        password: {
            minlength: "Passwords must be at least 8 characters"
        },
        confirm_password: {
            minlength: "Passwords must be at least 8 characters",
            equalTo: "Password does not match - please retry"
        }
    }
}); // end validate form
function validateEmail(subjectEmail){      
   var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
   return emailPattern.test(subjectEmail); 
 } 
 $('#email').on('change', function() {
     if (!validateEmail( $(this).val() )) {
         $(this).val("");
         alert("This does not appear to be a valid email: please re-enter");
     }
 });
 $('#form').on('submit', function(evt) {
     evt.preventDefault();
    // below is necessary as window.close precludes html validation
    if ($('input[name=firstname]').val() == ''
        || $('input[name=lastname]').val() == ''
        || $('input[name=username]').val() == ''
        || $('input[name=password]').val() == ''
        || $('#confirm_password').val() == ''
        || $('#email').val() == '') {
            alert("All required inputs must be supplied");
            return;
    }
    var usr = $('input[name=username]').val();
    var ajaxData = new FormData();
    ajaxData.append('firstname', $('input[name=firstname]').val());
    ajaxData.append('lastname',  $('input[name=lastname]').val());
    ajaxData.append('username',  usr);
    ajaxData.append('password',  $('input[name=password]').val());
    ajaxData.append('email',     $('input[name=email]').val());
    ajaxData.append('facebook',  $('input[name=facebook]').val());
    ajaxData.append('twitter',   $('input[name=twitter]').val());
    ajaxData.append('bio',       $('textarea[name=bio]').val());
    ajaxData.append('submitter',    'create');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'create_user.php');
    xhr.onload = function() {
        if (this.status !== 200) {
            if (this.response !== 'DONE') {
                alert("The registration did not occur\n\n" +
                    "The following unexpected result occurred:\n" +
                    "Server returned status " + this.status);
            }
        }
        window.open('../index.html?usr=' + usr, '_self');
    }
    xhr.onerror = function() {
        alert("The request failed: registration did not occur\n" +
            "Contact the site master or try again.");
    }
    xhr.send(ajaxData);
});

});  // end page loaded

// jQuery method to submit form asynch then close this window
//$.post($('#registration').attr('action'),
//$('#registration').serializeArray());
