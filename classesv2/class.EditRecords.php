<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author ivan
 */
class EditRecords extends SetMainObjects {

    private $_mTable;
    private $_offsetforredo;
    private $_doimageset = False;
    private $_getimageset = False;
    private $_spectralines;
    private $_tablename;
    private $_refTable;
    private $_specAccess;
    private $_band = False;
    private $_bandsql = "";
    private $_postdata;
    public $owner;

    public function __construct($myConfig, $currId, $postdata, $isAdmin, $userid) {
        parent::__construct($myConfig, $userid, $isAdmin);
        $this->_postdata = $postdata;
        if (empty($this->_postdata))
            return False;
        if (isset($this->_postdata["table"]))
            $this->_setTables();
        $this->_setObjectId($currId);
        $this->_specAccess = $this->_grantAccesstoTable($this->_readtables->readUserData($userid));
        $this->_checkOwner();
        $this->_setBandMapped();
        $this->_setBandSql();
        if (($this->_isAdmin or $this->owner) or isset($this->_postdata["addcoment"]))
            $this->_adminMysqlAccess($myConfig);
        $this->_offsetforredo = $myConfig["ofsetforredo"];
        $this->_spectralines = $myConfig["spectralines"];

        if (isset($this->_postdata["redoim"]))
            $this->_redoImages($this->_postdata);
        if (isset($this->_postdata["chinuse"]) and $this->_postdata["chinuse"] == "y")
            $this->_changeInUse();
        if (isset($this->_postdata["unsetinuse"]) and $this->_postdata["unsetinuse"] == "y")
            $this->_unsetInUse();
        if (isset($this->_postdata["delrec"]) and $this->_postdata["delrec"] == "y")
            $this->_delRecord($this->_postdata);
        if (isset($this->_postdata["addrec"]) and $this->_postdata["addrec"] == "y")
            $this->_addRecord($this->_postdata);
        if (isset($this->_postdata["addcoment"]) and $this->_postdata["addcoment"] == "add")
            $this->_addComment($this->_postdata);
        if (isset($this->_postdata["addcoment"]) and $this->_postdata["addcoment"] == "edit")
            $this->_editComment();
        if (isset($this->_postdata["addcoment"]) and $this->_postdata["addcoment"] == "del")
            $this->_delComment();
        if (isset($this->_postdata["editrec"]) and $this->_postdata["editrec"] == "y")
            $this->_editRecord($this->_postdata);
//        if (isset($this->_postdata["rgbscale"]) and $this->_postdata["rgbscale"] == "y")
//            $this->_rescaleRGB($this->_postdata);
        if (isset($this->_postdata["intscale"]) and $this->_postdata["intscale"] == "y")
            $this->_rescaleInt($this->_postdata);
        if (isset($this->_postdata["linesave"]) and $this->_postdata["linesave"] == "y")
            $this->_setDefaultLines();
        if (isset($this->_postdata["deletesplink"]) and $this->_postdata["deletesplink"] == "y")
            $this->_deletesplink();
        if (isset($this->_postdata["addsplink"]) and $this->_postdata["addsplink"] == "y")
            $this->_addsplink($this->_postdata);
    }

    /**
     * set flag $this->_band = band if the table is bandmapped or False if is not
     * @param array $postdata
     */
    private function _setBandMapped() {
        if (isset($this->_postdata["band"]) and ! empty($this->_postdata["band"])) {
            $this->_band = $this->_postdata["band"];
        } else
            $this->_band = False;
    }

    /**
     * set sql for band mapped (AND `band` = "someband")
     */
    private function _setBandSql() {
        $this->_bandsql = $this->_band === False ? "" : " AND  `band` = '" . $this->_band . "' ";
    }

    /**
     * set _mysqldriver var for admin access to the database (for writing)
     * @param type $myConfig
     */
    private function _adminMysqlAccess($myConfig) {
        unset($this->_mysqldriver);
        $this->_mysqldriver = new MysqlDriver($myConfig["dbhost_admin"], $myConfig["dbuser_admin"], $myConfig["dbpass_admin"]);
    }

