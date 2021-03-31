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
class ReadTables {

    private $_infoTable = FALSE;
    private $_infoTableNames = array();
    private $_infocolumns;
    private $_samplesTable = False;
    private $_pngimginfoTable;
    private $_dataInfoTable = False;
    private $_userId;
    private $_isAdmin;
    private $_mysqldriver;
    private $_fitsimginfoTable = False;

    public function __construct($myConfig, $userid, $isAdmin) {
        $this->_myconfig = $myConfig;
        $this->_userId = $userid;
        $this->_isAdmin = $isAdmin == 1 ? TRUE : FALSE;
        $mysqlpriv = $isAdmin == 1 ? "admin" : "user";
        $this->_mysqldriver = new MysqlDriver($myConfig["dbhost_" . $mysqlpriv], $myConfig["dbuser_" . $mysqlpriv], $myConfig["dbpass_" . $mysqlpriv]);
    }

    public function readInfoTable($columns = False, $where = FALSE) {
        if (!$this->_isAdmin) $where = $where ? $where . " AND `public` = 'y'" : "`public` = 'y'";
        $columns = $columns ? $this->_mysqldriver->setListForQuery($columns, "`") : "*";
        $fetch = $this->_mysqldriver->select($columns . ",IF(`bandMapped` = 'y',CONCAT(`varTable`,'_',`varColumn`,'_',`band`),`varColumn`) as `clmnName`", 
                $this->_myconfig["MainInfo"], $where, "varOrder");
        foreach ($fetch as $data)
            $this->_infoTable[$data["idInfo"]] = $data;
        return $this->_infoTable;
    }

    public function readInfoTableNames() {
        $where = False;
        if (!$this->_isAdmin) $where = "`public` = 'y'";
        $fetch = $this->_mysqldriver->select("varTable,tableLongName", $this->_myconfig["MainInfo"], $where, False, "varTable");
        foreach ($fetch as $data)
            $this->_infoTableNames[$data["varTable"]] = $data["tableLongName"];
        return $this->_infoTableNames;
    }

    public function readColumnsFromInfoTable() {
        if (!$this->_infoTable)
            $this->readInfoTable();
        foreach ($this->_infoTable as $table) {
            if (!isset($this->_infocolumns[$table["varTable"]]))
                $this->_infocolumns[$table["varTable"]] = array();
            array_push($this->_infocolumns[$table["varTable"]], $table["clmnName"]);
        }
        return $this->_infocolumns;
    }

    public function readSamplesTable() { //push Samples table into array (key = idSamples)
        $fetch = $this->_mysqldriver->select("*", $this->_myconfig["SamplesInfo"], "`User` IN ('sys','" . $this->_userId . "')");
        foreach ($fetch as $data)
            $this->_samplesTable[$data["Name"]] = $data;
        $this->combineSamplesAnStatus();
        $this->combineSamplesAndDatainfo();
        $this->combineSamplesAndOrigin();
        return $this->_samplesTable;
    }

    public function combineSamplesAndDatainfo() {
        if (!$this->_dataInfoTable)
            $this->_dataInfoTable = $this->readDataInfoTable();

        $result = array();
        foreach ($this->_dataInfoTable as $key => $val) {
            $tmp = array(
                "idSamples" => "datainfo_" . $key,
                "Name" => "datainfo_" . $key,
                "User" => "sys",
                "database" => "MainPNData",
                "table" => $val["Name"],
                "column" => "mapFlag",
                "sampleid" => "y",
                "title" => "<a href='http://adsabs.harvard.edu/abs/" . $val["sourcePaper"] . "' title='Catalogue: " . $val["CatTitle"] . "\n Table: " . 
                                                    $val["TabTitle"] . "' target='_blank'>" . $val["Name"] . "</a>",
                "class" => "Catalogues",
                "type" => "idbased",
                "color" => "black",
                "default" => 0,
                "canAdd" => "n",
                "checked" => "");
            $result["datainfo_" . $key] = $tmp;
        }

        $this->_samplesTable = array_merge($this->_samplesTable, $result);
    }

