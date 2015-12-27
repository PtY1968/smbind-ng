<?php
require_once "include.php";

if($user->isAdmin()) {
    $i = (isset($_GET['i'])) ? intval($_GET['i']) : 0;
    if ($i > 1) {
        $smarty->assign("pagetitle", "Viewing user");
        $smarty->assign("admin_array", array("yes", "no"));
        $xuser = new User($i);
        $data = $xuser->getUser();
        $smarty->assign("user", $data);
        $xuser->loadUserZones();
        $masters = $xuser->getMasters('live');
        $masterlist = array();
        foreach ($masters as $id) {
            $temp = new masterZone(intval($id));
            $temp->loadZoneHead();
            $head = $temp->getZoneHead();
            $temp = array();
            foreach (array('id', 'name', 'serial') as $key) {
                $temp[$key] = $head[$key];
            }
            $masterlist[] = $temp;
        }
        $slaves = $xuser->getSlaves('live');
        $slavelist = array();
        foreach ($slaves as $id) {
            $temp = new slaveZone(intval($id));
            $temp->loadZoneHead();
            $head = $temp->getZoneHead();
            $temp = array();
            foreach (array('id', 'name', 'master') as $key) {
                $temp[$key] = $head[$key];
            }
            $slavelist[] = $temp;
            $temp = array();
        }
        $smarty->assign("zonelist", $masterlist);
        $smarty->assign("szonelist", $slavelist);
        $smarty->assign("template", "userread.tpl");
        $smarty->assign("help", help("userread"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->display("main.tpl");
    } else {
        problem();
    }
} else {
    access_denied();
}

?>
