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

// Class which defines output for base event and location info
class parseEvent{
	function output($statement){
		if ($row = oci_fetch_object($statement)){
			
			// Redefine and reformat missing or broken information
			if (isset($row->SUMMARY_TXT)){
				$row->SUMMARY_TXT = substr($row->SUMMARY_TXT,10);
			} else {$row->SUMMARY_TXT = "No summary available.";}
			
			if (isset($row->CITY) && !$row->CITY == "Unknown") {
				$row->CITY = $row->CITY . ", ";
			} else {$row->CITY = "";}
			
			if ($row->PROV_STATE != ".") {
			$row->PROV_STATE = $row->PROV_STATE . ", "; 
			} else {$row->PROV_STATE = "";}
			
			if (!isset($row->COUNTRY_TXT)) $row->COUNTRY_TXT = "No country location available.";
			
			if ($row->SUICIDE == 1) {
				$row->SUICIDE = "Yes";
			} else {$row->SUICIDE = "No";}
			
			if ($row->SUCCESSFUL_ATTACK == 1) {
				$row->SUCCESSFUL_ATTACK = "Yes";
			} else {$row->SUCCESSFUL_ATTACK = "No";}			
			
			if (isset($row->NPERPS) && $row->NPERPS!=-99) {
				$row->NPERPS = "Number of perpetrators: " . $row->NPERPS; 
			} else {$row->NPERPS = "Number of perpetrators: Unknown";}
			
			if (isset($row->NPERCAP) && $row->NPERCAP!=-99) {
				$row->NPERCAP = "Number captured: " . $row->NPERCAP; 
			} else {$row->NPERCAP = "Number captured: Unknown";}
			
			if (isset($row->PROP_VALUE) && $row->PROP_VALUE!=-99) {
				$row->PROP_EXTENT_TXT = "Property Damage: $" . $row->PROP_VALUE;
			} else {
				($row->PROP_EXTENT_TXT!=".") ? $row->PROP_EXTENT_TXT = "Property Damage: " . $row->PROP_EXTENT_TXT : $row->PROP_EXTENT_TXT="Property Damage: None";			
			}
			
			if (isset($row->PROP_COMMENT)) $row->PROP_COMMENT = $row->PROP_COMMENT . ".<br>";
			
			if (isset($row->MOTIVE)){
				$row->MOTIVE = "Motive: " . $row->MOTIVE;
			} else {
				$row->MOTIVE = "Motive: Unknown";
			}
						
			// Display event_id
			echo $row->EVENT_ID."<br><br>";
			// Display location (city, state, country, region)
			echo $row->CITY . $row->PROV_STATE . $row->COUNTRY_TXT."<br>";
			// Display latitude and longitude
			echo $row->LATITUDE . ", " . $row->LONGITUDE . "<br>";
			// Display date (MM/DD/YYYY)
			echo $row->IMONTH ."/". $row->IDAY ."/". $row->IYEAR."<br>";
			// Display summary
			echo $row->SUMMARY_TXT."<br><br>";
			// Display suicide boolean
			echo "Suicide attack: " . $row->SUICIDE."<br>";
			// Display success of attack
			echo "Attack successful: " . $row->SUCCESSFUL_ATTACK. "<br><br>";
			// Display number of perpetrator and number captures
			echo $row->NPERPS ."<br>".$row->NPERCAP."<br><br>";
			// Display property damages
			echo $row->PROP_EXTENT_TXT . "<br>"; 
			echo $row->PROP_COMMENT . "<br>";
			// Display number of casualties 
			echo "Number killed: " . $row->N_KILL ."<br>" . "Number wounded: " .$row->N_WOUND."<br><br>";
			echo "Number terrorists killed: " . $row->N_KILL_TER ."<br>". "Number terrorists wounded: " .$row->N_WOUND_TER."<br><br>";
			// Display motive
			echo $row->MOTIVE . ".<br><br>";
			
			// Display Hostage Situation information
			if (isset($row->HOSTAGE_SITUATION_ID)){
				// Redefine and reformat information
				if ($row->NHOSTKID == -99) $row->NHOSTKID = "Unknown";
				
				if ($row->NHOURS == -1 || $row->NHOURS = -99){$row->NHOURS = ", unknown hours";}
				else {$row->NHOURS= ", " . $row->NHOURS . " hours";}
				
				if ($row->NDAYS == -1 || $row->NDAYS == -9) {$row->NDAYS = "0 days";}
				else if ($row->NDAYS == -99) {$row->NDAYS = "unknown days";}
				else {$row->NDAYS = $row->NDAYS . " days";}
				
				if ($row->RANSOM == 1){
					$row->RANSOM = "Ransom: Yes";
					($row->RANSOMAMT == -99) ? $row->RANSOMAMT = "Ransom amount: Unknown<br>" : $row->RANSOMAMT = "Ransom amount: " . $row->RANSOMAMT . "<br>";
					($row->RANSOMPAID == -99) ? $row->RANSOMPAID = "Ransom paid: Unknown<br>" : $row->RANSOMPAID = "Ransom paid: " . $row->RANSOMPAID . "<br>";
				} else if ($row->RANSOM == -9) {
					$row->RANSOM = "Ransom: Unknown";
					$row->RANSOMAMT = "";
					$row->RANSOMPAID = "";
				} else {
					$row->RANSOM = "Ransom: No";
					$row->RANSOMAMT = "";
					$row->RANSOMPAID = "";
				}
				
				if ($row->RANSOMNOTE == "" || $row->RANSOMNOTE ==0){
					$row->RANSOMNOTE = "None";
				}
				
				echo "Hostage Situation: Yes<br>";
				// Display number of hostages
				echo "Number of hostages: " . $row->NHOSTKID . "<br>";
				// Display duration of situation
				echo "Duration: " . $row->NDAYS . $row->NHOURS . "<br>";
				// Display ransom information
				echo $row->RANSOM . "<br>";
				echo $row->RANSOMAMT;
				echo $row->RANSOMPAID;
				echo "Note: " . $row->RANSOMNOTE . "<br><br>";
				
			} else {
				echo "Hostage Situation: No<br><br>";
			}
		}
		else {
			echo "Error: EVENT_ID \"".$q."\" not found!";
		}
	}
}
$eventPrinter = new parseEvent();
$eventQuery = "SELECT * FROM EVENTS e LEFT OUTER JOIN HOSTAGE_SITUATIONS hs ON e.HOSTAGE_SITUATION_ID = hs.HOST_SIT_ID, LOCATIONS l, COUNTRY c, REGION r
				WHERE e.LOCATION_ID=l.LOCATION_ID AND l.COUNTRY_ID = c.COUNTRY_ID and l.REGION_ID = r.REGION_ID 
				AND e.EVENT_ID=".$q;
