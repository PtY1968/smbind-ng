<?php
/*
 * deleteuser.php
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
 * Check what need to delete and provides info for confirmation
 *
 */

require_once "include.php";

if($user->isAdmin()) {
    $num = intval($_GET['i']);
    switch ($num) {
        case 0:
        case 1:
            problem("deleteadmin");
            break;
        case $user->getId();
            problem("deleteys");
            break;
        default:
            $smarty->assign("pagetitle", "Delete user");
            $duser = new User($num);
            $smarty->assign("user", $duser->getUser());
            $smarty->assign("template", "deleteuser.tpl");
            $smarty->assign("help", help("deleteuser"));
            $smarty->assign("menu_button", menu_buttons());
            $smarty->display("main.tpl");
    }
} else {
    access_denied();
}
?>
