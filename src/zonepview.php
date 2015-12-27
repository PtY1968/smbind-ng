<?php
require_once "include.php";

$param = (isset($_GET['i'])) ? intval($_GET['i']) : 0;

if ($param > 0) {
    if($user->isOwned($param, 'master')) {
        $smarty->assign("pagetitle", "Preview master zone");
        $zone = new masterZone(array('id' => $_GET['i']));
        $fname = tempnam($conf->Tmp_Path,"zone_");
        $zone->writeZone($fname, $conf->HostMaster);
        $head = $zone->getZoneHead();
        $name = $head['name'];
        $issec = ($head['secured'] == "yes");
        $sec = (($_GET['s'] == "1") && ($issec));
        $zonetype = "master zone";
        $zonetype .= (!$issec) ? " not" : "";
        $zonetype .= " secured, displayed as plain";
        $ownerid = $head['owner'];
        $ouser = new User(array('id' => $ownerid));
        $owner = $ouser->getFullName();
        if (file_exists($fname)) {
            $lines = file($fname);
            $zonedef = implode("", $lines);
            unlink($fname);
        } else {
            $zonedef = NULL;
            $smarty->assign("popuperror", "There is a problem with this zone!");
        }
        $smarty->assign("zonename", $name);
        $smarty->assign("zonetype", $zonetype);
        $smarty->assign("owner", $owner);
        $smarty->assign("zonedef", $zonedef);
        $smarty->assign("template", "zoneview.tpl");
        $smarty->assign("help", help("zonepview"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->display("main.tpl");
    } else {
        problem('notown');
    }
} else {
    problem();
}
?>
