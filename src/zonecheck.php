<?php
require_once "include.php";

$param = (isset($_GET['i'])) ? intval($_GET['i']) : 0;

if($user->isOwned($param, 'master')) {
    $zone = new masterZone(array('id' => $_GET['i']));
    $file = tempnam($conf->Tmp_Path,"zone_");
    $check = $zone->validateZone($file, $conf->HostMaster, $conf->NamedCheckZone);
    if (!$check[0]) {
        $smarty->assign("popuperror",$check[1]);
    }
    if (file_exists($file)) {
        unlink($file);
    }
} else {
    problem('notown');
}

?>
