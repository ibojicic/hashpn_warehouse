<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/**
 * Main configuration file
 */
//$basefolder = file_get_contents(".basefolder");
$basefolder = "gpne_working";
$server = "hku";
// ************* CONSTANTS ****************************
define("VERSION","4.5"); // version of the system
define("BASE_FOLDER",$basefolder); // base folder
define("WEBSITE_URL","http://202.189.117.101:8999/".BASE_FOLDER."/");
define("MAIN_DB", "MainGPN");
define("TEMP_DB", "MainGPNTemp");
define("TEMP_MAIN", "TEMPPNMain");
define("MAIN_DB_DATA", "MainPNData");
define("MAIN_SAMPLES", "MainPNSamples");
define("MAIN_TABLE","PNMain");
define("CSCOORD_TABLE","tbCSCoords");
define("MAIN_DESIGNATION","PNG");
define("DES_PREFIX","PNG");
define("MAIN_ID","id".MAIN_TABLE);
define("MAIN_DIST",MAIN_TABLE."Dist");
define("MAIN_IMAGES","PNImages");
define("MAIN_IMAGES_SOURCES","ImagesSources");
define("MAIN_pngIMAGES","pngimages");
define("MAIN_fitsIMAGES","fitsimages");
define ("PNIMAGES","/data/fermenter/PNImages/"); //absolute path to the .fits image folders
define ("PNGPNIMAGES","/data/kegs/pngPNImages/"); //absolute path to the .png image folders
define ("SPECTRAPLOTS","/data/kegs/spectraPlots/"); //absolute path to the .dat spectra files

define("WORKDISC","/data/copper/");
define("SITETITLE", "HASH PN Database".VERSION);
define("USERS_DB","MainPNUsers");
define("VIEW_TABLE","GPNFullView");
define("USER_VIEW_TABLE","GPNUserView");
define("sessionsuffix", "_session");
define("SESSIONS_TABLE", "userssession_working");
define("INTEGRATORS_DB", "Integrators");
define("INTEGRATORS_ROOT","/data/kegs/Integrators/");
define("RGBCUBES","/data/fermenter/RGBCUBES/");
define("ZOUTS","/data/fermenter/ZOUTS/");
define("CRONFLAG","/tmp/cronflag.txt");
define("BREWER","tcooper");
define("BREWER_SSH","/var/www/.ssh/");
define("TMPFIDO","tempfido"); //temp folder for fido

// **MySQL CONFIGURATION*************************************************************


$mydbConfig["dbhost_user"] = "niksicko"; // MySQL Server Host URL
$mydbConfig["dbuser_user"] = "gpneuser"; // MySQL Username
$mydbConfig["dbpass_user"] = "(g.pne.user)"; // MySQL Password

$mydbConfig["dbhost_admin"] = "niksicko"; // MySQL Server Host URL
$mydbConfig["dbuser_admin"] = "gpneadmin"; // MySQL Username
$mydbConfig["dbpass_admin"] = "(g.pne.admin)"; // MySQL Password


