<?php
require_once "include.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("recordwrite.php");
}
include("recordread.php");

?>
