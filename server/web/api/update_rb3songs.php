<?php

include 'rb3.inc';

function do_curl($url) {

	$ch = curl_init();
	
  	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");

  	return curl_exec($ch);
}

echo "Checking for new songs from rockband.com...\n";

// Download the song data JSON from rockband.com
$json = do_curl("http://www.rockband.com/services.php/music/all-songs.json");
$rbsongs = json_decode($json);

dbConnect();

// Get the list of song sources
$query = "select * from RB3Sources";
$rows = mysql_query($query);

$sources = array();

while ($row = mysql_fetch_object($rows))
{
	$sources[$row->rbid] = $row->id;
}

$addcount = 0;

// Add any songs we don't already have
foreach ($rbsongs as $rbsong)
{
	// rbsong is an object
	// we care about:
	// id => rbid
	// name
	// source
	
	$rbid = mysql_real_escape_string($rbsong->id);
	
	//echo "Looking up song with id $rbid";
	
	$query = "select * from RB3Songs where rbid = '$rbid'";
	$rows = mysql_query($query);
	$numrows = mysql_num_rows($rows);
	
	if ($numrows > 0) 
	{
		// TODO: We already have this song, update it
		
	}
	else 
	{
		// Add the song
		$rbsourceid = mysql_real_escape_string($rbsong->source);
		$sourceid = $sources[$rbsourceid];
		$name = mysql_real_escape_string($rbsong->name);
		
		$query = "insert into RB3Songs (rbid, name, sourceid) values ('$rbid', '$name', '$sourceid')";
		
		//echo $query;
		$result = mysql_query($query);
		
		$addcount += 1;
	}
	
}

echo "Added $addcount new songs.\n";

dbClose();


?>