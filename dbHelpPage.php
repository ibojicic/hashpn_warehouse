<?php
// TODO CHECK DOUBLE QUOTES -- CHECKING
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
$pageName = "infoobjectpage"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
// ****************** END HEADER ********************************
// **************************************************************
?>

<?php
include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.ReadTables.php");
include_once ("classesv2/class.HtmlConstructor.php");
include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.MakeHelpPages.php");


// ************* log access *********************
/*
include_once ("classesv2/class.Logger.php");
$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
$logger->addLog($currid);
unset($logger);
 * 
 */
// ***********************************************

$HelpPages = new MakeHelpPages($mydbConfig,$curUser,$isAdmin,$_POST);

$hpages = $HelpPages->prepareHelpPages();

include("includes/header.php");
	
include_once ("sectionsmainpage/HelpPage.php");


// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>