$mydbConfig["MainInfo"] = "`" . MAIN_DB . "`.`tablesInfo`"; //Name of the Info table: Main schema, contains information about created Tables
$mydbConfig["SamplesInfo"] = "`" . MAIN_DB . "`.`samplesInfo`"; //Name of the Samples table: Main schema, contains information about created samples
$mydbConfig["References"] = "`" . MAIN_DB . "`.`ReferenceIDs`"; //NAme of the References table, contains references used in the DB
$mydbConfig["DataInfo"] = "`" . MAIN_DB_DATA . "`.`DataInfo`"; // Name of the Data Info table: Data schema, contain information about imported sources
$mydbConfig["pngImagesInfo"] = "`" . MAIN_IMAGES . "`.`pngimagesinfo`"; // Name of the png images Info table: Contain information about created png images
$mydbConfig["fitsImagesInfo"] = "`" . MAIN_IMAGES . "`.`imagesets`"; // Name of the fits images Info table: 
$mydbConfig["Comments"] = "`" . MAIN_DB . "`.`tbUsrComm`"; // Name of the User Comments table: Contain user comments about specific object
$mydbConfig["mainIDfull"] = "`" . MAIN_DB . "`.`" . MAIN_TABLE . "`.`" . MAIN_ID . "`";
$mydbConfig["mainPNGroups"] = "`".MAIN_SAMPLES."`.`mainPNGroups`";
$mydbConfig["groupedObjects"] = "`".MAIN_SAMPLES."`.`groupedObjects`";
$mydbConfig["mainIDinfofull"] = "`Catalogue`";
$mydbConfig["mysqlescapeGet"] = array("textsearch");
$mydbConfig["extraFields"] = array(MAIN_ID,MAIN_DESIGNATION,"RAJ2000","DECJ2000","DRAJ2000","DDECJ2000","Glon","Glat","Catalogue","PNstat","PNstatus","Name","MajDiam","SimbadID","mainClass","subClass");
$mydbConfig["addObjectFields"] = array(MAIN_ID,"PNG" ,"RAJ2000" ,"DECJ2000" ,"DRAJ2000" ,"DDECJ2000" ,"Glon" ,"Glat" ,"Catalogue" ,"PNstatus" );
$mydbConfig["genDataExcl"] = array("tbUsrComm");
$mydbConfig["checkUsrnRef"] = $basefolder."/regPage.php?action=newreg";
// **END MySQL CONFIGURATION*********************************************************

// **** JAVASCRIPTS CONFIGURATION ***************************************************
$mydbConfig["javascripts"] = array(
		"galery"        =>	"<script type='text/javascript' src='javascript/jquery.galleriffic_ext.js'></script>",
    		//"galery"		=>	"<script type='text/javascript' src='javascript/jquery.galleriffic.js'></script>",

		"flot1"		=>	"<script type='text/javascript' src='javascript/flot/jquery.flot.js'></script>",
		"flot2"		=>	"<script type='text/javascript' src='javascript/flot/jquery.flot.selection.js'></script>",
		"flotaxis"	=>	"<script type='text/javascript' src='javascript/flot/jquery.flot.axislabels.js'></script>",
		"flotsymbols"	=>	"<script type='text/javascript' src='javascript/flot/jquery.flot.symbol.js'></script>",
		"datatables1"	=>	"<script type='text/javascript' src='//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js'></script>",
        //"datatables1"	=>	"<script type='text/javascript' src='javascript/jquery.dataTables.min.js'></script>",
        "tabboxes"	=>	"<script type='text/javascript' src='javascript/tabboxes.js'></script>",
		"mainpage"	=>	"<script type='text/javascript' src='javascript/mainpage.js'></script>",
		"hideshow"	=>	"<script type='text/javascript' src='javascript/hideshow.js' ></script>",
		"register"	=>	"<script type='text/javascript' src='javascript/register.js' ></script>",
		"orbitcont"	=>	"<script type='text/javascript' src='javascript/OrbitControls.js' ></script>",
		"stats" 	=>	"<script type='text/javascript' src='javascript/stats.min.js' ></script>",
		"detector"	=>	"<script type='text/javascript' src='javascript/Detector.js' ></script>",
		"three"         =>	"<script type='text/javascript' src='javascript/three.min.js' ></script>",
		"3dplot"	=>	"<script type='text/javascript' src='javascript/3dplot.js' ></script>"
    
    );


// **FOLDERS CONFIGURATION***********************************************************

# RELATIVE PATHS

$mydbConfig["pngfiles"] = "../images/"; //relative path to the png folder
$mydbConfig["plots"] = "../plots/"; //relative path to the plots folder
$mydbConfig["fitsfiles"] = "../datasets/"; //relative path to the fits folder
$mydbConfig["extrafiles"] = "../additionaldata/"; //relative path to the extra files folder
$mydbConfig["rgbcubes"] = "../rgbcubes/"; //relative path to the rgbcubes folder
$mydbConfig["integrators"] = "../integrators/"; //relative path to the rgbcubes folder
$mydbConfig["exporteddata"] = "/tmp/"; //relative path to the temporary exported files folder
$mydbConfig["spectraplots"] = "../splots/"; //relative path to the plots folder
$mydbConfig["spectramarkers"] = "../splotmarkers/"; //relative path to the plots folder

