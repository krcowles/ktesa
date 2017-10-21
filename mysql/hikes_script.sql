USE mysql;
\W	# Turn on warnings
DROP TABLE HIKES;
CREATE TABLE HIKES (
        indxNo smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
        pgTitle varchar(30) NOT NULL,
        usrid varchar(32), # removed  NOT NULL,  just to make warning go away
        locale varchar(20),
        marker varchar(11),
        collection varchar(15),
        cgroup varchar(3),
        cname varchar(25),
        logistics varchar(12),
        miles decimal(4,2),	# changed from miles dec(4,1),
        feet smallint(5),
        diff varchar(14),
        fac varchar(30),
        wow varchar(50),
        seasons varchar(12),
        expo varchar(15),
        gpx varchar(30),
        trk varchar(30),
        lat double(13,10),
        lng double(13,10),
        aoimg1 varchar(100),
        aoimg2 varchar(100),
        purl1 varchar(200),
        purl2 varchar(200),
        dirs varchar(512),	# changed from 250 because a few of the dir strings are longer
        tips varchar(500),
        info varchar(1500),
        refs varchar(1500),
        props varchar(500),
        acts varchar(500),
        tsv text);
LOAD XML LOCAL INFILE 'data/database.xml' INTO TABLE HIKES;
