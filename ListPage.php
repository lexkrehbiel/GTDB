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
  
	// Query variables for final SQL statement
    $locationQuery = "";
    $timeQueryBefore = "";
    $timeQueryAfter = "";
    $keywordQuery = "";
    $allConstraints = "";
    $excessJoins = "";
    $resultsNum = 0;
    $hostageCountQuery = "";
    $hostageLengthQuery = "";
	$casualtiesQuery = "";
    $weaponQuery = "";
    $targetQuery = "";
	$groupsQuery = "";

	// Checks if the value is empty or not and sets the value accordingly
    function ifSetElseEmpty($valueName){
      if(!empty($_POST[$valueName])){
        return $_POST[$valueName];
      } else {
        return "";
      }
    }

	// Keep track of number of constraints for form submission
    $criteria_count = 0;
    if($_SERVER["REQUEST_METHOD"] == "POST") {
      $criteria_count = isset($_POST['criteria_count']) ? $_POST['criteria_count'] : 0;
	  
	  // Add criteria if "+" button is pushed
      if(isset($_POST["add_criteria"])){
          $criteria_count++;
		  
	  // Remove criteria if "-" button pushed (don't let count go below 0)
      } else if(isset($_POST["remove_criteria"]) && $criteria_count>0){
          $criteria_count--;
      } else {
		  
        // Keyword processing - if no keyword is entered, don't add the summary constraint
        if(!empty($_POST['keyword'])){
          $keywordQuery = " AND UPPER(EVENTS.SUMMARY_TXT) LIKE UPPER('%".$_POST['keyword']."%') ";
        } else {
          $keywordQuery = "";
        }

        // Criteria processing
        unset($_POST['Hostages']); // Make sure hostage_situations isn't included twice

        for($crit_proc = 0; $crit_proc<=$criteria_count; $crit_proc++){
          $attrStr = "attribute".$crit_proc;
          $valStr = "value".$crit_proc;

          if(!empty($_POST[$attrStr]) && !empty($_POST[$valStr]) && strlen($_POST[$valStr])>0){
            $attribute = $_POST[$attrStr];
            $value = $_POST[$valStr];
            switch($attribute){
              case "Location":
                $locationQuery = " AND (UPPER(COUNTRY_TXT) = UPPER('".$value."') OR UPPER(CITY) = UPPER('".$value."') OR UPPER(PROV_STATE) = UPPER('".$value."') ) ";
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
					 $excessJoins = $excessJoins." LEFT OUTER JOIN HOSTAGE_SITUATIONS ON EVENTS.HOSTAGE_SITUATION_ID=hostage_situations.host_sit_id";
				}
				$_POST['Hostages'] = 'INCLUDED';
                $hostageCountQuery = " AND NHOSTKID >= ".$value;
                break;
              case "Hostages: Days":
                if(!isset($_POST['Hostages'])){
					$excessJoins = $excessJoins." LEFT OUTER JOIN HOSTAGE_SITUATIONS ON EVENTS.HOSTAGE_SITUATION_ID=hostage_situations.host_sit_id";
				}
				$_POST['Hostages'] = 'INCLUDED';
                $hostageLengthQuery = " AND NDAYS >= ".$value;
                break;
              case "Weapon":
                $excessJoins = $excessJoins." NATURAL JOIN EVENTS_WEAPONS NATURAL JOIN WEAPON_TYPE NATURAL JOIN WEAPON_SUBTYPE";
                $weaponQuery = " AND (UPPER(WEAPON_TYPE_TXT) LIKE UPPER('%".$value."%') OR UPPER(WEAPON_SUBTYPE_TXT) LIKE UPPER('%".$value."%') )";
                break;
              case "Target":
                $excessJoins = $excessJoins." NATURAL JOIN EVENTS_TARGETS NATURAL JOIN TARGETS NATURAL JOIN TARGET_TYPE NATURAL JOIN TARGET_SUBTYPE";
                $targetQuery = " AND (UPPER(TARGET_TYPE.TYPE_TXT) LIKE UPPER('%".$value."%') OR UPPER(TARGET_SUBTYPE.SUBTYPE_TXT) LIKE UPPER('%".$value."%')
				OR UPPER(TARGETS.TARGET) LIKE UPPER('%".$value."%') )";
                break;
			  case "Casualties":
				$casualtiesQuery = " AND (N_KILL+N_WOUND)>=".$value;
			    break;
			  case "Groups":
			    $excessJoins = $excessJoins." NATURAL JOIN EVENTS_GROUPS NATURAL JOIN GROUPS NATURAL JOIN GROUP_SUBNAMES";
				$groupsQuery = " AND UPPER(GROUPS.GROUP_NAME) LIKE UPPER('%".$value."%')";
			    break;
            }

          }
        }
		// Include every constraint in the final query
        $allConstraints = $keywordQuery.$timeQueryBefore.$timeQueryAfter.$hostageCountQuery.$hostageLengthQuery.$locationQuery.$weaponQuery.$targetQuery.$casualtiesQuery.$groupsQuery;
      }
    }

  ?>
  <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method = POST>
    <input type="text" name="keyword" value="<?php echo ifSetElseEmpty("keyword");?>"></input>
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
    <p style="margin-right: 7px; margin-top: 30px">Search Results
      </p>
  </h4>
  <?php

    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {

	// This class implements an output method so that oracle_query can be called
    class q {
      function output($statement){
		    $lnum = 0; //keep track of collapsible divs
        while ($row = oci_fetch_object($statement)) {
            echo "<div class='listitem'>"; //Holds a full entry for an event
            echo "<a data-toggle='collapse' href='#collapse".$lnum."' style='text-decoration: none'> 
			<h5 title='".$row->EVENT_ID."'>".($row->CITY).", ".($row->COUNTRY_TXT)."</h5></a>"; //set title to event_id, show city and country
			echo "<div id='collapse".$lnum."' class='collapse'>"; // Holds rest of information, collapsed by default
            echo "<p>";
            if(isset($row->SUMMARY_TXT)){
              echo $row->SUMMARY_TXT;
            }else{
              echo ($row->IMONTH)."/".($row->IDAY)."/".($row->IYEAR).": ";
              if(isset($row->MOTIVE)){
                echo $row->MOTIVE;
              }
              echo "(No summary available)";
            }
            if(isset($row->WEAPON_TYPE_TXT)){
              echo "<p><b>Weapon: </b>".$row->WEAPON_TYPE_TXT.": ".$row->WEAPON_SUBTYPE_TXT;
            }
            if(isset($row->NHOSTKID)){
              echo "<p><b>Hostages: </b>".$row->NHOSTKID;
              if($row->NDAYS > 0){
                echo " for ".$row->NDAYS." days";
              }
            }
            if(isset($row->TARGET)){
              echo "<p><b>Target: </b>".$row->TARGET;
            }
			if(isset($row->N_KILL) && isset($row->N_WOUND)){
				$casualties = $row->N_KILL+$row->N_WOUND;
				echo "<p><b>Casualties: </b>".$casualties;
			}
			if(isset($row->PROP_VALUE)){
				if ($row->PROP_VALUE != -99){
					echo "<p><b>Property Damage: </b>".$row->PROP_VALUE;
				} else {
					echo "<p><b>Property Damage: </b> Unknown";
				}
			}
            echo "</p>";
            echo "</div>"; //collapsible info
			echo "</div>"; //listitem container
			$lnum++;
        }
      }
    }
    include("include.php");
    $spec = new q;
	
	// I want to be able to display consistent information for each event, but idk how to make the proper joins to account for multiplicity of weapons, groups, targets, etc
	/*$excessJoins = "NATURAL LEFT JOIN EVENTS_TARGETS NATURAL LEFT JOIN TARGETS
					NATURAL LEFT JOIN TARGET_TYPE NATURAL LEFT JOIN TARGET_SUBTYPE 
					NATURAL LEFT JOIN EVENTS_WEAPONS NATURAL LEFT JOIN WEAPON_TYPE 
					NATURAL LEFT JOIN WEAPON_SUBTYPE 
					NATURAL LEFT JOIN EVENTS_GROUPS NATURAL LEFT JOIN GROUPS
					NATURAL LEFT JOIN GROUP_SUBNAMES NATURAL LEFT JOIN EVENTS_ATTACK_TYPES 
					NATURAL LEFT JOIN ATTACK_TYPES LEFT OUTER JOIN HOSTAGE_SITUATIONS 
					ON EVENTS.HOSTAGE_SITUATION_ID=hostage_situations.host_sit_id";*/ 
					
    $query = "SELECT DISTINCT * FROM EVENTS NATURAL JOIN LOCATIONS NATURAL JOIN COUNTRY NATURAL JOIN REGION "
              .$excessJoins.  " WHERE event_id>0 "
              .$allConstraints. " AND ROWNUM < 51";

    //echo $query;

    oracle_query($query, $spec);
  }
  ?>

  </div>

</div>

</body>

</html>
