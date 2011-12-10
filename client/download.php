<?php

function do_curl($url, $filename) {

	$out = fopen($filename, 'wb');

	$ch = curl_init();
	
  	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0); 
	curl_setopt($ch, CURLOPT_FILE, $out); 
	    
  	curl_exec($ch);
  	
  	
  	curl_close($ch);
}

function debug($msg) {
	file_put_contents("download-log.txt", $msg, FILE_APPEND);
}

debug("\n\nStarting download at GMT " . gmdate("Y-m-d H:i:s") . "\n");

$platforms = array("xbox", "ps3", "wii");
$instruments = array("bass", "drums", "guitar", "harmony", "keys", "real_bass", "real_drums", "real_guitar", "real_keys", "vocals", "band");

$baseUrl = "http://www.rockband.com/files/leaderboard-data/high_scores_";

foreach ($platforms as $platform)
{
	foreach ($instruments as $instrument)
	{
		$url = $baseUrl . $instrument . "." . $platform . ".gz";
		$filename = "data/data-$instrument-$platform.gz";
		
		debug("Downloading $platform - $instrument\n");
		debug("$url\n");
		
		do_curl($url, $filename);
	}
}

debug("\n\nFinished download at GMT " . gmdate("Y-m-d H:i:s") . "\n");

?>