<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="styles/listpage.css"/>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	</head>
<body>

<form action="getEvent.php" method = "get" target="_blank">
<input type="text" id = "inputText" name = "q" onkeyup="showEvent(this.value)"></input>
<!--<a href="#" class="button" onclick="window.open('getEvent.php')">test</a> -->
</form>
<?php
$val = 197000000001;
//echo "<a href="#;" class="button" onclick="window.open('getEvent.php')">test</a>"
?>
<a href="#;" class="button" onclick=<?php echo "window.open('getEvent.php?q='".$val.")" ?>>test1</a>
<a href="#;" class="button" onclick="window.open('getEvent.php?q=<?= $val?>')">test2</a>
<br>
<div id="txtHint"><b>Event info will be listed here...</b></div>

</body>
<script>
	function showEvent(str) {
		if (str == "") {
			document.getElementById("txtHint").innerHTML = "Please enter a value...";
			return;
		} else { 
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
			
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					document.getElementById("txtHint").innerHTML = this.responseText;
				}
			};
			xmlhttp.open("GET","getEvent.php?q="+str,true);
			xmlhttp.send();
		}
	}
		
</script>
</html>