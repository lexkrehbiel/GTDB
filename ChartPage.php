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
</head>

<body>

<div class = "container">
  <div style="text-align:center; margin-top:10px">
    <h8 class="menubar">
      <button style="margin-top: 18px" onclick="javascript:document.location='MainPage.html'"><i class="material-icons" style>home</i></button>
      <button><i class="material-icons" onclick="javascript:document.location='ChartPage.html'">assessment</i></button>
      <button><i class="material-icons" onclick="javascript:document.location='ListPage.html'">list</i></button>
      <button><i class="material-icons" onclick="javascript:document.location='TimePage.html'">schedule</i></button>
      <button><i class="material-icons" onclick="javascript:document.location='DangerPage.html'">warning</i></button>
      <button><i class="material-icons" onclick="javascript:document.location='MapPage.html'">pin_drop</i></button>
    </h8>
  </div>
  <h1>Bar Chart</h1>
  <h3>
    <p> Attribute to Analyze </p>
    <select name="Attribute">
      <option value="Location">Location</option>
      <option value="Target">Target</option>
    </select>
  </h3>
  <div class="box">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Constraints</p>
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

  <div class="box" style="height:40em">
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Graph</p>
    <button><i class="material-icons">refresh</i></button>
  </h4>
  <div class = "barchart">
  </div>

</div>

</body>

</html>
