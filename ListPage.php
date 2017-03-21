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
  <div>
    <input type="text"></input>
    <button><i class="material-icons">search</i></button>
  </div>
  <div class="box">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Search Criteria </p>
    <button><i class="material-icons">add</i></button>
  </h4>
  <h6>
    <p> Constraint Type</p>
    <select name="Attribute">
      <option value="Time: before">Time: before</option>
      <option value="Time: after">Time: after</option>
      <option value="Location">Location</option>
      <option value="Target">Target</option>
    </select>
    <p style="margin-left:9px"> Value</p>
    <input type="text"></input>
    <i class="material-icons blackmat">close</i>
  </h6>
  <h6>
    <p> Constraint Type</p>
    <select name="Attribute">
      <option value="Time: before">Time: before</option>
      <option value="Time: after">Time: after</option>
      <option value="Location">Location</option>
      <option value="Target">Target</option>
    </select>
    <p style="margin-left:9px"> Value</p>
    <input type="text"></input>
    <i class="material-icons blackmat">close</i>
  </h6>
  </div>

  <div class="box">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Search Results </p>
    <button><i class="material-icons">refresh</i></button>
  </h4>
  <?php
    class q {
      function output($statement){
        $row = oci_fetch_object($statement);
        //var_dump($row);
        $i = 0;
        while ($i<=5 && ($row = oci_fetch_object($statement))) {
            echo "<div class=\"listitem\">";
            echo "<h5>".($row->CITY).", ".($row->COUNTRY_TXT)."</h5>";
            echo "<p>".($row->SUMMARY_TXT)."</p>";
            $i++;
        }
      }
    }
    include("include.php");
    $spec = new q;
    $query = "SELECT DISTINCT * FROM EVENTS, LOCATIONS, COUNTRY
              WHERE EVENTS.LOCATION_ID = LOCATIONS.LOCATION_ID
              AND COUNTRY.COUNTRY_ID = LOCATIONS.COUNTRY_ID";
    oracle_query($query, $spec);
  ?>
  </div>

</div>

</body>

</html>
