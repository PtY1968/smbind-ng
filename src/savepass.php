<?php
require_once "include.php";

if ($user->getPasswordHash() == $_POST['password_old']) {
    if ((strlen($_POST['password_one']) == 32) && ($session->isEnoughOld())) {
        $user->set(NULL, $_POST['password_one']);
        $_SESSION['p'] = $user->getPasswordHash();
        $smarty->assign("pagetitle", "Change password");
        $smarty->assign("template", "savepass.tpl");
        $smarty->assign("help", help("savepass"));
        $smarty->assign("menu_button", menu_buttons());
        $smarty->display("main.tpl");
    } else {
        problem();
    }
} else {
    problem("unauth");
}

?>
