<?php

// TODO CHECK VARIABLES -- CHECKING FINISHED
// TODO CHECK DOUBLE QUOTES --  FINISHED CHECKING

/**
 * Description of class
 *
 * @author ivan
 */
class GetObjectData extends SetMainObjects {

    private $_dataInfoTables;
    private $_sqls;
    private $_designation = array();
    private $_cnamesinfo;
    private $_commentsTable;
    private $_namedInfoTable;
    private $_pnStat = array();
    public $results;
    public $userComments = array();

    public function __construct($myConfig, $objid, $userid, $isAdmin) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_dataInfoTables = array(
            "infoTable" => $this->_readtables->readInfoTable(),
            "infocolumns" => $this->_readtables->readColumnsFromInfoTable(),
            "listoftables" => $this->_readtables->listOfTables(),
            "PNstatinfo" => $this->_readtables->setPNstatusArray());

        $this->_designation = $myConfig["maindesignation"];
        $this->_cnamesinfo = $myConfig["commonnames"];
        $this->_commentsTable = $myConfig["Comments"];
        $this->_setObjectId($objid);
        $this->_setUserId($userid);
        $this->_createNamedInfoTable();
        $this->_createSQLs();
        $this->_getSQLResults();
        $this->_setPNstat();
        $this->_setMainParameters();
        $this->_readCommentsTable();
    }

    private function _createNamedInfoTable() {
        $this->_namedInfoTable = makeOneColMain($this->_dataInfoTables["infoTable"], "clmnName");
        return True;
    }

    private function _createSQLs() {
        foreach ($this->_dataInfoTables["listoftables"] as $tableInfo) {
            $tablename = $tableInfo["varTable"];
            $this->_sqls[$tablename] = "SELECT * FROM `" . MAIN_DB . "`.`" . $tablename . "` WHERE `" . MAIN_ID . "` = " . $this->_objectId . ";";
        }
        return;
    }

    private function _getSQLResults() {
        foreach ($this->_sqls as $tbname => $tbsql) {
            $this->results[$tbname] = array("inuse" => FALSE, "notinuse" => FALSE, "dummy" => FALSE);
            $results = $this->_mysqldriver->selectquery($tbsql);
            if ($tbname == MAIN_TABLE or $tbname == "tbUsrComm" and ! empty($results)) {
                $this->results[$tbname]["inuse"] = $tbname == MAIN_TABLE ? $results[0] : $results;
            } elseif (!empty($results)) {
                $tmpresults = $results;
                foreach ($results as $key => $result) {
                    if ($result["InUse"] == 1) {
                        if (!$this->results[$tbname]["inuse"])
                            $this->results[$tbname]["inuse"] = array();
                        array_push($this->results[$tbname]["inuse"], $result);
                        unset($tmpresults[$key]);
                    }
                }
                if (!empty($tmpresults))
                    $this->results[$tbname]["notinuse"] = $tmpresults;
            } else
                $this->results[$tbname]["dummy"] = $this->_createDummyResults($tbname);
        }
    }

    private function _createDummyResults($table) {
        $columns = $this->_dataInfoTables["infocolumns"][$table];
        $result = array("id" . $table => "-");
        foreach ($columns as $nm)
            $result[$nm] = "-";
        $tmpresult = array("reference" => "-", "refTable" => "-", "comments" => "-", "userRecord" => "-", MAIN_ID => $this->_objectId, "InUse" => "-1");
        $result = array_merge($result, $tmpresult);
        return $result;
    }

    private function _setPNstat() {
        $this->_pnStat["status"] = $this->results[MAIN_TABLE]["inuse"]["PNstat"];
        $this->_pnStat["infostat"] = $this->_dataInfoTables["PNstatinfo"][$this->_pnStat["status"]]["statusTitle"];
        $this->results["PNstat"] = $this->_pnStat;
        return;
    }

    public function getField($tableName, $field) {
        $res = $this->results[$tableName]["inuse"];
        $result = $res[$field];
        return $result;
    }

    private function _setMainParameters() {
        $this->results["userRecord"] = $this->results[$this->_designation["table"]]["inuse"]["userRecord"];
        $this->results["headerName"] = $this->_designation["prefix"] . $this->results[$this->_designation["table"]]["inuse"][$this->_designation["column"]] . $this->_designation["sufix"];
        $this->results["extraName"] = (isset($this->results["tbCNames"]["inuse"][0]["Name"]) and trim($this->results["tbCNames"]["inuse"][0]["Name"]) != "") ? $this->results["tbCNames"]["inuse"][0]["Name"] : "n/a";
        return;
    }

    private function _readCommentsTable() { //push Comments table into array (id = MAIN_ID)
        $fetch = (isset($this->results["tbUsrComm"]["inuse"]) and ! empty($this->results["tbUsrComm"]["inuse"])) ? $this->results["tbUsrComm"]["inuse"] : False;
        if ($fetch)
            foreach ($fetch as $data)
                array_push($this->userComments, $data);
        return $this->userComments;
    }

}

?>
