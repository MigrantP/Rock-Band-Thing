<?php

// FILL THIS IN WITH YOUR SERVER
$baseUrl = "http://myserver.com/rb3/api";

function debug($msg) {
	file_put_contents("process-log.txt", $msg, FILE_APPEND);
}

function do_curl($url, $sleep) 
{

	$ch = curl_init();

	if ($sleep) {
		sleep(1);
	}
	
  	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");

  	return curl_exec($ch);
}

function do_post_request($url, $data, $optional_headers = null)
{
     $params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
               ));
     if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
     }
     $ctx = stream_context_create($params);
     $fp = @fopen($url, 'rb', false, $ctx);
     if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
     }
     $response = @stream_get_contents($fp);
     if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
     }
     return $response;
}


// Returns an array with rbid => [id]
function get_songs() {
	global $baseUrl;
	
  $xml = do_curl("$baseUrl/query_RB3Songs.php", false);

  $dom = new DOMDocument();
  $dom->loadXML($xml);

  $result = array();
  $songs = $dom->getElementsByTagName("song");
  foreach ($songs as $song) {
       
     $id = $song->getAttribute("id");
     $rbid = $song->getAttribute("rbid");

     $result[$rbid] = $id;
  }

  return $result;
}

// Returns an array with id => [rbid]
function get_instruments() {

global $baseUrl;

  $xml = do_curl("$baseUrl/query_RB3Instruments.php", false);

  $dom = new DOMDocument();
  $dom->loadXML($xml);

  $result = array();
  $instruments = $dom->getElementsByTagName("instrument");
  foreach ($instruments as $instrument) {
       
     $id = $instrument->getAttribute("id");
     $rbid = $instrument->getAttribute("rbid");

     $result[$id] = $rbid;
  }

  return $result;
}

// Returns an array with name => [id, platformid]
function get_users() {

global $baseUrl;

  $xml = do_curl("$baseUrl/query_users.php", false);

  $dom = new DOMDocument();
  $dom->loadXML($xml);

  $result = array();
  $users = $dom->getElementsByTagName("user");
  foreach ($users as $user) {
       
     $id = $user->getAttribute("id");
     $name = $user->getAttribute("name");
     $platform = $user->getAttribute("platform");

     $result[$name] = array('id' => $id, 'platform' => $platform);
  }

  return $result;
}

// Returns an array with id => [rbid]
function get_platforms() {

global $baseUrl;

  $xml = do_curl("$baseUrl/query_Platforms.php", false);

  $dom = new DOMDocument();
  $dom->loadXML($xml);

  $result = array();
  $platforms = $dom->getElementsByTagName("platform");
  foreach ($platforms as $platform) {
       
     $id = $platform->getAttribute("id");
     $tbrbid = $platform->getAttribute("rb3id");

     $result[$id] = $tbrbid;
  }

  return $result;
}

// Returns array of scores as 0 => user_id, 1 => song_id, 2 => score
function process_scores($platform_rbid, $instrument_rbid, $songs, $users)
{
	debug("Processing $platform_rbid - $instrument_rbid\n");
	
	$result = array();
	
	// Read the score file
	$filename = "data/data-$instrument_rbid-$platform_rbid";
	$handle = @fopen($filename, "r");
	if ($handle) {
	    while (($buffer = fgets($handle, 4096)) !== false) 
	    {
	        // Each line is formatted tab-separated as:
	        // song_rbid username difficulty score
	        $vars = explode("\t", $buffer);
	        $username = $vars[1];
	        
	        // We only care about this score if the username matches one of ours on the right platform
	        if (array_key_exists($username, $users))
	        {
	        	$user = $users[$username];
	        	
	        	// It is required that we know about the song
	        	$song_rbid = $vars[0];
	        	if (array_key_exists($song_rbid, $songs))
	        	{
		        	$song_id = $songs[$song_rbid];
		        	
		        	$score = $vars[3];
		        	
		        	$score_array = array($user['id'], $song_id, $score);
		        						 
		        	$result[] = $score_array;
		        }
		        else
		        {
		        	debug("WARNING: Unknown song with rbid $song_rbid, user = $username\n");
		        }
	        }
	        
	    }
	    if (!feof($handle)) 
	    {
	        debug("Error: unexpected fgets() fail\n");
	    }
	    fclose($handle);
	}
	
	debug("Found " . count($result) . " scores\n");
	
	return $result;
}

function upload_scores($instrument_id, $scores)
{
global $baseUrl;

	$url="$baseUrl/upload_rb3.php";
	
	$data = array('instrument_id' => $instrument_id, 'scores' => $scores);
	$data = http_build_query($data);
	
	$result = do_post_request($url, $data);
	
	debug($result);
	
	
}

debug("\n\nStarting process at GMT " . gmdate("Y-m-d H:i:s") . "\n");


// Have the server grab the latest songs
$updatesongs_result = do_curl("$baseUrl/update_rb3songs.php", false);
debug($updatesongs_result);

$songs = get_songs();
$instruments = get_instruments();
$users = get_users();
$platforms = get_platforms();

debug("Found " . count($songs) . " songs\n");
debug("Found " . count($instruments) . " instruments\n");
debug("Found " . count($users) . " users\n");
debug("Found " . count($platforms) . " platforms\n");

// For each platform
foreach ($platforms as $platform_id => $platform_rbid)
{
	// Limit the users to the ones on that platform
	$platformusers = array();
	foreach ($users as $username => $user)
	{
		if ($user['platform'] == $platform_id)
		{
			$platformusers[$username] = $user;
		}
	}
	
	debug("$platform_rbid has " . count($platformusers) . " users\n");
	
	if (count($platformusers) > 0)
	{
		// For each instrument
		foreach ($instruments as $instrument_id => $instrument_rbid)
		{
			// Get all the scores we care about out of the data file 
			$scores = process_scores($platform_rbid, $instrument_rbid, $songs, $platformusers);
			
			if (count($scores) > 0)
			{
				// Upload the raw scores
				upload_scores($instrument_id, $scores);
			}
		}
	}
}

// Call processing page
$process_result = do_curl("$baseUrl/process_rb3.php", false);
debug($process_result);

debug("\n\nFinished process at GMT " . gmdate("Y-m-d H:i:s") . "\n");


?>