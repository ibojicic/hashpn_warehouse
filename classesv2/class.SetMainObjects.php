<?php
require 'vendor/autoload.php';

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use MyPHP\MyPythons;
/**
 * Description of class
 *
 * @author ivan
 */
class SetMainObjects {

    use MyPythons;

    protected $_callPage = "dbMainPage.php";
    protected $_infoPage = "objectInfoPage.php";
    protected $_objectId;
    protected $_userId;
    protected $_linkImages;
    protected $_fitsImages;
    protected $_objectpngPath;
    protected $_objectfitsPath;
    protected $_isAdmin = False;
    protected $_mysqlpriv;
    protected $_mysqldriver;
    protected $_cssclasses;
    protected $_extrasfiles;
    protected $_referer;
    protected $_expfolder;
    protected $_exportdata;
    protected $_exportfile;
    protected $_fileformats = array("csv" => "csv");
    protected $_currentview;
    protected $_objectData = False;
    protected $_linkSPlots;
    protected $_linkSPmarkers;



    public $runresponse = False;
    public $pydriverdir;
    public $refTable;

    public function __construct($myConfig, $userid, $isAdmin) {
        $this->_setUserId($userid);

        $this->_setIsAdmin($isAdmin);

        $this->_setMysqlPriv();

        $this->_switchView();

        $this->_expfolder = $myConfig["exporteddata"];


        $this->_mysqldriver = new MysqlDriver($myConfig["dbhost_" . $this->_mysqlpriv], $myConfig["dbuser_" . $this->_mysqlpriv], $myConfig["dbpass_" . $this->_mysqlpriv]);


        //$this->_isAdmin = $isAdmin == 1 ? TRUE : FALSE;
        //$mysqlpriv = $isAdmin == 1 ? "admin" : "user";
        //$this->_mysqldriver = new MysqlDriver($myConfig["dbhost_".$mysqlpriv],$myConfig["dbuser_".$mysqlpriv],$myConfig["dbpass_".$mysqlpriv]);
        //$this->_htmlconstructor = new htmlConstructor();

        $this->_readtables = new ReadTables($myConfig, $userid, $isAdmin);

        $this->_linkSPlots = pathslash($myConfig["spectraplots"]);
        $this->_linkSPmarkers = pathslash($myConfig["spectramarkers"]);
        $this->_linkImages = pathslash($myConfig["pngfiles"]);
        $this->_fitsImages = pathslash($myConfig["fitsfiles"]);
        $this->_linkPlots = pathslash($myConfig["plots"]);

        $this->refTable = $myConfig["References"];

        $this->_extrasfiles = $myConfig["extrafiles"];

        //$this->_linkrgbcubes = $myConfig["rgbcubes"];

        $this->_cssclasses = $myConfig["cssclasses"];
        $this->pydriverdir = $myConfig["pydriverdir"];

        $this->_setReferer();
    }

    /**
     * switch view table depending on user status
     * admin = > full view
     * user = > limited view
     */
    protected function _switchView() {
        if ($this->_isAdmin) {
            $this->_currentview = VIEW_TABLE;
        } else
            $this->_currentview = USER_VIEW_TABLE;
    }
    
    protected function _getObjectsData() {
        $this->_objectData = $this->_mysqldriver->selectOne("*", $this->_mysqldriver->tblName(MAIN_DB, $this->_currentview) , 
                "`".MAIN_ID."` = ".$this->_objectId );
    }

    protected function _setIsAdmin($isAdmin) {
        $this->_isAdmin = $isAdmin == 1 ? TRUE : FALSE;
    }

    protected function _setUserId($userid) {
        $this->_userId = $userid;
    }

    protected function _setObjectId($mainId) {
        $this->_objectId = $mainId;
    }

    protected function _setObjectpngPath($mainId) {
        $this->_objectpngPath = $this->_linkImages . $mainId;
    }

