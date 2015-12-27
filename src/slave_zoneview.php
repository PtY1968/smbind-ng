<?php
require_once "include.php";

$param = intval($_GET['i']);

if ($param > 0) {
    if ($user->isOwned($param, 'slave')) {
        $smarty->assign("pagetitle", "Dump slave zone");
        $zone = new slaveZone(array('id' => $_GET['i']));
        $zone->loadZoneHead();
        $zonedef = '';
        $head = $zone->getZoneHead();
        $headraw = $zone->getZoneHeadRaw();
        $name = $head['name'];
        $zonetype = "slave zone ";
        $ownerid = $head['owner'];
        $ouser = new User(array('id' => $ownerid));
        $owner = $ouser->getFullName();
        if ((isset($_GET['pre'])) && ($_GET['pre'] == 'y')) {
            $zonedef = $zone->dumpZone($conf->dig);
            $zonetype .= "(AXFR dump from it's master)";
        } else {
            $zonetype .= "(locally mirrored)";
            $lines = file($conf->path . $headraw['name']);
            $zonedef = '';
            foreach ($lines as $line) {
                $zonedef .= $line;
            }
        }
        $smarty->assign("zonename", $name);
        $smarty->assign("zonetype", $zonetype);
        $smarty->assign("owner", $owner);
        $smarty->assign("zonedef", $zonedef);
        $smarty->assign("template", "zoneview.tpl");
        $smarty->assign("help", help("zoneview"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->display("main.tpl");
    } else {
        problem('notown');
    }
} else {
    problem();
}

?>
