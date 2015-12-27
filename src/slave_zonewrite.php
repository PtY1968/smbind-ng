<?php
require_once "include.php";

$i = intval($_GET['i']);
$name = (isset($_POST['name']) && (trim($_POST['name']) > '')) ? trim($_POST['name']) : '';
$owner = intval($_POST['owner']);
$master = (isset($_POST['master']) && (trim($_POST['master']) > '')) ? trim($_POST['master']) : '';

if ((($i * $owner) > 0) && ($name > '') && ($master > '')) {
    if ($user->isOwned($i, 'master', 'live')) {
        $sz = new slaveZone(array(
            'id'        => $i,
            'name'      => $name,
            'master'    => $master,
            'owner'     => $owner,
            'updated'   => 'yes',
            'valid'     => 'may',
        ));
        $sz->saveZoneHead();
    } else {
        problem('notown');
    }
} else {
    problem();
}
?>