    /**
     * if special access to the _tablename Table is granted 
     * or the user is admin  
     * return True
     * @param type $specAccess
     * @return boolean
     */
    private function _grantAccesstoTable($specAccess) {
        if (!$specAccess or ! isset($specAccess["specAccess"]))
            return False;
        if ($this->_isAdmin)
            return True;
        if (in_array($this->_tablename, $specAccess["specAccess"]))
            return True;
        return False;
    }

    /**
     * set owner flag to the record (boolean)
     */
    private function _checkOwner() {
        $res = $this->_mysqldriver->selectquery("SELECT `" . MAIN_ID . "` FROM `" . MAIN_DB . "`.`" . MAIN_TABLE . "` WHERE `" . MAIN_ID . "` = " . $this->_objectId . " AND `userRecord` = '" . $this->_userId . "';");
        $this->owner = $res ? True : False;
    }

    /**
     * change record to be inuse
     */
    private function _changeInUse() {
        $olddata = $this->_getSetData();
        $this->_unsetInUse();
        $this->_updateInUse($this->_postdata["idtable"]);
        $newdata = $this->_getSetData();
        $this->_checkRedoImage($newdata, $olddata);
        if ($this->_postdata["table"] == "tbAngDiam" or $this->_postdata["table"] == "tbPA")
            $this->_setBrewImage();
    }

    private function _checkRedoImage($new, $old = False) {
        if (!$new or ( !isset($new[0]["MajDiam"]) and !isset($new[0]["MajExt"])))
            return False;

        $majnew = isset($new[0]["MajDiam"]) ? floatval($new[0]["MajDiam"]) : floatval($new[0]["MajExt"]);
        if (!$old or ( !isset($old[0]["MajDiam"]) and ! isset($old[0]["MajExt"]))) {
            $majold = 0;
        } else {
            $majold = isset($old[0]["MajDiam"]) ? $old[0]["MajDiam"] : $old[0]["MajExt"];
        }
        $oldsize = floatval(getCutoutSize("n", $majold, 120));
        $newsize = floatval(getCutoutSize("n", $majnew, 120));
        if (!($oldsize  * 0.8 < $newsize and $oldsize * 1.2 > $newsize) or ($majold == 0 && $majnew > 0)) {//if the new image is within 20% of old don't fido images
            $this->_setFetchImage();
        }
        return True;
    }

    /**
     * set private vars:
     * _tablename = name of the current table
     * _mtable  = full sql name of the table
     * _refTable = full sql name of the mapping table  
     */
    private function _setTables() {
        $this->_tablename = $this->_postdata["table"];
        $this->_mTable = $this->_mysqldriver->tblName(MAIN_DB, $this->_postdata["table"]);
        $this->_refTable = $this->_mysqldriver->tblName(MAIN_DB, MAIN_TABLE . "_" . $this->_postdata["table"]);
    }

    private function _getSetData() {
        return $this->_mysqldriver->select("*", $this->_mTable, "`" . MAIN_ID . "`=" . $this->_objectId . " AND `InUse` = 1 $this->_bandsql;");
    }

    private function _unsetInUse() {
        $this->_mysqldriver->query("DELETE FROM " . $this->_refTable . " WHERE  `" . MAIN_ID . "` = " . $this->_objectId . $this->_bandsql . ";");
    }

    private function _updateInUse($refid) {
        $bandwhere = $this->_band === False ? "" : " AND `band` = '" . $this->_band . "'";
        if ($this->_checkrefId()) {
            $this->_mysqldriver->query("UPDATE " . $this->_refTable . " "
                    . "SET `id" . $this->_tablename . "` = " . $refid . " WHERE"
                    . "`" . MAIN_ID . "` =  " . $this->_objectId
                    . $bandwhere . ";");
        } else
            $this->_insertInUse($refid);
    }

    private function _insertInUse($refid) {
        if ($this->_band === False) {
            $query = "INSERT INTO " . $this->_refTable . " (`" . MAIN_ID . "`,`id" . $this->_tablename . "`) VALUES (" . $this->_objectId . "," . $refid . ");";
        } else {
            $query = "INSERT INTO " . $this->_refTable . " (`" . MAIN_ID . "`,`id" . $this->_tablename . "`, `band`) VALUES (" . $this->_objectId . "," . $refid . ",'" . $this->_band . "');";
        }
        $this->_mysqldriver->query($query);
    }

