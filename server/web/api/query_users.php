<?php

include 'rb3.inc';

dbConnect();

$query = "SELECT id, platform, name FROM Users";
$result = mysql_query($query);
$numrows = mysql_num_rows($result);

$doc = new DOMDocument();
$doc->formatOutput = true;

$root = $doc->createElement("users");
$doc->appendChild($root);

for ($i=0; $i < $numrows; $i++) {

  $row = mysql_fetch_assoc($result);

  $song_el = $doc->createElement("user");
  foreach ($row as $key => $value) {
    $song_el->setAttribute($key, $value);
  }
  $root->appendChild($song_el);

}

echo $doc->saveXML();

dbClose();

?>
