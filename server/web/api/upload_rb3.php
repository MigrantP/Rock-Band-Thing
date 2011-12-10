<?php

include 'rb3.inc';

dbConnect();

$instrument_id = mysql_real_escape_string($_POST['instrument_id']);
$scores=$_POST['scores'];

$query = "INSERT INTO `RB3RawScores` (`user`, `song`, `instrument`, `score`, `recorded`) VALUES ";

$first = true;

foreach ($scores as $score_obj) {

  $user_id = mysql_real_escape_string($score_obj[0]);
  $song_id = mysql_real_escape_string($score_obj[1]);
  $score = mysql_real_escape_string($score_obj[2]);
  $recorded = gmdate("Y-m-d H:i:s");

  if ($first) {
    $first = false;
  }
  else {
    $query .= ", ";
  }
  
  $query .= "('$user_id', '$song_id', '$instrument_id', '$score', '$recorded')";

}

$query .= ";";

//echo $query;

$result = mysql_query($query);
if ($result) {
  echo "Inserted " . count($scores) . " records\n";
}
else {
  echo "Error inserting rows: " . mysql_error();;
}

dbClose();

?>
