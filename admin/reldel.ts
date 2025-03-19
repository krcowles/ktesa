declare var enos: number[];  // defined by php and embedded in reldel.php
declare function toggleScrollSelect(state: boolean): void;
declare function setNodDatAlerts(): void;
declare function tableSort(tableid: string): void;
/**
 * @fileoverview Adjust links in the table of hikes to point to the appropriate page.
 * Note that cluster pages will have an added 'clus=y' in the query string. The 'del',
 * or delete hike function is not yet implemented.
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 Accommodate Cluster Pages
 * @version 2.1 Typescripted
 */
$(function () { // doc ready

$('table.sortable').attr('id', 'pubTbl');
var exe = $('#action').text();
var linkbase: string;
if (exe === 'rel') {
    linkbase = 'publish.php?hno=';
} else if (exe === 'del') {
    linkbase = '../php/delete.php?hno=';
}
var $tbl = $('#pubTbl tbody tr');
var hikeCol: number;
var $hdr = $('#pubTbl thead').find('th');
$hdr.each( function(indx) {
    if ($(this).text() === 'Hike/Trail Name') {
        hikeCol = indx;
    }
});
$tbl.each( function(indx) {
    let newlink = linkbase + enos[indx];
    let lnk = <string>$(this).find('td').eq(hikeCol).children().attr('href');
    if (lnk.indexOf('clus=y') !== -1) {
        // this is a cluster page
        newlink += "&clus=y";
    } 
    $(this).find('td').eq(hikeCol).children().attr('href',newlink); 
    $(this).find('td').eq(hikeCol).children().attr('target','');   
});
tableSort('#pubTbl');

});
