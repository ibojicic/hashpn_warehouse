<?php

// TODO CHECK VARIABLES - FINISHED
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 * test
 * @author ivan
 */
class AddNewObjects extends SetMainObjects
{
    private $_fields;
    private $_htmlconstructor;
    
    public $newobjects = array();
    public $flagsucces = false;
    public $flagoperation;
    public $reftable = false;

    public function __construct($myConfig, $postdata, $isAdmin, $userid)
    {
        parent::__construct($myConfig, $userid, $isAdmin);

        if (empty($postdata) or (!isset($postdata["addnewobj"]) and !isset($postdata["insrtnewobj"]))) {
            return false;
        }

        $this->_htmlconstructor = new HtmlConstructor();
        $this->_fields = $this->_mysqldriver->makeFieldsFromArray($myConfig["addObjectFields"]);

        if (isset($postdata["addnewobj"]) and $postdata["addnewobj"] == "y") {
            $this->flagoperation = "adddata";
            $this->_addNewObj($postdata);
        }
        if (isset($postdata["insrtnewobj"]) and $postdata["insrtnewobj"] == "y") {
            $this->flagoperation = "submitdata";
            $this->flagsucces = $this->_insertNewObj($postdata);
        }
    }

    private function _addNewObj($postdata)
    {
        $this->_prepareInputTable();
        $this->newobjects = $this->_prepareNewObj($postdata);
    }

    private function _insertNewObj($postdata)
    {
        unset($postdata["insrtnewobj"]);
        foreach (array_keys($postdata) as $key) {
            if (stripos($key, "in_") !== false) {
                $id = (int) str_ireplace("in_", "", $key);
                $newid = $this->_insertInputData($id);
                if (!$newid) {
                    return false;
                }
                $this->newobjects = $this->_showInsertedObject($newid);
            }
        }
        return true;
    }

    private function _prepareNewObj($newobject)
    {
        $catalogue = $this->_checkSetReference($newobject['incat']);
        if (!$catalogue) {
            return false;
        }
        
        $coords = $this->_prepareCoords($newobject);
        if (!$coords) {
            $this->_setRunResponse("error",
                    "Error in parsing coordinates: ".$newobject["inpos"]." (" . $newobject["incoords"]."). Please <a href='dbMainPage.php'>go back</a>.");
            return false;
        }

        $inputdata = $this->_prepareInputData($newobject, $coords);

        $checknearby = $this->_parsePositionRule($coords["radec"]["deg"]["X"] . " " . $coords["radec"]["deg"]["Y"], 10, "deg");
        
        if (!$checknearby) {
            $this->_setRunResponse("error",
                    "Error in parsing coordinates: ".$newobject["inpos"]." (" . $newobject["incoords"]."). Please <a href='dbMainPage.php'>go back</a>.");
            return false;
        }
        
        $sql = "SELECT " . $checknearby["distsql"] . "," . $this->_fields . " FROM `" . MAIN_DB . "`.`" . $this->_currentview . "`  ORDER BY `r [arcsec]` ASC LIMIT 0,3";
        $lastid = $this->_insertTempData($inputdata, $coords);
        
        if (!$lastid) {
            $this->_setRunResponse("error",
                    "Error in inserting temporary data. Please <a href='dbMainPage.php'>go back</a>.");
            return false;
        }

        $result = array(
            "inputdata" => $inputdata,
            "nearby_full" => $this->_mysqldriver->selectquery($sql),
            "simbad" => $this->_prepareSimbadLink($coords),
            "lastid" => $lastid[0]["lastid"],
            "checkbox" => "<input type='checkbox' name='in_" . $lastid[0]["lastid"] . "' value='1'>Add this object to the database...<br>"
            );

        return $result;
    }
    
    private function _showInsertedObject($id)
    {
        $sql = "SELECT $this->_fields FROM `" . MAIN_DB . "`.`" . $this->_currentview . "` WHERE `" . MAIN_ID . "` = $id";
        $result = $this->_mysqldriver->selectquery($sql);
        $result[0]['link'] = $this->_htmlconstructor->makeLink($this->_infoPage . "?id=" . $id, "PNG ".$result[0]["PNG"], "_blank");
        return $result[0];
    }

    private function _prepareCoords($newobject)
    {
        $result = $this->_parsePosition($newobject["inpos"], $newobject["incoords"]);
        if (!isset($newobject["incoords"]) or trim($newobject["incoords"]) == "" or ! $result) {
            return false;
        }
        return $result;
    }

