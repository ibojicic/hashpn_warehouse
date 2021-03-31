<?php

$username="gpneadmin";
$password="(g.pne.admin)";
$host="localhost";

$link = mysql_connect($host,$username,$password);

if (!$link) die('Could not connect: ' . mysql_error());
//@mysql_select_db($database) or die( "Unable to select database");


?>