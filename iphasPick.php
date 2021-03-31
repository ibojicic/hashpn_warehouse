<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

// **************************************************************
// ******************** HEADER **********************************
// 
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot=new protect(False,1);
if ($prot->showPage) {
$curUser = $prot->getUser(); //name of the logged user
$isAdmin = $prot->userStatus(); //user priviledges 1 if admin
$pageName = "iphaspick"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************

include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.HtmlConstructor.php");
include_once ("classesv2/class.ExtrasConstruct.php");
include_once ("classesv2/class.ReadTables.php");

$currid = $_GET["id"];

// ************* log access *********************
include_once ("classesv2/class.Logger.php");
$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
$logger->addLog($currid);
unset($logger);
// ***********************************************

/*
$ReadTables = new readTables($mydbConfig,$curUser,$isAdmin);
$infoTable = $ReadTables->readInfoTable();
*/

$SpecTable = new ExtrasConstruct($pageName,$mydbConfig,$isAdmin, $curUser,$currid);

if (isset ($_GET["irun"]) and $_GET["irun"] != "")
{
	$SpecTable->updateIPHASPick($currid, $_GET["irun"]);
	echo "<script>window.close();</script>";
}
$table = $SpecTable->createIPHASPicker();
print $table;

// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>

