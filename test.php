
<?php
#!/usr/local/bin/php
include("include.php");
$spec = new query_specifier;
var_dump($spec);
oracle_query("SELECT * FROM EVENTS", $spec);

// $connection = oci_connect('bickell',
//                           'M2n3ca1a1!1',
//                           '//oracle.cise.ufl.edu/orcl');
// $statement = oci_parse($connection, 'SELECT * FROM EVENTS');
// // $connection = oci_connect('krehbiel',
// //                           'G14t0r0r14cl3',
// //                           '//oracle.cise.ufl.edu/orcl');
// // $statement = oci_parse($connection, 'SELECT * FROM GEO_Island');
// oci_execute($statement);
//
// while (($row = oci_fetch_object($statement))) {
//     var_dump($row);
// }
//
// //
// // VERY important to close Oracle Database Connections and free statements!
// //
// oci_free_statement($statement);
// oci_close($connection);
// //  $connection_string = 'krehbiel@//oracle.cise.ufl.edu:1521/orcl';
// //  echo $connection_string;
// //
// //  $connection = oci_connect('krehbiel'
// //                           'G14t0r0r14cl3',
// //                           $connection_string);
// // //echo $connection;
// //
// // if (!$connection) {
// //     $e = oci_error();
// //     trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
// // }
// //
// // $stid = oci_parse($conn, 'SELECT * FROM GEO_Island');
// // oci_execute($stid);
// // // $statement = oci_parse($connection, 'SELECT * FROM tablename');
// // // oci_execute($statement);
// // //
// // // while (($row = oci_fetch_object($statement))) {
// // //     var_dump($row);
// // // }
// // //
// // // //
// // // // VERY important to close Oracle Database Connections and free statements!
// // // //
// // oci_free_statement($statement);
// // oci_close($connection);
?>
