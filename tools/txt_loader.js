var xhr = new XMLHttpRequest();

xhr.onload = function() {
	if (xhr.status === 200 ) {
		document.getElementById('fritz').innerHTML = '<p>SUCCESS</p>';
	}
	if (xhr.status === 404 ) {
		document.getElementById('fritz').innerHtml = '<p>oops 404</>';
	}
	if (xhr.status === 304 ) {
		document.getElementById('fritz').innerHtml = '<p>dont know 304</>';
	}
	if (xhr.status === 500 ) {
		document.getElementById('fritz').innerHtml = '<p>FAILED</p>';
	}
};

xhr.open('GET', 'diablo.tsv', true);
xhr.send(null);