    public function combineSamplesAndOrigin() {
        $sql = "SELECT m.`Catalogue`,r.`Author`,r.`Title`,r.`Year` "
                . "FROM `" . MAIN_DB . "`.`" . MAIN_TABLE . "` m  "
                . "LEFT JOIN `MainGPN`.`ReferenceIDs` r "
                . "ON m.`Catalogue` = r.`Identifier` "
                . "WHERE m.`Catalogue` <> '' "
                . "GROUP BY m.`Catalogue`";

        $res = $this->_mysqldriver->selectquery($sql);

        $result = array();
        foreach ($res as $key => $val) {
            $tmp = array(
                "idSamples" => "origin_" . $key,
                "Name" => "origin_" . $key,
                "User" => "sys",
                "database" => MAIN_DB,
                "table" => MAIN_TABLE,
                "column" => "Catalogue",
                "sampleid" => $val["Catalogue"],
                "title" => "<a href='http://adsabs.harvard.edu/abs/" . $val["Catalogue"] . "' title='Author: " . $val["Author"] . "\nYear: " . $val["Year"] . "\nTitle: " . $val["Title"] . "' target='_blank'>" . $val["Catalogue"] . "</a>",
                "class" => "Origin",
                "type" => "premade",
                "color" => "black",
                "default" => 0,
                "canAdd" => "n",
                "checked" => "");
            $result["origin_" . $key] = $tmp;
        }

        $this->_samplesTable = array_merge($this->_samplesTable, $result);
    }

    public function combineSamplesAnStatus() {
        $sql = "SELECT * FROM `" . MAIN_DB . "`.`objStatus` ORDER BY `order` ASC, `statusTitle` ;";

        $res = $this->_mysqldriver->selectquery($sql);

        $result = array();
        foreach ($res as $key => $val) {
            $tmp = array(
                "idSamples" => "status_" . $key,
                "Name" => "status_" . $key,
                "User" => "sys",
                "database" => MAIN_DB,
                "table" => MAIN_TABLE,
                "column" => "PNstat",
                "sampleid" => $val["statusId"],
                "title" => $val["statusTitle"],
                "class" => "Status",
                "type" => "premade",
                "color" => "black",
                "default" => in_array($val["statusId"], $this->_myconfig["defaultstats"]) ? 1 : 0,
                "canAdd" => "n",
                "checked" => "");
            $result["status_" . $key] = $tmp;
        }

        $this->_samplesTable = array_merge($this->_samplesTable, $result);
    }

    public function readpngImagesInfoTable($where = "`tempimage` = 'n' and `skip` = 'n'", $orderby = "`imageorder`") { //push pngnmagesinfo table into array (key = idInfo)
        $where = $where ? $where : "";
        $fetch = $this->_mysqldriver->select("*", $this->_myconfig["pngImagesInfo"], $where, $orderby);
        foreach ($fetch as $data)
            $this->_pngimginfoTable[$data["idpngImagesInfo"]] = $data;
        return $this->_pngimginfoTable;
    }

    public function readfitsImagesInfoTable() { //push fitsnmagesinfo table into array (key = idInfo)
        $fetch = $this->_mysqldriver->select("*", $this->_myconfig["fitsImagesInfo"]);
        foreach ($fetch as $data)
            $this->_fitsimginfoTable[$data["idimagesets"]] = $data;
        return $this->_fitsimginfoTable;
    }

    public function readpngGroupedImagesInfoTable() { //push pngnmages groups into array
        $result = array();
        $fetch = $this->_mysqldriver->select("*", $this->_myconfig["pngImagesInfo"], "`tempimage` = 'n' and `showingallery` = 'y'", "`groupid`", "`group`");
        foreach ($fetch as $data)
            $result[$data["idpngImagesInfo"]] = $data;
        return $result;
    }

    public function readmainPNGroups() { //reads MainPNSamples.mainPNGroups into array
        $result = array();
        $fetch = $this->_mysqldriver->select("*", $this->_myconfig["mainPNGroups"]);
        foreach ($fetch as $data) {
            if (!isset($result[$data["groupID"]]))
                $result[$data["groupID"]] = array();
            array_push($result[$data["groupID"]], $data);
        }
        return $result;
    }

    public function readDataInfoTable($where = False) { //push DataInfo table into array (key = idDataInfo)
        $result = array();
        $fetch = $this->_mysqldriver->select("*", $this->_myconfig["DataInfo"],$where);
        foreach ($fetch as $data)
            $result[$data["idDataInfo"]] = $data;
        return $result;
    }

    public function listOfTables() {
        $where = False;
        if (!$this->_isAdmin) $where = "`public` = 'y'";
        //$list = $this->_mysqldriver->selectquery("SELECT `varTable` FROM " . $this->_myconfig["MainInfo"] . " GROUP BY `varTable`;");
        $list = $this->_mysqldriver->select("varTable", $this->_myconfig["MainInfo"], $where, False, "varTable");
        return $list;
    }

    public function changeKeyArray($array, $key) {
        $result = array();
        foreach ($array as $subarray) {
            $result[$subarray[$key]] = $subarray;
        }
        return $result;
    }

