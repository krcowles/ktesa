$( function () { // anonymous function waits until page is loaded
// CONVENTION: variable names preceded by '$' represent jQuery node lists

var msg;  // for printing info during debug

// Form the table header & tail for each of the regions
var tblHtml = '<table class="rsortable">';
tblHtml += ' <colgroup>';
tblHtml += '  <col style="width:180px">';
tblHtml += '  <col style="width:170px">';
tblHtml += '  <col style="width:70px">';
tblHtml += '  <col style="width:78px">';
tblHtml += '  <col style="width:100px">';
tblHtml += '  <col style="width:100px">';
tblHtml += '  <col style="width:70px">';
tblHtml += '  <col style="width:70px">';
tblHtml += ' <colgroup>';
tblHtml += ' <thead>';
tblHtml += '  <tr>';
tblHtml += '   <th class="hdr_row" data-sort="std">General Location</th>';
tblHtml += '   <th class="hdr_row" data-sort="std">Hike/Trail Name</th>';
tblHtml += '   <th class="hdr_row">Web Pg</th>';
tblHtml += '   <th class="hdr_row" data-sort="lan">Length</th>';
tblHtml += '   <th class="hdr_row" data-sort="lan">Elev Chg</th>';
tblHtml += '   <th class="hdr_row" data-sort="std">Difficulty</th>';
tblHtml += '   <th class="hdr_row">Exposure</th>';
tblHtml += '   <th class="hdr_row">Flickr</th>';
tblHtml += '  </tr>';
tblHtml += ' </thead>';
tblHtml += '<tbody>';

var endTbl = '</tbody>';
endTbl += '</table>';

// create node list for each region's row data; that node list is regRows[region#]
var $regRows = new Array();
var regIndx;
for ( var j=0; j<7; j++ ) {
	regIndx = '.reg' + (j + 1);
	$regRows[j] = $(regIndx);
}
// get count of hikes for each region
var regCnt = new Array();
var totalHikes = 0;
for ( var k=0; k<7; k++ ) {
	regCnt[k] = $regRows[k].length;
	totalHikes += regCnt[k];
}
var i = 0;
// populate with correct hike counts
var $regHikes = $('.hikeNo');
$regHikes.each( function() {
	var hLoc = this.textContent;
	var hikeIndx = hLoc.indexOf(':') + 2;
	var curCnt = hLoc.substring(0,hikeIndx) + regCnt[i];
	this.textContent = curCnt;
	i++;
})

// get the count of indexed hikes documented to date
var indxTxt;
var bracket;
var slash;
var indxCnt;

$('tr.indxd').children().each( function() {
	indxTxt = this.textContent;
	bracket = indxTxt.indexOf('[') + 1;
	if ( bracket > 0 ) {
		slash = indxTxt.indexOf('/');
		indxCnt = indxTxt.substring(bracket,slash);
		totalHikes += parseFloat(indxCnt);
	}
});
msg = totalHikes + ' ]';
$('#hikeCnt').append(msg);

window.onload = function() { // AFTER window loads, it is safe to use $.offset()
	// get offset from everything prior to the map and centering of the map
	var nwLoc = $('#nmnw').offset();
	var topLoc = parseInt(nwLoc.top);
	var leftLoc = parseInt(nwLoc.left);

	// adjust cities & text using absolute positioning for centered map
	$('.tloc').each( function() {
		var toff = $(this).css('top');
		var loff = $(this).css('left');
		var tindx = toff.indexOf('px');
		var lindx = loff.indexOf('px');
		var oldTop = parseFloat(toff.substring(0,tindx));
		var oldLeft = parseFloat(loff.substring(0,lindx));
		var newTop = oldTop + topLoc - 8; // 8 is page margin
		var newLeft = oldLeft + 255; //255 : map centering (960body - 450map)/2
		var topAdj = newTop + 'px';
		var leftAdj = newLeft + 'px';
		$(this).css('top',topAdj);
		$(this).css('left',leftAdj)
	});
}

// Create the tables for each specific region
var regRowTblDat = new Array();
var regRowId;
$tblArea = $('.tblDivs');
// for each tblDiv:
for ( var m=0; m<7; m++ ) {
	if ( regCnt[m] == 0 ) {
		var regRowDat = 'No Hikes Available In This Region (yet!)';
		$tblArea[m].textContent = regRowDat;
	}  else {
		// collect whatever row data exists
		regRowTblDat[m] = tblHtml;
		$regRows[m].each( function() {
			// get class info for this row
			var classInfo = $(this).attr('class');
			regRowTblDat[m] += ' <tr class="' + classInfo + '">';
			regRowTblDat[m] += $(this).html() + ' </tr>';
		});
		regRowTblDat[m] += endTbl;
		$tblArea[m].innerHTML = regRowTblDat[m];
	}

}


// to see the whole table...
$('#getWholeTbl').on('click', function() {
	$tblArea.css('display','none');
	$('.dressing').css('display','block');
	$('#wholeTbl').css('display','block');
});

var compare = {
	std: function(a,b) {	// standard sorting - literal
		if ( a < b ) {
			return -1;
		} else {
			return a > b ? 1 : 0;
		}
	},
	lan: function(a,b) {    // "Like A Number": extract numeric portion for sort
		// commas allowed in numbers, so;
		var indx = a.indexOf(',');
		if ( indx < 0 ) {
			a = parseFloat(a);
		} else {
			noPart1 = parseFloat(a);
			msg = a.substring(indx + 1, indx + 4);
			noPart2 = msg.valueOf();
			a = noPart1 + noPart2;
		}
		indx = b.indexOf(',');
		if ( indx < 0 ) {
			b = parseFloat(b);
		} else {
			noPart1 = parseFloat(b);
			msg = b.substring(indx + 1, indx + 4);
			noPart2 = msg.valueOf();
			b = noPart1 + noPart2;
		}
		return a - b;
	} 
};  // end of object declaration
$('.rsortable').each(function() {
	var $table = $(this); 
	var $tbody = $table.find('tbody');
	var $controls = $table.find('th'); // store all headers
	var trows = $tbody.find('tr').toArray();  // array of rows
	
	$controls.on('click', function() {
		var $header = $(this);
		var order = $header.data('sort');
		var column;
		
		// IF defined for selected column, toggle ascending/descending class
		if ( $header.is('.ascending') || $header.is('.descending') ) {
			$header.toggleClass('ascending descending');
			$tbody.append(trows.reverse());
		} else {
		// NOT DEFINED - add 'ascending' to current; remove remaining headers' classes
			$header.addClass('ascending');
			$header.siblings().removeClass('ascending descending');
			if ( compare.hasOwnProperty(order) ) {
				column = $controls.index(this);  // index into the row array's data
				trows.sort(function(a,b) {
					a = $(a).find('td').eq(column).text();
					b = $(b).find('td').eq(column).text();
					return compare[order](a,b);
				});
				$tbody.append(trows);
			} // end if-compare
		} // end else	
	}); // end on.click
}); // end '.rsortable each' loop


// if click occurs on the region block
$('.region').on('click', function(sel) {
	$('.dressing').css('display','block');
	var regionSel = sel.target;
	var selId = regionSel.id;
	$tblArea.css('display','none');
	switch (selId) {
		case 'nmnw':
			$('#region1').css('display','block');
			break;
		case 'nmne':
			$('#region2').css('display','block');
			break;
		case 'nmcw':
			$('#region3').css('display','block');
			break;
		case 'nmc':
			$('#region4').css('display','block');
			break;
		case 'nmce':
			$('#region5').css('display','block');
			break;
		case 'nmsw':
			$('#region6').css('display','block');
			break;
		case 'nmse':
			$('#region7').css('display','block');
			break;
		case 'nmbtm1':
			$('#region6').css('display','block');
			break;
		case 'nmbtm2':
			$('#region6').css('display','block');
			break;
	}
});

// if the click occurs on the display of number of hikes in the region
$('.hikeNo').on('click', function(num) {
	$('.dressing').css('display','block');
	var selNo = num.target;
	var hNo = selNo.id;
	$tblArea.css('display','none');
	switch (hNo) {
		case 'hikes1':
			$('#region1').css('display','block');
			break;
		case 'hikes2':
			$('#region2').css('display','block');
			break;
		case 'hikes3':
			$('#region3').css('display','block');
			break;
		case 'hikes4':
			$('#region4').css('display','block');
			break;
		case 'hikes5':
			$('#region5').css('display','block');
			break;
		case 'hikes6':
			$('#region6').css('display','block');
			break;
		case 'hikes7':
			$('#region7').css('display','block');
			break;
	}
});

// if the click occurs on the text naming the city
$('.ctxt').on('click', function(place) {
	$('.dressing').css('display','block');
	var cLoc = place.target;
	var cityTxt = cLoc.id;
	$tblArea.css('display','none');
	switch (cityTxt) {
		case 'abqtxt':
			$('#region4').css('display','block');
			break;
		case 'soctxt':
			$('#region4').css('display','block');
			break;
		case 'sftxt':
			$('#region2').css('display','block');
			break;
		case 'galtxt':
			$('#region3').css('display','block');
			break;
		case 'scitytxt':
			$('#region6').css('display','block');
			break;
		case 'lctxt':
			$('#region6').css('display','block');
			break;
		case 'rostxt':
			$('#region7').css('display','block');
			break;
		case 'srosatxt':
			$('#region5').css('display','block');
			break;
		case 'rattxt':
			$('#region2').css('display','block');
			break;
		case 'farmtxt':
			$('#region1').css('display','block');
			break;
	}	
});

$('.cities').on('click', function(cit) {
	$('.dressing').css('display','block');
	var cCirc = cit.target;
	var city = cCirc.id;
	$tblArea.css('display','none');
	switch (city) {
		case 'abq':
			$('#region4').css('display','block');
			break;
		case 'socor':
			$('#region4').css('display','block');
			break;
		case 'santafe':
			$('#region2').css('display','block');
			break;
		case 'gallup':
			$('#region3').css('display','block');
			break;
		case 'scity':
			$('#region6').css('display','block');
			break;
		case 'lascruces':
			$('#region6').css('display','block');
			break;
		case 'ros':
			$('#region7').css('display','block');
			break;
		case 'srosa':
			$('#region5').css('display','block');
			break;
		case 'raton':
			$('#region2').css('display','block');
			break;
		case 'farm':
			$('#region1').css('display','block');
			break;
	}	
});

// as needed... (but not currently used)
function getBrowserInfo()
{
	var ua = navigator.userAgent, tem,
	M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
	if(/trident/i.test(M[1]))
	{
		tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
		return 'IE '+(tem[1] || '');
	}
	if(M[1]=== 'Chrome')
	{
		tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
		if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
	}
	M = M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
	if((tem= ua.match(/version\/(\d+)/i))!= null) 
		M.splice(1, 1, tem[1]);
	return M.join(' ');
}
var browserInfo = getBrowserInfo();

}); // end function for page loading