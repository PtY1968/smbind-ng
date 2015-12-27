<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("zoneadd.php");
}
if ((isset($_GET['delete'])) &&
    ($_GET['delete'] == 'y') &&
    (isset($_GET['i'])) &&
    (is_numeric($_GET['i']))) {
    include("zonedelete.php");
}
if ((isset($_GET['check'])) &&
    ($_GET['check'] == 'y') &&
    (isset($_GET['i'])) &&
    (is_numeric($_GET['i']))) {
    include "zonecheck.php";
}

include("zoneread.php");

?>
