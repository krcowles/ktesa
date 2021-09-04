<?php
/**
 * This routine is ajaxed in order to retrieve the visitation data for
 * the incoming range of dates specified.
 * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$start_date = filter_input(INPUT_POST, 'start');
$end_date   = filter_input(INPUT_POST, 'end');

$start_date .= ' 00:00:00';
$end_date   .= ' 23:59:59';

if ($start_date === $end_date) {
    $dataReq = "SELECT * FROM `VISITORS` WHERE `vdatetime` > '{$start_date}';";
} else {
    $dataReq = "SELECT * FROM `VISITORS` WHERE `vdatetime` BETWEEN '{$start_date}' "
        . " AND '{$end_date}';";
}
$data = $pdo->query($dataReq);
$visitor_data = $data->fetchAll(PDO::FETCH_ASSOC);

/**
 * Create the page html
 */
if (count($visitor_data) === 0) {
    echo '<span id="nodat">There is no visitation data to display</span>';
    exit;
}
$html = <<<EOH
<table id="vdat">
    <thead>
        <tr>
            <th>User IP</th>
            <th>User Browser</th>
            <th>Platform</th>
            <th>Time of Visit</th>
            <th>Page Visited</th>
        </tr>
    </thead>
    <tbody>
EOH;
foreach ($visitor_data as $visit) {
    $html .= '<tr>' . PHP_EOL . 
                '<td>' . $visit['vip'] . '</td>' . PHP_EOL .
                '<td>' . $visit['vbrowser'] . '</td>' . PHP_EOL .
                '<td>' . $visit['vplatform'] . '</td>' . PHP_EOL .
                '<td>' . $visit['vdatetime'] . '</td>' . PHP_EOL .
                '<td>' . $visit['vpage'] . '</td>' . PHP_EOL .
            '</tr>';
}
$html .= '</tbody>' . PHP_EOL . '</table>' . PHP_EOL;
echo $html;
