<?php
/*
 * slave_newzone.php
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
 * Gathers data from config and the db to present informations to the
 * template.
 *
 */

require_once "include.php";

if($user->isAdmin())  {
    $smarty->assign("pagetitle", "New slave zone");
    $smarty->assign("userlist", $user->getAllusers());
    $smarty->assign("current_user", $user->getId());
    $smarty->assign("master", $conf->master);
    $smarty->assign("template", "slave_newzone.tpl");
    $smarty->assign("help", help("newslavezone"));
    $smarty->assign("menu_button", menu_buttons());
    $smarty->display("main.tpl");
}
else {
    access_denied();
}

?>
