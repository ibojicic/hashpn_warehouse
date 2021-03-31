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
class CheckObjects extends SetMainObjects
{
    private $_tableId = false;
    private $_getdata;
    private $_where;

    public $_sampleId;

    
    public $currentId;
    public $nextId;
    public $togo = false;
    public $done = false;
    public $finished = false;

    public function __construct($myConfig, $getdata, $isAdmin, $userid)
    {
        parent::__construct($myConfig, $userid, $isAdmin);
        $this->_getdata = $getdata;
        if (isset($this->_getdata["finish"]) and $this->_getdata["finish"] == "y") {
            $this->finished = true;
            return;
        }
        $this->_checkDelete();
        if (!$this->_checkCheckTable()) {
            $this->finished = true;
            return;
        }
        if (!$this->_tableId) {
            $this->_tableId = $this->_setTableId($this->_sampleId);
        }
        $this->_setDoneId();
        $this->_setDoneTogo();
        if (!$this->_setCurNextIds()) {
            $this->finished = true;
            return;
        }
    }

    private function _checkDelete()
    {
        $redirect = false;
        foreach ($this->_getdata as $key => $val) {
            if (substr($key, 0, 7) == "delsmp_") {
                $redirect = true;
                $this->_deleteSampleTables($val);
            }
        }
        if ($redirect) {
            header("Location: dbMainPage.php");
        }
        return;
    }

    private function _setDoneId()
    {
        if (!isset($this->_getdata["doneid"])) {
            return false;
        }
        $sql_update = "UPDATE `MainGPNTemp`.`".$this->_tableId."` SET `checked` = 'y' WHERE `".MAIN_ID."` = ".$this->_getdata["doneid"].";";
        $this->_mysqldriver->query($sql_update);
        return true;
    }

    private function _checkCheckTable()
    {
        if (!isset($this->_getdata["chksmpl"])) {
            return false;
        }
        $this->_sampleId = $this->_getdata["chksmpl"];
        if ($this->_sampleId == "currsample") {
            $this->_createCheckTable();
        }
        return true;
    }

    private function _getWhere()
    {
        $sql = "SELECT `fullWhere` FROM `".USERS_DB."`.`".SESSIONS_TABLE."`
			WHERE `id` =
			(SELECT MAX(a.`id`) FROM `".USERS_DB."`.`".SESSIONS_TABLE."` a
			WHERE a.`userName` = '".$this->_userId."')";
        if (!($results = $this->_mysqldriver->selectquery($sql))) {
            return false;
        }
        $this->_where = $results[0]["fullWhere"];
        return true;
    }

    private function _createCheckTable()
    {
        if (!$this->_getWhere()) {
            return false;
        }
        $sql_insert = "INSERT INTO `MainPNSamples`.`checkObjects` (`userId`,`query`,`Name`,`Date`,`Description`) VALUES
						('".$this->_userId."','".mysql_escape_string($this->_where)."','".$this->_getdata["name"]."',NOW(),'".$this->_getdata["descr"]."')";
        if (!($this->_mysqldriver->query($sql_insert))) {
            return false;
        }
        $this->_sampleId = mysql_insert_id();
        
        $this->_tableId = $this->_setTableId($this->_sampleId);

        $sql_create = "CREATE TABLE `MainGPNTemp`.`".$this->_tableId."` (
						`id".$this->_tableId."` INT NOT NULL AUTO_INCREMENT,
						`".MAIN_ID."` INT NULL,
						`checked` VARCHAR(1) NOT NULL DEFAULT 'n',
						`userId` VARCHAR(45) NULL,
						`date` DATETIME NULL,
						PRIMARY KEY (`id".$this->_tableId."`, `".MAIN_ID."`),
						UNIQUE INDEX `id".$this->_tableId."_UNIQUE` (`id".$this->_tableId."` ASC),
						UNIQUE INDEX `".MAIN_ID."_UNIQUE` (`".MAIN_ID."` ASC));";
        if (!($this->_mysqldriver->query($sql_create))) {
            $this->_mysqldriver->query("DELETE FROM `MainPNSamples`.`checkObjects` WHERE `idcheckObjects` = ".$this->_sampleId);
            return false;
        }
        $sql_insert = "INSERT INTO `MainGPNTemp`.`".$this->_tableId."` (`".MAIN_ID."`) SELECT `".MAIN_ID."` FROM `".MAIN_DB."`.`".$this->_currentview."` ".$this->_where.";";

        if (!$this->_mysqldriver->query($sql_insert)) {
            $this->_deleteSampleTables($this->_sampleId);
            return false;
        }
        return true;
    }

    private function _deleteSampleTables($tableId)
    {
        $this->_mysqldriver->query("DELETE FROM `MainPNSamples`.`checkObjects` WHERE `idcheckObjects` = $tableId");
        $this->_mysqldriver->query("DROP TABLE `MainGPNTemp`.`".$this->_setTableId($tableId)."`;");
        return true;
    }

    private function _setTableId($sampleId)
    {
        return "checkTable_". $sampleId;
    }

    private function _setCurNextIds()
    {
        if (isset($this->_getdata["id"]) and isset($this->_getdata["nextid"])) {
            $this->currentId = $this->_getdata["id"];
            $this->nextId = $this->_getdata["nextid"];
        } else {
            if (!($firstTwo = $this->_mysqldriver->select(MAIN_ID, $this->_mysqldriver->tblName(TEMP_DB, $this->_tableId), "`checked` = 'n'", false, false, "0,2"))) {
                return false;
            }
            $this->currentId = $firstTwo[0][MAIN_ID];
            $this->nextId = (isset($firstTwo[1]) and isset($firstTwo[1][MAIN_ID])) ? $firstTwo[1][MAIN_ID] : false;
        }
        return true;
    }

    private function _setDoneTogo()
    {
        $this->togo = $this->_mysqldriver->selectOneValue("COUNT(`checked`)", $this->_mysqldriver->tblName(TEMP_DB, $this->_tableId), "`checked` = 'n'");
        $this->done = $this->_mysqldriver->selectOneValue("COUNT(`checked`)", $this->_mysqldriver->tblName(TEMP_DB, $this->_tableId), "`checked` = 'y'");
        return;
    }
}
