<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING
// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once("includes/pndb_config.php");
include_once("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot = new protect();
if ($prot->showPage) {
    $curUser = $prot->getUser(); //name of the logged user
    $isAdmin = $prot->userStatus(); //user priviledges 1 if admin
    $pageName = "mainpage"; //info for the header
    $includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascripts
// ****************** END HEADER ********************************
// **************************************************************
    ?>

    <?php
    include_once("classesv2/class.MysqlDriver.php");
    include_once("classesv2/class.ReadTables.php");
    include_once("classesv2/class.SetMainObjects.php");
    include_once("classesv2/class.SetCurrentState.php");
    include_once("classesv2/class.SetMainViews.php");
    include_once("classesv2/class.Paginator.php");
    include_once("classesv2/class.HtmlConstructor.php");
    include_once("classesv2/class.HelpPages.php");



// ************* log access *********************
    include_once("classesv2/class.Logger.php");
    $logger = new Logger($mydbConfig, $curUser, 1, $pageName);
    $logger->addLog();
    unset($logger);
// ***********************************************

    
// ************** SET CURRENT STATE ***********************
    $input = array_merge($_GET, $_POST); // read input data from _POST/_GET into array
    $setSelection = new SetCurrentState($input, $curUser, $mydbConfig, $isAdmin);

// ************** PAGINATOR *****************
    $pages = new Paginator($mydbConfig, $setSelection->first, $setSelection->view);
    $pages->items_total = $setSelection->norecords;
    $pages->mid_range = 3;
    $curripp = $pages->setItemsPerPage($setSelection->getIpp($mydbConfig["defaultipp"]));
    $pages->paginate();
    $limit = $pages->limit;
    $setSelection->addIpp($curripp);
    $maxpages = ceil($pages->items_total / $curripp);
// ************ END PAGINATOR *****************

// ************* HELP *************************
    $Help = new HelpPages($mydbConfig, $curUser, $isAdmin);

// ***********END HELP ************************

//*********************************************    CREATE SQL QUERY  *************************************************************
    $setSelection->createMainSqls($limit);
    $setSelection->getResults();
    $selections = $setSelection->setDisplays($mydbConfig["posdefselection"]);
//*********************************************************************************************************************************
    $setSelection->make3Dplot();

    $MainPage = new SetMainViews($setSelection->selection, $mydbConfig, $curUser, $isAdmin, $setSelection->restoreTextResults());
// ***********************************************

// ************ MAIN DATABASE QUERY ***********
    if ($setSelection->result) {
        $setSelection->storeSession();

        $MainPage->setOrderByDir($setSelection->orderby, $setSelection->orderdir);

        if ($setSelection->view == "table") {
            $displayRes = $MainPage->tableViewResults($setSelection->result, "MainTable", "table2", False);
            $mydbConfig["headervars"]["position"] = "Table View";
            $selectBox = $MainPage->createScrollCheckBox();
            $selecttype = "fselect";
            $selectmessage = "Show columns...";
        } elseif ($setSelection->view == "image") {
            $displayRes = $MainPage->imageViewResults($setSelection->result, $setSelection->extraresults, "single");
            $mydbConfig["headervars"]["position"] = "Image View";
            $selectBox = $MainPage->createImageCheckBox();
            $selecttype = "imselect";
            $selectmessage = "Show images...";
        } elseif ($setSelection->view == "groupimage") {
            $displayRes = $MainPage->imageViewResults($setSelection->result, $setSelection->extraresults, "group");
            $mydbConfig["headervars"]["position"] = "Grouped Image View";
            $selectBox = $MainPage->createGroupedImageCheckBox();
            $selecttype = "grimselect";
            $selectmessage = "Show images...";
        } elseif ($setSelection->view == "wall") {
            $displayRes = $MainPage->imageWallResults($setSelection->result, $setSelection->extraresults, "wall");
            $mydbConfig["headervars"]["position"] = "Wall";
        }

        $wallcheckbox = $MainPage->createImageCheckBox(True);

        if (isset ($displayRes) and $displayRes != "") {

            include("includes/header.php");
            echo "<div class='loader'></div>";
            echo "<section id='main'>";
            include_once("sectionsmainpage/viewBarMainPage.php");//views bar + user info
            echo "<script> $('#main').css('visibility', 'hidden'); </script>";
            //**************** DISPLAY NOTIFICATIONS **********************
            if ($setSelection->runresponse) {
                foreach ($setSelection->runresponse as $notification) echo $notification;
            }
            //**************** DISPLAY RESULTS **********************
            include_once("sectionsmainpage/resultsMainPage.php");
            // ********** DISPLAY SELECTION AND SEARCH BOXES ************************
            include_once("sectionsmainpage/tabboxesMainPage.php");
            echo "</section>";

        }
    } else {
        include("includes/header.php");
        echo "<section id='main'>\n";
        //**************** DISPLAY INFO TABS **********************
        include_once("sectionsmainpage/resultsErrorsPage.php");
        include_once("sectionsmainpage/tabboxesMainPage.php");
        echo "</section>\n";
    }

// ********* FOOTER ********************
    include_once("includes/bottom.php");
} //end of adminpro
else header("Location: index.php");

?>
