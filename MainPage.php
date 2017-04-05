<!DOCTYPE html>

<html lang = "en">
<head>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="styles/mainpage.css"/>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>

<div class = "container">
  <h1>Welcome to TerrorismFacts!</h1>
  <p>Select the information type you're after</p>
  <div>
  <button class = "terrmap" onclick="javascript:document.location='MapPage.php'">
    <h2>Map</h2>
    <p>Visualize hits in a specified area </p>
  </button>
  <button class = "terrchart" onclick="javascript:document.location='ChartPage.php'">
    <h2>Bar Chart</h2>
    <p>See the prevalence of different attributes of attacks </p>
  </button>
  <button class = "terrclock" onclick="javascript:document.location='TimePage.php'">
    <h2>Timing</h2>
    <p>Analyze how attacks vary over time</p>
  </button>
  <button class = "terrlist" onclick="javascript:document.location='ListPage.php'">
    <h2>List</h2>
    <p>Search for and learn about specific attacks</p>
  </button>
  <button class = "terrdanger" onclick="javascript:document.location='DangerPage.php'">
    <h2>Danger</h2>
    <p>Discover the level of safety in a given location</p>
  </button>
</div>

<br><br>
<div class="popup" onclick="showTuplePopup()">Tuple Count
  <span class="popuptext" id="tupleCount">
	<?php
		## Output object
		class q{
			function output($statement){
				echo "<p>". oci_fetch_object($statement)->TCOUNT ." tuples</p>";
			}
		}
		## Query stuff
		include("include.php");
		$spec = new q;
		$query = "SELECT
				(SELECT COUNT(*) FROM EVENTS)+
				(SELECT COUNT(*) FROM TARGETS)+
				(SELECT COUNT(*) FROM GROUPS)+
				(SELECT COUNT(*) FROM LOCATIONS)+
				(SELECT COUNT(*) FROM HOSTAGE_SITUATIONS)
				AS TCOUNT FROM EVENTS WHERE ROWNUM =1";
		oracle_query($query, $spec);
	
		
	?>
  </span>
</div>
<script>
// When the user clicks on <div>, open the popup
function showTuplePopup() {
    var popup = document.getElementById("tupleCount");
    popup.classList.toggle("show");
}
</script>

</body>

</html>
