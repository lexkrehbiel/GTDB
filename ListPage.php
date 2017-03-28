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
  <h1>List</h1>
  <h3>Search by Keyword</h3>
  <?php
    $locationQuery = "";
    $timeQueryBefore = "";
    $timeQueryAfter = "";
    $keywordQuery = "";
    $allConstraints = "";
    $excessSets = "";
    $excessJoins = "";
    $resultsNum = 0;
    $hostageQuery = "";

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
              case "Hostages":
                $excessSets = $excessSets.", Hostage_Situations ";
                $excessJoins = $excessJoins." AND EVENTS.HOSTAGE_SITUATION_ID = HOSTAGE_SITUATIONS.HOST_SIT_ID";
                $hostageQuery = " AND NHOSTKID >= ".$value;
              break;
            }

          }
        }
        $allConstraints = $keywordQuery.$timeQueryBefore.$timeQueryAfter.$hostageQuery.$locationQuery;
      }
    }

  ?>
  <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = POST>
    <input type="text" name="keyword"></input>
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

          foreach($allConstraintTypes as $attr){
            echo "<option value=\"".$attr ."\">".$attr."</option>";
          }


          echo "</select>
            <p style=\"margin-left:9px\"> Value</p>
            <input type=\"text\" name=\"value".$criteria_num."\"></input>
          </h6>";
        }
     ?>
  <?php

  ?>
  </div>
  </form>

  <div class="box">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Search Results
      </p>
  </h4>
  <?php

    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {


    class q {
      function output($statement){
        $row = oci_fetch_object($statement);
        while ($row = oci_fetch_object($statement)) {
            echo "<div class=\"listitem\">";
            echo "<h5>".($row->CITY).", ".($row->COUNTRY_TXT)."</h5>";
            echo "<p>";
            if(strlen($row->SUMMARY_TXT) > 0){
              echo $row->SUMMARY_TXT;
            }else{
              echo ($row->IMONTH)."/".($row->IDAY)."/".($row->IYEAR).": ";
              if(strlen($row->MOTIVE) > 0){
                echo $row->MOTIVE;
              }
              echo "\n(No other information)";
            }
            echo "</p>";
        }
      }
    }
    include("include.php");
    $spec = new q;


    $query = "SELECT DISTINCT * FROM EVENTS, LOCATIONS, COUNTRY "
              .$excessSets." WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID "
              .$excessJoins." AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID "
              .$allConstraints. " AND ROWNUM < 51";

    oracle_query($query, $spec);
  }
  ?>

  </div>

</div>

</body>

</html>
