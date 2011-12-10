<?php


include 'api/rb3.inc';


function getInstruments() {

  $query = "SELECT * FROM RB3Instruments";
  $rows = mysql_query($query);
  $result = array();
  while ($row = mysql_fetch_object($rows)) {
    $result[$row->id] = $row->name;
  }

  return $result;
}

function getSources() {

  $query = "SELECT * FROM RB3Sources";
  $rows = mysql_query($query);
  $result = array();
  while ($row = mysql_fetch_object($rows)) {
    $result[$row->id] = $row->name;
  }

  return $result;
}

function getLastUpdateTime() {
	$query = "SELECT last_recorded 
		FROM `RB3CurrentScores` 
		ORDER BY last_recorded DESC
		LIMIT 1";
	$rows = mysql_query($query);
	if (mysql_num_rows($rows) == 1) {
		$row = mysql_fetch_object($rows);
		return $row->last_recorded . " GMT";
	}
	else {
		return "Unknown";
	}
}

function displayScores($instrument) {
	?>
	<table id="instrument_<?php echo $instrument; ?>">
  <thead>
  	<tr>
  		<th>Rank</th>
  		<th></th>
  		<th>Name</th>
  		<th>Ars Name</th>
  		<th>Score</th>
  		<th>Last Score</th>
  		<th># Songs</th>
  		<th>Last # Songs</th>
  	</tr>
  </thead>
  <tbody>
  <tr><td colspan="6" class="dataTables_empty">Loading data from server...</td></tr>
  </tbody>
	</table>
	<?php
}

// Start of PHP processing

dbConnect();
$instruments = getInstruments();

// Add "special instruments"
$instruments["total_solo_amateur"] = "Total Amateur";
$instruments["total_solo_pro"] = "Total Pro";
//$instruments["combined_drums"] = "Combined Drums";

//$instruments = array("1" => "Band");

$sources = getSources();


?>
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<html>
<head><title>The Rock Band 3 Thing</title>
<link type="text/css" href="css/style.css" rel="stylesheet" />
<link type="text/css" href="css/eggplant/jquery-ui-1.8.11.custom" rel="stylesheet" />	
<link type="text/css" href="css/demo_table.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>

<script type="text/javascript">

function addCommas(nStr){
 nStr += '';
 x = nStr.split('.');
 x1 = x[0];
 x2 = x.length > 1 ? '.' + x[1] : '';
 var rgx = /(\d+)(\d{3})/;
 while (rgx.test(x1)) {
  x1 = x1.replace(rgx, '$1' + ',' + '$2');
 }
 return x1 + x2;
}

jQuery.fn.dataTableExt.oSort['formatted-num-desc'] = function(x,y){
 x = x.replace(/[^\d\-\.\/]/g,'');
 y = y.replace(/[^\d\-\.\/]/g,'');
 if(x.indexOf('/')>=0)x = eval(x);
 if(y.indexOf('/')>=0)y = eval(y);
 return x/1 - y/1;
}
jQuery.fn.dataTableExt.oSort['formatted-num-asc'] = function(x,y){
 x = x.replace(/[^\d\-\.\/]/g,'');
 y = y.replace(/[^\d\-\.\/]/g,'');
 if(x.indexOf('/')>=0)x = eval(x);
 if(y.indexOf('/')>=0)y = eval(y);
 return y/1 - x/1;
}

$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback )
{
	if ( typeof sNewSource != 'undefined' )
	{
		oSettings.sAjaxSource = sNewSource;
	}
	this.fnClearTable( this );
	this.oApi._fnProcessingDisplay( oSettings, true );
	var that = this;
	
	$.getJSON( oSettings.sAjaxSource, null, function(json) {
		/* Got the data - add it to the table */
		for ( var i=0 ; i<json.aaData.length ; i++ )
		{
			that.oApi._fnAddData( oSettings, json.aaData[i] );
		}
		
		oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
		that.fnDraw( that );
		that.oApi._fnProcessingDisplay( oSettings, false );
		
		/* Callback user function - for event handlers etc */
		if ( typeof fnCallback == 'function' )
		{
			fnCallback( oSettings );
		}
	} );
}

function changeSource(table, instrument, source) {
	table.fnReloadAjax('queryScores.php?instrument=' + instrument + '&song=career&source=' + source);
}

