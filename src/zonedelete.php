<?php
require_once "include.php";

$zoneid = intval($_GET['i']);

if($user->isOwned($zoneid, 'master')) {
    if($zoneid > 0) {
        $zone = new masterZone($zoneid);
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
