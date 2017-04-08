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
      list($latitude,$longitude) = explode(', ', $_POST["location"]);
      $distance = "2*6367.4445*asin(power(power(sin((".$latitude."-LATITUDE)/2),2)+cos(".$latitude.")*cos(LATITUDE)*power(sin((".$longitude."-LONGITUDE)/2),2)),.5)";

      $query = "SELECT * FROM(SELECT IDAY,IMONTH,IYEAR,SUMMARY_TXT,CITY,COUNTRY_TXT,"
        .$distance."AS DISTANCE FROM EVENTS,LOCATIONS,COUNTRY
        WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID
        AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID
        ORDER BY DISTANCE DESC) WHERE ROWNUM < 21";

        // $query = "SELECT DISTINCT * FROM EVENTS, LOCATIONS, COUNTRY "
        //           .$excessSets." WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID "
        //           .$excessJoins." AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID "
        //           .$allConstraints. " AND ROWNUM < 51";
    }
  ?>
  <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = POST>
    <input type="text" name="location" value="<?php echo ifSetElseEmpty("location");?>"></input>
    <button type="submit" name="search"><i class="material-icons">search</i></button>
  </form>

  <div class="box">
    <?php

      if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {


      class q {
        function output($statement){
          echo "<h3> Your danger rating is: </h3>";
          echo "<h3 class=\"danger\">";
          $strDate = "12 31 2015";
          list($m,$d,$y) = explode(" ",$strDate);
          $date = 10000*$y+100*$m+$d;
          $dateAvg = 0;
          $distAvg = 0;
          $row = oci_fetch_object($statement);
          $arr = array();
          //echo " today is ".$date;
          while ($row = oci_fetch_object($statement)) {
            $eventDate = $row->IDAY+$row->IMONTH*100+$row->IYEAR*10000;
            //echo "   ".$eventDate;
            $dateAvg += $date-$eventDate;
            $distAvg += $row->DISTANCE;
            $arr[] = $row;
          }
          $distAvg = $distAvg / (40*pi());
          $dateAvg = $dateAvg/ (20*451130);
          $danger = 5-25*pow($dateAvg,2)*pow($distAvg,.25);
          //echo "DISTAVG: ".$distAvg."DATEAVG: ".$dateAvg."\n ";
          if($danger > 5){
            $danger = 4.99;
          } else if($danger < 0 ){
            $danger = 0.01;
          }
          echo "<h3 class=\"danger\">";
          $final = round(1000*$danger)/1000;
          echo $final." out of 5";
          echo "</h3> </h4> </div> <div class=\"box\">";
          //echo "AVG DIST: ".$distAvg." AVG TIME: ".$dateAvg." DANGER: ".$danger;

          foreach($arr as $row){
              $distance = round($row->DISTANCE * 2 * 6371*100)/100;
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
              //echo "AVG DIST: ".$distAvg." AVG TIME: ".$dateAvg;
          }
        }

      }
      include("include.php");
      $spec = new q;

      list($latitude,$longitude) = explode(', ', $_POST['location']);
      //$distance = "2*6367.4445*asin(power(power(sin((".$latitude."-LATITUDE)/2),2)+cos(".$latitude.")*cos(LATITUDE)*power(sin((".$longitude."-LONGITUDE)/2),2)),.5)";

      //echo "<p>".$distance."</p>";

      // $query = "SELECT * FROM (SELECT IDAY, IMONTH, IYEAR, SUMMARY_TXT, CITY, COUNTRY_TXT, "
      //   .$distance." AS DISTANCE FROM EVENTS, LOCATIONS, COUNTRY
      //   WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID
      //   AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID
      //   ORDER BY DISTANCE DESC) WHERE ROWNUM < 21";

      $query = "SELECT DISTANCE, IDAY, IMONTH, IYEAR, SUMMARY_TXT, CITY, COUNTRY_TXT
              FROM (SELECT Latitude, longitude, IDAY, IMONTH, IYEAR, SUMMARY_TXT, CITY, COUNTRY_TXT,
              (ASIN(SQRT(SIN((".$latitude."-latitude)/(57.29578*2))*SIN((".$latitude."-latitude)/(57.29578*2))
                         + COS(latitude/57.29578)
                         * COS(".$latitude."/57.29578)
                         * SIN((".$longitude."-longitude)/(57.29578*2))*SIN((".$longitude."-longitude)/(57.29578*2))
                       ))) AS distance
         FROM EVENTS, LOCATIONS, COUNTRY
         WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID
         AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID
         ORDER BY distance ASC)
         WHERE ROWNUM < 21";
         //LIMIT 25";
      //echo $query;

      oracle_query($query, $spec);
    }
    ?>
  </div>

</div>

</body>

</html>
