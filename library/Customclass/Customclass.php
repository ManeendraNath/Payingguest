<?php
class Customclass{
/**
  *@ created by Rahul 07-08-2018 12:25:00
  *@ used in mistracker model for remove safexss scripts
  *@ used in Mistracker Report
  **/
public function safexss($value = null){
	$badtags=array('<','>','&lt;','&gt;','javascript','script','onmouseover','onclick','src','img');
		$filtered_string=str_replace($badtags,"",trim($value));
		return $filtered_string;
	}
}
?>