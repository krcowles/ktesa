$( function() {  // wait until document is loaded...

var seconds = 12;
var msg = seconds + " Seconds";
$('#countdown').text(msg);

(function countdown() {
	setTimeout( function() {
		if (seconds === 0) { // go to new page 
			window.open("https://new-mexico-hikes.000webhostapp.com/index.html");
		} else {
			seconds--;
			msg = seconds + " Seconds";
			$('#countdown').text(msg)
			countdown();
		}
	}, 1000 );
	
}());


}); // end of page-loading wait statement