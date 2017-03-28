<?php

    if(!session_id()) session_start();
    $attrList = array("Time","Location","Banana");
    $allConstraintTypes = array("Location", "Hostages", "Time: Before","Time: After",);
    if(!isset($_SESSION['filename'])) {
        $_SESSION['attrList'] = $attrList;
        $_SESSION['allConstraintTypes'] = $allConstraintTypes;
    }
?>
