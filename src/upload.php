<?php
require_once "include.php";

if($user->isAdmin()) {
    $files = getorphan();
    $smarty->assign("pagetitle", "Import master zones");
    $smarty->assign("count",sizeof($files));
    $smarty->assign("files",$files);
    $smarty->assign("template", "upload.tpl");
    $smarty->assign("help", help("upload"));
    $smarty->assign("menu_button", menu_buttons());
    $smarty->display("main.tpl");
}
else {
    access_denied();
}
?>
