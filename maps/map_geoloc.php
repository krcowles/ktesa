<?php
if (isset($_GET[show_geoloc]) == true && $_GET[show_geoloc] == "true") {
    echo "<p><a href='javascript:GV_Geolocate({marker:true,info_window:true})' style='font-size:12px'>Geolocate me!</a></p>";
}
