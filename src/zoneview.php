<?php
require_once "include.php";

$param = (isset($_GET['i'])) ? intval($_GET['i']) : 0;

if ($param > 0) {
    if($user->isOwned($param, 'master')) {
        $smarty->assign("pagetitle", "Dump master zone");
        $zone = new masterZone(array('id' => $_GET['i']));
        $zone->loadZoneHead();
        $head = $zone->getZoneHead();
        $headraw = $zone->getZoneHeadRaw();
        $name = $head['name'];
        $fname = $headraw['name'];
        $issec = ($head['secured'] == "yes");
        $sec = (($_GET['s'] == "1") && ($issec));
        $zonetype = "master zone";
        $zonetype .= (!$issec) ? " not" : "";
        $zonetype .= " secured, displayed as ";
        $zonetype .= ($sec) ? "secured" : "plain";
        $fname .= ($issec) ? '.signed' : "";
        $ownerid = $head['owner'];
        $ouser = new User(array('id' => $ownerid));
        $owner = $ouser->getFullName();
        $filename = $conf->path . $fname;
        if (file_exists($filename)) {
            $lines = file($conf->path . $fname);
            $zonedef = '';
            foreach ($lines as $line) {
                $zonedef .= $line;
            }
        } else {
            $zonedef = NULL;
            $smarty->assign("popuperror", "There is no saved zonefile in the system!");
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
