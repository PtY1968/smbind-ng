<?php
require_once "include.php";

if($user->isAdmin()) {
    $arr = array(
        'name'      => $_POST['name'],
        'pri_dns'   => $_POST['pri_dns'],
        'sec_dns'   => $_POST['sec_dns'],
        'refresh'   => $_POST['refresh'],
        'retry'     => $_POST['retry'],
        'expire'    => $_POST['expire'],
        'ttl'       => $_POST['ttl'],
        'owner'     => $_POST['owner'],
    );
    $nz = new masterZone($arr);
    if (!$nz->loadZoneHead()) {
        $nz->setZoneHead($arr);
        $nz->saveZoneHead();
        $www = ((isset($_POST['www'])) && ($_POST['www'] >'')) ? $_POST['www'] : NULL;
        if  ($www){
            $type = (filter_var($www, FILTER_VALIDATE_IP)) ? 'A' : 'CNAME';
            if ($type == 'A') {
                $nz->addRecord(array(
                    'host'          => '@',
                    'type'          => 'A',
                    'destination'   => $www,
                ));
                $nz->addRecord(array(
                    'host'          => 'www',
                    'type'          => 'CNAME',
                    'destination'   => '@',
                ));
            } else {
                $nz->addRecord(array(
                    'host'          => 'www',
                    'type'          => 'CNAME',
                    'destination'   => $www,
                ));
            }
        }
        $ftp = ((isset($_POST['ftp'])) && ($_POST['ftp'] >'')) ? $_POST['ftp'] : NULL;
        if ($ftp) {
            $type = (filter_var($ftp, FILTER_VALIDATE_IP)) ? 'A' : 'CNAME';
            $nz->addRecord(array(
                'host'          => 'ftp',
                'type'          => $type,
                'destination'   => $ftp,
            ));
        }
        $mail = ((isset($_POST['mail'])) && ($_POST['mail'] >'')) ? $_POST['mail'] : NULL;
        if ($mail) {
            $type = (filter_var($mail, FILTER_VALIDATE_IP)) ? 'A' : 'MX';
            $destination = ($type == 'A') ? 'mail' : $mail;
            if ($type == 'A') {
                $nz->addRecord(array(
                    'host'          => 'mail',
                    'type'          => $type,
                    'destination'   => $mail,
                ));
                $type = 'MX';
            }
            $nz->addRecord(array(
                'host'          => '@',
                'type'          => $type,
                'destination'   => $destination,
                'pri'           => 10,
            ));
        }
        $nz->saveZone();
        $user->loadUserZones();
    } else {
        problem("existzone");
    }
}

?>
