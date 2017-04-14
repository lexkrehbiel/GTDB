#!/usr/local/bin/php
<!DOCTYPE html>

<html lang = "en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="styles/listpage.css"/>
  <link rel="stylesheet" type="text/css" href="styles/popup.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>

<div class = "container">
  <div style="text-align:center; margin-top:10px">
    <h8 class="menubar">
        <button style="margin-top: 18px" onclick="javascript:document.location='index.php'"><i class="material-icons" style>home</i></button>
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
    $sets = array("EVENTS");
    $joins = array();
    $constraints = array();
    $queries = array();
    $resultsNum = 0;
    $criteria_txt = "";
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
      if(isset($_POST["add_criteria"])&& $criteria_count<9){
          $criteria_count++;

	  // Remove criteria if "-" button pushed (don't let count go below 0)
      } else if(isset($_POST["remove_criteria"]) && $criteria_count>0){
          $criteria_count--;
      } else {

        // Keyword processing - if no keyword is entered, don't add the summary constraint
        if(!empty($_POST['keyword'])){
          $constraints[] = "UPPER(EVENTS.SUMMARY_TXT) LIKE UPPER('%".$_POST['keyword']."%')";
        }

		 // Show user feedback on what they're searching
        // Criteria processing
        unset($_POST['Hostages']); // Make sure hostage_situations isn't included twice

        for($crit_proc = 0; $crit_proc<=$criteria_count; $crit_proc++){
          $attrStr = "attribute".$crit_proc;
          $valStr = "value".$crit_proc;

          if(isset($_POST[$attrStr]) && !empty($_POST[$valStr])){
            $attribute = $_POST[$attrStr];
            $value = $_POST[$valStr];
            switch($attribute){
              case "Location":
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

		$sets[] = "LOCATIONS";
		$sets[] = "COUNTRY";
		$joins[] = "EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID";
		$joins[] = "LOCATIONS.COUNTRY_ID = COUNTRY.COUNTRY_ID";

		// Include every constraint in the final query
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

		$query = "SELECT DISTINCT * FROM"
				 .$allSets.$allJoins.$allConstraints
				 . " AND ROWNUM < 51";
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
    <p style="margin-right: 7px; margin-top: 30px">
	<?php
	echo "Search Results"  . substr($criteria_txt,1) . ":";
	?>
      </p>
  </h4>
  <?php

    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {
	include("include.php");

	// This class implements an output method so that oracle_query can be called
    class outputObject {
      function output($statement){
		$lnum = 0; //keep track of collapsible divs
        while ($row = oci_fetch_object($statement)) {
			// Start of list item entry
            echo "<div class='listitem'>";
			// Start of collapsible <a> part
            echo "<a data-toggle='collapse' href='#collapse".$lnum."' style='text-decoration: none'>";
			// Header of the list item, which is clicked to collapse
			echo "<h5 title='".$row->EVENT_ID."'>".($row->IMONTH)."/".($row->IDAY)."/".($row->IYEAR).":
			".($row->CITY).", ".($row->COUNTRY_TXT);
			echo "</h5></a>";
			// Summary and information of the list item
			echo "<div id='collapse".$lnum."' class='collapse'>";
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
            /*if(isset($row->WEAPON_TYPE_TXT)){
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
			}*/

			// THIS LINE OPENS THE EVENT_ID SPECIFIC PAGE
			echo "<p><a href='#;' class='button' onclick=\"window.open('getEvent.php?q=" . $row->EVENT_ID . "')\">See more info...</a></p>";

            echo "</p>";
            echo "</div>"; //collapsible info
			echo "</div>"; //listitem container
			$lnum++;
        }
      }
    }
	$spec = new outputObject();
	//$query = "SELECT * FROM
	oracle_query($query, $spec);
  }

  ?>
	<div class="popup" style="margin-top:20px" style="margin-top:20px" onclick="showTuplePopup()">Show Query
	  <span class="popuptext" id="showquery">
		<?php echo $query?>
	  </span>
	</div>
  </div>

	<script>
	// When the user clicks on <div>, open the popup
	function showTuplePopup() {
		var popup = document.getElementById("showquery");
		popup.classList.toggle("show");
	}
	</script>
</div>

</body>

</html>
