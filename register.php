<?php
header("Content-Type:text/html;charset=utf-8");
include 'backend.php';
create();
$mobile = $_POST["mobile"];
$address = $_POST["address"];
$userNumber = $_POST["userNumber"];
$textNumber = $_POST["textNumber"];

echo register($textNumber, $userNumber, $mobile, $address);
