<?php
include 'backend.php';
create();
$address = $_POST["address"];

echo verify($address);
?>
