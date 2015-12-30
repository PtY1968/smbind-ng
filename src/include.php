<?php
/*
 * include.php
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
 * Contains the main definitions, functions and elements
 *
 */

define('PATH_PATTERN','#(src/|)[^\./]+\.php$#');

$docroot = preg_replace(PATH_PATTERN, '', $_SERVER['SCRIPT_FILENAME']);
$base = preg_replace(PATH_PATTERN, '', $_SERVER['PHP_SELF']);
$src = $base . 'src/';

set_include_path(get_include_path() . ":" . $docroot . 'lib/');
require_once("smbind.class.php");
$conf = new Configuration($docroot);
$session = new Session();

if ($conf->ReCaptcha) {
    require_once("recaptchalib.php");
}

set_include_path(get_include_path() . ":" . $conf->Smarty_Path);

header("Content-Type: text/html; charset=UTF-8");
header("X-Content-Security-Policy: default-src 'self'; script-src 'self' self www.gstatic.com www.google.com google.com; img-src 'self' data: www.gstatic.com www.google.com google.com; style-src 'self'; font-src 'self'; frame-src 'self'; connect-src 'self' apis.google.com; object-src 'self'");

require_once("Smarty.class.php");
$smarty = new Smarty;
$smarty->assign('TITLE',$var = $conf->Title);
$smarty->assign('footerleft',$conf->Footer);
$smarty->assign('footerright',$conf->Marker);
$smarty->assign('sdomain',$conf->StaticDomain);
$smarty->assign('skin',$conf->Template);
$smarty->template_dir = $conf->Smbind_Ng . "templates";
$smarty->compile_dir = $conf->Smbind_Ng . "templates_c";
$smarty->assign("base", $base);
$smarty->assign("static", $base . "static/");
$smarty->assign("src", $src);
$smarty->assign("captcha","no");
$smarty->assign("recaptcha","");
$smarty->assign("menu_button","");
$smarty->assign("donotcommit","no");
$smarty->assign('sec', ($conf->IsDNSSec) ? "yes" : "no");
$smarty->assign('popuperror',NULL);

$cap_rsp = NULL;
if(isset($_POST["recaptcha_response_field"])){
    if ($_POST["recaptcha_response_field"]!=''){
        $rsp = recaptcha_check_answer (
            $conf->RC_PrivKey,
            $_SERVER["REMOTE_ADDR"],
            $_POST["recaptcha_challenge_field"],
            $_POST["recaptcha_response_field"]
        );
        if(!$rsp->is_valid) {
            $cap_rsp = $rsp->error;
        }
      } else {
        $cap_rsp = 'incorrect-captcha-sol';
      }
}

if (isset($_POST['username']) && isset($_POST['password']) && ($cap_rsp == NULL)) {
    $session->login($_POST['username'], $_POST['password']);
}

$user = new User();
$smarty->assign("loggedinuser",$user->getFullName());

if ($user->getId() == 0) {
    login_page($smarty);
}

if ((isset($_SERVER['PHP_SELF'])) && (basename($_SERVER['PHP_SELF']) != 'index.php')) {
    $smarty->assign("menu_current", $src . basename($_SERVER['PHP_SELF']));
} else {
    $smarty->assign("menu_current", $base);
}

if($user->isAdmin()) {
    $smarty->assign("admin", "yes");
} else {
    $smarty->assign("admin", "no");
}

/*
 *
 * name: debug
 * @param   variable type a count of parameters for dumping it to the
 * error_log
 *
 * @return <empty>
 *
 */
function debug() {
    for($i = 0 ; $i < func_num_args(); $i++) {
        $param = func_get_arg($i);
        $dump = '';
        if ((is_array($param)) || (is_object($param))) {
            ob_start();
            if (is_object($param)) {
                $phpv = explode('.', phpversion());
                if (intval($phpv[0] . $phpv[1]) < 56) {
                    var_dump($param->__debugInfo());
                } else {
                    var_dump($param);
                }
            } else {
                var_dump($param);
            }
            $dump .= ob_get_clean();
        } else {
            $dump .= $param;
        }
        $bt = debug_backtrace();
        error_log($bt[0]['file'] . "(" . $bt[0]['line'] . "):\n" . $dump);
    }
}

