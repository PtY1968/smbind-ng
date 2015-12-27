<?php
require_once "include.php";

$zones = $user->getSlaves('live');
$count = sizeof($zones);
$maxitems = intval($conf->range);
$currpage = ((isset($_GET['page'])) && (intval($_GET['page']) > 0)) ? $currpage = intval($_GET['page']) : 1;
$fromto = makePart($count, $currpage);
$buffer = array();

$i = (isset($_GET['i'])) ? intval($_GET['i']) : NULL;

if ((isset($i)) && (isset($_GET['check'])) && ($_GET['check'] = 'y') ) {
    if ($i > 0) {
        if ($user->isOwned($i, 'slave', 'live')) {
            $sz = new slaveZone($i);
            if ($sz->validateZone($conf->dig)) {
                $arr = $sz->getZoneHead($i);
                $arr['updated'] = 'yes';
                $arr['valid'] = 'yes';
                $sz->setZoneHead($arr);
                $sz->saveZoneHead();
            } else {
                $smarty->assign("popuperror", $sz->getErr());
            }
        } else {
            problem('notown');
        }
    } else {
        problem();
    }
}

for ($i=$fromto[0];$i<$fromto[1];$i++) {
    $zone = new slaveZone(array('id' => $zones[$i]));
    $zone->loadZoneHead();
    $buffer[] = $zone->getZoneHead();
    unset($zone);
}

$smarty->assign("zonelist", $buffer);
$smarty->assign("pagetitle", "Slave zones");
$smarty->assign("template", "slave_zoneread.tpl");
$smarty->assign("help", help("zoneread"));
$smarty->assign("menu_button", menu_buttons());
$smarty->assign("page_root", $src . "zonelist.php?");
$smarty->display("main.tpl");

?>
