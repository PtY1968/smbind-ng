<?php
require_once "include.php";

$zoneid = intval($_GET['i']);

if ($zoneid > 0) {
    if ($user->isOwned($zoneid, 'slave')) {
        $smarty->assign("pagetitle", "Delete slave zone");
        $zone = new slaveZone($zoneid);
        $zone->loadZoneHead();
        $res = $zone->getZoneHead();
        $smarty->assign("zone", $res['name']);
        $smarty->assign("zoneid", $zoneid);
        $smarty->assign("template", "slave_deletezone.tpl");
        $smarty->assign("help", help("deletezone"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->display("main.tpl");
    } else {
        problem('notown');
    }
} else {
    problem();
}

?>
