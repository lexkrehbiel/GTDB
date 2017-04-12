<!DOCTYPE html>

<html lang = "en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="styles/chartpage.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <?php

    $sets = array("EVENTS");
    $joins = array();
    $constraints = array();
    $queries = array();
    $resultsNum = 0;
    $cat_type = "";
    $startYear = 1970;
    $endYear = 2015;
    $array = array(array("Type","Count"));
    $criteria_txt = "";

    function ifSetElseEmpty($valueName){
      if(isset($_POST[$valueName])){
        return $_POST[$valueName];
      } else {
        return "";
      }
    }

    $criteria_count = 0;
    if($_SERVER["REQUEST_METHOD"] == "POST") {
      $criteria_count = isset($_POST['criteria_count']) ? $_POST['criteria_count'] : 0;
      if(isset($_POST["add_criteria"]) && $criteria_count<9){
          $criteria_count++;
      } else if(isset($_POST["remove_criteria"]) && $criteria_count>0){
          $criteria_count--;
      } else {

        //process the other criteria
        unset($_POST['Hostages']);
		 // Keep track of this to display more information in our graph header

        for($crit_proc = 0; $crit_proc<=$criteria_count; $crit_proc++){
          $attrStr = "attribute".$crit_proc;
          $valStr = "value".$crit_proc;

		  // For every criteria and value, add the necessary sets, joins, and constraints
          if(isset($_POST[$attrStr]) && !empty($_POST[$valStr])){
            $attribute = $_POST[$attrStr];
            $value = $_POST[$valStr];
            switch($attribute){
              case "Location":
                $sets[] = "COUNTRY";
                $sets[] = "LOCATIONS";
                $joins[] = "EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID";
                $joins[] = "LOCATIONS.COUNTRY_ID = COUNTRY.COUNTRY_ID";
                $constraints[] = "(UPPER(COUNTRY_TXT) =UPPER('".$value."') OR UPPER(CITY) = UPPER('".$value."') OR UPPER(PROV_STATE) = UPPER('".$value."'))";
				$criteria_txt = $criteria_txt . ", in " .$value;
                break;
              case "Time: Before":
                list($month,$day,$year) = explode('/', $value);
                $inputDate = 10000*$year+100*$month+$day;
                $dbDate = "10000*IYEAR+100*IMONTH+IDAY";
                $constraints[] = $dbDate." < ".$inputDate;
				$criteria_txt = $criteria_txt . ", before " .$value ;
                break;
              case "Time: After":
                list($month,$day,$year) = explode('/', $value);
                $inputDate = 10000*$year+100*$month+$day;
                $dbDate = "10000*IYEAR+100*IMONTH+IDAY";
                $constraints[] = $dbDate." > ".$inputDate;
				$criteria_txt = $criteria_txt . ", after " .$value ;
                break;
              case "Hostages: Number of":
                $sets[] = "HOSTAGE_SITUATIONS";
                $joins[] = "EVENTS.HOSTAGE_SITUATION_ID = HOSTAGE_SITUATIONS.HOST_SIT_ID";
                $constraints[] = "NHOSTKID >= ".$value;
				$criteria_txt = $criteria_txt . ", with " .$value ." or more hostage(s)";
              break;
              case "Hostages: Days":
                $sets[] = "HOSTAGE_SITUATIONS";
                $joins[] = "EVENTS.HOSTAGE_SITUATION_ID = HOSTAGE_SITUATIONS.HOST_SIT_ID";
                $constraints[] = "NDAYS >= ".$value;
				$criteria_txt = $criteria_txt . ", where hostages were kept for " .$value. " day(s) or more";

              break;
              case "Weapon":
                $sets[] = "WEAPON_TYPE";
				$sets[] = "WEAPON_SUBTYPE";
                $sets[] = "EVENTS_WEAPONS";
                $joins[] = "EVENTS.EVENT_ID = EVENTS_WEAPONS.EVENT_ID";
                $joins[] = "EVENTS_WEAPONS.WEAPON_TYPE_ID = WEAPON_TYPE.WEAPON_TYPE_ID ";
				$joins[] = "EVENTS_WEAPONS.WEAPON_SUBTYPE_ID = WEAPON_SUBTYPE.WEAPON_SUBTYPE_ID ";
                $constraints[] = "(UPPER(WEAPON_TYPE_TXT) LIKE UPPER('%".$value."%')
								OR UPPER(WEAPON_SUBTYPE_TXT) LIKE UPPER('%".$value."%'))";
				$criteria_txt = $criteria_txt . ", committed with (a) " .$value;

              break;
              case "Target":
                $sets[] = "EVENTS_TARGETS";
                $sets[] = "TARGETS";
				$sets[] = "TARGET_TYPE";
				$sets[] = "TARGET_SUBTYPE";
                $joins[] = "EVENTS.EVENT_ID = EVENTS_TARGETS.EVENT_ID";
                $joins[] = "EVENTS_TARGETS.TARGET_ID = TARGETS.TARGET_ID";
				$joins[] = "TARGETS.TYPE_ID = TARGET_TYPE.TYPE_ID";
                $joins[] = "TARGETS.SUBTYPE_ID = TARGET_SUBTYPE.SUBTYPE_ID";
                $constraints[] = "(UPPER(TARGETS.TARGET) LIKE UPPER('%".$value."%')
								OR UPPER(TYPE_TXT) LIKE UPPER('%".$value."%')
								OR UPPER(SUBTYPE_TXT) LIKE UPPER('%".$value."%'))";
				$criteria_txt = $criteria_txt . ", targeting " .$value ;
			  break;
			  case "Casualties":
				$constraints[] = "(N_KILL+N_WOUND)>=".$value;
				$criteria_txt = $criteria_txt . ", with " .$value ." or more casualties";
			  break;
			  case "Groups":
				$sets[] = "EVENTS_GROUPS";
				$sets[] = "GROUPS";
				$sets[] = "GROUP_SUBNAMES";
				$joins[] = "EVENTS.EVENT_ID = EVENTS_GROUPS.EVENT_ID";
				$joins[] = "EVENTS_GROUPS.GROUP_ID = GROUPS.GROUP_ID";
				$joins[] = "EVENTS_GROUPS.GROUP_SUBNAME_ID = GROUP_SUBNAMES.GROUP_SUBNAME_ID";
				$constraints[] = "(UPPER(GROUP_NAME) LIKE UPPER('%".$value."%')
								OR UPPER(GROUP_SUBNAME) LIKE UPPER('%".$value."%'))";
				$criteria_txt = $criteria_txt . ", committed by " .$value ;
              break;
            }

          }
        }
    }

    $allSets = " ";
    $sets = array_unique($sets);
    $count = count($sets);
    foreach($sets as $set){
      $allSets = $allSets.$set;
      if(--$count > 0){
        $allSets = $allSets.", ";
      }
    }

    $allJoins = "";
    $joins = array_merge($joins,$constraints);
    $joins = array_unique($joins);
    $count = count($joins);
    if($count > 0){ $allJoins = " WHERE ";}
    foreach($joins as $join){
      $allJoins = $allJoins.$join;
      if(--$count > 0){
        $allJoins = $allJoins." AND ";
      }
    }

    $axisName = "";

    if($endYear - $startYear < 2){
      $date = "IMONTH";
      $axisName = "Month Number";
    } else {
      $date = "IYEAR";
      $axisName = "Year";
    }

    $query = "SELECT ".$date." as VALUE, COUNT(*) as COUNT FROM"
             .$allSets.$allJoins
             ." GROUP BY ".$date." ORDER BY ".$date." ASC";
             $connection = oci_connect('bickell',
                                       'M2n3ca1a1!1',
                                       '//oracle.cise.ufl.edu/orcl');

             $statement = oci_parse($connection, $query);
             oci_execute($statement);

             $lastValue = 0;
             $count = 0;
             while($row = oci_fetch_object($statement)){
               if($count > 0){
                 $diff = $row->VALUE-$lastValue;
                 while($diff > 1){
                   $lastValue = $lastValue+1;
                   $array[] = array($lastValue,0);
                   $diff = $row->VALUE-$lastValue;
                 }

               }
               $count++;
               $lastValue = $row->VALUE;
               $array[] = array($row->VALUE,$row->COUNT);
             }

             oci_free_statement($statement);
             oci_close($connection);
             $_POST['ready'] = "yes";

         }
  ?>

  <script src="chartsPHP/lib/js/jquery.min.js"></script>
  <script src="chartsPHP/lib/js/chartphp.js"></script>
  <link rel="stylesheet" href="chartsPHP/lib/js/chartphp.css">
  <!--Load the AJAX API-->
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
      <script type="text/javascript">
      // Load the Visualization API and the piechart package.
    google.charts.load('current', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      if("<?php echo (isset($_POST['ready']))?$_POST['ready']:''; ?>" == "yes"){


      var data = new google.visualization.arrayToDataTable(<?php echo json_encode($array,JSON_NUMERIC_CHECK)?>);

          var materialOptions = {
            hAxis: {title: "<?php echo $axisName;?>"},
            vAxis: {title: "Number of Attacks"},
            height: 480,
            series: {
              0: { color: 'orange' }
            }

          };

      // Instantiate and draw our chart, passing in some options.
      var chart = new google.visualization.LineChart(document.getElementById('linechart'));
      chart.draw(data, materialOptions);
    }

    }
    </script>
