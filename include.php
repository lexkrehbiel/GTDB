<?php
function oracle_query($query,$specifier){
  try{
    $connection = oci_connect('bickell',
                              'M2n3ca1a1!1',
                              '//oracle.cise.ufl.edu/orcl');
    $statement = oci_parse($connection, $query);
    oci_execute($statement);
    $specifier->output($statement);


    oci_free_statement($statement);
    oci_close($connection);

  } catch (Exception $e) {
    echo "Error handling query : ".$e->getMessage();
  }

}

//each time you have specific output needs, make an instance
//of a class with an 'output' function

// class query_specifier{
//   function output($statement){
//     while (($row = oci_fetch_object($statement))) {
//         var_dump($row);
//     }
//   }
// }

?>