$mydbConfig["mainpage"] = "dbMainPage.php"; //main page for display of table/images
$mydbConfig["ipparray"] = array(			//number of object shown in table per page
		"table"			=> array(5,10,25,50,100),
		"image"			=> array(5,10,25,50,100),
		"groupimage"	=> array(5,10,25,50,100),
		"wall"			=> array(5,10,25,50,100,200,1000),
		);
$mydbConfig["defaultipp"] = 25;

$mydbConfig["defaultstats"] = array("T","L","P");


$mydbConfig["pydriverdir"] = "/var/www/html/".BASE_FOLDER."/pydrivers/";

// **END FOLDERS CONFIGURATION*******************************************************

// **GET - POST CONFIGURATION********************************************************


// **END GET - POST CONFIGURATION****************************************************

// **HEADER CONFIGURATION************************************************************

$mydbConfig["headervars"] = array(
		"position"	=> ""
		
);
$mydbConfig["servervars"] = array(
                "macquarie" => array(
                    "imagelogo" => "MQlogo_small.png",
                    "linklogo"  => "http://physics.mq.edu.au/astronomy/"
                ),
                "hku"       => array(
                    "imagelogo" => "hkuphysicslogo.png",
                    "linklogo"  => "http://www.physics.hku.hk"                    
                )
);

// **END HEADER CONFIGURATION********************************************************

// **CSS CONFIGURATION ************************************************************** 

$mydbConfig["divids"] = array(
		"objectheader" => "headertable_info"
);

$mydbConfig["cssclasses"] = array(
		"fullinfotable"		=>	"table1",
		"emmlines"		=>	"table1",
		"fulltablesedit"	=>	"table1",
		"basictablesedit"	=>	"table1",
		"samplecheckbox"	=>	"table1",
		"quejobs"		=>	"table1",
		"exportdata"		=>	"table1",
		"addnewobject"		=>	"table1",
		"splinerefs"		=>	"table1"		
);

// **END CSS CONFIGURATION **********************************************************

// **GENERAL CONFIGURATION***********************************************************

$mydbConfig["inits"] =	array("exportdata","currentsample","intextsearch","rulesearch","positionsearch", "viewfirst", "orderby", "view", "eselect", "usersamples");// "sselect", "usmplcols", "upvicols" ,"imgcols","grimgcols");
$mydbConfig["selectionfields"] = array("currentsample","currentRule","displayRule","currentText","displayText","currentPosition","displayPosition","eselect");
$mydbConfig["posdefselection"] = array("errormessage" => array(), "rulesearch" => "", "textsearch" => "", "displayposition" => False, "possearch" => "", "radec" => "selected", "galactic" => "", "posrad" => "2", "sec" => "", "min" => "selected", "deg" => "");


$mydbConfig["alwayson"] = array(MAIN_DESIGNATION); //always visible columns
$mydbConfig["donotshow"] = array("refRecord","InUse","refTable",MAIN_ID);

$mydbConfig["mainlink"] = MAIN_DESIGNATION;
$mydbConfig["maindesignation"] = array("table" => MAIN_TABLE, "column" => MAIN_DESIGNATION, "prefix" => DES_PREFIX." ", "sufix" => "");
$mydbConfig["commonnames"] = array("table" => "tbCNames", "column" => "Name", "showall" => FALSE);
$mydbConfig["maxResults"] = 10000; //max number of results of the sql query
$mydbConfig["separators"] = array(
		"coma"	=>	",",
		"space"	=>	" ",
		"tab"	=>	"\t"
		);

