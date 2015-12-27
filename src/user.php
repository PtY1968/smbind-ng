<?php
require_once "include.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("userwrite.php");
} else {
    $smarty->assign("success",'');
}
include("userread.php");

?>
