<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Convert TSV Files</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail">Convert TSV Files</p>

        <?php
        $database = '../data/database.xml';
        $xml = simplexml_load_file($database);
        if ($xml === false) {
            $errmsg = '<p style="margin-left:20px;color:red;font-size:18px;">' .
                'Could not load xml database</p>';
            die ($errmsg);
        }
        $tsvLoc = '../gpsv/';
        foreach ($xml->row as $row) {
            if (strlen($row->tsv->file) !== 0) {
                $cnv = $row->tsv->addChild('picDat');
                $tsvfile = $tsvLoc . $row->tsv->file;
                # countless efforts to get the fgetcsv fct to use tab char - no go
                $tsvdat = file($tsvfile);
                $lines = count($tsvdat);
                for ($i=0; $i<$lines; $i++) {
                    if ($i === 0) {
                        $fileHeaders = explode("\t",$tsvdat[0]);
                        $noOfFlds = count($fileHeaders);  
                    } else {
                        $data = explode("\t",$tsvdat[$i]);
                        $hasFolder = false;
                        for ($k=0; $k<$noOfFlds; $k++ ) {
                            $field = trim($fileHeaders[$k]);
                            switch ($field) {
                                case 'folder':
                                    $cnv->addChild('folder',$data[$k]);
                                    $hasFolder = true;
                                    break;
                                case 'desc':
                                    $cnv->addChild('title',$data[$k]);
                                    break;
                                case 'name':
                                    $cnv->addChild('desc',$data[$k]);
                                    break;
                                case 'album-link':
                                case 'url':
                                    $cnv->addChild('alblnk',$data[$k]);
                                    break;
                                case 'date':
                                    $cnv->addChild('date',$data[$k]);
                                    break;
                                case 'n-size':
                                    $cnv->addChild('mid',$data[$k]);
                                    break;
                                case 'Latitude':
                                    $cnv->addChild('lat',$data[$k]);
                                    break;
                                case 'Longitude':
                                    $cnv->addChild('lng',$data[$k]);
                                    break;
                                case 'thumbnail':
                                    $cnv->addChild('thumb',$data[$k]);
                                    break;
                                case 'color':
                                    $cnv->addChild('iclr',$data[$k]);
                                    break;
                                case 'symbol':
                                    $cnv->addChild('symbol',$data[$k]);
                                    break;
                                case 'icon_size':
                                    $cnv->addChild('icon_size',$data[$k]);
                                    break;
                                default:
                                    # ignore any other data, like 'every size'
                                    echo "Got:" . $fileHeaders[$k];
                                    break;
                            }  # end switch
                        }  # end of for all fields in header row
                        $cnv->addChild('imgHt');
                        $cnv->addChild('imgWd');
                        $cnv->addChild('org');
                        if (!$hasFolder) {
                            $cnv->addChild('folder','Folder');
                        }
                        $cnv = $row->tsv->addChild('picDat');
                    }  # end of else - collect a line of data
                }  # end of for each line in the tsv file
                $row->asXML('tmp.xml');
                die("1st File");
            }  # if tsv is has a file (note: none on Index Pages or later rows)
        }
        ?>
        <h2 style="margin-left:16px;">TSV File Data Converted in Database</h2>

    </body>
</html>