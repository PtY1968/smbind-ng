<?php
require_once "include.php";

if($user->isAdmin()) {
    $i = intval($_GET['i']);
    if (($i > 1)  && ($session->isEnoughOld())) {
        $us = new User($i);
        $us->eraseUser();
    } else {
        problem();
    }
} else {
    access_denied();
}
?>
