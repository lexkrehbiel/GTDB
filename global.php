<?php

    if(!session_id()) session_start();
    $attrList = array("Time","Location","Banana");
    $allConstraintTypes = array("Location", "Time: Before","Time: After","Hostages: Number of", "Hostages: Days","Weapon","Target","Casualties","Groups");
    if(!isset($_SESSION['filename'])) {
        $_SESSION['attrList'] = $attrList;
        $_SESSION['allConstraintTypes'] = $allConstraintTypes;
    }
?>
