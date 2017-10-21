USE mysql;
\W	# Turn on warnings
DROP TABLE TSV;
CREATE TABLE TSV (
        picIdx smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
        folder varchar(30),
        usrid varchar(32),
        title varchar(64),
        hpg varchar(1),
        mpg varchar(1),
        gpsv_desc varchar(128),  # does not like the keyword desc
        lat double(13,10),
        lng double(13,10),
        thumb varchar(256),
        alblnk varchar(256),
        date DATETIME,
        mid varchar(256),
        imgHt smallint,
        imgWd smallint,
        iclr varchar(32),
        org varchar(256)
		);
LOAD XML LOCAL INFILE 'data/database.xml' INTO TABLE TSV ROWS IDENTIFIED BY '<picDat>';
