<?php
require_once "include.php";

$zoneid = intval($_GET['i']);

if($user->isOwned($zoneid, 'slave')) {
    if($zoneid > 0) {
        $zone = new slaveZone($zoneid);
        $zone->loadZoneHead();
        $zone->setZoneHead(array('updated' => 'del'));
        $zone->saveZoneHead();
    }
    else {
        problem();
    }
}
else {
    problem("notown");
}

?>
