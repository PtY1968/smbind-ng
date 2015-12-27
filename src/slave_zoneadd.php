<?php
require_once "include.php";

if($user->isAdmin()) {
    $arr = array();
    foreach (array('name', 'master', 'owner') as $key) {
        $arr[$key] = $_POST[$key];
    }
    $nz = new slaveZone($arr);
    if (!$nz->loadZoneHead()) {
        $nz->setZoneHead($arr);
        $nz->saveZoneHead();
        $user->loadUserZones();
    } else {
        problem("existzone");
    }
} else {
    access_denied();
}

?>
