<?php
/*
 * commit.php
 *
 * Copyright 2015 Péter Szládovics <peti@szladovics.hu>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 **********************************************************************
 *
 * Applies the zone/record changes to the system
 *
 */

require_once "include.php";

$dslave = array();
$dmaster = array();

$dslave = $user->getDeletedZones('slave');
$dmaster = $user->getDeletedZones('master');
$cslave = $user->getCommitableZones('slave');
$cmaster = $user->getCommitableZones('master');
$allz = count($dmaster) + count($dslave) + count($cmaster) + count($cslave);
$done = 0;

if (isset($_SERVER['HTTP_REFERER']) &&
    (preg_replace('/https?:\/\/[^\/]+/', '', $_SERVER['HTTP_REFERER']) != $_SERVER['PHP_SELF']) &&
    (!isset($_GET['commit'])) &&
    ($allz >0)) {
    $smarty->assign("pagetitle", "Commit changes");
    $smarty->assign("template", "commit.tpl");
    $smarty->assign("help", help("precommit"));
    $smarty->assign("menu_button", menu_buttons());
    $smarty->display("main.tpl");
    die();
} elseif ((isset($_GET['commit'])) && ($_GET['commit'] != 'y')) {
    problem();
} elseif (count($dmaster) +
        count($dslave) +
        count($cmaster) +
        count($cslave) == 0) {
    problem("nocommit");
}

$bind = new bindConfig($conf->conf);

$deleted = '';
$commited = '';
$error = '';

$delm = (count($dmaster) > 0) ? "<strong>" . "Deleting master records" . "</strong>\n\n" : '';

foreach ($dmaster as $master) {
    $dmz = new masterZone(intval($master['id']));
    $dmz->loadZoneHead();
    $hd = $dmz->getZoneHead();
    $hdr = $dmz->getZoneHeadRaw();
    $dmz->eraseZone();
    $err = $dmz->getErr();
    if ($err > '') {
        $error .= "<u>" . $hd['name'] . ":</u> " . "Error in deleting" . "\n" . $err . '\n\n';
    } else {
        $deleted .= "<u>" . $hd['name'] . ":</u> Deleting success.\n\n";
        $bind->eraseConfig($hdr['name']);
        $done++;
    }
}

$error = ($error > '') ? $delm . $error : '';
$deleted = ($deleted > '') ? $delm . $deleted : '';

$errors = '';
$deleteds = '';
$delm .= (count($dslave) > 0) ? "<strong>" . "Deleting slave records" . "</strong>\n\n" : '';
$delm = ($deleted > '') ? "\n" . $delm : $delm;

foreach ($dslave as $slave) {
    $dsz = new slaveZone(intval($slave['id']));
    $dsz->loadZoneHead();
    $hd = $dsz->getZoneHead();
    $hdr = $dsz->getZoneHeadRaw();
    $dsz->eraseZone();
    $err = $dsz->getErr();
    if ($err > '') {
        $errors .= "<u>" . $hd['name'] . ":</u> Error in deleting\n" . $err . '\n\n';
    } else {
        $deleteds .= "<u>" . $hd['name'] . "</u>: Deleting success.\n\n";
        $bind->eraseConfig($hdr['name']);
        $done++;
    }
}

$error .= ($errors > '') ? $delm . $errors : '';
$deleted .= ($deleteds > '') ? $delm . $deleteds : '';
$errors = '';

$comm = (count($cmaster) > 0) ? "<strong>" . "Committing master records" . "</strong>\n\n" : '';
$mcomm = '';

foreach ($cmaster as $master) {
    $cmz = new masterZone(intval($master['id']));
    $cmz->loadZoneHead();
    $hd = $cmz->getZoneHead();
    $hdr = $cmz->getZoneHeadRaw();
    $cmz->writeZone($conf->path . $hdr['name'], $conf->hostMaster);
    $zarray = array(
        'type'  => 'master',
        'file'  => $hdr['name'],
    );
    $zarray['file'] .= (($hd['secured'] == 'yes') && ($cmz->doSecure($conf->path, $conf->zoneSigner, $conf->rollInit, $conf->rollerConf))) ? ".signed" : "";
    $cmz->doCommit();
    $err = $cmz->getErr();
    if ($err > '') {
        $errors .= "<u>" . $hd['name'] . ":</u> Error in committing\n" . $err . '\n\n';
    } else {
        $mcomm .= "<u>" . $hd['name'] . "</u>: Committing success.\n" . $cmz->getMsg() . "\n";
        $bind->addConfig($hdr['name'], $zarray);
        $done++;
    }
}

$error .= ($errors > '') ? $comm . $errors : '';
$commited .= ($mcomm > '') ? $comm . $mcomm: '';
$errors = '';

$comm = (count($cslave) > 0) ? "<strong>" . "Committing slave records" . "</strong>\n\n" : '';
$commited .= ($comm > '') ? "\n" : '';
$scomm = '';

foreach ($cslave as $slave) {
    $csz = new slaveZone(intval($slave['id']));
    $csz->loadZoneHead();
    $hd = $csz->getZoneHead();
    $hdr = $csz->getZoneHeadRaw();
    $err = $csz->getErr();
    if ($err > '') {
        $errors .= "<u>" . $hd['name'] . ":</u> Error in committing\n" . $err . '\n\n';
    } else {
        $csz->doCommit();
        $scomm .= "<u>" . $hd['name'] . "</u>: Committing success.\n\n";
        $bind->addConfig($hdr['name'], array (
            'type'      => 'slave',
            'masters'   => $hdr['master'],
            'file'      => $hdr['name'],
        ));
        $done++;
    }
}

$error .= ($errors > '') ? $comm . $errors : '';
$commited .= ($scomm > '') ? $comm . $scomm : '';
$bind->saveConfig($conf->conf);

if ($done > 0) {
    $cmd = $conf->rndc . " reload 2> /dev/stdout";
    unset($coutput);
    exec($cmd, $coutput, $exit);
    if ($exit != 0) {
        $error .= "Rndc error(" . $exit . "):\n" . implode("\n", $coutput);
    } else {
        $multi = ($done > 1) ? "zones" : "zone";
        $commited .= "<b>" . $done  . " " . $multi . " has been commited and reloaded:" . "</b>\n  " . implode("\n  ", $coutput) . "\n";
    }
} else {
    $commited .= "<b>" . "There wasn't commited zone" . "</b>";
}

$error = implode("<br />", explode("\n", $error));

$smarty->assign("popuperror", $error);
$smarty->assign("success", $deleted . $commited);
$smarty->assign("pagetitle", "Commit changes");
$smarty->assign("template", "commitdone.tpl");
$smarty->assign("help", help("commit"));
$smarty->assign("menu_button", menu_buttons());
$smarty->display("main.tpl");
?>
