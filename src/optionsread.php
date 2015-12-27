<?php
/*
 * optionsread.php
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
 * Reads and provides informations for the options page
 *
 */

require_once "include.php";

if($user->isAdmin()) {
    $res = $db->query("SELECT prefkey, prefval FROM options " .
        "WHERE preftype = 'record' ORDER by prefkey"
    );
    if (MDB2::isError($res)) {
        $smarty->assign("popuperror", $res->getMessage() . "<br />" . $res->getDebugInfo());
        problem();
    }
    $recordarray = array();
    $rows = $res->numRows();
    for($x=0, $y=0, $i=0; $i<$rows; $y++, $i++) {
        if($y == 4) {
            $x++;
            $y = 0;
        }
        $recordarray[$x][$y] = $res->fetchRow();
    }
    $options = array();
    $res = $db->query("SELECT prefkey, prefval FROM options " .
        "WHERE preftype = 'normal' ORDER by prefkey"
    );
    if (MDB2::isError($res)) {
        $smarty->assign("popuperror", $res->getMessage() . "<br />" . $res->getDebugInfo());
        problem();
    }
    while ($rec = $res->fetchRow()) {
        switch ($rec['prefkey']) {
            case 'hostmaster':
                $key = 0;
                break;
            case 'prins':
                $key = 1;
                $rec['prefval'] = idnToHost($rec['prefval']);
                break;
            case 'secns':
                $key = 2;
                $rec['prefval'] = idnToHost($rec['prefval']);
                break;
            case 'master':
                $key = 3;
                $rec['prefval'] = idnToHost($rec['prefval']);
                break;
            default:
                $key = 4;
        }
        $options[$key] = $rec;
    }
    $smarty->assign("records", $recordarray);
    $smarty->assign("options", $options);
    $smarty->assign("pagetitle", "Options");
    $smarty->assign("template", "optionsread.tpl");
    $smarty->assign("help", help("optionsread"));
    $smarty->assign("menu_button", menu_buttons());
    $smarty->display("main.tpl");
} else {
    access_denied();
}

?>