    protected function _setObjectfitsPath($mainId) {
        $this->_objectfitsPath = $this->_fitsImages . $mainId . "/";
    }

    protected function _setMysqlPriv() {
        $this->_mysqlpriv = $this->_isAdmin ? "admin" : "user";
    }

    protected function _parsePositionRule($position, $rad = False, $radUnit = False, $coords = "radec", $searchbox = "cone") {

        $cM = array("radec" => array(0 => "DRAJ2000", 1 => "DDECJ2000"),
            "galactic" => array(0 => "Glon", 1 => "Glat"));

        $cU = array("sec" => "3600", "min" => "60", "deg" => "1");

        if ($coords == "radec") {
            if (!($coordinates = regex_radec($position)))
                return False;

            if ($coordinates["flag"] == "sex") {
                $coord["X"] = round(trans_to_deg($coordinates["X"], 15), 4);
                $coord["Y"] = round(trans_to_deg($coordinates["Y"], 1), 4);
            } elseif ($coordinates["flag"] == "deg")
                $coord = $coordinates;
        }
        elseif ($coords == "galactic") {
            if (!($coord = regex_gal($position)))
                return False;
        }

        $radchunks = explode(":", $rad);
        foreach ($radchunks as $radtest)
            if (!is_numeric($radtest))
                return FALSE;

        $xrad = $radchunks[0] / $cU[$radUnit];
        $yrad = (isset($radchunks[1]) and $searchbox == "box") ? $radchunks[1] / $cU[$radUnit] : $xrad;

        if ($rad and $radUnit and ( $xrad >= 180 or $yrad >= 180))
            return False;

        $x1 = "`" . $cM[$coords][0] . "`";
        $y1 = "`" . $cM[$coords][1] . "`";
        $x2 = $coord["X"];
        $y2 = $coord["Y"];

        $boxRules = array();

        if (($x2 - $xrad) < 0) {
            array_push($boxRules, " " . $x1 . " BETWEEN " . ($x2 - $xrad + 360) . " AND 360 ");
            array_push($boxRules, " " . $x1 . " BETWEEN 0 AND " . ($x2 + $xrad) . " ");
        } elseif (($x2 + $xrad) > 360) {
            array_push($boxRules, " " . $x1 . " BETWEEN " . ($x2 - $xrad) . " AND 360 ");
            array_push($boxRules, " " . $x1 . " BETWEEN 0 AND " . ($x2 + $xrad - 360) . " ");
        } else {
            array_push($boxRules, " " . $x1 . " BETWEEN " . ($x2 - $xrad) . " AND " . ($x2 + $xrad) . " ");
        }

        $ymin = ($y2 - $yrad) < -90 ? -90 : $y2 - $yrad;
        $ymax = ($x2 + $xrad) > 90 ? 90 : $y2 + $yrad;

        if ($rad and $radUnit) {
            $cone = ($searchbox == "cone") ?
                    "`" . MAIN_DB . "`.`GPNspherDist_ib`($x1,$y1,$x2,$y2) < " . $xrad : False;

            $box = "(" . implode(" OR ", $boxRules) . ") AND " . $y1 . " BETWEEN " . $ymin . " AND " . $ymax . " ";

            $distSQL = $cone ? $cone . " AND " . $box : $box;
        } else
            $distSQL = False;

        $zSQL = " ROUND(`" . MAIN_DB . "`.`GPNspherDist_ib`($x1,$y1,$x2,$y2) * 3600,1) as 'r [arcsec]' ";
        $distDisplay = array("rad" => $xrad, "showrad" => $radchunks[0], "radunit" => $radUnit, "pos" => $position, "coords" => $coords);

        return array("searchsql" => $distSQL, "results" => $distDisplay, "distsql" => $zSQL, "searchbox" => $box);
    }

    protected function _regexCoords($position, $system) {
        switch ($system) {
            case "radec":
                if (!($coordinates = regex_radec($position)))
                    return False;
                break;
            case "galactic" or "gal":
                if (!($coordinates = regex_gal($position)))
                    return False;
                break;
        }
        return $coordinates;
    }

