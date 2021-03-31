<?php
include_once ("../includes/pndb_config.php");
//TODO CHECKING IF REQUESTS ARE COMING FROM OUTSIDE
//if (!isset($_SERVER["HTTP_REFERER"]) or stripos(str_ireplace("//", "/", $_SERVER["HTTP_REFERER"]), $mydbConfig["checkUsrnRef"]) === False) die("Who are you and what are you doing?");
include_once ("../includes/functions.php");	
include("adminpro_config.php");
mysql_connect($globalConfig['dbhost'], $globalConfig['dbuser'], $globalConfig['dbpass']);
mysql_select_db( $globalConfig['dbase']);
$username = $_POST["username"];
$query = mysql_query("SELECT * from `userslist` where `userName`='$username' ");
$find = mysql_num_rows($query);
echo $find;

?>
