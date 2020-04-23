<?php

include_once(realpath(__DIR__ . '/connection_mysqli.php'));
$ltime = time();
//print_r($con);
$aa = array();
$sessiontimelimit = 20 * 60;
$row = mysqli_query($con, "select id,last_refresh_time from dbt_sessions where status=1");
while ($res = mysqli_fetch_array($row)) {

    $last_refresh_time = $res['last_refresh_time'];
    $gaptime = $ltime - $last_refresh_time;
    if ($gaptime > $sessiontimelimit) {
         mysqli_query($con, "update dbt_sessions set status=0 where id='" . $res['id'] . "'");
    }
}
$count_my_page = "/var/www/html/data/hit/hitcounter.txt";
$hits = array();
$hits = file($count_my_page);
$date = date('Y-m-d');
$row = mysqli_query($con, "INSERT INTO dbt_visitors_count (no_of_visitors, visitors_count_date) VALUES ('" . $hits[0] . "', '" . $date . "')");
?>