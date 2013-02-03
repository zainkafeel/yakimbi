<?php
/* 

Yakimbi test

*/
require("config.php");
//Calling Api
$api = new api();
$action = $_REQUEST['action'];

if(method_exists($api, $action)){
	print $api->$action();	
}else{
	die("Error invalid");
}

?>