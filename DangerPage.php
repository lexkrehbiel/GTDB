<!DOCTYPE html>

<html lang = "en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="styles/listpage.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>

<div class = "container">
  <div style="text-align:center; margin-top:10px">
    <h8 class="menubar">
        <button style="margin-top: 18px" onclick="javascript:document.location='MainPage.php'"><i class="material-icons" style>home</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='ChartPage.php'">assessment</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='ListPage.php'">list</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='TimePage.php'">schedule</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='DangerPage.php'">warning</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='MapPage.php'">pin_drop</i></button>
    </h8>
  </div>
  <h1>Danger Rating</h1>
  <h3>Enter your location</h3>
  <?php
    //echo date();
    //1491343181
    function ifSetElseEmpty($valueName){
      if(isset($_POST[$valueName])){
        return $_POST[$valueName];
      } else {
        return "";
      }
    }
    if($_SERVER["REQUEST_METHOD"] == "POST") {
	  $city = $_POST["location"];
	  $city = str_replace(" ","%",$city); // Replace spaces with % so it works with the API
	  
	  //Find latitude and longitude
	  $url = "http://maps.googleapis.com/maps/api/geocode/json?address=$city";
	  $json_data = file_get_contents($url);
	  $result = json_decode($json_data, TRUE);
	  $latitude = $result['results'][0]['geometry']['location']['lat'];
	  $longitude = $result['results'][0]['geometry']['location']['lng'];
	  
	  // Display lat and long at top for verification purposes
	  echo $latitude . ", " . $longitude;

    }
  ?>
  <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = POST>
    <input type="text" name="location" value="<?php echo ifSetElseEmpty("location");?>"></input>
    <button type="submit" name="search"><i class="material-icons">search</i></button>
  </form>

  <div class="box">
    <?php
	  include("include.php"); // contains "oracle_query" method
	  
      if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {

	  
	  // First class type of output takes simple date and lat/long data and calculates every distance
	  $events_distances = array(); // associative array to hold all event_ids and their distance from input lat/long
	  class q1{
		  private $latitude;
		  private $longitude;
		  
		  // Necessary for global latitude and longitude to be accessed (better practice than using $GLOBALS)
		  public function __construct($latitude, $longitude){
			  $this->latitude = $latitude;
			  $this->longitude = $longitude;
		  }
		  
		  function output($statement){
			  
			  // Get distances of every row
			  while ($row = oci_fetch_object($statement)){
				$distance = $this->distance($this->latitude, $this->longitude, $row->LATITUDE, $row->LONGITUDE);
				
				// Add data to array
				$GLOBALS['events_distances'][$row->EVENT_ID] = $distance;
			  }
		  }
		  
		  // Distance formula which returns kilometers
		  function distance($lat1, $lon1, $lat2, $lon2){
			  $theta = $lon1 - $lon2;
			  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			  $dist = acos($dist);
			  $dist = rad2deg($dist);
			  $miles = $dist * 60 * 1.1515;
			  return $miles * 1.60934;
		  }
	  }
	  
	  // Get only the fields necessary to perform the distance calculation
	  $query = "SELECT event_id, latitude, longitude FROM EVENTS NATURAL JOIN LOCATIONS";
	  $spec = new q1($latitude, $longitude);
	  
	  oracle_query($query, $spec);
	  
	  // Sort by distance ascending, so we know how close each event is (and can access the (NUM_LIST_ROWS) closest events)
	  asort($events_distances);
	  $NUM_LIST_ROWS = 20;
	  
	  // Get the first (NUM_LIST_ROWS) keys(event_id) of the array and add them to an SQL formatted string
	  $event_id_queries = "(";
	  for ($i = 0; $i < $NUM_LIST_ROWS; $i++){
		  
		  $event_id_queries = $event_id_queries . " EVENT_ID=" . key($events_distances) . " OR ";
		  next($events_distances);
	  }
	  $event_id_queries = substr($event_id_queries,0,-3); // remove last "OR" from the string
	  $event_id_queries = $event_id_queries . ")";

	  // Second output class for calculating danger and listing the closest (NUM_LIST_ROWS) events and their info
      class q2 {
        function output($statement){
			  $lastDate = 10000*2015+100*12+31; // 12 31 2015 (last date in the dataset)
			  $dateTotal = 0;
			  $distanceTotal = 0;
			  
			  $arr = array(); //Store each row so we can print the list afterwards
			  
			  // Add up distances and dates of every row
			  while ($row = oci_fetch_object($statement)){
				$distanceTotal += $row->DISTANCE;
				
				$date = $row->IDAY+$row->IMONTH*100+$row->IYEAR*10000;
				$dateTotal += $lastDate-$date;
				
				$arr[] = $row;
			  }
			  
			  // Calculate averages and danger rating
			  $distAvg = $distanceTotal / (40*pi());
			  $dateAvg = $dateTotal / (20*451130);
			  $danger = 5-25*pow($dateAvg,2)*pow($distAvg,.25);
			  
			  // Normalize danger rating
			  if($danger > 5){
				$danger = 4.99;
			  } else if($danger < 0 ){
				$danger = 0.01;
              }
			  $final = round(1000*$danger)/1000;
			  
			  // Display the rating
			  echo "<h3> Your danger rating is: </h3>";
			  echo "<h3 class=\"danger\">";
			  echo $final." out of 5";
              echo "</h3> </h4> </div> <div class=\"box\">";	
			  
		  // List the (NUM_LIST_ROWS) selected
          foreach($arr as $row){
              $distance = $GLOBALS['events_distances'][$row->EVENT_ID]; // Get the distance associated with this entry of the associative array
			  $distance = round($distance*100)/100;
              echo "<div class=\"listitem\">";
              echo "<h5>".$distance." km away: ".($row->CITY).", ".($row->COUNTRY_TXT)."</h5>";

              echo "<p>";
              if(isset($row->SUMMARY_TXT)){
                echo $row->SUMMARY_TXT;
              }else{
                echo ($row->IMONTH)."/".($row->IDAY)."/".($row->IYEAR).": ";
                if(isset($row->MOTIVE)){
                  echo $row->MOTIVE;
                }
                echo "\n(No other information)";
              }
              if(isset($row->WEAPON_TYPE_TXT)){
                echo "<p>Weapon: ".$row->WEAPON_TYPE_TXT;
              }
              if(isset($row->NHOSTKID)){
                echo "<p>Hostages: ".$row->NHOSTKID;
                if($row->NDAYS > 0){
                  echo " for ".$row->NDAYS." days";
                }
              }
              if(isset($row->TARGET)){
                echo "<p>Target: ".$row->TARGET;
              }
              echo "</p>";
              echo "</div>";
          }
        }

      }
      
      $spec = new q2;
	  
	  // Note that the distance calculation is only performed on (NUM_LIST_ROWS) tuples 
      $query = "SELECT EVENT_ID, IDAY, IMONTH, IYEAR, SUMMARY_TXT, CITY, COUNTRY_TXT,  
	  (ASIN(SQRT(SIN((".$latitude."-latitude)/(57.29578*2))*SIN((".$latitude."-latitude)/(57.29578*2))
                         + COS(latitude/57.29578)
                         * COS(".$latitude."/57.29578)
                         * SIN((".$longitude."-longitude)/(57.29578*2))*SIN((".$longitude."-longitude)/(57.29578*2))
                       ))) AS DISTANCE
         FROM EVENTS, LOCATIONS, COUNTRY
         WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID
         AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID AND " .$event_id_queries ." ORDER BY distance ASC";
         
      //echo $query;

      oracle_query($query, $spec);
    }
    ?>
  </div>

</div>

</body>

</html>
