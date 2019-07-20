<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New User Registration</title>
    <meta charset="utf-8" />
    <meta name="description" content="New user sign-up" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="Registration.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery.validate.min.js"></script>
    <script src="../scripts/jquery.validate.password.js"></script>
    <script type="text/javascript">
        $(document).ready( function() {
            $('#registration').validate( {
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
        });
    </script>
</head>

<body>
<?php require "../pages/pageTop.html"; ?>
<p id="trail">New User Registration</p>

<div id="container">
<h2>
    Welcome to the New Mexico Hikes Registration Page!
</h2>
<p>Please fill out the form below, making sure to supply at least the "required"
    information.</p>
<form id="registration" target="_blank" action="create_user.php" method="POST">
    <fieldset>
        <legend>Required Information</legend>
        <label for="firstname">First Name: [Max 20 Characters]</label>
        <input type="text" name="firstname" size="30" 
               class="required" maxlength="20" value="" /><br />
        <label for="lastname">Last Name: [Max 30 Characters]</label>
        <input type="text" name="lastname" size="40" 
               class="required" maxlength="30" value="" /><br />
        <label for="usr">Supply a User Name: [Max 32 Characters]</label>
        <input type="text" name="usr" size="30" 
               class="required" maxlength="32" value="" /><br /><br />
        <p id="pnote">Note: Passwords must be at least 8 characters long and 
            should contain a mix of characters (alpha, numeric, special).</p>
        <label for="password">Enter a password: </label>
        <input id="passwd" type="password" name="password" size="20"
               class="required password" />&nbsp;
        <div class="password-meter">
            <div class="password-meter-message"></div>
            <div class="password-meter-bg">
                <div class="password-meter-bar"></div>
            </div>
            <br />
            <div id ="confirm">
            <label for="confirm_password">Confirm password: </label>
            <input id="confirm_password" type="password" 
                   name="confirm_password" size="20" class="required" />
            </div>
        </div><br />
        <label for="email">email address: </label>
        <input id="umail" type="text" name="email" size="40" 
               class="required email" />
    </fieldset>
    <fieldset>
        <legend>Optional</legend>
        <label for="facebook">Facebook URL: [Max 100 Characters]</label>
        <input type="text" name="facebook" size="50" 
            maxlength="100" class="url" /><br />
        <label for="twitter">Twitter Handle: [Max 20 Characters]</label>
        <input type="text" name="twitter" size="20" maxlength="20" /><br />
        <label for="bio">Anything you would like us to know about you? [Max 500 Characters]</label><br />
        <textarea name="bio" cols="80" rows="10" maxlength="500"></textarea>
    </fieldset><br />
    <input id="setuser" type="submit" value="Submit My Info" /><br />
    <input type="reset" value="Clear all fields" /><br /><br />
</form>
</div>   <!-- end of container -->
<script src="user_val.js"></script>
</body>
</html>