<?php

include 'rb3.inc';

function getCurrentScore($rawScore) {
	return getOtherScore($rawScore, "RB3CurrentScores");
}

function getHistoricalScore($rawScore) {
	return getOtherScore($rawScore, "RB3HistoricalScores");
}


function getOtherScore($rawScore, $tableName) {

	$query = "SELECT * FROM $tableName 
			  WHERE user = $rawScore->user
			  AND song = $rawScore->song
			  AND instrument = $rawScore->instrument
			  LIMIT 1";
	$rows = mysql_query($query);	
	
	$numrows = mysql_num_rows($rows);	  
	if ($numrows > 0) {
		return mysql_fetch_object($rows);
	}
	else {
		return null;
	}
}



function addCurrentScore($rawScore) {

	$query = "INSERT INTO `RB3CurrentScores` (`user`, `song`, `instrument`, `score`, `first_recorded`, `last_recorded`) 
		VALUES ($rawScore->user, $rawScore->song, $rawScore->instrument, $rawScore->score, '$rawScore->recorded', '$rawScore->recorded')";
	mysql_query($query);
}

function addHistoricalScore($currentScore) {

	$query = "INSERT INTO `RB3HistoricalScores` (`user`, `song`, `instrument`, `score`, `first_recorded`, `last_recorded`) 
		VALUES ($currentScore->user, $currentScore->song, $currentScore->instrument, $currentScore->score, '$currentScore->first_recorded', '$currentScore->last_recorded')";
	mysql_query($query);

}

function updateBetterCurrentScore($rawScore, $currentScore) {

	$query = "UPDATE `RB3CurrentScores` 
		SET score = $rawScore->score,
			first_recorded = '$rawScore->recorded', 
			last_recorded = '$rawScore->recorded'
		WHERE id = $currentScore->id";
	mysql_query($query);
}

// Just update the last recorded time
function updateSameCurrentScore($rawScore, $currentScore) {

	$query = "UPDATE `RB3CurrentScores` 
		SET last_recorded = '$rawScore->recorded'
		WHERE id = $currentScore->id";
	mysql_query($query);
}



dbConnect();

$query = "SELECT * FROM RB3RawScores";
$rawscores = mysql_query($query);
$numscores = mysql_num_rows($rawscores);
	
echo "Processing $numscores raw scores..\n";

while ($rawScore = mysql_fetch_object($rawscores)) {

	// Get the corresponding Current score
	$currentScore = getCurrentScore($rawScore);
	
	if ($currentScore == null) {
		// Add the score to Current
		echo "n";
		addCurrentScore($rawScore);
	}
	else if ($rawScore->score > $currentScore->score) {
		// Add current to history
		// Update current with new score
		echo "b";
		addHistoricalScore($currentScore);
		updateBetterCurrentScore($rawScore, $currentScore);
	}
	else {
		// Update current last_recorded
		echo "s";
		updateSameCurrentScore($rawScore, $currentScore);
	}
}

echo "\nFinished processing, clearing raw scores..\n";

// All done, clear the raw scores
$query = "DELETE FROM RB3RawScores";
mysql_query($query);
printf("\nRecords deleted: %d\n", mysql_affected_rows());

dbClose();

?>