<?php

include 'api/rb3.inc';

function makeScoreArray($current, $lastweek) 
{
	//echo "hi" . mysql_num_rows($lastweek);

	$lastscores = array();
	$lastrank = 1;
	while ($row = mysql_fetch_assoc($lastweek)) 
	{
		$lastscores[$row['user']] = array("rank" => $lastrank, "score" => $row['score'], "numsongs" => $row['numsongs']);
		$lastrank = $lastrank + 1;
	}
	
	//print_r($lastscores);

	$scores = array();
	$rank = 1;
	while ($row = mysql_fetch_assoc($current)) 
	{
		$user = $row['user'];
		$platform = $row['platform'];
		$name = "<img src='images/platform-$platform.png'/> " . $row['name'];
		
		$lastrank = "new";
		$lastscore = "new";
		$lastnumsongs = "new";

		if (array_key_exists($user, $lastscores))
		{
			$last = $lastscores[$user];
			$lastrank = $last['rank'];
			$lastscore = $last['score'];
			$lastnumsongs = $last['numsongs'];
		}	
		else
		{
			//echo "User $user is new! ";
		}
	
		$score = array($rank, $lastrank, $name, $row['arsname'], $row['score'], $lastscore, $row['numsongs'], $lastnumsongs);
		$scores[] = $score;
	
		$rank = $rank + 1;
	}
	
	return $scores;
}



function getPopularSongs($source)
{
	$sourceClause = "1 = 1";
	if ($source > 0)
	{
		$sourceClause = "RB3Songs.sourceid = '$source'";
	}

	$query = "SELECT song, count(song) as playerCount, RB3Songs.name as songName, RB3Sources.name as sourceName FROM
	(
		SELECT *
		FROM RB3CurrentScores
		GROUP BY user, song
	) g
	INNER JOIN RB3Songs on RB3Songs.id = g.song
	INNER JOIN RB3Sources on RB3Sources.id = RB3Songs.sourceid
	WHERE
	$sourceClause
	GROUP BY song
	ORDER BY playerCount DESC";

	//echo $query;
	$rows = mysql_query($query);
	
	$result = array();
	while ($row = mysql_fetch_assoc($rows)) 
	{
		$song = array($row['songName'], $row['sourceName'], $row['playerCount']);
		$result[] = $song;
	}
	
	return $result;
}


dbConnect();

//This stops SQL Injection in GET vars 
foreach ($_GET as $key => $value) { 
	$_GET[$key] = mysql_real_escape_string($value); 
} 

$source = $_GET['source'];

if ($source != '') 
{
	$songs = getPopularSongs($source);

	$output = (object) array('aaData' => $songs);

	echo json_encode($output);
}

dbClose();

?>