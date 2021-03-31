<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot=new protect(False,1);
if ($prot->showPage) {
$curUser = $prot->getUser(); //name of the logged user
$isAdmin = $prot->userStatus(); //user priviledges 1 if admin
$userGroup = $prot->groupStatus(); 
$pageName = "checkobjectpage"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
//include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************

include_once ("classesv2/class.MysqlDriver.php");
include_once ("classesv2/class.ReadTables.php");
include_once ("classesv2/class.SetMainObjects.php");
include_once ("classesv2/class.GetObjectData.php");
include_once ("classesv2/class.GetObjectElements.php");
include_once ("classesv2/class.GetObjectSpectra.php");
include_once ("classesv2/class.EditRecords.php");
include_once ("classesv2/class.CheckObjects.php");
include_once ("classesv2/class.HtmlConstructor.php");

// ************* log access *********************
include_once ("classesv2/class.Logger.php");
$logger = new Logger($mydbConfig, $curUser, 1, $pageName);
$logger->addLog();
unset($logger);
// ***********************************************

$CheckObjects = new CheckObjects($mydbConfig, $_GET, $isAdmin, $curUser);

if ($CheckObjects->finished) header( "Location: dbMainPage.php" ) ;

$currid = $CheckObjects->currentId;


/* APPLY CHANGES */

/* TODO CHECK THAT USER CAN ONLY DELETE IT'S OWN RECORD */

// ********* input information tables ***********

$EditRecs = !empty ($_POST) ? new EditRecords($mydbConfig,$currid, $_POST,$isAdmin,$curUser) : False;

$ObjectData = new GetObjectData($mydbConfig,$currid,$curUser,$isAdmin);

$PNG = $ObjectData->getField(MAIN_TABLE, MAIN_DESIGNATION);

if ($CheckObjects->nextId) {
	$mydbConfig["headervars"]["position"] = "PNG ".$PNG." / <a href='objectCheckPage.php?doneid=".$currid."&chksmpl=".$CheckObjects->_sampleId."'>Next Object (".$CheckObjects->done." done / ".$CheckObjects->togo." to do)</a>";
} else {
 	$mydbConfig["headervars"]["position"] = "PNG ".$PNG." / <a href='objectCheckPage.php?doneid=".$currid."&chksmpl=".$CheckObjects->_sampleId."&finish=y'>Finish This Sample</a>";
}

$SpecTable = new GetObjectElements($mydbConfig,$currid,$curUser,$isAdmin,$ObjectData->results,"objectCheckPage.php?nextid=".$CheckObjects->nextId."&chksmpl=".$CheckObjects->_sampleId."&");
$Spectra = new GetObjectSpectra($mydbConfig,$currid,$curUser,$isAdmin);

$fullinfo = $SpecTable->createGenInfoTables();
$tabNotes = $SpecTable->createUserComments($ObjectData->userComments);
$headerTable = $SpecTable->createObjectHeader($mydbConfig["divids"]["objectheader"]);
$coordsTable = $SpecTable->createObjectCoords();
$CScoordsTable = $SpecTable->createCSCoords();
$objLinks = $SpecTable->createObjLinks();

$extlinks = $SpecTable->createMenyExtLinks();
$galeryBox = $SpecTable->createGalleryBox();

/*spectra*/
$plots = $Spectra->createPlots();
$splinks = $Spectra->getSpectraLinks();
$splinkstable = $SpecTable->createSpectraLinksTable($splinks);
$deflines = $SpecTable->setDefaultLinesForm();

$fitslinks = $SpecTable->fitsImagesTable();

//**************** DISPLAY RESULTS **********************
include("includes/header.php");
include("sectionsobjectinfopage/menyinfoObjectPage.php");
include("sectionsobjectinfopage/resultsobjectInfoPage.php");
// ********* FOOTER ********************
include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");
?>
