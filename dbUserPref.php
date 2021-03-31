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
//if ($isAdmin != 1) exit();
$pageName = "userpref"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]);//include extra javascripts
// ****************** END HEADER ********************************
// **************************************************************
?>

<?php

include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.ReadTables.php");
include_once ("classesv2/class.HtmlConstructor.php");

// ************* log access *********************
include_once ("classesv2/class.Logger.php");
$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
$logger->addLog();
unset($logger);
//***********************************************


$input = array_merge($_GET, $_POST); // read input data from _POST/_GET into array

$tables = new readTables($mydbConfig,$curUser,$isAdmin);

$userdata = $tables->readUserData(True);
if (isset($input["changesubmit"])) {
    
}
include("includes/header.php");
include("sectionuserinfo/resultsuserinfopage.php");



// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>
