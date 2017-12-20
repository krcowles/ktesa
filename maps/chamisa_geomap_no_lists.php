<?php
    $lines = file('chamisa_geomap_no_lists.html');
for ($i = 0; $i < count($lines); ++$i) {
    echo ($lines[$i]);
    if (strpos($lines[$i], "Although GPS Visualizer didn't create") !== false) {
        include 'map_geoloc.php';       // insert geolocation code
    }
    if (strpos($lines[$i], "this must be loaded AFTER gv_options are set") !== false) {
        include 'map_gv_options.php';   // insert gv_options code
    }
}
