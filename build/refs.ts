declare var authors: string[];
declare var titles: string[];
/**
 * @fileoverview This script is a stand-alone for manipulating reference 
 * items on tab4 of the editor, and on the cluster page editor.
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 Support for cluster pages; duplicate code removed from editDB.js
 * @version 2.1 Typescripted
 */
$( function () { // DOM loaded

// A: This code refers to existing refs (in database), not new ones...
var refCnt = parseInt($('#refcnt').text());
var item0: string;  // <p> element containing text = rtype
var rtype: string;
var item1: string;  // <p> element containing text = rit1
var rit1: string; 
//var item2: string  // <p> element containing text = rit2
//var rit2: string;
var selbox: string; // <select> element holding reference type selection
var boxid: string;
var box: HTMLSelectElement;
// initialize element contents for pre-populated references
for (var i=0; i<refCnt; i++) {
    item0 = '#rtype' + i;
    rtype = $(item0).text().trim();  // get the rtype for this reference item
    item1 = '#rit1' + i;
    rit1 = $(item1).text().trim();  // get the rit1 for this item (numeric for a book)
    //item2 = '#rit2' + i;
    //rit2 = $(item2).text().trim();  // get the rit2 for this item
    selbox = '#sel' + i;
    $(selbox).val(rtype); // pre-populate reference type drop-down
    boxid = 'sel' + i;
    if (rtype === 'Book:' || rtype === 'Photo Essay:') {
        let indx = parseInt(rit1) - 1;
        let bkname = '#bkname' + i;  // input box id for book name                
        $(bkname).val(rit1);
        let auth = '#auth' + i;
        $(auth).attr('value', authors[indx]);  // get the name from the array
        box = <HTMLSelectElement>document.getElementById(boxid);
        // disable non-book entries
        for (let u=2; u<box.options.length; u++) {
            box.options[u].disabled = true;
        }
    } else if (rtype === 'Text:') {
        let url = '#url' + i;
        $(url).val('');
        $(url).attr('placeholder','THIS BOX IGNORED');
        // disable book type entries
        let boxopts = <HTMLSelectElement>document.getElementById(boxid);
        boxopts.options[0].disabled = true;
        boxopts.options[1].disabled = true;
    } else {
        // disable book type entries
        let selboxes = <HTMLSelectElement>document.getElementById(boxid);
        selboxes.options[0].disabled = true;
        selboxes.options[1].disabled = true;
    }
}
// user can change book selection in pre-populated area:
var $bksels = <JQuery<HTMLSelectElement>>$('select[id^=bkname]');
// jQuery quirk: id is required above, not name or type etc.
$bksels.each(function() {
    let ino = this.id;
    let bksel = '#' + ino + ' option:selected';
    let inpid = '#auth' + ino.substr(6);
    $(this).on('change', function() {
        let selected = <string>$(bksel).val();
        let newbk = parseInt(selected) - 1;
        let newauth = authors[newbk];
        $(inpid).attr('value', newauth);
    });
});

// B: This code refers to the new refs which can be added by the user
/*
 * This code detects when the user selects a reference type other than
 * book/photo essay and displays a different set of boxes with appropriate
 * placeholder text. 
 */
let $reftags = $('select[id^="href"]');
$reftags.each( function() {
    $(this).on('change', function() {
        var refno = this.id;
        var elementNo = refno.substr(4,1); // assumes href # is not double digit
        var bkid = '#bk' + elementNo;   // span holding book elements
        var nbkid = '#nbk' + elementNo; // span holding url/text elements
        var box1 = '#nr1' + elementNo;  // url box
        var box2 = '#nr2' + elementNo;  // click-on text box
        var bkbox = '#usebk' + elementNo;  // yes/no
        var notbk = '#notbk' + elementNo;  // yes/no
        if ($(this).val() === 'Book:' || $(this).val() === 'Photo Essay:') {
            $(bkid).css('display','inline'); // show span for books
            $(nbkid).css('display','none');  // hide span for url/text
            var ttl = '#bkttl' + elementNo;   // book selected in drop-down (select)
            var auth = '#bkauth' + elementNo; // author input box
            for (var n=0; n<titles.length; n++) {
                if (titles[n] === $(ttl).val()) {
                    $(auth).val(authors[n]);
                    break;
                }
            }
            $(bkbox).val('yes');
            $(notbk).val('no');
        } else if ($(this).val() !== 'Text:') {
            $(bkid).css('display','none');
            $(nbkid).css('display','inline');
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','URL');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Clickable text');
            }
            $(bkbox).val('no');
            $(notbk).val('yes');
        } else {
            $(bkid).css('display','none');
            $(nbkid).css('display','inline');
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Enter Text Here');
            } 
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','THIS BOX IGNORED');
            }
            $(bkbox).val('no');
            $(notbk).val('yes');
        }
    });
});
// validate length of URL's and click-on text
$('input[id^=nr1]').each(function() {
    $(this).on('change', function() {
        let nritem = <string>$(this).val();
        if(nritem.length > 1024) {
            alert("This URL exceeds the max length of 1024 characters");
            $(this).val("");
        }
    });
});
$('input[id^=nr2]').each(function() {
    $(this).on('change', function() {
        let nr2item = <string>$(this).val();
        if (nr2item.length > 512) {
            alert("The maximum no of characters allowed in this field is 512");
            $(this).val("");
        }
    });
});
var $bktags = $('select[id^="bkttl"]');
$bktags.each( function() {
    $(this).val(''); // initialize to show no selection:
    $(this).on('change', function() {
        var bkid = this.id;
        bkid = bkid.substr(bkid.length-1, 1);
        var authid = '#bkauth' + bkid;
        var authitem = <string>$(this).val();
        let authindx = parseInt(authitem) -1;
        $(authid).val(authors[authindx]);
    });
});

}); // end of doc ready