</head>

<body>

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
    <h1>Time Chart</h1>
    <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = POST>

    <div class="box">
    <h4>
      <p style="margin-right: 7px; margin-top: 30px">Search Criteria:
      </p>
      <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = post>

          <button type="submit" name="add_criteria"><i class="material-icons" >add</i></button>
          <button type="submit" name="remove_criteria"><i class="material-icons" >remove</i></button>
          <input type = "hidden" name = "criteria_count" value = "<?php print $criteria_count; ?>"; />
          <button type="submit" name="search"><i class="material-icons">search</i></button>
      </form>
    </h4>
    </h6>

       <?php
          for($criteria_num=0; $criteria_num <= $criteria_count; $criteria_num++){
            echo "<h6>
              <p> Constraint Type</p>
              <select name=\"attribute".$criteria_num."\">";

            if(!session_id()) session_start();
            include("global.php");
            $allConstraintTypes = $_SESSION['allConstraintTypes'];
            $oldValue = ifSetElseEmpty("attribute".$criteria_num);

            foreach($allConstraintTypes as $attr){
              echo "<option value=\"".$attr ."\"";
              if($oldValue == $attr){
                echo " selected ";
              }
              echo ">".$attr."</option>";
            }


            echo "</select>
              <p style=\"margin-left:9px\"> Value</p>
              <input type=\"text\" name=\"value".$criteria_num."\"";

            echo "value=\"".ifSetElseEmpty("value".$criteria_num)."\"";

            echo "></input>
            </h6>";
          }
       ?>
    </div>
    </form>

  <div class="box" style="height:40em">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">
	<?php
	echo "Breakdown of attacks " . substr($criteria_txt,1);
	?>
	</p>
  </h4>
  <div id="linechart">
  </div>

</div>

</body>

</html>
