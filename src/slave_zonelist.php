<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($_POST['Submit'] == 'Add zone') {
        include("slave_zoneadd.php");
    }
    if($_POST['Submit'] == 'Save') {
        include("slave_zonewrite.php");
    }
}
if(isset($_GET['delete']) && $_GET['delete'] == 'y') {
    include("slave_zonedelete.php");
}

include("slave_zoneread.php");

?>
