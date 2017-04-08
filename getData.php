<?php

// if(!session_id()) session_start();
// include("ChartPage.php");
//$allConstraintTypes = $_SESSION['allConstraintTypes'];
//$query = $_SESSION['query'];

$query = $_POST['query'];

$connection = oci_connect('bickell',
                          'M2n3ca1a1!1',
                          '//oracle.cise.ufl.edu/orcl');
$statement = oci_parse($connection, $query);
oci_execute($statement);

$json = file_get_contents("skeleton.json");
//var_dump($statement);
//$r = oci_fetch_object($statement);
while($r = oci_fetch_object($statement)) {
  //var_dump($r);
    //get WEAPON_TYPE_TXT and COUNT from $r, then put into $json
    $json = $json."{\"c\":[{\"v\":\"";
    $json = $json.($r->VALUE);
    $json = $json."\",\"f\":null},{\"v\":";
    $json = $json.($r->COUNT);
    $json = $json.",\"f\":null}]}, ";
}
$json = substr($json, 0, -2);
$json = $json." ] }";

echo $json;

oci_free_statement($statement);
oci_close($connection);
//
// $string = file_get_contents("test.json");
// echo $string;
//
// echo strcmp($json, $string);

?>