    protected function _parsePosition($position, $type) {
        $coords = array(
            "radec" => array(
                "sex" => False,
                "deg" => False,
            ),
            "galactic" => array(
                "deg" => False,
            )
        );

        $coordsmap = array("radecsex" => "hmsdms", "radecdeg" => "radec", "galacticdeg" => "gal");
        $coordinates = $this->_regexCoords($position, $type);
        if (!$coordinates)
            return False;
        if ($type == "radec") {

            if ($coordinates["flag"] == "sex") {
                $xcrd = str_ireplace(" ", ":", cleanSpaces(str_ireplace(":", " ", $coordinates["X"])));
                $ycrd = str_ireplace(" ", ":", cleanSpaces(str_ireplace(":", " ", $coordinates["Y"])));
                $from = "hmsdms";

                $coords["radec"]["sex"] = array(
                    "X" => $xcrd,
                    "Y" => $ycrd);
            } elseif ($coordinates["flag"] == "deg") {
                $xcrd = $coordinates["X"];
                $ycrd = $coordinates["Y"];
                $from = "radec";

                $coords["radec"]["deg"] = $coordinates;
            }
        } elseif ($type == "galactic") {

            $xcrd = $coordinates["X"];
            $ycrd = $coordinates["Y"];
            $from = "gal";

            $coords["galactic"]["deg"] = $coordinates;
        }
        $tempcoords = $coords;
        foreach ($tempcoords as $system => $types)
            foreach ($types as $type => $data) {
                if (!$data)
                    $coords[$system][$type] = $this->transferCoords($from, $coordsmap[$system . $type], $xcrd, $ycrd);
            }

        return $coords;
    }

    /**
     * Convert coordinates from one system to another using python driver
     * @param $from string from system (hmsdms,radec,gal)
     * @param $to string to system (-||-)
     * @param $xcrd string or float X coordinate
     * @param $ycrd string or float Y coordinate
     * @param string $framein frame in
     * @param string $frameout frame out
     * @return type array (X => newX, Y => newY)
     */
    public function transferCoords($from, $to, $xcrd, $ycrd, $framein = "fk5", $frameout = "fk5") {
        $inputarray =  [[
            'id' => 'tmp',
            'X_val_in' => $xcrd,
            'Y_val_in' => $ycrd,
            'func' => $from . "2" . $to,
            'frame_in' => $framein,
            'frame_out' => $frameout,
            'X_name_out' => "X",
            'Y_name_out' => "Y",
        ]];
        $coordtransfer = "/usr/lib/qb_drivers/qb_drivers/coord_transfer_driver.py";
        $this->setBrewer('tcooper');
        $results = $this->PythonToPhp($coordtransfer,$inputarray,'corona',True);
        if ($results and is_array($results)) {
            $results = $results[0]['tmp'];
        } else {
            $results = False;
        }
        return $results;
    }


