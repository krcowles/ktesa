<?php
require_once "../mysql/setenv.php";
$usrname = trim($_REQUEST['nmhid']);
$usrpass = trim($_REQUEST['nmpass']);
$usr_req = sprintf("SELECT username,passwd FROM USERS WHERE username = '%s';",
    mysqli_real_escape_string($link,$usrname));
$usr_srch = mysqli_query($link,$usr_req);
if (mysqli_num_rows($usr_srch) == 1) {  # located user
    $user_dat = mysqli_fetch_assoc($usr_srch);
    if (password_verify($usrpass,$user_dat['passwd'])) {  # user data correct
        echo "LOCATED";
    } else {  # user exists, but password doesn't match:
        echo "BADPASSWD" . $usrpass . ";" . $user_dat['passwd'];
    }
} else {  # not in USER table
    echo "FAIL";
}
?>