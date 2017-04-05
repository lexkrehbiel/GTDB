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
      var jsonData = $.ajax({
          url: "getData.php",
          dataType: "json",
          async: false
          }).responseText;

      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(jsonData);

      // Instantiate and draw our chart, passing in some options.
      var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
      chart.draw(data, {width: 800, height: 480});
    }
    </script>
</head>

<body>
  <?php
    $categories = array("Month","Attack Type","Weapon Type","Target Type","Country","City","Group","Success","Suicide")
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
  <h1>Bar Chart</h1>
  <h3>Category to Compare</h3>
  <?php
    $locationQuery = "";
    $timeQueryBefore = "";
    $timeQueryAfter = "";
    $keywordQuery = "";
    $allConstraints = "";
    $excessSets = "";
    $excessJoins = "";
    $resultsNum = 0;
    $hostageCountQuery = "";
    $hostageLengthQuery = "";
    $weaponQuery = "";
    $targetQuery = "";

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
      if(isset($_POST["add_criteria"])){
          $criteria_count++;
      } else if(isset($_POST["remove_criteria"])){
          $criteria_count--;
      } else {

        //process the keyword
        if(isset($_POST['keyword'])){
          $keywordQuery = " AND EVENTS.SUMMARY_TXT LIKE '%".$_POST['keyword']."%' ";
        } else {
          $keywordQuery = "";
        }

        //process the other criteria
        unset($_POST['Hostages']);

        for($crit_proc = 0; $crit_proc<=$criteria_count; $crit_proc++){
          $attrStr = "attribute".$crit_proc;
          $valStr = "value".$crit_proc;


          if(isset($_POST[$attrStr]) && isset($_POST[$valStr]) && strlen($_POST[$valStr])>0){
            $attribute = $_POST[$attrStr];
            $value = $_POST[$valStr];
            switch($attribute){
              case "Location":
                $locationQuery = " AND (COUNTRY_TXT ='".$value."' OR CITY ='".$value."') ";
                break;
              case "Time: Before":
                list($month,$day,$year) = explode('/', $value);
                $inputDate = 10000*$year+100*$month+$day;
                $dbDate = "10000*IYEAR+100*IMONTH+IDAY";
                $timeQueryBefore = " AND ".$dbDate." < ".$inputDate;
                break;
              case "Time: After":
                list($month,$day,$year) = explode('/', $value);
                $inputDate = 10000*$year+100*$month+$day;
                $dbDate = "10000*IYEAR+100*IMONTH+IDAY";
                $timeQueryAfter = " AND ".$dbDate." > ".$inputDate;
                break;
              case "Hostages: Number of":
                if(!isset($_POST['Hostages'])){
                  $excessSets = $excessSets.", HOSTAGE_SITUATIONS";
                  $excessJoins = $excessJoins." AND EVENTS.HOSTAGE_SITUATION_ID = HOSTAGE_SITUATIONS.HOST_SIT_ID";
                }
                $_POST['Hostages'] = 'yes';
                $excessJoins = $excessJoins." AND EVENTS.HOSTAGE_SITUATION_ID = HOSTAGE_SITUATIONS.HOST_SIT_ID";
                $hostageCountQuery = " AND NHOSTKID >= ".$value;
              break;
              case "Hostages: Days":
                if(!isset($_POST['Hostages'])){
                  $excessSets = $excessSets.", HOSTAGE_SITUATIONS";
                  $excessJoins = $excessJoins." AND EVENTS.HOSTAGE_SITUATION_ID = HOSTAGE_SITUATIONS.HOST_SIT_ID";
                }
                $_POST['Hostages'] = 'yes';
                $hostageCountQuery = " AND NDAYS >= ".$value;
              break;
              case "Weapon":
                $excessSets = $excessSets.", WEAPON_TYPE, EVENTS_WEAPONS";
                $excessJoins = $excessJoins." AND EVENTS.EVENT_ID = EVENTS_WEAPONS.EVENT_ID AND EVENTS_WEAPONS.WEAPON_TYPE_ID = WEAPON_TYPE.WEAPON_TYPE_ID ";
                $weaponQuery = " AND WEAPON_TYPE_TXT LIKE '%".$value."%' ";
              break;
              case "Target":
                $excessSets = $excessSets.", EVENTS_TARGETS, TARGETS";
                $excessJoins = $excessJoins." AND EVENTS.EVENT_ID = EVENTS_TARGETS.EVENT_ID AND EVENTS_TARGETS.TARGET_ID = TARGETS.TARGET_ID";
                $targetQuery = " AND TARGETS.TARGET LIKE '%".$value."%' ";
              break;
            }

          }
        }
        $allConstraints = $keywordQuery.$timeQueryBefore.$timeQueryAfter.$hostageCountQuery.$locationQuery.$weaponQuery.$targetQuery;
      }
    }

  ?>
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
  <div class="box" style="height:40em">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Graph</p>
  </h4>
    <div id="chart_div"></div>
  </div>

</div>

</body>

</html>
