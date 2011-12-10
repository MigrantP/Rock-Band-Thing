<?php


include 'api/rb3.inc';

function getSources() {

  $query = "SELECT * FROM RB3Sources";
  $rows = mysql_query($query);
  $result = array();
  while ($row = mysql_fetch_object($rows)) {
    $result[$row->id] = $row->name;
  }

  return $result;
}


// Start of PHP processing

dbConnect();

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

function changeSource(table, source) {
	table.fnReloadAjax('queryPopular.php?source=' + source);
}

$(function(){
		oTable = $('#songsTable').dataTable({
			"bJQueryUI": true,
			"bPaginate": true,
			"bLengthChange": false,
			"bFilter": false,
			"bInfo": false,	
			"bProcessing": true,
			"sAjaxSource": "queryPopular.php?source=0",
			"aaSorting": [[ 2, "desc" ]],
			"iDisplayLength": 25,
			"oLanguage": {"sEmptyTable": "Loading data from server..."},
			});
		$("#songsTable").css("width","100%");

		
		$("#select-source").change(function() {
			changeSource(oTable, $(this).val());
		});
});

</script>


</head>
<body>

<img src="images/header.png" alt="The Rock Band 3 Thing"</img>

<div style="width: 800px">
<h1>Songs by Popularity on Ars Technica</h1>
<a href="index.php">Back to Scores</a>
</div>

<div style="width: 800px">

<div class="source">
Show songs from: 
<select id="select-source" style="width:200px">
	
<?php
	foreach ($sources as $source_id => $source_name) {
		?>
		<option value="<?php echo $source_id; ?>"><?php echo $source_name; ?></option>
    	<?php
	}
?>
	<option value="0" selected>Everything</option>
	</select>
</div>


<table id="songsTable">
<thead>
	<tr>
		<th>Song Name</th>
		<th>Source</th>
		<th># Players</th>
	</tr>
</thead>
<tbody>
<tr><td colspan="3" class="dataTables_empty">Loading data from server...</td></tr>
</tbody>
	</table>


</div>

<?php  
  dbClose();
?>

<script type="text/javascript">
// You could put your google analytics here.
</script>

</body>
</html>
