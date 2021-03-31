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
$pageName = "plotdata"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
//include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************

include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.HtmlConstructor.php");
include_once ("classesv2/class.PlotData.php");
include_once ("classesv2/class.ReadTables.php");


// ************* log access *********************
include_once ("classesv2/class.Logger.php");
$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
$logger->addLog();
unset($logger);
// ***********************************************

$ReadTables = new ReadTables($mydbConfig,$curUser,$isAdmin);

$datainfotables = array ("infoTable" => $ReadTables->readInfoTable(False,"`varType` = 'REAL'"));

$dataplot = new PlotData($_POST,$mydbConfig, $curUser, $isAdmin,$datainfotables);

$selectplot = $dataplot->setSelectPlot();

$selectvarsX = $dataplot->createColumnsSelect("selectxvar","pushvalue(this,\"xvar\")");
$selectvarsY = $dataplot->createColumnsSelect("selectyvar","pushvalue(this,\"yvar\")");

$currplot = $dataplot->selectedPlot;

$data = $dataplot->getData();

$plot = $dataplot->createPlot();

//**************** DISPLAY RESULTS **********************
include("includes/header.php");
//**************** DISPLAY NOTIFICATIONS **********************

if ($dataplot->runresponse) {
    echo "<section id='main'>";
    foreach ($dataplot->runresponse as $notification) echo $notification;
    echo "</section>";
} else {
    include("sectionplotdata/plotplot.php");
    include("sectionplotdata/plotmeny.php");
}
// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>
