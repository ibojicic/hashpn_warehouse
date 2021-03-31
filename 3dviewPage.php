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
if (!$isAdmin) die("You are not permitted to access this page....<br>");
$userGroup = $prot->groupStatus(); 
$pageName = "3dviewobjectpage"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
//include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************

/*
include_once ("classesv2/class.HtmlConstructor.php");
include_once ("classesv2/class.GetObjectData.php");
include_once ("classesv2/class.GetObjectElements.php");
include_once ("classesv2/class.GetObjectSpectra.php");
include_once ("classesv2/class.EditRecords.php");

*/

include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.ReadTables.php");


// ************* log access *********************
include_once ("classesv2/class.Logger.php");
$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
$logger->addLog();
unset($logger);
// ***********************************************

/* APPLY CHANGES */

/* TODO CHECK THAT USER CAN ONLY DELETE IT'S OWN RECORD */
include("includes/header.php");

// ********* input information tables ***********


#    echo "<div id='info3d'></div>";
include("sectionsmainpage/3dview.php");


// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>