/*
 *
 * name: makePart
 *
 * Creates paging feature
 *
 * @param
 * $all -> lentgt of the listed elements
 * $page -> actual page count
 *
 * @return range for this page
 *
 */
function makePart($all, $page) {
    global $smarty;
    global $conf;
    $from = 0;
    $to = $all;
    $pages = 0;
    $max = 0;
    if ($conf->isExists('range')) {
        $max = $conf->Range;
    }
    if ($max > 0) {
        $pages = (int)($all/$max);
        $mod = $all % $max;
        if ($mod > 0) {
            $pages++;
        }
        if ($pages < $page) {
            $page = $pages;
        }
        $from = ($page-1) * $max;
        $to = $from;
        if (($page == $pages) && ($mod > 0)) {
            $to += $mod;
        } else {
            $to += $max;
        }
        $from = ($from < 0) ? 0 : $from;
    }
    $pagelist = array();
    for ($i=1;$i<=$pages;$i++) {
        $pagelist[] = $i;
    }
    $pagelist = (sizeof($pagelist) > 1) ? $pagelist : NULL;
    $smarty->assign("current_page", $page);
    $smarty->assign("pages", $pagelist);
    return array($from, $to);
}

/*
 *
 * name: rndc_status
 * @param no
 * @return the status of rndc command
 *
 */
function rndc_status() {
    global $conf;
    $cmd = $conf->Rndc . " status > /dev/null";
    system($cmd, $exit);
    return $exit;
}

/*
 *
 * name: logout
 * @param no
 * @return no
 *
 * Displays the logout page and destroy the http session
 *
 */
function logout() {
    global $smarty;
    global $session;
    $session->destroy();
    $smarty->assign("menu_button", array());
    $smarty->assign("pagetitle", "Logout");
    $smarty->assign("template", "logout.tpl");
    $smarty->assign("help", help("logout"));
    $smarty->display("main.tpl");
}

/*
 *
 * name: login_page
 * @param Smarty object
 * @return no
 *
 * Checks the captcha requirements and provides the login page
 *
 */
function login_page($smarty) {
    global $cap_rsp;
    global $base;
    global $conf;

    if($conf->ReCaptcha) {
        $nocap = false;
        foreach ($conf->NoCaptcha as $ip) {
            $nocap = ($nocap or ($ip == $_SERVER['REMOTE_ADDR']));
        }
        if(!$nocap) {
        $smarty->assign("captcha","yes");
        $smarty->assign("recaptcha",recaptcha_get_html($conf->Rc_Pubkey,$cap_rsp,true));
        }
    }
    $smarty->assign("action", $base);
    $smarty->assign("pagetitle", "Login");
    $smarty->assign("template", "login.tpl");
    $smarty->assign("help", help("login"));
    $smarty->display("main.tpl");
    die();
}

/*
 *
 * name: problem
 * @param
 * $reason -> short tag for the problem
 * $title -> header title
 *
 * @return no
 *
 * Provides a kind of error page
 *
 */
function problem($reason = NULL, $title = NULL) {
    global $smarty;
    $tit = (isset($title)) ? $title : title($reason);
    $smarty->assign("pagetitle", $tit);
    $smarty->assign("template", "accessdenied.tpl");
    $smarty->assign("reason", reason($reason));
    $smarty->assign("help", help("accessdenied"));
    $smarty->assign("menu_button", menu_buttons());
    $smarty->display("main.tpl");
    die();
}

/*
 *
 * name: access_denied
 * @param no
 * @return no
 *
 * Generates the access denied problem
 *
 */
function access_denied() {
    problem("notadmin");
}

/*
 *
 * name: reason
 * @param optional
 * $reason -> short tag of the problem
 *
 * @return description string
 *
 * Generates description string for the known reason
 *
 */