    /**
     * submit new cron job
     * @param string $user
     * @param string $script cronjob script
     * @param array $parameters for the script
     * @param int $id it of the object
     * @return boolean : True on success, False on failure
     */
    protected function _submitCronJob($user, $script, $parameters, $id) {
        $sql_deleteunfinished = "DELETE FROM `" . USERS_DB . "`.`cronJobs` WHERE `user` = '$user' AND `" . MAIN_ID . "` = $id AND `cronScript` = '$script' AND `date_start` IS NULL AND `date_exec` IS NULL;";
        if (!$this->_mysqldriver->query($sql_deleteunfinished))
            return False;
        $parameters = mysql_escape_string(serialize($parameters));
        $sqlinsert = "INSERT INTO `" . USERS_DB . "`.`cronJobs` (`user`,`" . MAIN_ID . "`,`cronScript`,`parameters`,`date_subm`) VALUES ('$user',$id,'$script','$parameters',NOW());";
        if (!$this->_mysqldriver->query($sqlinsert)) {
            $this->_setRunResponse('error','Problem with submitting the cron job. Please contact administrator.');
            return False;
        }
        return True;
    }
//    protected function _submitCronJob($user, $script, $parameters, $id) {
//        $sql_deleteunfinished = "DELETE FROM `" . USERS_DB . "`.`cronJobs` WHERE `user` = '$user' AND `" . MAIN_ID . "` = $id AND `cronScript` = '$script' AND `date_start` IS NULL AND `date_exec` IS NULL;";
//        if (!$this->_mysqldriver->query($sql_deleteunfinished))
//            return False;
//        $parameters = mysql_escape_string(serialize($parameters));
//        $sqlinsert = "INSERT INTO `" . USERS_DB . "`.`cronJobs` (`user`,`" . MAIN_ID . "`,`cronScript`,`parameters`,`date_subm`) VALUES ('$user',$id,'$script','$parameters',NOW());";
//        if (!$this->_mysqldriver->query($sqlinsert)) {
//            echo "mysql error";
//            return False;
//        }
//        return True;
//    }

    /**
     * submit download images and make pngs for an object (no flags, used mainly for new objects)
     * @param integer $id
     * @return boolean
     */
//    protected function _setGetDoimage($id) {
//        $cron_parameters_get = array("options" => array("o" => $id));
//        if (!$this->_submitCronJob($this->_userId, "download_images", $cron_parameters_get, $id))
//            return False;
//        $cron_parameters_do = array("options" => array("o" => $id));
//        if (!$this->_submitCronJob($this->_userId, "make_pngs", $cron_parameters_do, $id))
//            return False;
//        return True;
//    }

    /**
     * Creates system message in $this->runresponse (array)
     * @param string $type : select from: error, info, warning, success
     * @param string $errormessage : message to be displayed
     */
    protected function _setRunResponse($type, $errormessage) {
        if (!$this->runresponse)
            $this->runresponse = array();
        $currunn = "<h4 class='alert_" . $type . "'>" . $errormessage . "</h4>";
        array_push($this->runresponse, $currunn);
    }

    /**
     * get name of the script from the $_SERVER["SCRIPT_NAME"] (no folder)
     */
    protected function _setReferer() {
        $chunks = explode("/", $_SERVER["SCRIPT_NAME"]);
        $this->_referer = end($chunks);
    }

    protected function _parseSearchList($file, $rad, $radUnit, $coords) {
        $found = array();
        $idlist = array("id,pndb,dist[arcsec]\n");
        $list = file($file);
        unlink($file);
        $k = 0;
        foreach ($list as $data) {
            $k++;
            $chunks = explode(",", $data);
            if (count($chunks) != 3) {
                $this->_setRunResponse("error", "Problem with input list format (" . $file . "). "
                        . "Please check row " . $k . ".");
                return False;
            }
            $id = trim($chunks[0]);
            $position = trim($chunks[1]) . " " . trim($chunks[2]);

            if (!$this->_regexCoords($position, $coords)) {

                $this->_setRunResponse("error", "Problem with coordinates format (" . $file . "). "
                        . "Please check row " . $k . ".");
                return False;
            }
            $posrule = $this->_parsePositionRule($position, $rad, $radUnit, $coords);
            $sql = "SELECT `" . MAIN_ID . "`," . $posrule["distsql"] . " FROM `" . MAIN_DB . "`.`" . 
                    $this->_currentview . "` WHERE " . $posrule["searchsql"] . ";";
            $search = $this->_mysqldriver->selectquery($sql);
            if (!$search) {
                array_push($idlist, $id . ",,\n");
            } else {
                foreach ($search as $objects) {
                    array_push($idlist, $id . "," . $objects[MAIN_ID] . "," . $objects["r [arcsec]"] . "\n");
                    array_push($found, $objects[MAIN_ID]);
                }
            }
        }
        if (empty($found)) {
            $this->_setRunResponse("warning", "No corresponing objects in the database (" . $file . ").");
            return False;
        }
        $result["displayText"] = "Search in list: " . $file;
        $result["currentText"] = "`" . MAIN_ID . "` IN (" . implode(",", $found) . ") ";
        $resultfile = implode("", $idlist);
        $resfile = genRandomString() . ".csv";
        $fp = fopen($this->_expfolder . $resfile, "w");
        fwrite($fp, $resultfile);
        fclose($fp);
        $message = "You can download the search results from "
                . "<a href='download.php?p=e&f=" . $resfile . "'>" . $resfile . ".</a>
                        The file be erased in 24h!<a href='dbMainPage.php'> 
                        Go back</a> or <a href='dbMainPage.php?view=table&restart=RESTART'>restart </a> current results.";
        $this->_setRunResponse("success", $message);
        return $result;
    }

