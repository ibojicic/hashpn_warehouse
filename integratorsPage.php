<?php

// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING
// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
//include_once ("includes/open_db.php"); //to del correct integratepage

include_once("adminpro/adminpro_class.php");
$prot = new protect(False, 1);
if ($prot->showPage) {
    $curUser = $prot->getUser(); //name of the logged user
    $isAdmin = $prot->userStatus(); //user priviledges 1 if admin
    $pageName = "integrators"; //info for the header
    $includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
    include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************

    include_once ("classesv2/class.MysqlDriver.php");
    include_once ("classesv2/class.SetMainObjects.php");
    include_once ("classesv2/class.HtmlConstructor.php");
    include_once ("classesv2/class.ExtrasConstruct.php");
    include_once ("classesv2/class.ReadTables.php");
    include_once ("classesv2/class.Integrators.php");

// ************* log access *********************
    include_once ("classesv2/class.Logger.php");
    $logger = new Logger($mydbConfig, $curUser, 1, $pageName);
    $logger->addLog();
    unset($logger);
// ***********************************************
    $input = array_merge($_GET, $_POST); // read input data from _POST/_GET into array
    $currid = $input["id"];
    $currextra = $input["ext"];
    $set = str_ireplace("int", "", $currextra);

    $ReadTables = new ReadTables($mydbConfig, $curUser, $isAdmin);
    $SpecTable = new ExtrasConstruct($pageName, $mydbConfig, $isAdmin, $curUser, $currid);
    $Integrator = new Integrators($mydbConfig, $curUser, $isAdmin, $input);

    $SpecTable->tablesFrom = $ReadTables->listOfTables();

    $surveyspecs = array(
        "SHASSA" => array(
            "intrestable"   => array("corrFlux", "logFred", "logFHalpha", "checkFlag", "use"),
            "imprefix"      => ""
        ),
        "VTSS" => array(
            "intrestable" => array("corrFlux", "logFred", "logFHalpha", "checkFlag", "use"),
            "imprefix"      => ""
        ),
        "SHS" => array(
            "intrestable" => array("CF", "fieldrank", "flux", "corrFlux", "NIItoHalpha", "logFred", "logFHalpha", "checkFlag", "use"),
            "imprefix"      => ""
            
        )
    );

    if (isset($input[$set . "integrator"]) and $input[$set . "integrator"] == "1") {
        $Integrator->setCentAlgorithm();
        if (!$Integrator->setSourceData()) die("Problem with input imaging data....bye");
        if (!$Integrator->getData()) die("Problem with input observational data....bye");
        $Integrator->setMsrNo();
        
        foreach ($Integrator->sourcedata as $data) {
            $Integrator->setInputData($data);
            $Integrator->setField();
            if ($Integrator->setForInt()) {
                if ($Integrator->Integrate()) {
                    if (!$Integrator->recordResults()) die("Problem with recording results...bye");
                    //$Integrator->SHScalibrationData(); SHS
                    $Integrator->calculateFinalResults();
                    //$Integrator->updateSHSSamplesTable(); SHS
                } else {
                    var_dump($Integrator->pythonerror);
//                    exit();
                }
            }
        }
    } elseif (isset($input["delmeasure"]) and $input["delmeasure"] == "1") {
        $Integrator->_deleteMeas($input, $set);
    } elseif (isset($input["chinuse"]) and $input["chinuse"] == "1") {
        $Integrator->_changeInUse($input, $currid, $set);
    } elseif (isset($input["chflag"]) and $input["chflag"] == "1") {
        $Integrator->_changeFlag($input, $currid, $set);
    }


    $SpecTable->getIntInfo($set);
    $SpecTable->setIntResultsData($set . "_results", $surveyspecs[$set]['intrestable']);
    $table = $SpecTable->createIntResultsBox($set, $surveyspecs[$set]['imprefix']);

    echo "<div style='margin-left:50px;margin-top:20px;text-align:center;'>" . $table . "</div>";
    ?>


    <?php

// ********* FOOTER ********************
    include_once("includes/bottom.php");
} //end of adminpro
else
    header("Location: index.php");
?>