function reason($reason = '') {
    switch ($reason) {
        case "notown":
            return "You don't own this zone.";
        case "unauth":
            return "You are not authorized for this procedure";
        case "notadmin":
            return "You are not an administrator.";
        case "noslave":
            return "The slave zone hasn't replicated yet. Try again later.";
        case "existzone":
            return "The zone already exists in the database.";
        case "existfile":
            return "The zonefile already exists on the system.";
        case "existuser":
            return "The user already exists in the database.";
        case "nozonename":
            return "That's not much of a zone name.";
        case "deleteadmin":
            return "You may not delete the default admin user.";
        case "deleteys":
            return "You may not delete yourself.";
        case "nocontent":
            return "The given content empty or invalid.";
        case "nocommit":
            return "There is no commitable content.";
        default:
            return "Unknown error. Please it report to your administrator";
    }
}

/*
 *
 * name: title
 * @param optional
 * $reason -> short tag of the problem
 *
 * @return title string
 *
 * Generates title string for the known reason
 *
 */
 function title($reason = '') {
    switch ($reason) {
        case "notown":
        case "notadmin":
        case "deleteadmin":
        case "deleteys":
        case "unauth":
            return "Access denied";
        case "noslave":
        case "nocontent":
            return "No data";
        case "nocommit":
        case "existzone":
        case "existfile":
        case "existuser":
        case "nozonename":
            return "Error in process";
        default:
            return "Something wrong happened";
    }
}

/*
 *
 * name: help
 * @param
 * $help tag for the info footer
 *
 * @return the info footer
 *
 * Generates the info footer
 *
 */
function help($help) {
    switch ($help) {
        case "login":
            return "Please log in.";
        case "mainpage":
            return "User status and Server status are displayed, along with any zone informations.";
        case "zoneview":
            return "Your zone is dumped. Here you can view the zone in bind syntax.<small><br />" .
                "<span class=attention>You cannot edit the zonefile directly!</span></small>";
        case "zonepview":
            return "Your zone will look like as above. You can view it in bind syntax.<small><br />" .
                "<span class=attention>This zone hasn't commited yet!</span></small>";
        case "zoneread":
            return "Your zones are displayed. Here you can create a zone, edit a zone, view a zone, or delete a zone.";
        case "newzone":
            return "Enter your new zone's domain name, name servers and smbind-ng owner. " .
                "This will create a new zone with a SOA and NS record.<small><br />" .
                "The Web/Mail/FTP fields will create these A, CNAME, and MX template records for you. " .
                "Otherwise, leave them blank.<br />In these fields you can use IP addresses or hostnames too. In this case you need to take care of the trailing dots.</small>";
        case "newslavezone":
            return "Enter your new slave zone's domain name, address of the master server and smbind-ng owner.";
        case "recordread":
            return "Here you can modify your zone's SOA record, or add, edit, or delete resource records.";
        case "userlistread":
            return "Here you can add, edit, or delete smbind-ng users.";
        case "commit":
            return "Your zone files are commited to disk, error checked, and reloaded.";
        case "precommit":
            return "Your modifications will be applied to the system, and the related services will notified about the changes.";
        case "optionsread":
            return "Here you can change options that define how smbind-ng works.";
        case "deletezone":
            return "Please confirm.";
        case "uploadreview":
            return "Please confirm your uploaded data. Some records may be missig basad on your handled record-type options";
        case "uploadproblem":
            return "Please fix the errors. The file output of namedcheckzone has errors.";
        case "upload":
            return "Here you can import a zone what is in legal bind zonefile format. Choose import method!" .
                " Available methods:<small><br /><strong>Orphan files:</strong>(maybe disabled) Some file in" .
                " your config directory without database records. <strong>Browse:</strong> Zone file upload" .
                " from your computer. <strong>Edit:</strong> Paste contents into the box from your clipboard.</small>";
        case "deleteuser":
            return "Please confirm.";
        case "newuser":
            return "Here can you add a new user.<br />" .
                "<span class=attention>Password requirements:</span> You must use letters (both upper- and lowercase), numbers and symols. Minimum length is 8 characters. " .
                "10 charachers length, and using more uppercase letters and numbers is recommended.";
        case "userread":
            return "Here can you change the user's properties. If you don't want to change the password, leave it empty." .
                "<br /><span class=attention>Password requirements:</span> You must use letters (both upper- and lowercase), numbers and symols. Minimum length is 8 characters. " .
                "10 charachers length, and using more uppercase letters and numbers is recommended.";
        case "chpass":
            return "Here can you change your password. <small>You need to give your current password before the new!</small><br />" .
                "<span class=attention>Password requirements:</span> You must use letters (both upper- and lowercase), numbers and symols. Minimum length is 8 characters. " .
                "10 charachers length, and using more uppercase letters and numbers is recommended.";
        case "savepass":
            return "Login using your new password." .
                "<small><br />This page automatically open it within 10 seconds</small>";
        case "accessdenied":
            return "<span class=attention>Access denied.</span><br />This procedure not allowed with your privileges.";
        case "problem":
            return "A problem has occurred.";
        case "logout":
            return "You have been successfully logged out. Click <a class=attention id=reload href=\"../\">here</a> if you wish to log in again." .
                "<small><br />This page automatically open it within <span id=counter>10</span> seconds</small>";
        default:
            return "";
    }
}

