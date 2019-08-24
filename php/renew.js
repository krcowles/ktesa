$(function() {
  
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

});