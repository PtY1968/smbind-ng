<?php
/*
 * convert.php
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
 *********************************************************************
 *
 * Processes the inport form and do the import.
 *
 */

require_once "include.php";

$out = array();

if($user->isAdmin()) {
    if(($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['method']))) {
        $method = $_POST['method'];
        $valid = true;
        $problem = '';
        $zone = '';
        $content = array();
        $temp = '';
        switch ($method) {
            case "file":
                $zone = ((isset($_POST['fil_domain'])) && (isset($_FILES['fil']['tmp_name']))) ? $zone = $_POST['fil_domain'] : '';
                $content = ($zone > '') ? file($_FILES['fil']['tmp_name']) :array();
                $smarty->assign("method", "Upload from file");
                break;
            case "list":
                $zone = ((isset($_POST['sel_domain'])) && ($_POST['sel'] != '- select file -')) ? $_POST['sel_domain'] : '';
                $content = ($zone > '') ? file($conf->path . idnToHost($_POST['sel'])) : array();
                $smarty->assign("method", "Select orphan file from list");
                break;
            case "text":
                $zone = ((isset($_POST['txt_domain'])) && (isset($_POST['txt']))) ? $_POST['txt_domain'] : '';
                $content = ($zone > '') ? explode("\n", $_POST['txt']) : array();
                $smarty->assign("method", "Write zone manually or pasted from clipboard");
                break;
            default:
                problem();
        }
        if (count($content) < 4) {
            problem("nocontent");
        }
        $gzone = $zone;
        $zone = hostToIdn($zone);
        if ($method != 'list') {
            $temp = tempnam($conf->tmp_path, "$zone");
            $fh = fopen($temp, "w");
            fwrite($fh, implode("\n", $content));
            fclose($fh);
            $check = checkZoneFile($temp, $zone);
            unlink($temp);
            if (!$check) {
                problem("nocontent");
            }
        }
        $mz = new masterZone($gzone);
        $sz = new slaveZone($gzone);
        if (($mz->loadZoneHead()) || ($sz->loadZoneHead())) {
            $mz = array();
            $sz = array();
            problem("existzone");
        }
        $smarty->assign("zone", $gzone);
        $nz = new masterZone();
        if (($nz->parseZone($content, $zone, $user->getId())) && ($nz->getId() > 0)) {
            $smarty->assign("pagetitle", "Review imported records");
            $smarty->assign("template", "uploadreview.tpl");
            $smarty->assign("output", explode("\n", $nz->getConf($conf->hostMaster)));
            $smarty->assign("help", help("uploadreview"));
            $smarty->assign("menu_button", menu_buttons());
        } else {
            $smarty->assign("problem", explode("\n", $nz->getErr()));
            $smarty->assign("pagetitle", "Import error");
            $smarty->assign("template", "uploadproblem.tpl");
            $smarty->assign("help", help("uploadproblem"));
            $smarty->assign("menu_button", menu_buttons());
        }
        $smarty->display("main.tpl");
    } else {
        problem();
    }
} else {
    access_denied();
}
?>
