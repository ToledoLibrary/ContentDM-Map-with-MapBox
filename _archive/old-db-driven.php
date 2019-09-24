
<!DOCTYPE HTML>
<html>
<head>

    <title>Collection by Decade</title>
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="UTF-8">

</head>
<body>

<?php


$servername = "localhost";
$username = "lechlakc_cdm";
$password = "386North";
$dbname = "lechlakc_cdm";


$mysqli = new mysqli("$servername", "$username", "$password", "$dbname");

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$query = "SELECT time, COUNT(time) AS timecount FROM cdm GROUP BY time ORDER BY time ASC";

if ($result = $mysqli->query($query)) {

    /* fetch object array */
    while ($row = $result->fetch_row()) {
    	$decade = $row[0];
    	$itemcount = $row[1];
    	//$itemcount = trim($row[1], '(');
    	//$itemcount .= trim($itemcount, ')');
    	
        $array .= "['$decade',$itemcount],";
    }
	$array = rtrim($array,',');
	//echo $array;
    /* free result set */
    $result->close();
}

/* close connection */
$mysqli->close();
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<div id="chart_div" style=""></div>
      
<script type="text/javascript">
google.charts.load('current', {
  'packages': ['bar']
});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
  var data = google.visualization.arrayToDataTable([
    ['Decade', 'Items'],
    <?php echo $array ?>
  ]);
  
   var view = new google.visualization.DataView(data);

    view.setColumns([0, //The "descr column"
    1, //Downlink column
    {
        calc: "stringify",
        sourceColumn: 1, // Create an annotation column with source column "1"
        type: "string",
        role: "annotation"
    }]);
    
    var columnWrapper = new google.visualization.ChartWrapper({
        chartType: 'ColumnChart',
        containerId: 'chart_div',
        dataTable: view
    });

    columnWrapper.draw();

  var options = {
    height:600,
    chart: {
      title: 'Collections by Decade',
      subtitle: 'Items in Collections',
    },
    bars: 'vertical'
  };

  var chart = new google.charts.Bar(document.getElementById('chart_div'));

  chart.draw(data, google.charts.Bar.convertOptions(options));
  
  google.visualization.events.addListener(chart, 'select', selectHandler);


function selectHandler() {
var selection = chart.getSelection();
var message = '';

alert('You selected ' + message);

}
}



            
</script>

</body>
</html>





