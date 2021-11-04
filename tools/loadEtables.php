<?php
/**
 * This script will create the new gpx database tables for EHIKES.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
require "toolFunctions.php";

$metatbl = <<<EMETA
CREATE TABLE `EMETA` (
    `gpxindx` INT NOT NULL AUTO_INCREMENT,
    `fname` VARCHAR(100) NOT NULL,
    `fileno` INT NOT NULL,
    `meta` TEXT NULL,
    `trkno` INT NOT NULL,
    `trkext` VARCHAR(2500) NULL,
    `trkname` VARCHAR(200) NULL,
    `length` DECIMAL(4,2) NULL,
    `min2max` INT NULL,
    `asc` INT NULL,
    `dsc` INT NULL,
    PRIMARY KEY (`gpxindx`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EMETA;

$gpxtbl = <<<EGPX
CREATE TABLE `EGPX` (
    `indx` INT NOT NULL AUTO_INCREMENT,
    `fileno` INT NOT NULL,
    `trackno` INT NOT NULL,
    `segno` INT NOT NULL,
    `lat` DECIMAL(13,11) NULL,
    `lon` DECIMAL(14,11) NULL,
    `ele` DECIMAL(6,2) NULL,
    `time` DATETIME NULL,
    PRIMARY KEY (`indx`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EGPX;

$clearMeta = $gdb->query("DROP TABLE IF EXISTS `EMETA`;");
$clearEGpx = $gdb->query("DROP TABLE IF EXISTS `EGPX`;");
$emeta = $gdb->query($metatbl);
$egpx  = $gdb->query($gpxtbl);
echo "DONE";
