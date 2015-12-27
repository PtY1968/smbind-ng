<?php
require_once "include.php";

$i = intval($_GET['i']);

if ($i > 0) {
    if ($user->isOwned($i, 'slave', 'live')) {
        $sz = new slaveZone($i);
        $sz->loadZoneHead();
        $zone = $sz->getZoneHead();
        $smarty->assign("zone", $zone);
        $smarty->assign("pagetitle", "Editing slave zone");
        $smarty->assign("userlist", $user->getAllusers());
        $smarty->assign("template", "slave_recordread.tpl");
        $smarty->assign("help", help("recordread"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->assign("page_root", $src . "slave_record.php?i=" . $_GET['i'] . "&amp;");
        $smarty->display("main.tpl");
    } else {
        problem('notown');
    }
} else {
    problem();
}

?>
