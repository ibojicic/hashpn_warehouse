<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot=new protect();
if ($prot->showPage) {
$curUser = $prot->getUser(); //name of the logged user
$isAdmin = $prot->userStatus(); //user priviledges 1 if admin
if ($isAdmin != 1) exit();
$pageName = "statspage"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]);//include extra javascripts
// ****************** END HEADER ********************************
// **************************************************************
?>

<?php

include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.ReadTables.php");
include_once ("classesv2/class.HtmlConstructor.php");
include_once ("classesv2/class.StatsPages.php");

// ************* log access *********************
//include_once ("classesv2/class.Logger.php");
//$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
//$logger->_addLog();
//unset($logger);
//***********************************************


$input = array_merge($_GET, $_POST); // read input data from _POST/_GET into array
	

$Stats = new StatsPages($mydbConfig,$curUser,$isAdmin,$input);
$links = $Stats->setLinks();

$plots = $Stats->plotAccessLog();

$ques = $Stats->getQuedObjects();
include("includes/header.php");


include("sectionplotdata/statsplot.php");

// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>
