$(function () { // when page is loaded...

var cookies = navigator.cookieEnabled ? true : false;
if (!cookies) {
    var addendum = 'Cookies are not enabled on your machine, therefore ' +
        'you will also be required to enter your user password';
    $('#cookies').append(addendum);
    $('#cookies').after('<br />');
} else {
    var d = new Date();
    d.setTime(d.getTime() + (365*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = "nmh_id=" + $('#usrid').text() + ";" + expires + ";path=/";
}

});


