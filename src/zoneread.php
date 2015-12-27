<?php
require_once "include.php";

$zones = $user->getMasters('live');
$count = sizeof($zones);
$maxitems = intval($conf->range);
$currpage = ((isset($_GET['page'])) && (intval($_GET['page']) > 0)) ? $currpage = intval($_GET['page']) : 1;
$fromto = makePart($count, $currpage);
$buffer = array();

for ($i=$fromto[0];$i<$fromto[1];$i++) {
    $zone = new masterZone(array('id' => $zones[$i]));
    $zone->loadZoneHead();
    $buffer[] = $zone->getZoneHead();
    unset($zone);
}

$smarty->assign("zonelist", $buffer);
$smarty->assign("pagetitle", "Master zones");
$smarty->assign("template", "zoneread.tpl");
$smarty->assign("help", help("zoneread"));
$smarty->assign("menu_button", menu_buttons());
$smarty->assign("page_root", $src . "zonelist.php?");
$smarty->display("main.tpl");

?>
