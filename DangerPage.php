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
        <button style="margin-top: 18px" onclick="javascript:document.location='MainPage.html'"><i class="material-icons" style>home</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='ChartPage.html'">assessment</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='ListPage.html'">list</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='TimePage.html'">schedule</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='DangerPage.html'">warning</i></button>
        <button><i class="material-icons" onclick="javascript:document.location='MapPage.html'">pin_drop</i></button>
    </h8>
  </div>
  <h1>Danger Rating</h1>
  <h3>Enter your location</h3>
  <div>
    <input type="text"></input>
    <button><i class="material-icons">search</i></button>
  </div>

  <div class="box">
    <h3> Your danger rating is: </h3>
    <h3 class="danger"> 3.7/5</h3>
  <h4>
    <p style="margin-right: 7px; margin-top: 30px">Recent Activity </p>
  </h4>
  <div class="listitem">
    <h5> Title 1 </h5>
    <p> Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam</p>
  </div>
  <div class="listitem">
    <h5> Title 2 </h5>
    <p> details</p>
  </div>
  </div>

</div>

</body>

</html>
