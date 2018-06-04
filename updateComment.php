<?php
include 'backend.php';
create();
$id = $_POST["id"];
$status = $_POST["status"];

updateComment($id, $status);
