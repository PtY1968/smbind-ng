<?php
require_once "include.php";

$znum = (intval($_GET['i']) > 0) ? intval($_GET['i']) : NULL;

if ($znum) {
    if ($user->isOwned($znum, 'master', 'live')) {
        $zone = new masterZone(array('id' => $znum));
        $zone->loadZone();
        $zonerec = $zone->getZoneHead();
        $currpage = ((isset($_GET['page'])) && (intval($_GET['page']) > 0)) ? $currpage = intval($_GET['page']) : 1;
        $allrec = $zone->getRecords(true);
        $count = sizeof($allrec);
        $fromto = makePart($count, $currpage);
        $rec = array();

        for ($i=$fromto[0];$i<$fromto[1];$i++) {
            $allrec[$i]['ttl'] = ($allrec[$i]['ttl'] > 0) ? $allrec[$i]['ttl'] : '';
            $rec[] = $allrec[$i];
        }

        $users = $user->getAllusers();
        $types = $conf->parameters;
        $err = '';
        $err .= ((!is_array($zonerec)) || (!is_array($rec))) ? $zone->getErr() : '';
        $err .= (!is_array($users)) ? $user->getErr() : '';
        $err .= (!is_array($types)) ? "Record types not foud\n" : '';
        if ($err > '') {
            $smarty->assign("popuperror",implode("<br />", explode("\n", $err)));
        }
        $smarty->assign("zone", $zonerec);
        $smarty->assign("pagetitle", "Editing master zone");
        $smarty->assign("rcount", sizeof($rec));
        $smarty->assign("record",$rec);
        $smarty->assign("types", $types);
        $smarty->assign("userlist", $users);
        $smarty->assign("template", "recordread.tpl");
        $smarty->assign("help", help("recordread"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->assign("page_root", $src . "record.php?i=" . $_GET['i'] . "&amp;");
        $smarty->display("main.tpl");
    } else {
        problem("notown");
    }
} else {
    access_denied();
}

?>