$mydbConfig["sessionArray"] = array(
			"sselect"		=> array("type" => "array" , "default" => array()),
			"fselect"		=> array("type" => "array" , "default" => array()),
			"imselect"		=> array("type" => "array" , "default" => array()),
			"grimselect"            => array("type" => "array" , "default" => array()),
			"wallselect"            => array("type" => "array", "default" => array()),
			"view"			=> array("type" => "string", "default" => "table"),
			"fullWhere"		=> array("type" => "string", "default" => ""),
			"currentRule"           => array("type" => "string", "default" => ""),
			"displayRule"           => array("type" => "string", "default" => ""),
			"currentText"           => array("type" => "string", "default" => ""),
			"displayText"           => array("type" => "string", "default" => ""),
			"currentPosition"       => array("type" => "string", "default" => ""),
			"displayPosition"       => array("type" => "string", "default" => ""),
			"what"			=> array("type" => "string", "default" => ""),
			"orderby"		=> array("type" => "string", "default" => "DRAJ2000"),
			"ipp"			=> array("type" => "int", "default" => 25),
			"viewfirst"		=> array("type" => "int", "default" => 1),
			"orderdir"		=> array("type" => "string", "default" => "ASC"),
			"eselect"		=> array("type" => "array", "default" => array()),
			"whereMD5"		=> array("type"	=> "string", "default" =>""),
			"noResults"		=> array("type"	=> "int","default"	=> 0));

$mydbConfig["possviews"] = array("table","image","groupimage");

$mydbConfig["posselections"] = array (							// possible selections
		"fselect"		=>	array(								// columns selection
			"dataInfoVar"		=>	"infoTable",				// key variable of the datainfotables
			"defSeeColumn"		=>	"varSee",					// column name where to look for default value
			"defSeeVal"		=>	1,							// default value (look prev. val)
			"dataInfoColumn"	=>	"varVar",					// name of the column with table names
			"indexColumn"		=>	"idInfo",					// name of the index column
			"alwaysOn"		=>	array(MAIN_DESIGNATION)),	// column which is always on
                        "sselect"		=>	array(								// predefined sample selection
			"dataInfoVar"		=>	"samplesTable",				
			"defSeeColumn"		=>	"default",
			"defSeeVal"		=>	1,
			"dataInfoColumn"	=>	"Name",
			"indexColumn"		=>	"Name",
			"alwaysOn"		=>	False),
                        "imselect"		=>	array(						// image selection (image view 1)
                            "dataInfoVar"	=>	"pngimginfoTable",
                            "defSeeColumn"	=>	"defaultsee",
                            "defSeeVal"		=>	1,
                            "dataInfoColumn"	=>	"name",
                            "indexColumn"	=>	"idpngImagesInfo",
                            "alwaysOn"		=>	False),
                        "wallselect"            =>	array(						// wall view
                            "dataInfoVar"		=>	"pngimginfoTable",
                            "defSeeColumn"		=>	"defaultwall",
                            "defSeeVal"			=>	1,
                            "dataInfoColumn"            =>	"name",
                            "indexColumn"		=>	"idpngImagesInfo",
                            "alwaysOn"			=>	False),
                        "grimselect"            =>	array(						// image selection (image view 2)
                            "dataInfoVar"	=>	"imagegroups",
                            "defSeeColumn"	=>	"defaultgrp",
                            "defSeeVal"		=>	1,
                            "dataInfoColumn"	=>	"group",
                            "indexColumn"	=>	"group",
                            "alwaysOn"		=>	False)
   );


$mydbConfig["pageDisplay"] = array(
		"mainpage"          => "Views / ",
                "infoobjectpage"    => "PN Info / ",
		"integrators"       => "Integrator / ",
                "iphaspick"         => "IPHAS image picker / ",
		"statspage"         => "Stats /",
		"downloaddata"      => "Download Data",
    		"plotdata"          => "Plot Data",
                "checkobjectpage"   => "Check Objects / ",
                "addnewobjectpage"  => "Add New Object /",
                "userpref"          => "User Preferences" ,
                "3dviewobjectpage"  => "3D View"
    
    );

$mydbConfig["fixedVals"]	= array(
					"tbPNMorph"	=> array(
						"mainClass"	=> array(
							"multiple"	=> False,
							"values"	=> array(
										"E"	=>	"Elliptical/oval",
										"R"	=>	"Round",
										"B"	=>	"Bipolar",
										"I"	=>	"Irregular",
										"A"	=>	"Asymmetric",
										"S"	=>	"quasi-Stellar"
												)
											),
						"subClass"	=> array(
							"multiple"	=> True,
							"values"	=> array(
										"a"	=>	"a: one sided enhancement/asymmetry",
										"m"	=>	"m: multiple shells/external structure",
										"p"	=>	"p: point symmetry",
										"r"	=>	"r: ring structure/annulus",
										"s"	=>	"s: internal structure"
												)
											)
										)

									);
