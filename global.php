<?php

    if(!session_id()) session_start();
    $attrList = array("Time","Location","Banana");
    $allConstraintTypes = array("Time:Before","Time:after","Apples");
    if(!isset($_SESSION['filename'])) {
        $_SESSION['attrList'] = $attrList;
        $_SESSION['constraintList'] = $allConstraintTypes;
    }
?>
