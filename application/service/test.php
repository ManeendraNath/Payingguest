<?php
// echo date('Y-m-d H:i:s');
$date = '17/11/2019 21:00:00';
echo $newDateTime = date('A', strtotime($date));
if ($newDateTime == 'AM') {
	$time_val = 'morning';
} elseif ($newDateTime == 'PM') {
	$time_val = 'evening';
}
echo $time_val;