<!--?php
include("chartsPHP/config.php");
include("chartsPHP/lib/inc/chartphp_dist.php");
include("config.php");

$p = new chartphp();

$p->data_sql = "select country_txt, count(event_id) as attacks
				from country natural join location natural join events
				where country_txt= 'Peru' or country_txt= 'China' or country_txt='Iran'
				group by country_txt";

$p->chart_type = "bar";

$p->title = "Total Attacks per Country";
$p->xlabel = "country_txt";
$p->ylabel = "attacks";

$out = $p->render('c1');
?-->

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

    function ifSetElseEmpty($valueName){
      if(isset($_POST[$valueName])){
        return $_POST[$valueName];
      } else {
        return "";
      }
    }

	// Keep track of number of criteria
    $criteria_count = 0;
    if($_SERVER["REQUEST_METHOD"] == "POST") {
      $criteria_count = isset($_POST['criteria_count']) ? $_POST['criteria_count'] : 0;
      if(isset($_POST["add_criteria"])){
          $criteria_count++;
      } else if(isset($_POST["remove_criteria"])){
          $criteria_count--;
      } else {

        // Process the category attribute
        if(isset($_POST['bar_attribute'])){
          $cat_type = $_POST['bar_attribute'];
          switch($_POST['bar_attribute']){
            case "Weapon Type":
              $cat_type = "WEAPON_TYPE_TXT";
              $sets[] = "WEAPON_TYPE";
              $sets[] = "EVENTS_WEAPONS";
              $joins[] = "EVENTS.EVENT_ID = EVENTS_WEAPONS.EVENT_ID";
              $joins[] = "EVENTS_WEAPONS.WEAPON_TYPE_ID = WEAPON_TYPE.WEAPON_TYPE_ID";
            break;
            case "Target Type":
              $cat_type = "TYPE_TXT";
              $sets[] = "TARGET_TYPE";
              $sets[] = "TARGETS";
              $sets[] = "EVENTS_TARGETS";
              $joins[] = "EVENTS.EVENT_ID = EVENTS_TARGETS.EVENT_ID";
              $joins[] = "EVENTS_TARGETS.TARGET_ID = TARGETS.TARGET_ID";
              $joins[] = "TARGETS.TYPE_ID = TARGET_TYPE.TYPE_ID";
            break;
            case "Country":
              $cat_type = "COUNTRY_TXT";
              $sets[] = "LOCATIONS";
              $sets[] = "COUNTRY";
              $joins[] = "EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID";
              $joins[] = "LOCATIONS.COUNTRY_ID = COUNTRY.COUNTRY_ID";
            break;
            case "Attack Type":
              $cat_type = "ATTACK_TYPE_TXT";
              $sets[] = "EVENTS_ATTACK_TYPES";
              $sets[] = "ATTACK_TYPES";
              $joins[] = "EVENTS.EVENT_ID = EVENTS_ATTACK_TYPES.EVENT_ID";
              $joins[] = "EVENTS_ATTACK_TYPES.ATTACK_TYPE_ID = ATTACK_TYPES.ATTACK_TYPE_ID";
            break;
            case "Month":
              $cat_type = "WORD";
              $sets[] = "MONTH_WORDS";
              $joins[] = "MONTH_WORDS.VAL = IMONTH";
            break;
            case "Group":
              $cat_type = "GROUP_NAME";
              $sets[] = "EVENTS_GROUPS";
              $sets[] = "GROUPS";
              $joins[] = "EVENTS.EVENT_ID = EVENTS_GROUPS.EVENT_ID";
              $joins[] = "EVENTS_GROUPS.GROUP_ID = GROUPS.GROUP_ID";
              $constraints[] = "ROWNUM < 3435";
            break;
            case "City":
              $cat_type = "CITY";
              $sets[] = "LOCATIONS";
              $joins[] = "EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID";
            break;
            case "Success of Attack":
              $cat_type = "WORD";
              $sets[] = "YES_NO";
              $joins[] = "SUCCESSFUL_ATTACK = YES_NO.VAL";
            break;
            case "Suicide":
              $cat_type = "WORD";
              $sets[] = "YES_NO";
              $joins[] = "SUICIDE = YES_NO.VAL";
            break;
            default:
              $cat_type = "SUCCESSFUL_ATTACK";
          }
        }

        // Process the search criteria
        unset($_POST['Hostages']); // Keep track of whether there is already a hostage-related criteria

		$criteria_txt = ""; // Keep track of this to display more information in our graph header
		
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
				$sets = "EVENTS_GROUPS";
				$sets = "GROUPS";
				$sets = "GROUP_SUBNAMES";
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
    $joins = array_unique($joins);
    $count = count($joins);
    if($count > 0){ $allJoins = " WHERE ";}
    foreach($joins as $join){
      $allJoins = $allJoins.$join;
      if(--$count > 0){
        $allJoins = $allJoins." AND ";
      }
    }

    $allConstraints = "";
    $constraints = array_unique($constraints);
    $count = count($constraints);
    foreach($constraints as $constraint){
      $allConstraints = " AND ".$constraint.$allConstraints;
    }
    // //$cat_type = "weapon_type_txt";
    $query = "SELECT ".$cat_type." as VALUE, COUNT(".$cat_type.") as COUNT FROM"
             .$allSets.$allJoins.$allConstraints
             ." GROUP BY ".$cat_type
             ." ORDER BY COUNT DESC";
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

      var quer = "<?php echo $query; ?>";

      var jsonData = $.ajax({
          type: "POST",
          data: {query: quer},
          url: "getData.php",
          dataType: "json",
          async: false
          }).responseText;

      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(jsonData);

      // Instantiate and draw our chart, passing in some options.
      var piechart = new google.visualization.PieChart(document.getElementById('piechart_div'));
      piechart.draw(data, {height: 480});

      var colchart = new google.visualization.ColumnChart(document.getElementById('colchart_div'));
      colchart.draw(data, {height: 480});
    }
    </script>
</head>

<body>
  <?php
    $categories = array("Month","Attack Type","Weapon Type","Target Type","Country","City","Group","Success of Attack","Suicide")
  ?>

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
  <h1>Charts</h1>
  <h3>Category to Compare</h3>
  <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = POST>
    <select type="text" name="bar_attribute">
    <?php

      foreach($categories as $attr){
        $oldValue = ifSetElseEmpty("bar_attribute");
        echo "<option value=\"".$attr."\"";
        if($oldValue == $attr){
          echo " selected ";
        }
        echo ">".$attr."</option>";
      }
    ?>
  </select>
    <button type="submit" name="search"><i class="material-icons">search</i></button>

  <div class="box">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Search Criteria:
    </p>
    <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = post>

        <button type="submit" name="add_criteria"><i class="material-icons" >add</i></button>
        <button type="submit" name="remove_criteria"><i class="material-icons" >remove</i></button>
        <input type = "hidden" name = "criteria_count" value = "<?php print $criteria_count; ?>"; />
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
  <div class="box">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">
      <?php

	  
        if(isset($_POST['bar_attribute'])){
          echo "Breakdown of attacks by " . $_POST['bar_attribute'] .$criteria_txt .":";
        } else {
          echo "Graph";
        }
      ?>
    </p>
  </h4>
    <div>
    <div id="piechart_div"></div>
    <div id="colchart_div"></div>
  </div>
  </div>

</div>

</body>

</html>
