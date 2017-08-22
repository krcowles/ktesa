$( function () { // when page is loaded...

var database = '../data/database.xml';
var hike = $('#trail').text();
// REQUIRED PHOTO DATA:
var descs = [];
var alblnks = [];
var piclnks = [];
var capts = [];
var aspects = [];
var widths = [];
var ht;
// Translate the month digits:
var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct",
    "Nov","Dec"];
// Import the database
$.ajax({
    dataType: "xml",
    url: database,
    success: function(db) {
        var $rows = $("row",db);
        // put the relevant data into the photo dat arrays:
        $rows.each( function() {
            if ( $(this).find('pgTitle').text() === hike) {
                var $tsvdat = $(this).find('tsv');
                $tsvdat.find('picDat').each( function(i) {
                    if ( $(this).find('hpg').text() === 'Y' ) {
                        //names[i] = $(this).find('title').text();
                        descs[i] = $(this).find('desc').text();
                        alblnks[i] = $(this).find('alblnk').text();
                        piclnks[i] = $(this).find('mid').text();
                        var dateStr = $(this).find('date').text();
                        var year = dateStr.substring(0,4);
                        var month = parseInt(dateStr.substring(5,7));
                        var day = parseInt(dateStr.substring(8,10));
                        capts[i] = months[month] + ' ' + day + ', ' + 
                            year + ': ' + descs[i];
                        ht = parseInt($(this).find('imgHt').text());
                        widths[i] = parseInt($(this).find('imgWd').text());
                        aspects[i] = Math.floor((widths[i])/ht);
                    }
                });  // end of each picDat tag processing
                // are there any 'additional' images (non-photo)?
                var itemcnt = piclnks.length;
                var $addimg1 = $(this).find('aoimg1');
                var $addimg2 = $(this).find('aoimg2');
                if ( $addimg1.text() !== '') {
                    ht = parseInt($addimg1.find('iht').text());
                    widths[itemcnt] = parseInt($addimg1.find('iwd').text());
                    aspects[itemcnt] = Math.floor(widths[itemcnt]/ht);
                    piclnks[itemcnt] = '../images/' + $addimg1.text();
                    capts[itemcnt] = '';
                    itemcnt++;
                }
                if ( $addimg2.text() !== '') {
                    ht = parseInt($addimg2.find('iht').text());
                    widths[itemcnt] = parseInt($addimg2.find('iwd').text());
                    aspects[itemcnt] = Math.floor(widths[itemcnt]/ht);
                    piclnks[itemcnt] = '../images/' + $addimg2.text();
                    capts[itemcnt] = '';
                    itemcnt++;
                }
            }
        });   
    },
    error: function() {
        msg = '<p>Did not succeed in loading the xml database</p>';
        alert(msg);
    }
    });
    // Now form the pic rows from the array data:
    alert("Ready to roll");
});