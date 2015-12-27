<?php
require_once "include.php";

if ($user->isAdmin()) {
    if ((strlen($_POST['password_']) == 32) &&
        (isset($_POST['username_'])) &&
        (strlen($_POST['username_']) > 2)) {
        $real = ((isset($_POST['realname'])) && ($_POST['realname'] >= '')) ? $_POST['realname'] : $_POST['username_'];
        $urec = array(
            'id'        => 0,
            'username'  => $_POST['username_'],
            'realname'  => $real,
            'admin'     => $_POST['admin'],
            'password'  => $_POST['password_'],
        );
        $nuser = new User($urec);
        if ($nuser->getId() == 0) {
            problem();
        }
    } else {
        problem();
    }
} else {
    access_denied();
}

?>
