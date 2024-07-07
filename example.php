<?php
$location = realpath(dirname(__FILE__));
require_once $location . '/function.php';
$address = 'example.com';
$return = pingAddress1($address, 1);
var_dump($return);
?>