    /**
     * Prepare input data for input yo the db and for display in the temporary table
     * @param array $newobject =>  array of input data (from input form)
     * @param array $coords => parsed coords
     * @return array: prepared results
     */
    private function _prepareInputData($newobject, $coords)
    {
        $prevdes = $this->_prevMainDes();
        $result = array(
            0 => array(
                MAIN_DESIGNATION => calcPNG($coords["galactic"]["deg"]["X"], $coords["galactic"]["deg"]["Y"], $prevdes),
                "RAJ2000" => $coords["radec"]["sex"]["X"],
                "DECJ2000" => $coords["radec"]["sex"]["Y"],
                "Coord. ref." => $newobject["incoordref"],
                "Catalogue" => $newobject["incat"],
                "Domain" => $newobject["indomain"],
                "Status" => $newobject["instatus"]
        ));
        return $result;
    }

    private function _prepareSimbadLink($coords)
    {
        $result = $this->_htmlconstructor->composeVizier($coords["radec"]["sex"]["X"], $coords["radec"]["sex"]["Y"], false, false, false, 600, false);
        return $result;
    }

    private function _prepareInputTable()
    {
        $sqldelete = "DELETE FROM `" . TEMP_DB . "`.`" . TEMP_MAIN . "` WHERE `insertUser` = '" . $this->_userId . "';";
        $this->_mysqldriver->query($sqldelete);
    }

    private function _insertTempData($inputdata, $coords)
    {
        $inp = $inputdata[0];
        $sqlinsert = "INSERT INTO `" . TEMP_DB . "`.`" . TEMP_MAIN . "`
						(
						`" . MAIN_DESIGNATION . "`,
						`RAJ2000`,
						`DECJ2000`,
						`DRAJ2000`,
						`DDECJ2000`,
						`Glon`,
						`Glat`,
						`refCoord`,
						`Catalogue`,
						`refCatalogue`,
						`userRecord`,
						`domain`,
						`refDomain`,
						`PNstat`,
						`refPNstat`,
						`insertUser`
						) VALUES (
						'" . $inp[MAIN_DESIGNATION] . "',
						'" . $coords["radec"]["sex"]["X"] . "',
						'" . $coords["radec"]["sex"]["Y"] . "',
						" . $coords["radec"]["deg"]["X"] . ",
						" . $coords["radec"]["deg"]["Y"] . ",
						" . $coords["galactic"]["deg"]["X"] . ",
						" . $coords["galactic"]["deg"]["Y"] . ",
						'" . $inp["Coord. ref."] . "',
						'" . $inp["Catalogue"] . "',
						'" . $this->_userId . "',
						'" . $this->_userId . "',
						'" . $inp["Domain"] . "',
						'" . $this->_userId . "',
						'" . $inp["Status"] . "',
						'" . $this->_userId . "',
						'" . $this->_userId . "'
						);";
        if (!$this->_mysqldriver->query($sqlinsert)) {
            return false;
        }
        $sqllastid = "SELECT LAST_INSERT_ID() as 'lastid';";
        return $this->_mysqldriver->selectquery($sqllastid);
    }

    private function _insertInputData($tmpid)
    {
        $inpdatatmp = $this->_readFromTemp(" `id" . TEMP_MAIN . "` = $tmpid AND `insertUser` = '" . $this->_userId . "' ");
        $inpdata = $inpdatatmp[0];
        $tmpimpdata = $inpdata;
        foreach ($tmpimpdata as $key => $val) {
            if ($val == "") {
                unset($inpdata[$key]);
            }
        }
        unset($inpdata["id" . TEMP_MAIN], $inpdata["insertUser"]);
        $insertline = "`" . implode("`,`", array_keys($inpdata)) . "`";
        $insertvals = "'" . implode("','", $inpdata) . "'";

        $sqlinsert = "INSERT INTO `" . MAIN_DB . "`.`" . MAIN_TABLE . "`
						(
						$insertline
						) VALUES (
						$insertvals
						);";

        if (!$this->_mysqldriver->query($sqlinsert)) {
            return false;
        }
        $tmplastid = $this->_mysqldriver->selectquery("SELECT LAST_INSERT_ID() as 'last';");
        $lastid = (isset($tmplastid[0]) and isset($tmplastid[0]["last"])) ? $tmplastid[0]["last"] : false;
        return $lastid;
    }

    private function _readFromTemp($where = false)
    {
        $where = $where ? "WHERE " . $where : "";
        $sqlselect = "SELECT * FROM `" . TEMP_DB . "`.`" . TEMP_MAIN . "` " . $where . ";";
        $resselect = $this->_mysqldriver->selectquery($sqlselect);
        return $resselect;
    }

 
    private function _prevMainDes()
    {
        return $this->_mysqldriver->selectColumn("`" . MAIN_DESIGNATION . "`", $this->_mysqldriver->tblName(MAIN_DB, MAIN_TABLE));
    }
}
