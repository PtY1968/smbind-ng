<?php
require_once "include.php";

if($user->isAdmin()) {
    $smarty->assign("pagetitle", "Users");
    $usermap = $user->getAllusers();
    $currpage = ((isset($_GET['page'])) && (intval($_GET['page']) > 0)) ? $currpage = intval($_GET['page']) : 1;
    $count = sizeof($usermap);
    $fromto = makePart($count, $currpage);
    $listed = array();
    for ($i=$fromto[0];$i<$fromto[1];$i++) {
        $listed[] = $usermap[$i];
    }
    $smarty->assign("userlist", $listed);
    $smarty->assign("template", "userlistread.tpl");
    $smarty->assign("help", help("userlistread"));
    $smarty->assign("menu_button", menu_buttons());
    $smarty->assign("page_root", $src . "userlist.php?");
    $smarty->display("main.tpl");
}
else {
    access_denied();
}

?>
