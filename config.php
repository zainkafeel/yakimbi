<?php
include_once("classes/db.class.php");
include_once("classes/api.php");
session_start();
//Anymous user id every time valid till session live
if(empty($_SESSION['anymous_usr']))
$_SESSION['anymous_usr'] = time();

$usrid = $_SESSION['anymous_usr'];

// Database config here
$dbconfig = array(
	'host' => 'localhost',
	'user' => 'root',
	'pass' => 'root',
	'name' => 'yakimbi'
);

$db = db_mysql::getInstance();
/*
$records = $db->query("
	SELECT *
	FROM `fav_images`
") or $db->raise_error(); // Leaving 'raise_error()' blank will create an error message with the SQL

while($row = $db->fetch_array($records)){
	echo $row['id'];
}
*/
$api_key 	=	"d423731024e76b6ab9193b236f329cdd";
$per_page   =	"20";
 
?>