    /**
     * set uploda file
     * @param strig $name name of the input file
     * @return string link to the file
     */
    protected function _setUploadFile($name) {
        //$file = $this->_expfolder.genRandomString();
        $file = genRndFileFolder("file", $this->_expfolder);
        $file = move_uploaded_file($_FILES[$name]["tmp_name"], $file) ? $file : False;
        return $file;
    }

    protected function _setTextList($file) {
        $result = array();
        $checkresult = array();
        foreach (file($file) as $line) {
            $correctedline = strtolower(str_ireplace(" ", "", $line));
            if (!in_array($correctedline, $checkresult)) {
                array_push($checkresult, $correctedline);
                array_push($result, trim($line));
            }
        }
        if (empty($result))
            return False;
        return implode(",", $result);
    }

    /*     * ********************************************************************************************** */
    /*     * ****************************** EXPORT DATA     ****************************************** */
    /*     * ********************************************************************************************** */

    protected function _restoreQuery() {
        $restsql = "SELECT `what`,`fullWhere` FROM `" . USERS_DB . "`.`" . SESSIONS_TABLE . "`
			WHERE `id` = (SELECT MAX(a.`id`) FROM `" . USERS_DB . "`.`" . SESSIONS_TABLE . "` a
			WHERE a.`userName` = '" . $this->_userId . "')";
        $restdata = $this->_mysqldriver->selectquery($restsql);
        if (!$restdata)
            return False;
        $query = "SELECT " . $restdata[0]["what"] . " FROM `" . MAIN_DB . "`.`" . $this->_currentview . "` " . $restdata[0]["fullWhere"] . ";";
        $this->_exportdata = $this->_mysqldriver->selectquery($query);
        if ($this->_exportdata)
            return True;
        return False;
    }

    protected function exportformat($format) {
        switch ($format) {
            case "csv":
                $this->_csvFormat();
                break;
        }
        return True;
    }

    private function _csvFormat() {
        $this->_exportfile = $this->_createFileName("csv");
        $fp = fopen($this->_expfolder . $this->_exportfile, "w");
        $keys = array_keys($this->_exportdata[0]);
        fputcsv($fp, $keys);
        foreach ($this->_exportdata as $data)
            fputcsv($fp, $data);
        fclose($fp);
        return True;
    }

    private function _createFileName($format) {
        $random = genRandomString();
        return $this->_userId . "_" . $random . "." . $this->_fileformats[$format];
    }

    /**
     * check if input ($reference) is in the ReferenceIDs table:
     * if yes: returns True
     * if not: if it is available on ADS parse it and insert into ReferenceIDs table and returns True
     * if not and not available on ADS returns False
     * @param string $reference
     */
    protected function _checkSetReference($reference) {
        $query = "SELECT `Identifier` FROM " . $this->refTable . " WHERE `Identifier` = '" . $reference . "';";
        if ($this->_mysqldriver->selectquery($query))
            return True;

        $newref = parseADSBibquery($reference);

        if (!$newref) {
            $this->_setRunResponse("error", "The entered reference (" . $reference . ") do not exist...TODO");
            return False;
        }

        $insertstring = $this->_mysqldriver->makeInsertString($newref, $this->refTable);
        $insertquery = "INSERT INTO " . $this->refTable . " " . $insertstring;
        if (!$this->_mysqldriver->query($insertquery)) {
            $this->_setRunResponse("error", "Something went wrong with inserting new reference (" . $reference . ") to the db...TODO");
            return False;
        }

        return True;
    }