oracle_query($eventQuery, $eventPrinter);

// Class which defines output for targets and their info
class parseTargets{
	function output($statement){
		$i = 1;
		
		echo "Targets: <br>";
		while ($row = oci_fetch_object($statement)){
			echo "Target " . $i . ": " . $row->TYPE_TXT . "<br>Subtype: " . $row->SUBTYPE_TXT . "  (" . $row->TARGET . ")<br>";
			$i++;
		}
		echo "<br>";
	}
}
$targetPrinter = new parseTargets();
$targetQuery = "SELECT TYPE_TXT, SUBTYPE_TXT, TARGET FROM EVENTS  e, EVENTS_TARGETS et, TARGETS t, TARGET_TYPE tt, TARGET_SUBTYPE ts
				WHERE e.EVENT_ID=et.EVENT_ID AND et.TARGET_ID=t.TARGET_ID AND t.TYPE_ID = tt.TYPE_ID AND t.SUBTYPE_ID = ts.SUBTYPE_ID 
				AND e.EVENT_ID=".$q;

oracle_query($targetQuery, $targetPrinter);

// Class which defines output for up to three attack types
class parseAttackTypes{
	function output($statement){
		$i = 1;
		
		echo "Attack Types: <br>";
		while ($row = oci_fetch_object($statement)){
			echo "Attack Type  " . $i . ": " . $row->ATTACK_TYPE_TXT . "<br>";
			$i++;
		}
		echo "<br>";
	}
}
$attacktypePrinter = new parseAttackTypes();
$attacktypeQuery = "SELECT ATTACK_TYPE_TXT FROM EVENTS e, EVENTS_ATTACK_TYPES eat, ATTACK_TYPES at
					WHERE e.EVENT_ID = eat.EVENT_ID AND eat.ATTACK_TYPE_ID=at.ATTACK_TYPE_ID 
					AND e.EVENT_ID=".$q;

oracle_query($attacktypeQuery, $attacktypePrinter);

// Class which defines output for weapon types
class parseWeapons{
	function output($statement){
		$i = 1;
		
		echo "Weapons: <br>";
		while ($row = oci_fetch_object($statement)){
			echo "Weapon " . $i . ": " . $row->WEAPON_TYPE_TXT . "<br>Subtype: ". $row->WEAPON_SUBTYPE_TXT ."<br>";
			$i++;
		}
		echo "<br>";
	}
}
$weaponPrinter = new parseWeapons();
$weaponQuery = "SELECT WEAPON_TYPE_TXT, WEAPON_SUBTYPE_TXT FROM EVENTS e, EVENTS_WEAPONS ew, WEAPON_TYPE wt, WEAPON_SUBTYPE ws
				WHERE e.EVENT_ID=ew.EVENT_ID AND ew.WEAPON_TYPE_ID = wt.WEAPON_TYPE_ID AND 
				ew.WEAPON_SUBTYPE_ID = ws.WEAPON_SUBTYPE_ID 
				AND e.EVENT_ID=".$q;

oracle_query($weaponQuery, $weaponPrinter);

// Class which defines output for multiple groups
class parseGroups{
	function output($statement){
		$i = 1;
		
		echo "Groups: <br>";
		while ($row = oci_fetch_object($statement)){
			echo "Group " . $i . ": " . $row->GROUP_NAME . "<br>Size: ". $row->G_SIZE ."<br>";
			$i++;
		}
		echo "<br>";
	}
}
$groupPrinter = new parseGroups();
$groupQuery = "SELECT GROUP_NAME, G_SIZE, GROUP_SUBNAME FROM EVENTS e, EVENTS_GROUPS eg, GROUPS g, GROUP_SUBNAMES gs
		  WHERE e.EVENT_ID = eg.EVENT_ID AND eg.GROUP_ID=g.GROUP_ID and eg.GROUP_SUBNAME_ID=gs.GROUP_SUBNAME_ID 
		  AND e.EVENT_ID=".$q;

oracle_query($groupQuery, $groupPrinter);

?>
</body>
</html>