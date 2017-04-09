<!DOCTYPE html>
<html>
	<head>
	  <link rel="stylesheet" type="text/css" href="styles/listpage.css"/>
	  <style>
		table {
			width: 100%;
			border-collapse: collapse;
		}

		table, td, th {
			border: 1px solid black;
			padding: 5px;
		}

		th {text-align: left;}
	  </style>
	</head>
<body>

<?php
$q = ($_GET['q']);
include("include.php");
class parse{
	function output($statement){
		if ($row = oci_fetch_object($statement)){
			echo "<table>
			<tr>
			<th>Event ID</th>
			<th>Year</th>
			<th>Casualties</th>
			<th>Summary</th>
			<th>Success</th>
			</tr>";
			echo "<tr>";
			echo "<td>" . $row->EVENT_ID . "</td>";
			echo "<td>" . $row->IYEAR . "</td>";
			echo "<td>" . $row->N_KILL . "</td>";
			echo "<td>" . $row->SUMMARY_TXT . "</td>";
			echo "<td>" . $row->SUCCESSFUL_ATTACK . "</td>";
			echo "</tr>";
		
			echo "</table>";
		}
	}
}
$spec = new parse();
$query = "SELECT * FROM EVENTS WHERE EVENT_ID=".$q;
oracle_query($query, $spec);
?>
</body>
</html>