    private function _checkrefId() {
        if ($this->_mysqldriver->selectOne(MAIN_ID, $this->_refTable, "`" . MAIN_ID . "` = " . $this->_objectId . $this->_bandsql))
            return TRUE;
        return False;
    }

    private function _delRecord() {
        $this->_mysqldriver->query("DELETE FROM " . $this->_mTable . " WHERE `id" . $this->_tablename . "`=" . $this->_postdata["idtable"] . ";");
        $this->_mysqldriver->query("DELETE FROM " . $this->_refTable . " WHERE `id" . $this->_tablename . "`=" . $this->_postdata["idtable"] . ";");
    }

    private function _addRecord($indata) {
        if ($this->_isAdmin or $this->_specAccess or $this->owner) {
            $checkref = $this->_checkSetReference($indata["reference"]);
            if (!$checkref) return False;

            $setinuse = (isset($indata["InUse"]) and $indata["InUse"] == 1) ? True : False;
            $tmpindata = $indata;
            foreach ($tmpindata as $key => $val) {
                if (stripos($key, "__") !== False) {
                    $chunks = explode("__", $key);
                    $newkey = $chunks[0];
                    if (!isset($indata[$newkey]))
                        $indata[$newkey] = "";
                    $indata[$newkey] .= $val . ";";
                    unset($indata[$key]);
                }
            }

            unset($indata["table"], $indata["id"], $indata["addrec"], $indata["InUse"]);
            $addkeys = $this->_mysqldriver->makeFieldsFromArray(array_keys($indata)) . ",`id" . MAIN_TABLE . "`";
            $addvals = "(" . implode(",", $this->_setAddVals($indata)) . ",$this->_objectId)";
            $this->_mysqldriver->query("INSERT INTO $this->_mTable ($addkeys) VALUES $addvals;");
            $olddata = $this->_getSetData();
            $newdata = False;
            if ($setinuse) {
                $this->_unsetInUse();
                $maxid = $this->_mysqldriver->selectquery("SELECT MAX(`id" . $this->_tablename . "`) as 'max' FROM " . $this->_mTable);
                $this->_updateInUse($maxid[0]["max"]);
                $newdata = $this->_getSetData();
            }
            $this->_checkRedoImage($newdata, $olddata);
        }
        return True;
    }

    private function _setAddVals($indata) {
        $result = array();
        foreach ($indata as $key => $val)
            $result[$key] = trim($val) == "" ? "NULL" : "'" . $val . "'";
        return $result;
    }

    private function _addComment($indata) {
        unset($indata["table"], $indata["id"], $indata["addcoment"], $indata["noteid"]);
        $escaped = mysql_escape_array($indata);
        $addkeys = $this->_mysqldriver->makeFieldsFromArray(array_keys($escaped)) . ",`id" . MAIN_TABLE . "`, `date`";
        $addvals = "('" . implode("','", $escaped) . "',$this->_objectId,NOW())";
        $this->_mysqldriver->query("INSERT INTO " . $this->_mTable . " ($addkeys) VALUES $addvals;");
    }

    private function _editComment() {
        if ($this->_delComment($this->_postdata)) {
            $this->_addComment($this->_postdata);
        }
    }

    private function _delComment() {
        $user = $this->_isAdmin ? "" : " AND `user` = '" . $this->_userId . "'";
        return $this->_mysqldriver->query("DELETE FROM " . $this->_mTable . " WHERE "
                        . "`id" . $this->_tablename . "` = " . $this->_postdata["noteid"] . $user . " ;");
    }