$(function(){
	$('#tabs').tabs();
	
	<?php
	foreach ($instruments as $id => $name) {
	?>
		oTable_<?php echo $id; ?> = $('#instrument_<?php echo $id;?>').dataTable({
			"bJQueryUI": true,
			"bPaginate": false,
			"bLengthChange": false,
			"bFilter": false,
			"bInfo": false,	
			"bProcessing": true,
			"oLanguage": {"sEmptyTable": "Loading data from server..."},
			"sAjaxSource": "queryScores.php?instrument=<?php echo $id; ?>&song=career&source=4",
			"aoColumns": [ 
				{ 
					// Rank
					"fnRender": function ( oObj ) 
					{
						var current = oObj.aData[0];
						var result = current;

						return result;
					},
					"sType": "numeric",
				},
				{ 
					// Last Rank (use for Change)
					"fnRender": function ( oObj ) 
					{
						var current = oObj.aData[0];
						var last = oObj.aData[1];
						var result = "";
						
						if (current != last)
						{
							var color = "";
							var str = "";
							
							if (last == "new")
							{
								color = "new";
								str = "new";
							}
							else if (current < last)
							{
								color = "good";
								str = "&#9650;" + Math.abs(current - last);
							}
							else
							{
								color = "bad";
								str = "&#9660;" + Math.abs(current - last);
							}
							
							result += "<span style='float:right' class='" + color + "'>";
							result += str;
							result += "</span>";
						}
						
						return result;
					},
					"bSortable": false,
					
				},
				null, // Name
				null, // Ars Name
				{ 
					// Score
					"sType": "formatted-num",
					"fnRender": function ( oObj ) 
					{
						var current = oObj.aData[4];
						var last = oObj.aData[5];
						
						var num = addCommas(current);
						var result = num + " ";
						
						if (current != last)
						{
							var color = "";
							var str = "";
							var hint = "";
							
							if (last == "new")
							{
								color = "new";
								str = "&#8195;";
							}
							else if (1*current > 1*last)
							{
								color = "good";
								str = "&#9650;";
								hint = "Up " + addCommas(current - last) + " points";
							}
							else
							{
								color = "bad";
								str = "&#9660;";
								hint = "Down " + addCommas(current - last) + " points";
							}
							
							result += "<span href='' class='tooltip " + color + "'>";
							result += str;
							result += "<span>" + hint + "</span>";
							result += "</span>";
						}
						else
						{
							result += "&#8195;";
						}
						
						return result;
					},
					"sClass": "rightalign"
				},
				{ 
					// Last Score
					"bVisible": false 
				},
				{ 
					// # Songs
					"fnRender": function ( oObj ) 
					{
						var current = oObj.aData[6];
						var last = oObj.aData[7];
						var result = current;
						
						if (current != last)
						{
							var color = "";
							var str = "";
							
							if (last == "new")
							{
								color = "new";
								str = "new";
							}
							else if (current > last)
							{
								color = "good";
								str = "&#9650;" + Math.abs(current - last);
							}
							else
							{
								color = "bad";
								str = "&#9660;" + Math.abs(current - last);
							}
							
							result += "<span style='float:right' class='" + color + "'>";
							result += str;
							result += "</span>";
						}
						
						return result;
					},
					"sType": "numeric",
					"sClass": ""
				},
				{ 
					// Last # songs
					"bVisible": false 
				},
			]
			});
		$("#instrument_<?php echo $id; ?>").css("width","100%");

		
		$("#select-source").change(function() {
			changeSource(oTable_<?php echo $id; ?>, "<?php echo $id; ?>", $(this).val());
		});

	<?php
	
	}
	?>
});

</script>


</head>
<body>

<img src="images/header.png" alt="The Rock Band 3 Thing"</img>

<div style="width: 1300px">
<p>Welcome to the Rock Band 3 Thing! This page lists the current standings for Ars players on <a href="http://www.rockband.com">Rock Band 3</a>.</p>

<p>
Right now you can see the career totals by instrument and song source (default is RB3 disc songs). Just select a tab below to see the leaderboards. The scores are updated from the online leaderboards about once a week. Note that the scores are calculated by the total of individual song scores, so they may differ slightly from the "Career" score you see in the game.
</p>

<p>
To join the leaderboard, send a PM to "SITE MAINTAINER" with your Xbox Live gamertag, PSN name, or Wii name.</p>

<p>New feature: see which songs are the <a href="popular.php">most popular</a> among your fellow Arsians!</p>
</div>

<div id="tabs" style="width: 1300px">
	<ul>
<?php
	foreach ($instruments as $id => $name) {
		?>
    	<li><a href="#tabs-<?php echo $id; ?>"><?php echo $name; ?></a></li>
    	<?php
	}
?>
	</ul>
	
	<div class="source">
	Show scores from: 
	<select id="select-source" style="width:200px">
		
	<?php
		foreach ($sources as $source_id => $source_name) {
			$selected = "";
			if ($source_id == 4)
			{
				$selected = "selected";
			}
			
			?>
			<option value="<?php echo $source_id; ?>" <?php echo $selected; ?>><?php echo $source_name; ?></option>
	    	<?php
		}
	?>
		<option value="0">Everything</option>
		</select>
	</div>

<?php
	foreach ($instruments as $id => $name) {
		?>
		<div id="tabs-<?php echo $id; ?>">
				
		<?php
		displayScores($id);
		?>
		</div>
		<?php
	}
?>
</div>

<p>Data last updated: <?php echo getLastUpdateTime(); ?></p>

<?php  
  dbClose();
?>

<p>

<h5>Questions?</h5>
PM to "SITE MAINTAINER".

<h5>TODO List</h5>
<ul>
<li>Data history</li>
<li>Per song charts/breakdown</li>
<li>Add user request form</li>
<li>User prefs</li>
</ul>

Images courtesy of and copyright <a href="http://www.brittneymcisaac.com/BrittneyMcIsaac/Welcome.html">Brittney McIsaac</a>, except then I scribbled on it.

<script type="text/javascript">
// You could put your google analytics here.
</script>

</body>
</html>
