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

function makeCombinedScoreArray($amateur_rows, $pro_rows) 
{
	$names = array();
	$scores = array();
	$rank = 1;
	$amateur_row = mysql_fetch_assoc($amateur_rows);
	$pro_row = mysql_fetch_assoc($pro_rows);
	while ($amateur_row || $pro_row)
	{
		// Use whichever one has the highest score
		$amateur_score = 0;
		$pro_score = 0;
		
		if ($amateur_row)
		{
			$amateur_score = $amateur_row['score'];
		}
		
		if ($pro_row)
		{
			$pro_score = $pro_row['score'];
		}
		
		if ($amateur_score > $pro_score)
		{
			// Use the amateur score and go to the next row on that
			$row = $amateur_row; 
			$amateur_row = mysql_fetch_assoc($amateur_rows);
			$kind = "A";
		}
		else
		{
			// Use the pro score and go to the next row on that
			$row = $pro_row;
			$pro_row = mysql_fetch_assoc($pro_rows);
			$kind = "P";
		}
		
		// If we already saw this name, skip it
		if (!in_array($row['name'], $names))
		{
			// Add it to the names array
			$names[] = $row['name'];
			
			$platform = $row['platform'];
			$name = "<img src='images/platform-$platform.png'/> " . $row['name'];
		
			$score = array($rank, $name, $row['arsname'], $row['score'], $row['numsongs']);
			$scores[] = $score;
			
			$rank = $rank + 1;
			
		}
		
}
	
	return $scores;
}

function getScores($table, $instrument, $song, $source, $dateClause) {

	$instrumentClause = "";
	$scoreField = "";
	$songClause = "";
	$sourceClause = "";
	
	if ($instrument == "total_solo_amateur")
	{
		$instrumentClause = "instrument IN (2, 4, 6, 8, 10)";
	}
	else if ($instrument == "total_solo_pro")
	{
		$instrumentClause = "instrument IN (3, 5, 7, 9, 11)";
	}
	else
	{
		$instrumentClause = "instrument = '$instrument'";
	}	
	
	if ($song == "career") {
		$scoreField = "SUM(score) AS score";
		$songClause = "";
	}
	else {
		$scoreField = "score";
		$songClause = "AND song = '$song'";
	}
	
	if ($source == "0") {
		// Everything
		$sourceClause = "";	
	}
	else {
		$sourceClause = "AND RB3Songs.sourceid = '$source'";
	}
	
	$query = "SELECT user, Users.name, Users.arsname, $scoreField, COUNT(score) AS numsongs, Platforms.rb3id as platform
  		FROM $table
  		INNER JOIN Users ON Users.id = $table.user
  		INNER JOIN Platforms ON Platforms.id = Users.platform
  		INNER JOIN RB3Songs on RB3Songs.id = $table.song
  		WHERE 
  		$instrumentClause
  		$songClause
  		$sourceClause
  		$dateClause
  		GROUP by Users.name
  		ORDER BY score DESC";	
  		
	$result = mysql_query($query);
	return $result;
}