    private function _editRecord($indata) {
        if ($this->_isAdmin or $this->owner) {

            if (isset($indata["Catalogue"])) {
                $checkref = $this->_checkSetReference($indata["Catalogue"]);
                if (!$checkref) return False;
            }
            
            $refcolumn = $indata["ref"];

            unset($indata["table"], $indata["id"], $indata["editrec"], $indata["userRecord"], $indata["ref"]);

            if ($refcolumn == "refCoord") {
                $this->_editCoords($indata, $refcolumn);
            } else {
                foreach ($indata as $key => $val) {
                    $val = (trim($val) == "") ? "NULL" : "'" . $val . "'";
                    $this->_mysqldriver->query("UPDATE " . $this->_mTable . "
								SET `" . $key . "` = " . $val . " , `" . $refcolumn . "` = '" . $this->_userId . "'
								WHERE `" . MAIN_ID . "`= " . $this->_objectId . ";");
                }
            }
        }
        return True;
        
    }

    private function _editCoords($indata) {
        $resultcoords = array();
        $crdsys = FALSE;
        $coords = array(
            "hmsdms" => array("RAJ2000", "DECJ2000"),
            "radec" => array("DRAJ2000", "DDECJ2000"),
            "gal" => array("Glon", "Glat"));
        foreach ($coords as $csys => $crds) {
            if (array_key_exists($crds[0], $indata) and array_key_exists($crds[1], $indata)) {
                if (!checkCoordinates(array($indata[$crds[0]], $indata[$crds[1]]), $csys))
                    return False;
                $crdsys = $csys;
                $xcrd = $indata[$crds[0]];
                $ycrd = $indata[$crds[1]];
                $resultcoords[$csys] = array($crds[0] => $indata[$crds[0]], $crds[1] => $indata[$crds[1]]);
                break;
            }
        }
        if ($crdsys) {
            foreach ($coords as $csys => $crds) {
                if ($crdsys != $csys) {
                    $newcoords = $this->transferCoords($crdsys, $csys, $xcrd, $ycrd);
                    $resultcoords[$csys] = array($crds[0] => $newcoords["X"], $crds[1] => $newcoords["Y"]);
                }
            }
            if ($newcoords)
                $this->_applyUpdateCoords($resultcoords);
        }
        // TODO $this->_checkNearbyObjects($resultcoords['gal']['Glon'], $resultcoords['gal']['Glat'], 3000);
    }

    private function _applyUpdateCoords($inarray) {
        $update_array = array();
        foreach ($inarray as $val) {
            foreach ($val as $nm => $vl) {
                if ($vl and trim($vl) != "") {
                    array_push($update_array, "`" . trim($nm) . "` = '" . trim($vl) . "'");
                }
            }
        }
        $update_string = implode(",", $update_array);

        $oldcoords = $this->_mysqldriver->select("DRAJ2000,DDECJ2000", $this->_mysqldriver->tblName(MAIN_DB, MAIN_TABLE), "`" . MAIN_ID . "` = " . $this->_objectId . ";");

        $distance = sqrt(pow($inarray["radec"]["DRAJ2000"] - $oldcoords[0]["DRAJ2000"], 2) + pow($inarray["radec"]["DDECJ2000"] - $oldcoords[0]["DDECJ2000"], 2)) * 3600;

        if ($distance > $this->_offsetforredo)
            $this->_setFetchImage();

        $this->_mysqldriver->query("UPDATE `" . MAIN_DB . "`.`" . MAIN_TABLE . "` SET $update_string , `refCoord` = '" . $this->_userId . "' WHERE `" . MAIN_ID . "` = " . $this->_objectId . ";");
        $this->_setBrewImage();
        return;
    }

    private function _setBrewImage() {
        if ($this->_doimageset)
            return False;
        if (!$this->_submitCronJob($this->_userId, "brew all " . $this->_objectId, ['w'=>'', 'vvv' => ''], $this->_objectId))
            return False;
        $this->_doimageset = True;
        return True;
    }
//    private function _setDoimage() {
//        if ($this->_doimageset)
//            return False;
//        $cron_parameters = array("options" => array("o" => $this->_objectId, "w" => "", "j" => "")); //TODO CHECK FLAG k
//        if (!$this->_submitCronJob($this->_userId, "make_pngs", $cron_parameters, $this->_objectId))
//            return False;
//        $this->_doimageset = True;
//        return True;
//    }

    private function _setFetchImage($rewrite = True) {
        if ($this->_getimageset)
            return False;
        $parameters = $rewrite ? ['w' => 'force', 'vvv' => ''] : ['w' => 'redo', 'vvv' => ''];
        if (!$this->_submitCronJob($this->_userId, "fetch all " . $this->_objectId, $parameters, $this->_objectId))
            return False;
        $this->_setBrewImage();
        $this->_doimageset = True;
        $this->_getimageset = True;
        return True;
    }
//    private function _setGetimage($rewrite = True) {
//        if ($this->_getimageset)
//            return False;
//        $cron_parameters = $rewrite ? array("options" => array("o" => $this->_objectId, "w" => "", "r" => "")) : array("options" => array("o" => $this->_objectId, "r" => ""));
//        if (!$this->_submitCronJob($this->_userId, "download_images", $cron_parameters, $this->_objectId))
//            return False;
//        $this->_setBrewImage();
//        $this->_doimageset = True;
//        $this->_getimageset = True;
//        return True;
//    }

    private function _redoImages() {
        switch ($this->_postdata["redoim"]) {
            case "download":
                $this->_setFetchImage(False);
                break;
            case "downloadall":
                $this->_setFetchImage();
                break;
            case "redo":
                $this->_setBrewImage();
                break;
        }
        return;
    }

//    private function _rescaleRGB($indata) {
//        $cron_parameters = array();
//        $tmpupdate = array();
//        $tmparchive = array();
//        $set = $indata["source"];
//        unset($indata["rgbscale"], $indata["source"]);
//        if (!$this->_checkScaleVals($indata, "rgbscale"))
//            return False;
//        foreach ($indata as $key => $val) {
//            array_push($tmparchive, $key);
//            array_push($tmpupdate, "`" . $key . "` = " . $val);
//        }
//        $imagestbl = $this->_mysqldriver->tblName(MAIN_IMAGES, MAIN_pngIMAGES);
//        $scalearchres = $this->_mysqldriver->select(implode(",", $tmparchive), $imagestbl, "`" . MAIN_ID . "` = " . $this->_objectId . " AND `name` = '" . $set . "';");
//        $tmparchive = array();
//        foreach ($scalearchres[0] as $key => $val)
//            array_push($tmparchive, "`" . $key . "` = " . $val);
//        $cron_parameters["sql_restore"] = "UPDATE $imagestbl SET " . implode(",", $tmparchive) . " WHERE `" . MAIN_ID . "` = " . $this->_objectId . " AND `name` = '" . $set . "';";
//        $cron_parameters["sql_update"] = "UPDATE $imagestbl SET " . implode(",", $tmpupdate) . " WHERE `" . MAIN_ID . "` = " . $this->_objectId . " AND `name` = '" . $set . "';";
//        $cron_parameters["options"] = array("k" => $set, "o" => $this->_objectId, "w" => "");
//        if (!$this->_submitCronJob($this->_userId, "make_pngs", $cron_parameters, $this->_objectId))
//            echo "Not submited...:(<br>";
//        return TRUE;
//    }

//    private function _checkScaleVals($post, $type) {
//        $array = array("rgbscale" => array("r_imLevel", "g_imLevel", "b_imLevel"), "intscale" => array("imLevel"));
//        foreach ($post as $val)
//            if (!is_numeric($val))
//                return False;
//        foreach ($array[$type] as $level)
//            if (floatval($post["min_" . $level]) >= floatval($post[$level]))
//                return False;
//        return True;
//    }

    private function _setDefaultLines() {
        $temp = $this->_spectralines;
        foreach ($temp as $wav => $line)
            $this->_spectralines[$wav]["chkd"] = isset($this->_postdata[$wav]) ? "checked" : "";
		$pathtofile = $this->_linkSPmarkers  . $this->_userId."_splinesmarkers.txt";
        setSpLinesMarkers($pathtofile, $this->_spectralines);
    }

    private function _deletesplink() {
        if ($this->_isAdmin or $this->_userId == $this->_postdata['user']) {
            $sql = "DELETE FROM `PNSpectra_Sources`.`spectraLinks` WHERE `idspectraLinks` = " . $this->_postdata['iddata'];
            $this->_mysqldriver->query($sql);
        }
    }

    private function _addsplink($indata) {
        if ($this->_isAdmin) {
            $catalogue = $this->_checkSetReference($indata['reference']);
            if (!$catalogue) return False;
            $indata[MAIN_ID] = $indata['id'];
            unset($indata['addsplink'], $indata['id']);
            $fields = $this->_mysqldriver->makeFieldsFromArray(array_keys($indata)) . ",`date`";
            $values = "'" . implode("','", $indata) . "'";
            $sql = "INSERT INTO `PNSpectra_Sources`.`spectraLinks` ($fields) VALUES (" . $values . ",NOW());";
            $this->_mysqldriver->query($sql);
        }
    }

}

?>