    public function setPNstatusArray() {
        return makeOneColMain($this->readStatuses(), "statusId");
    }

    /**
     * Sort rows from Info table into search fields
     *
     * public search field = array (1 => array (vals from Info table where varGroup = 1), 2 => array (vals from Info table where varGroup = 2))
     */
    public function createSearchFields() {
        $searcharray = array("varVar" => "", "varColumn" => "", "idInfo" => "", "varTable" => "", "varName" => "", "varUnits" => "", "varSearch" => "", "clmnName" => "");
        if (!$this->_infoTable)
            $this->readInfoTable();
        foreach ($this->_infoTable as $info) {
            if (!isset($this->searchFields[$info["varGroup"]]))
                $this->searchFields[$info["varGroup"]] = array();
            array_push($this->searchFields[$info["varGroup"]], array_intersect_key($info, $searcharray));
        }
        return $this->searchFields;
    }

    public function createImageGroupArray() {
        $result = array();
        if (!isset($this->_pngimginfoTable))
            $this->readpngImagesInfoTable();
        foreach ($this->_pngimginfoTable as $data) {
            if (!isset($result[$data["group"]]))
                $result[$data["group"]] = array();
            $result[$data["group"]][$data["prioringroup"]] = $data;
        }
        return $result;
    }

    public function createImageGalleryArray() {
        $result = array();
        if (!isset($this->_pngimginfoTable))
            $this->readpngImagesInfoTable();
        foreach ($this->_pngimginfoTable as $data) {
            if (!isset($result[$data["galerybatch"]]))
                $result[$data["galerybatch"]] = array();
            if (!isset($result[$data["galerybatch"]][$data["group"]]))
                $result[$data["galerybatch"]][$data["group"]] = array();

            $result[$data["galerybatch"]][$data["group"]][$data["prioringroup"]] = $data;
        }
        return $result;
    }

    public function createImageSetsArray() {
        $this->_imagesetsarray = makeOneColMain($this->_mysqldriver->selectquery("SELECT * FROM `" . MAIN_IMAGES . "`.`imagesets`;"), "set");
        return $this->_imagesetsarray;
    }


    public function readUserData($all = False) {
        $userdata = makeOneColMain($this->_mysqldriver->select("*", $this->_mysqldriver->tblName(USERS_DB,"userslist")), "userName");
        $tmpuserdata = $userdata;
        foreach ($tmpuserdata as $user => $data) {
            $chunks = explode(";", $data['specAccess']);
            $userdata[$user]['specAccess'] = $chunks;
        }
        if ($all) {
            return $userdata;
        } else
            return $userdata[$this->_userId];
    }

    public function readCheckSamples() { //push User Samples table into array (key = sampleName)
        $result = array();
        $fetch = $this->_mysqldriver->select("*", "`MainPNSamples`.`checkObjects`", "`userId` = '" . $this->_userId . "';");
        if ($fetch)
            $result = $fetch;
        return $result;
    }

    public function readStatuses() { //read all available statuses i.e. MainGPN.samplesInfo where class = 'status'
        $result = array();
        $fetch = $this->_mysqldriver->select("statusTitle,statusId", $this->_mysqldriver->tblName(MAIN_DB,"objStatus"),False," `order` ASC, `statusTitle` ");
        if ($fetch)
            $result = $fetch;
        return $result;
    }

    public function readUserSamples() {
        $sql = "SELECT * FROM `" . MAIN_SAMPLES . "`.`userSamples` WHERE (`user` = '" . $this->_userId . "' OR `user` = 'sys');";
        $res = $this->_mysqldriver->selectquery($sql);
        if (!$res or empty($res))
            return array();
        $tmpres = $res;

        foreach ($tmpres as $key => $vals) {
            if (empty($vals["sample"])) {
                $res[$key]["sample"] = array();
            } else {
                $ids = explode(",", $vals["sample"]);
                $res[$key]["sample"] = $ids;
            }
        }
        $result = groupArrayByField($res, "sampleName");
        return $result;
    }

    public function readHelpPages() {
        $pages = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(MAIN_DB,"helppages"), False, "`order`");
        return makeOneColMain($pages, "topic");
    }
    
    public function readFitsImages($id,$sets = False) {
        $wheresets = "";
        if ($sets and count($sets > 0)) $wheresets = " AND `set` IN ('".implode("','", $sets) ."')"; 
        $fitsimages = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(MAIN_IMAGES,"fitsimages"),"`idPNmain` = " . $id . $wheresets);
        return $fitsimages;
    }
    

            

}

?>