function getHistoricalScores($instrument, $song, $source, $historicalDateClause, $currentDateClause)
{
	$instrumentClause = "";
	$scoreField = "";
	$songClause = "";
	$sourceClause = "";
	
	if ($instrument == "total_solo_amateur")
	{
		$instrumentClause = "instrument IN (2, 4, 6, 8, 10)";
	}
	else if ($instrument == "total_solo_pro")
	{
		$instrumentClause = "instrument IN (3, 5, 7, 9, 11)";
	}
	else
	{
		$instrumentClause = "instrument = '$instrument'";
	}	
	
	if ($song == "career") {
		$scoreField = "SUM(score) AS score";
		$songClause = "";
	}
	else {
		$scoreField = "score";
		$songClause = "AND song = '$song'";
	}
	
	if ($source == "0") {
		// Everything
		$sourceClause = "";	
	}
	else {
		$sourceClause = "AND RB3Songs.sourceid = '$source'";
	}
	
	$query = "
			SELECT user, name, arsname, $scoreField, COUNT(score) AS numsongs, platform FROM
			(
				SELECT user, Users.name AS name, Users.arsname AS arsname, score, Platforms.rb3id as platform
				FROM RB3HistoricalScores
				INNER JOIN Users ON Users.id = RB3HistoricalScores.user
				INNER JOIN Platforms ON Platforms.id = Users.platform
				INNER JOIN RB3Songs on RB3Songs.id = RB3HistoricalScores.song
				WHERE 
				$instrumentClause
				$songClause
				$sourceClause
				$historicalDateClause
				UNION ALL
				SELECT user, Users.name AS name, Users.arsname AS arsname, score, Platforms.rb3id as platform
				FROM RB3CurrentScores
				INNER JOIN Users ON Users.id = RB3CurrentScores.user
				INNER JOIN Platforms ON Platforms.id = Users.platform
				INNER JOIN RB3Songs on RB3Songs.id = RB3CurrentScores.song
				WHERE 
				$instrumentClause
				$songClause
				$sourceClause
				$currentDateClause
			) s
			GROUP by user
			ORDER BY score DESC";	
		//echo $query;	
	$result = mysql_query($query);
	return $result;
}

function getCurrentScores($instrument, $song, $source) {
	return getScores("RB3CurrentScores", $instrument, $song, $source, "");
}

function getLastWeekScores($instrument, $song, $source)
{
	// This is the annoying part.
	// Once a week you have to update the $currentDate and $historicalDate like so:
	// $currentDate = the time that this week's update started
	// $historicalDate = the time the last week's update ended
	
	// TODO: make a table that the processing can add a record to so this can be automated instead of a manual step every time.

	// Get all scores in Current where first_recorded < X
	//$currentDate = '2011-04-10 20:00:00';
	//$currentDate = '2011-04-18 01:00';
	//$currentDate = '2011-04-25 00:00';
	//$currentDate = '2011-05-02 01:00:00';
	//$currentDate = '2011-05-10 02:00:00';
	//$currentDate = '2011-05-15 23:00:00';
	//$currentDate = '2011-05-23 03:00:00';
	//$currentDate = '2011-06-26 19:00:00';
	$currentDate = '2011-07-04 03:00:00';
	
	$currentDateClause = " AND first_recorded < '$currentDate' ";
	
	//$historicalDate = '2011-04-10 04:00:00';
	//$historicalDate = '2011-04-10 22:00:00';
	//$historicalDate = '2011-04-18 01:00:00';
	//$historicalDate = '2011-04-25 01:00:00';
	//$historicalDate = '2011-05-02 02:00:00';
	//$historicalDate = '2011-05-10 03:00:00';
	//$historicalDate = '2011-05-16 00:00:00';
	//$historicalDate = '2011-05-23 04:00:00';
	$historicalDate = '2011-06-26 20:00:00';
	
	$historicalDateClause = " AND last_recorded > '$historicalDate' ";
	
	$result = getHistoricalScores($instrument, $song, $source, $historicalDateClause, $currentDateClause);
	
	return $result;
}

dbConnect();

//This stops SQL Injection in GET vars 
foreach ($_GET as $key => $value) { 
	$_GET[$key] = mysql_real_escape_string($value); 
} 

$instrument = $_GET['instrument'];
$song = $_GET['song'];
$source = $_GET['source'];

if ($instrument != '' && $song != '' && $source != '') 
{
	$scores = array();
	
	if ($instrument == "combined_drums")
	{
		// Special chart, we need to get the scores for both drums and pro drums and merge them together
		$amateur_rows = getCurrentScores(6, $song, $source);
		$pro_rows = getCurrentScores(7, $song, $source);
		
		$scores = makeCombinedScoreArray($amateur_rows, $pro_rows);
	}
	else
	{
		$current = getCurrentScores($instrument, $song, $source);
		$lastweek = getLastWeekScores($instrument, $song, $source);
		
		
		
		$scores = makeScoreArray($current, $lastweek);
	}

	$output = (object) array('aaData' => $scores);

	echo json_encode($output);
}

dbClose();

?>