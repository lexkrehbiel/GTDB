
<?php
  #!/usr/local/bin/php
  include("include.php");
  $spec = new query_specifier;
  oracle_query("SELECT * FROM EVENTS", $spec);
?>
