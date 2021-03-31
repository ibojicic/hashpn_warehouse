<?php

// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING
// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot = new protect(False, 1);
if ($prot->showPage) {
    $curUser = $prot->getUser(); //name of the logged user
    $isAdmin = $prot->userStatus(); //user priviledges 1 if admin
    $pageName = "addnewobjectpage"; //info for the header
    $includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
    include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************

    include_once ("classesv2/class.MysqlDriver.php");
    include_once ("classesv2/class.SetMainObjects.php");
    include_once ("classesv2/class.AddNewObjects.php");
    include_once ("classesv2/class.SetMainViews.php");
    include_once ("classesv2/class.ReadTables.php");
    include_once ("classesv2/class.HtmlConstructor.php");

// ************* log access *********************
    include_once ("classesv2/class.Logger.php");
    $logger = new Logger($mydbConfig, $curUser, 1, $pageName);
    $logger->addLog();
    unset($logger);
// ***********************************************


    /*
     *  get input data
     * 	convert coordinates
     * 	calculate PNG
     * 	find 3 closest objects => if one of these cancel input
     * 	check simbad by coordinates => find 3 closest objects => if one of these radio button to link simbad ids	
     * 	if submit => input data to main => submit cronjob for images
     * 	return to main page
     */

    $addObjects = new AddNewObjects($mydbConfig, $_GET, $isAdmin, $curUser);

    $selection = array(
        "fselect" => array(1, 2, 3, 21),
        "sselect" => array(""), //MW","truePN","possPN"),
        "imselect" => array(3, 12, 2, 14),
        "grimselect" => array("shs/iphas/vphas/sss", "quotient", "nvss/mgps", "2mass_RGB", "wise432_RGB")
    );
    $views = new SetMainViews($selection, $mydbConfig, $curUser, $isAdmin, False);

    $display = array();

    if ($addObjects->flagoperation == "adddata") {
        if ($addObjects->runresponse) {
            $display = $addObjects->runresponse;
        } else {
            // INPUT DATA TABLE	
            $display[0] = "<h1>New object:</h1><div style='height: 50px'>" . $views->tableViewResults($addObjects->newobjects["inputdata"], "addnewtable", "table2 center") . "</div><br>";

            // NEARBY OBJECTS
            $display[1] = "<h4 class='alert_info'>I've found these nearby objects in the database...</h4><br><div class='nearestobjects'>" .
                    $views->imageViewResults($addObjects->newobjects["nearby_full"], makeOneColMain($addObjects->newobjects["nearby_full"], MAIN_ID), "group", "r [arcsec]")
                    . "</div>";

            // SIMBAD LINK
            $display[2] = "<h4 class='alert_info'>Please check nearby objects in <a href='" . $addObjects->newobjects["simbad"]["simbad"] . "' target='_blank'>Simbad</a></h4><br>";

            $display[3] = "<form action='addNewObjPage.php' method='GET'>" . $addObjects->newobjects["checkbox"] .
                    "<input type='hidden' name='insrtnewobj' value='y'><input type='submit' value='Submit'></form>";
        }
    } elseif ($addObjects->flagoperation == "submitdata" and $addObjects->flagsucces) {
        // MESSAGE
        $display[0] = "<h4 class='alert_info'>Object with coordinates: " . $addObjects->newobjects['RAJ2000'] . " " . $addObjects->newobjects['DECJ2000'] . " is successfully added to the database.<br>" .
                "Visit  " . $addObjects->newobjects['link'] . " to edit/add data and to submit the cron job for making images (Basic Data => Re-Download+Redo).<br></h4><br>";
    }

//**************** DISPLAY RESULTS **********************
    echo "<section id='main'>\n";
    include_once ("sectionsmainpage/resultsAddObjectsPage.php");
    echo "</section>\n";
// ********** DISPLAY SELECTION AND SEARCH BOXES ************************
// ********* FOOTER ********************
    include_once("includes/bottom.php");
} //end of adminpro
else
    header("Location: index.php");
?>
