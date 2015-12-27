<?php
/*
 * optionswrite.php
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
 * Process the options form and save the changes
 *
 */

require_once "include.php";

if($user->isAdmin()) {
    $allrec = array();
    foreach($_POST as $key => $value) {
        $val = $value;
        switch ($key) {
            case 'master':
            case 'prins':
            case 'secns':
                $val = hostToIdn(preg_replace('/([\s\r\n]+|\.$)/', '', $value));
                break;
            case 'range':
                $val = (intval($value) > 0) ? intval($value) : 10;
                break;
            case 'hostmaster':
                $val = preg_replace('/([\s\r\n]+|\.$)/', '', $value);
                break;
            default:
                $val = ((strtolower($value) == 'on') || (strtolower($value) == 'off')) ? strtolower($value) : 'off';
        }
        $allrec[$key] = $val;
    }
    foreach($allrec as $key => $value) {
        $res = $db->query("UPDATE options SET prefval = '" . $value . "' " .
            "WHERE prefkey = '" . $key . "'"
        );
    }
}
else {
    access_denied();
}

?>
