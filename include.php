<?php

function oracle_query($query,$specifier){
  $connection = oci_connect('bickell',
                            'M2n3ca1a1!1',
                            '//oracle.cise.ufl.edu/orcl');
  $statement = oci_parse($connection, $query);
  oci_execute($statement);

  $specifier->output($statement);
  // while (($row = oci_fetch_object($statement))) {
  //     var_dump($row);
  // }

  //
  // VERY important to close Oracle Database Connections and free statements!
  //
  oci_free_statement($statement);
  oci_close($connection);

}

class query_specifier{
  function output($statement){
    while (($row = oci_fetch_object($statement))) {
        var_dump($row);
    }
  }
}

?>
