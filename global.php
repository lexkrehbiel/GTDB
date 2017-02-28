<?php
if(!session_id()) session_start();
$attrList = array("Time","Location","Banana");
if(!isset($_SESSION['filename'])) {
    $_SESSION['attrList'] = $attrList;
}
?>