/*
 *
 * name: getorphan
 * @param no
 * @return an array with found filenames
 *
 * Find and check local files for importing
 *
 */
function getorphan() {
    global $conf;
    global $user;

    $files = ' ' . implode(' ', scandir($conf->Path)) . ' ';
    $mzones = $user->getMasters();
    $szones = $user->getSlaves();
    foreach ($mzones as $id) {
        $z = new masterZone(intval($id));
        $z->loadZoneHead();
        $zone = $z->getZoneHeadRaw();
        $files = str_replace(' ' . $zone['name'] . ' ', ' ', $files);
    }
    foreach ($szones as $id) {
        $z = new slaveZone(intval($id));
        $z->loadZoneHead();
        $zone = $z->getZoneHeadRaw();
        $files = str_replace(' ' . $zone['name'] . ' ', ' ', $files);
    }
    $vzf = array();
    foreach (explode(' ', trim($files)) as $entry) {
        if ((is_file($conf->Path . $entry)) && (preg_replace('/\.(signed|private|key|krf|jnl|bind)$/', '', $entry) == $entry)) {
            if (checkZoneFile($conf->Path . $entry, $entry)) {
                $vzf[] = hostToIdn($entry);
            }
        }
    }

    return $vzf;
}

/*
 *
 * name: checkZoneFile
 * @param
 * $file -> path the file
 * $zone -> zonename
 *
 * @return true/false (aftert chacking the zoneile)
 *
 */
function checkZoneFile($file, $zone) {
    global $conf;

    $cmd = $conf->namedCheckZone . " -i local " . $zone. " " . $file . " 2>/dev/stdout";
    unset($coutput);
    exec($cmd, $coutput, $exit);
    return ($coutput[sizeof($coutput)-1] == 'OK');
}

/*
 *
 * name: menu_buttons
 * @param no
 * @return no
 *
 * Generates the menu entries
 *
 */
function menu_buttons() {
    global $user;
    global $smarty;
    global $src;
    global $base;

    $cmasters = $user->getCommitableZones('master');
    $cmc = (is_array($cmasters)) ? sizeof($cmasters) : 0;
    $cslaves = $user->getCommitableZones('slave');
    $csc = (is_array($cslaves)) ? sizeof($cslaves) : 0;
    $commitables = $cmc + $csc;
    if($commitables == 0) {
        $committext = "";
        $smarty->assign("donotcommit","yes");
    }
    else {
        $committext = "\" id=\"commitable\" class=\"attention";
    }

    if (sizeof($user->getUnvalidatedZones('master')) +
        sizeof($user->getUnvalidatedZones('slave')) +
        sizeof($user->getDeletedZones('slave')) +
        sizeof($user->getDeletedZones('master')) > 0) {
        $maintext = "\" class=\"attention";
    }
    else {
        $maintext = "";
    }

    return array(
        array("title" => "Main", "link" => $base . $maintext),
        array("title" => "Master zones", "link" => $src . "zonelist.php"),
        array("title" => "Slave zones", "link" => $src . "slave_zonelist.php"),
        array("title" => "Import zones", "link" => $src . "upload.php"),
        array("title" => "Users", "link" => $src . "userlist.php"),
        array("title" => "Change password", "link" => $src . "chpass.php"),
        array("title" => "Commit changes", "link" => $src . "commit.php" . $committext),
        array("title" => "Options", "link" => $src . "options.php"),
        array("title" => "Log out", "link" => $src . "logout.php")
    );
}

?>
