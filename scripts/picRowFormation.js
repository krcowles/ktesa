$( function () { // when page is loaded...

// Ajax defnis
var database = '../data/database.xml';
var hike = $('#trail').text();
// REQUIRED PHOTO DATA:
var descs = [];
var alblnks = [];
var piclnks = [];
var capts = [];
var aspects = [];
var widths = [];

// Translate the month digits:
var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct",
    "Nov","Dec"];

// NOMINAL SETTINGS:
var maxRowHt = 260;	
var rowWidth = 950;

// Import the database & process picture rows
$.ajax({
    dataType: "xml",
    url: database,
    success: function(db) {
        var ht;
        var picCnt;
        var itemcnt;
        var $rows = $("row",db);
        // put the relevant data into the photo dat arrays:
        $rows.each( function() {
            if ( $(this).find('pgTitle').text() === hike) {
                var $tsvdat = $(this).find('tsv');
                var i = 0;
                $tsvdat.find('picDat').each( function() {
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
                        aspects[i] = widths[i]/ht;
                        i++;
                    }
                });  // end of each picDat tag processing
                // are there any 'additional' images (non-photo)?
                itemcnt = piclnks.length;
                picCnt = itemcnt;
                //alert("NOW");
                var $addimg1 = $(this).find('aoimg1');
                var $addimg2 = $(this).find('aoimg2');
                if ( $addimg1.text() !== '') {
                    ht = parseInt($addimg1.find('iht').text());
                    widths[itemcnt] = parseInt($addimg1.find('iwd').text());
                    aspects[itemcnt] = widths[itemcnt]/ht;
                    piclnks[itemcnt] = '../images/' + $addimg1.text();
                    capts[itemcnt] = '';
                    itemcnt++;
                }
                if ( $addimg2.text() !== '') {
                    ht = parseInt($addimg2.find('iht').text());
                    widths[itemcnt] = parseInt($addimg2.find('iwd').text());
                    aspects[itemcnt] = widths[itemcnt]/ht;
                    piclnks[itemcnt] = '../images/' + $addimg2.text();
                    capts[itemcnt] = '';
                    itemcnt++;
                }
            }  // end if this is the hike
        });  // end of search each row
        
        /*
         * Begin the process by starting with all images set to the same
         * height [maxRowHt] for initial placement in a row:
         */ 
        var widthAtMax = [];
        for (var j=0; j<itemcnt; j++) {
            widthAtMax[j] = Math.floor(maxRowHt * aspects[j]);
        }
        var rowNo = 0;
        var currWidth = 0;
        var scale;
        var rowHt;
        var imgStartNo = 0;
        var rowHtml = '';
        var styling;
        var iwidth;
        var imgRows = [];   // holds each row's html
        var noProcessed = 0;
        var startIndx = 0;
        var rowComplete = false; // ???????????? where to put this
        var itype = [];
        // row width calculation will include 1px between each image
        var leftMostImg = true;
        // calculation loop: place pix in row till exceeds rowWdith, then fit
        for (var n=0; n<itemcnt; n++) {
           if (leftMostImg === false) {
                currWidth += 1;
            }
            currWidth += widthAtMax[n]; // place next pic in row
            leftMostImg = false;
            if (n < picCnt) {
                itype[n] = "photo";  // popups need to know if captioned
            } else {
                itype[n] = "image";  // no popup
            }
            // when currWidth exceeds rowWidth, then force fit to rowWidth
            if (currWidth >= rowWidth) { 
                // this row is now filled
                rowComplete = true;
                scale = rowWidth/currWidth;
                rowHt = Math.floor(scale * maxRowHt);
                rowHtml += '<div id="row' + rowNo + 
                    '" class="ImgRow">' + "\n";
                for (var k=imgStartNo; k<n+1; k++) { // "n' was the last img added
                    // for each pic in this row, resize to fit
                    if (k === imgStartNo) {
                        styling = ''; // don't add left-margin to leftmost image
                    } else {
                        styling = 'margin-left:1px;';
                    }
                    if (itype[k] === "photo") {
                        iwidth = Math.floor(scale * widthAtMax[k]);
                        rowHtml += '<img id="pic' + k + '" style="' +
                            styling + '" width="' + iwidth + '" height="' +
                            rowHt + '" src="' + piclnks[k] + '" alt="' +
                            capts[k] + '" />' + "\n";
                    } else {
                        iwidth = Math.floor(scale * widthAtMax[k]);
                        rowHtml += '<img style="' + styling + '" width="' +
                            iwidth + '" height="' + rowHt + '" src="' +
                            piclnks[k] + '" alt="Additional non-captioned image" />' + "\n";
                    }
                }  // end of for each image -> fit
                imgStartNo = n+1;
                rowNo++;
                rowHtml += "</div>\n";
                leftMostImg = true;
                currWidth = 0;
            }
            if ( (n === itemcnt-1) && !rowComplete ) {
                // last row will not be filled, so no scaling
                rowHtml += '<div id="row' + rowNo + 
                    '" class="ImgRow">' + "\n";
                for (var l=imgStartNo; l< n+1; l++) {
                    if (l === imgStartNo) {
                        styling = ''; // don't add left-margin to leftmost image
                    } else {
                        styling = 'margin-left:1px;';
                    }
                    if (itype[l] === 'photo') {
                        rowHtml += '<img id="pic' + l + '" style="' +
                            styling + '" width="' + widthAtMax[l] + '" height="' +
                            maxRowHt + '" src="' + piclnks[l] + '" alt="' +
                            capts[l] + '" />' + "\n";
                    } else {
                        rowHtml += '<img style="' + styling + '" width="' +
                            widthAtMax[l] + '" height="' + maxRowHt + 
                            '" src="' + piclnks[l] + 
                            '" alt="Additional non-captioned image" />' + "\n";
                    }
                }
                rowHtml += "</div>\n";
            }
            rowComplete = false;
        } // end of processing images to fit in rows
        $('#imgArea').html(rowHtml);
        $('img[id^="pic"]').each( function(indx) {
            $(this).css('cursor','pointer');
            $(this).on('click', function() {
                window.open(alblnks[indx],"_blank");
            });
        });
    },
    error: function() {
        msg = '<p>Did not succeed in loading the xml database</p>';
        alert(msg);
    }
});  // end ajax

}); // end page load wait