    /*     * ********************************************************************************************** */
    /*     * ********************************************************************************************** */
    /*     * ********************************************************************************************** */

    protected function cleanWHEREfromstart($fullwhere) {
        return ltrim(trim($fullwhere), "WHERE");
    }

    protected function _fitsImages($id, $sets = False) {
        $result = array();
        $array = $this->_readtables->readFitsImages($id, $sets);
        foreach ($array as $data) {
            $set = $data["set"];
            $band = $data["band"];
            unset($data["set"], $data["band"], $data["idfitsimages"], $data["idPNMain"], $data["idsourcetable"]);
            if (!isset($result[$set]))
                $result[$set] = array();
            $result[$set][$band] = $data;
        }
        return $result;
    }
    
    protected function _pngImages($id) {
        $pngdata = makeOneColMain($this->_mysqldriver->select("*", $this->_mysqldriver->tblName(MAIN_IMAGES, MAIN_pngIMAGES),"`".MAIN_ID."` = ".$id),"Set");
        return $pngdata;
    }
    
    /**
     * get Integrators info 
     */
    protected function _getIntegratorsInfo($set) {
        $info = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(INTEGRATORS_DB, "Info"), "`Name` = '" . $set . "'");
        return $info[0];
    }
    
    /**
     * 
     * @return string sql query for idPNMain from current view
     */
    protected function _sqlUserViewIds() {
        return "SELECT `" . MAIN_ID . "` FROM `" . MAIN_DB . "`.`" . $this->_currentview . "`";
    }

    
    protected function _getRefData($identifier) {
        $result = $this->_mysqldriver->select("*", "`MainGPN`.`ReferenceIDs`", "`Identifier` = '".$identifier."'");
        return $result;
        
    }
    
    protected function _manipulateSamples($dowhat,$samplename,$sampledesc = "", $oldarray = array(), $newarray = array()) {
        switch ($dowhat) {
            case "addnewsample":
                $sql = "INSERT INTO `" . MAIN_SAMPLES . "`.`userSamples`
                    (`user`,`sampleName`,`sampleDesc`,`dateCreated`,`uniquemd5`)
                    VALUES
                    ('" . $this->_userId . "','" . $samplename . "','" . 
                    $sampledesc . "',NOW(),MD5(CONCAT('" . $this->_userId . "','" . $samplename . "')));";
                break;
            case "deletesample":
                $sql = "DELETE FROM `" . MAIN_SAMPLES . "`.`userSamples` WHERE
                    `sampleName` = '" . $samplename . "' AND `user` = '" . $this->_userId . "';";
                break;
            case "addnewobjects":
                if (!$newarray or empty($newarray)) return False;
                $newsample = trim(implode(",", $this->_crossArray($oldarray, $newarray)), ",\n\t ");
                $sql = "UPDATE `" . MAIN_SAMPLES . "`.`userSamples` 
                    SET 
                    `sample` = '" . $newsample . "'
                    WHERE `sampleName` = '" . $samplename . "' AND `user` = '" . $this->_userId . "';";

                    

        }
        
        return $sql;

    }
    
    protected function _getIphasRun($id) {
        $run_id = $this->_mysqldriver->selectOneValue("run_id", $this->_mysqldriver->tblName(MAIN_IMAGES, "   iphas"), "`" . MAIN_ID . "` = " . $id . " AND `inuse` = 1", False, "run_id");
        return $run_id;
    }
}
