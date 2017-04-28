#!/bin/bash
#VERSION: 1.1 added output path spec
GPX_FILE=0
SETOPATH=0
GPXfname=""
OPATH="."
for i
do
	# -f get filename for gpx file
	if [ $GPX_FILE -gt 0 ]
	then {
		GPXfname=$i
		GPX_FILE=0
	}
	fi
	# -p get pathspec for output file
	if [ $SETOPATH -gt 0 ]
	then {
		OPATH=$i
		SETOPATH=0
	}
	fi
	case $i in
		-f) GPX_FILE=1 ;;
		-p) SETOPATH=1 ;;
		-h) echo "
*** Version 1.1
This utility requires the -f flag followed by a space, then the GPX filename.
The filename can be preceded by a relative (or absolute) pathspec.
Use the -p to specify the pathspec for writing the output file: default (no flag) is local.

This routine will extract longitude/latitude data and convert it to 
an array of google-maps-compatible lat/lng objects, creating a file
with the same base filename, but a .json extension

"
exit ;;
	esac
done
if [ ! -r $GPXfname ]
then {
	echo "GPX file "${GPXfname}" does not exist or is not readable"
	exit
}
fi
if [ ! -d $OPATH ]
then {
	echo "${OPATH} is not a directory"
	exit
}
fi
# if GPXfname is a filespec w/path:
export GPXfname
#flgth=${#GPXfname}
#echo "input filespec is ${flgth} characters"
getName=`echo $GPXfname | tr "/" "\n"`
for partspec in $getName
do
	echo "$partspec" >> tmpfile
done
gpxSpec=`tail -1 tmpfile`
#echo $gpxSpec
rm tmpfile
echo "JSON data file will be created with name:
>>>>>   ${gpxSpec%.*}.json   <<<<<
- and will be written to: ${OPATH}"
outFile=${OPATH}/${gpxSpec%.*}.json
export outFile
cat $GPXfname | /usr/bin/awk -v gpxfile=$GPXFname '
BEGIN {
	OFS="{ \"lat\": "
	OBJ=", \"lng\": "
	fline=1
}
{
	if (match($0,/trkpt lat/)) {
		latstrt=match($0,/lat/)+5
		nxtstr=match($0,/\" lon/)
		latend=nxtstr-latstrt
		lat=substr($0,latstrt,latend)
		lngstrt=match($0,/lon/)+5
		eostr=match($0,/\">/)
		lngend=eostr-lngstrt
		lng=substr($0,lngstrt,lngend)
		if( fline == 1 ) {
			print "[" OFS lat OBJ lng " },"
			fline=2
		} else {
			print OFS lat OBJ lng " },"
		}
	}
} 
' > $outFile
# remove final comma:
sed -i.bck '$s/.$//' $outFile
# add closing bracket
sed -i.bck '$s/$/]/' $outFile
#echo "" >> $outFile
#sed -i.bck '$s/$/}/' $outFile
backupFile=${outFile}.bck
rm $backupFile
npts=`cat $outFile | wc -l`
npts=$(( $npts - 4 ))
echo "${npts} points found in ${GPXfname}"
echo "DONE"
