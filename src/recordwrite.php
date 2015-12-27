<?php
require_once "include.php";

$znum = (intval($_GET['i']) > 0) ? intval($_GET['i']) : NULL;

if ($znum) {
    if ($user->isOwned($znum, 'master', 'live')) {
        $zone = new masterZone(array('id' => $znum));
        $zone->loadZone();
        $zonerec = $zone->getZoneHead();
        $zrec = array();
        foreach (array('refresh', 'expire', 'retry', 'ttl', 'secured', 'pri_dns', 'sec_dns', 'owner') as $key) {
            $zrec[$key] = ((isset($_POST[$key])) and ($_POST[$key] > '')) ? $_POST[$key] : $zonerec[$key];
        }
        $zrec['valid'] = 'may';
        $zone->setZoneHead($zrec);
        $zone->saveZoneHead();
        $total = ((isset($_POST['total'])) && ($_POST['total'] > 0)) ? $_POST['total'] : 0;
        for ($x = 0; $x < $total; $x++) {
            if (isset($_POST['delete'][$x])) {
                $zone->eraseRecord(intval($_POST['host_id'][$x]));
            } else {
                $nrec=array();
                foreach (array('host', 'ttl', 'type', 'pri', 'destination') as $key) {
                    $pkey = ($key == 'ttl') ? 'rttl' : $key;
                    $nrec[$key] = (isset($_POST[$pkey][$x])) ? $_POST[$pkey][$x] : '';
                    $nrec[$key] = (($key == 'ttl') && ($nrec[$key] == '')) ? 0 :$nrec[$key];
                    $nrec[$key] = (($key == 'pri') && ($nrec[$key] == '')) ? 10 :$nrec[$key];
                    $nrec[$key] = (($key == 'host') && ($nrec[$key] == '')) ? '@' :$nrec[$key];
                    $nrec[$key] = (($key == 'destination') && ($nrec[$key] == '')) ? '@' :$nrec[$key];
                }
                if ($nrec['host'] != $nrec['destination']) {
                    $urec = new masterRecord(intval($_POST['host_id'][$x]));
                    $urec->loadRecord();
                    $urec->setRecord($nrec);
                    $xrec = $urec->getRecordRaw();
                    $urec->saveRecord();
                }
            }
        }
        $zone->clearZone();
        $zone->loadZone();
        $nrec = array();
        foreach (array('host', 'ttl', 'type', 'pri', 'destination') as $key) {
            $nrec[$key] = ((isset($_POST['new' . $key])) && ($_POST['new' . $key] > '')) ? $_POST['new' . $key] : NULL;
        }
        if ($nrec['host'] != $nrec['destination']) {
            $nrec['pri'] = ($nrec['type'] == 'MX') ? 10 : 0;
            $nrec['ttl'] = intval($nrec['ttl']);
            $nrec['zone'] = $znum;
            $zone->addRecord($nrec);
        }
        $zone->saveZone();
    } else {
        problem("notown");
    }
} else {
    access_denied();
}

?>