$mydbConfig["messages"] = array(
		
);

$mydbConfig["sampleSelectionOrder"] = array("Status","Morphology","galaxy","Catalogues","Origin","spectra"); /*order of sample selection*/
// **END GENERAL CONFIGURATION*******************************************************

$mydbConfig["spectraFields"] = array (
		"dateObs"	=> array("DATE-OBS"),
		"observer"	=> array("OBSERVER"),
		"object"	=> array("OBJECT"),
		"instrument"=> array("INSTRUME","CAMERA","DETECTOR"),
		"filter"	=> array("FILTER"),
		"telescope"	=> array("TELESCOP","OBSERVAT"),
		"RAJ2000"	=> array("RA"),
		"DECJ2000"	=> array("DEC"),
		"DRAJ2000"	=> array("RA_OBS"),
		"DDECJ2000"	=> array("DEC_OBS"),

);

// ********** REPORT CONFIGURATION ***************************************************

$mydbConfig["reportfields"] = "`".MAIN_ID."`, `".MAIN_DESIGNATION."`, `RAJ2000`, `DECJ2000`, `Catalogue`";

$mydbConfig["latex"] = array(
		"header" => "
\\documentclass[useAMS,amsmath,amssymb,usegraphicx,usenatbib,usedcolumn]{report}
\\usepackage{rotating}
%\\usepackage{stfloats}
\\usepackage{amssymb,amsmath}
\\usepackage{longtable,lscape}
\\usepackage{acronym}
\\usepackage{subfigure}
\\usepackage{latexsym}
\\usepackage[cm]{fullpage}
\\usepackage{float}
%\\floatstyle{ruled}
\\restylefloat{figure}");

// ********** CRONS CONFIGURATION ***************************************************

$mydbConfig["makepngsfolder"] = "/usr/lib/fido/";
$mydbConfig["downloadimagesfolder"] = "/usr/lib/fido/";


$mydbConfig["ofsetforredo"] = 30; //if offset is > re-download images

$mydbConfig["spectralines"] = array(
	"3727"	=> array("wav" => 3727,"nm" => "[OII]","chkd" => ""),
	"3969"	=> array("wav" => 3969,"nm" => "[NeIII]","chkd" => ""),
        "4026"	=> array("wav" => 4026,"nm" => "HeI","chkd" => ""),
        "4072"	=> array("wav" => 4072,"nm" => "[SII]","chkd" => ""),
        "4102"	=> array("wav" => 4102,"nm" => "H&delta;","chkd" => ""),
        "4267"	=> array("wav" => 4267,"nm" => "CII","chkd" => ""),
        "4340"	=> array("wav" => 4340,"nm" => "H&gamma;","chkd" => ""),	
        "4363"	=> array("wav" => 4363,"nm" => "[OIII]","chkd" => ""),
        "4388"	=> array("wav" => 4388,"nm" => "HeI","chkd" => ""),
        "4472"	=> array("wav" => 4472,"nm" => "HeI","chkd" => ""),
        "4542"	=> array("wav" => 4542,"nm" => "HeII","chkd" => ""),
        "4571"	=> array("wav" => 4571,"nm" => "[MgI]","chkd" => ""),
        "4686"	=> array("wav" => 4686,"nm" => "HeII","chkd" => ""),
        "4740"	=> array("wav" => 4740,"nm" => "[ArIV]","chkd" => ""),
        "4861"	=> array("wav" => 4861,"nm" => "H&beta;","chkd" => "checked"),
        "4922"	=> array("wav" => 4922,"nm" => "HeI","chkd" => ""),
        "4959"	=> array("wav" => 4959,"nm" => "[OIII]","chkd" => "checked"),
        "5007"	=> array("wav" => 5007,"nm" => "[OIII]","chkd" => "checked"),
        "5199"	=> array("wav" => 5199,"nm" => "[NI]","chkd" => ""),
        "5412"	=> array("wav" => 5412,"nm" => "HeII","chkd" => ""),
        "5518"	=> array("wav" => 5518,"nm" => "[ClIII]","chkd" => ""),
        "5538"	=> array("wav" => 5538,"nm" => "[ClIII]","chkd" => ""),
        "5577"	=> array("wav" => 5577,"nm" => "[OI]","chkd" => ""),
        "5754"	=> array("wav" => 5754,"nm" => "[NII]","chkd" => ""),
        "5876"	=> array("wav" => 5876,"nm" => "HeI","chkd" => ""),
        "6300"	=> array("wav" => 6300,"nm" => "[OI]","chkd" => ""),
        "6312"	=> array("wav" => 6312,"nm" => "[SIII]","chkd" => ""),
        "6364"	=> array("wav" => 6364,"nm" => "[OI]","chkd" => ""),
        "6435"	=> array("wav" => 6435,"nm" => "[ArV]","chkd" => ""),
        "6548"	=> array("wav" => 6548,"nm" => "[NII]","chkd" => "checked"),
        "6563"	=> array("wav" => 6563,"nm" => "H&alpha;","chkd" => "checked"),
        "6583"	=> array("wav" => 6583,"nm" => "[NII]","chkd" => "checked"),
        "6678"	=> array("wav" => 6678,"nm" => "HeI","chkd" => ""),
        "6716"	=> array("wav" => 6716,"nm" => "[SII]","chkd" => ""),
        "6731"	=> array("wav" => 6731,"nm" => "[SII]","chkd" => ""),
        "6891"	=> array("wav" => 6891,"nm" => "HeII","chkd" => ""),
        "7006"	=> array("wav" => 7006,"nm" => "[ArV]","chkd" => ""),
        "7065"	=> array("wav" => 7065,"nm" => "HeI","chkd" => ""),
        "7136"	=> array("wav" => 7136,"nm" => "[ArIII]","chkd" => ""),
        "7176"	=> array("wav" => 7176,"nm" => "HeII","chkd" => ""),
        "7237"	=> array("wav" => 7237,"nm" => "[ArIV]","chkd" => ""),
        "7263"	=> array("wav" => 7263,"nm" => "[ArIV]","chkd" => ""),
        "7281"	=> array("wav" => 7281,"nm" => "HeI","chkd" => ""),
        "7325"	=> array("wav" => 7325,"nm" => "[OII]","chkd" => ""),
	"9069"	=> array("wav" => 9069,"nm" => "[SIII]","chkd" => ""),
        "9532"	=> array("wav" => 9532,"nm" => "[SIII]","chkd" => "")

		);

/**
 * Alocates javascripts for pages
 * @param string $page
 * @param array $scripts
 * @return string
 */
function includeJavaScript($page,$scripts)
{
	$addjava = array();
	switch ($page) {
		case "register":
			$addjava = array("register");
		break;
		case "mainpage":
			$addjava = array("datatables1","tabboxes");
		break;
		case "infoobjectpage":
			$addjava = array("galery","tabboxes","flot1","flot2","datatables1","flotaxis","flotsymbols");//,"flotaxis","hideshow",);
		break;
                case "checkobjectpage":
			$addjava = array("galery","tabboxes","flot1","flot2","datatables1","flotaxis","flotsymbols");//,"flotaxis","hideshow",);
		break;
		case "iphaspick":
		case "integrators":
			$addjava = array("tabboxes","hideshow","galery","flot1","flot2");
		break;
		case "shscalibration":
			$addjava = array("tabboxes","hideshow","galery","flot1","flot2","datatables1");
		break;
		case "plotdata":
			$addjava = array("tabboxes","hideshow","galery","flot1","flot2","flotaxis","datatables1","flotsymbols");
		break;
		case "statspage":
			$addjava = array("tabboxes","hideshow","galery","flot1","flot2","flotaxis","datatables1","flotsymbols");
		break;
            	case "userpref":
			$addjava = array("register");
		break;
                case "3dviewobjectpage":
			$addjava = array("three","detector","stats","orbitcont");
		break;
            
	}	
	$result = "";
	foreach ($addjava as $script) $result .= $scripts[$script]."\n";
	return $result;

}

?>