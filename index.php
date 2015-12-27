<?php
require_once "src/include.php";

$smarty->assign("pagetitle", "Main");
$smarty->assign("user", $user->getFullName());
$smarty->assign("zones", sizeof($user->getMasters('live')));
$smarty->assign("slave_zones", sizeof($user->getSlaves('live')));
$smarty->assign("status", rndc_status());
$smarty->assign("bad", $user->getUnvalidatedZones('master'));
$smarty->assign("bad_slaves", $user->getUnvalidatedZones('slave'));
$smarty->assign("comm", $user->getCommitableZones('master'));
$smarty->assign("comm_slaves", $user->getCommitableZones('slave'));
$smarty->assign("del", $user->getDeletedZones('master'));
$smarty->assign("del_slaves", $user->getDeletedZones('slave'));
$smarty->assign("template", "mainpage.tpl");
$smarty->assign("help", help("mainpage"));
$smarty->assign("menu_button", menu_buttons());
$smarty->display("main.tpl");
?>
