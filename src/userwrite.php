<?php
require_once "include.php";

if($user->isAdmin()) {
    $adm = (isset($_POST['admin'])) ? $_POST['admin'] : '';
    $pass = (isset($_POST['password'])) ? $_POST['password'] : '';
    $rnam = (isset($_POST['realname'])) ? $_POST['realname'] : '';
    $i = (isset($_GET['i'])) ? intval($_GET['i']) : 0;
    if (($i > 1)  && ($session->isEnoughOld())) {
        $smarty->assign("pagetitle", "Viewing user");
        $newu = new User($i);
        $newu->set($rnam, $pass, $adm);
        if ($i == $user->getId() && (
                ($newu->isAdmin() xor $user->isAdmin()) ||
                ($newu->getPasswordHash() <> $user->getPasswordHash())
            )) {
            logout();
            die();
        }
        $smarty->assign("success","The user properties has been modified");
    } else {
        problem();
    }
} else {
    access_denied();
}

?>
