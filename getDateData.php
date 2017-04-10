<?php

$query = $_POST['query'];

$connection = oci_connect('bickell',
                          'M2n3ca1a1!1',
                          '//oracle.cise.ufl.edu/orcl');
$statement = oci_parse($connection, $query);
oci_execute($statement);

$json = file_get_contents("skeleton.json");
$count = 0;
//var_dump($statement);
//$r = oci_fetch_object($statement);
while($r = oci_fetch_object($statement)) {

  //add in 0's for empty rows
  if($count > 0){
    $diff = $r->VALUE-$lastValue;
    while($diff > 1){
      $lastValue = $lastValue+1;
      $diff = $r->VALUE-$lastValue;
      $json = $json."{\"c\":[{\"v\":\"";
      $json = $json.$lastValue;
      $json = $json."\",\"f\":null},{\"v\":";
      $json = $json."0";
      $json = $json.",\"f\":null}]}, ";
    }
  }

    $count ++;
    $json = $json."{\"c\":[{\"v\":\"";
    $json = $json.($r->VALUE);
    $json = $json."\",\"f\":null},{\"v\":";
    $json = $json.($r->COUNT);
    $json = $json.",\"f\":null}]}, ";

    $lastValue = $r->VALUE;
}
$json = substr($json, 0, -2);
$json = $json." ] }";

echo $json;

oci_free_statement($statement);
oci_close($connection);


?>
