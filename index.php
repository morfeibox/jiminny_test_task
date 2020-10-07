<?php
include './api/Api.php';
$api = new API();
header('Content-Type: application/json');
echo $api->select();
?>
