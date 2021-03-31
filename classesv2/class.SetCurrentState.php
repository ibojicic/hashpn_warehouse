<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/**
 * Description of SetCurrenState
 *
 * @author ivan
 */
class SetCurrentState extends SetMainObjects {

    private $_getArray; //input array combined $_GET and $_POST
    private $_sessionArray = array(); //array of parameters for the current session
    private $_searchTextResults = array();
    private $_where = "";
    private $_what = array(MAIN_ID);
    private $_from = False;
    private $_dataInfoTables;
    private $_samequery = False;
    public $view;
    public $first;
    public $selection = array();
    public $showSelectedSamples = False;
    public $sqlSearchFull;
    public $sqlSearchLimited;
    public $result;
    public $fullresult;
    public $extraresults = array();
    public $distance = False;
    public $orderby;
    public $orderdir;
    public $order;
    public $norecords;

    public function __construct($inputGetPost, $userid, $myConfig, $isAdmin) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_dataInfoTables = array(
            "infoTable" => $this->_readtables->readInfoTable(),
            "samplesTable" => $this->_readtables->readSamplesTable(),
            "pngimginfoTable" => $this->_readtables->readpngImagesInfoTable(),
            "searchFields" => $this->_readtables->createSearchFields(),
            "imagegroups" => $this->_readtables->readpngGroupedImagesInfoTable(),
            "usersamples" => $this->_readtables->readUserSamples(),
            "sessionArray" => $myConfig["sessionArray"],
            "posselections" => $myConfig["posselections"],
            "selectionfields" => $myConfig["selectionfields"],
            "extraFields" => $this->_mysqldriver->setListForQuery($myConfig["extraFields"], "`")
        );


        $this->_getArray = $inputGetPost;

        $this->_setSessionArray();

        $this->_parseGet($myConfig["inits"]);

        $this->_sampleSelect();

        $this->_setFirst();

        $this->_setSelections();

        $this->_setShowSelectedSamples();

        $this->_setWhere();

        $this->_checkSameQuery();

        $this->_setWhat();

        $this->_setDistance();

        $this->_setFrom();

        $this->_countRecords();
    }

    private function _setSessionArray() {
        if ((isset($this->_getArray["restart"]) and $this->_getArray["restart"] == "RESTART")) {
            $this->_restartSession();
        } else {
            $this->_sessionArray = $this->_restoreSession();
        }
        $this->_setDefaults();
    }

    private function _restartSession() { //restarts current session
        if (trim($this->_userId) == "")
            die("Fatal error(6653)");
        $this->_mysqldriver->query("DELETE FROM `" . USERS_DB . "`.`" . SESSIONS_TABLE . "` WHERE `userName` = '" . $this->_userId . "';");
        $this->_sessionArray = array();
    }

    private function _restoreSession() {
        $result = array();
        $where = " `id` = (SELECT MAX(`id`) FROM `" . USERS_DB . "`.`" . SESSIONS_TABLE . "` WHERE `userName` = '" . $this->_userId . "')";
        $results = $this->_mysqldriver->selectOne("*", $this->_mysqldriver->tblName(USERS_DB, SESSIONS_TABLE), $where);
        if (!$results)
            return array();
        foreach ($this->_dataInfoTables["sessionArray"] as $key => $val) {
            $newval = $results[$key];
            if ($val["type"] == "array")
                $newval = unserialize($newval);
            if ($newval)
                $result[$key] = $newval;
        }
        return $result;
    }

    private function _setDefaults() {
        // if session value is not set use default
        foreach ($this->_dataInfoTables["posselections"] as $selection => $selvals) {
            if (!isset($this->_sessionArray[$selection]) or empty($this->_sessionArray[$selection])) {
                $this->_sessionArray[$selection] = array();
                foreach ($this->_dataInfoTables[$selvals["dataInfoVar"]] as $info) {
                    if ((isset($info[$selvals["defSeeColumn"]]) and $info[$selvals["defSeeColumn"]] == $selvals["defSeeVal"]))
                        array_push($this->_sessionArray[$selection], $info[$selvals["dataInfoColumn"]]);
                }
            }
        }
        // import defaults 
        foreach ($this->_dataInfoTables["sessionArray"] as $key => $val)
            if (!isset($this->_sessionArray[$key]))
                $this->_sessionArray[$key] = $val["default"];
        // set default view
        $this->view = $this->_sessionArray["view"];
        // set default columns order
        $this->orderby = $this->_sessionArray["orderby"];
        $this->orderdir = $this->_sessionArray["orderdir"];
        $this->order = " ORDER BY `" . ($this->_sessionArray["orderby"]) . "` " . ($this->_sessionArray["orderdir"]) . ", `" . MAIN_DESIGNATION . "` ASC ";
    }

    private function _unsetSampleSelection() {
        $this->_sessionArray["sselect"] = "nosample";
        unset($this->_sessionArray["currentsample"]);
        $this->showSelectedSamples = "Full database.";
    }

    private function _setFullDatabase() {
        foreach ($this->_dataInfoTables["selectionfields"] as $field)
            if (isset($this->_sessionArray[$field]))
                unset($this->_sessionArray[$field]);
        $this->_sessionArray["sselect"] = "nosample";
    }

    public function setDisplays($sampleselections) {
        if (isset($this->_sessionArray["displayRule"]) and $this->_sessionArray["displayRule"] != "") {
            $sampleselections["rulesearch"] = $this->_sessionArray["displayRule"];
            array_push($sampleselections["errormessage"], $this->_sessionArray["displayRule"]);
        } elseif (isset($this->_sessionArray["displayText"]) and $this->_sessionArray["displayText"] != "") {
            $sampleselections["textsearch"] = $this->_sessionArray["displayText"];
            array_push($sampleselections["errormessage"], $this->_sessionArray["displayText"]);
        }
        if (isset($this->_sessionArray["displayPosition"]) and $this->_sessionArray["displayPosition"] != "") {
            $sampleselections["displayposition"] = "position in " . $this->_sessionArray["displayPosition"]["showrad"] .
                    " [" . $this->_sessionArray["displayPosition"]["radunit"] . "] from " . $this->_sessionArray["displayPosition"]["pos"] .
                    " (" . $this->_sessionArray["displayPosition"]["coords"] . ")";
            $sampleselections["possearch"] = $this->_sessionArray["displayPosition"]["pos"];
            $sampleselections["posrad"] = $this->_sessionArray["displayPosition"]["showrad"];
            $sampleselections[$this->_sessionArray["displayPosition"]["radunit"]] = "selected";
            $sampleselections[$this->_sessionArray["displayPosition"]["coords"]] = "selected";
            array_push($sampleselections["errormessage"], $sampleselections["displayposition"]);
        }
        return $sampleselections;
    }

    public function storeSession() {
        $fields = array();
        $values = array();
        $this->_sessionArray["fullWhere"] = $this->_where;
        $this->_sessionArray["whereMD5"] = md5($this->_where);
        $tempsession = $this->_sessionArray;
        foreach ($this->_dataInfoTables["sessionArray"] as $key => $val) {
            if (isset($tempsession[$key])) {
                $fieldval = $val["type"] == "array" ? serialize($tempsession[$key]) : $tempsession[$key];
                array_push($fields, "`" . $key . "`");
                array_push($values, "'" . mysql_escape_string($fieldval) . "'");
            }
        }

        $sql = "INSERT INTO `" . USERS_DB . "`.`" . SESSIONS_TABLE . "`
				(`userName`," . implode(",", $fields) . ",`time`)
				VALUES
				('" . $this->_userId . "'," . implode(",", $values) . ", NOW());";
        if (!$this->_mysqldriver->query($sql))
            return False;
        return True;
    }

    private function _unsetTextSearch() {
        if (isset($this->_sessionArray["eselect"]["text"]) or
                isset($this->_sessionArray["currentText"]) or
                isset($this->_sessionArray["displayText"])) {
            unset($this->_sessionArray["currentText"]);
            unset($this->_sessionArray["displayText"]);
            unset($this->_sessionArray["eselect"]["text"]);
        }
    }

    private function _checkEmptySselect() {
        return ((isset($this->_sessionArray["sselect"]) and empty($this->_sessionArray["sselect"])) or
                $this->_sessionArray["sselect"] == "nosample");
    }

    private function _parseGet($inits) { //parse input array (from _GET and _POST) and executes the called request
        if (isset($this->_getArray["wallselect"]))
            $this->_getArray[$this->_getArray["wallselect"]] = 1;
        foreach ($this->_dataInfoTables["posselections"] as $selection => $selvals) {
            if (isset($this->_getArray[$selection])) {
                $this->_getSelections($selection, $selvals["dataInfoVar"], $selvals["dataInfoColumn"]);
            }
        }
        if ($this->_checkEmptySselect())
            $this->_unsetSampleSelection(); //when everything is unselected in sample selection
        if (isset($this->_getArray["addorfull"]) and $this->_getArray["addorfull"] == "fulldb")
            $this->_setFullDatabase();
        foreach ($inits as $init) {
            if (isset($this->_getArray[$init])) {
                $finit = "run_" . $init;
                $this->{$finit}();
            }
        }
    }

    private function _getSelections($sessionVar, $dataInfoVar, $dataInfoColumn) {
        $tmparray = array_keys($this->_getArray);
        $this->_sessionArray[$sessionVar] = array();
        foreach ($this->_dataInfoTables[$dataInfoVar] as $samples) {
            if (in_array($samples[$dataInfoColumn], $tmparray))
                array_push($this->_sessionArray[$sessionVar], $samples[$dataInfoColumn]);
        }
    }

    private function _setSelections() {
        foreach ($this->_dataInfoTables["posselections"] as $selection => $selvals) {
            if ($selvals["alwaysOn"])
                foreach ($selvals["alwaysOn"] as $alon)
                    if (!in_array($alon, $this->_sessionArray[$selection]))
                        array_push($this->_sessionArray[$selection], $alon);
            if ($this->_dataInfoTables[$selvals["dataInfoVar"]] and ! empty($this->_dataInfoTables[$selvals["dataInfoVar"]])) {
                foreach ($this->_dataInfoTables[$selvals["dataInfoVar"]] as $info) {
                    if (is_array($this->_sessionArray[$selection]) and in_array($info[$selvals["dataInfoColumn"]], $this->_sessionArray[$selection])) {
                        if (!isset($this->selection[$selection]))
                            $this->selection[$selection] = array();
                        array_push($this->selection[$selection], $info[$selvals["indexColumn"]]);
                    }
                }
            }
        }
    }

    private function _setFirst($restart = False) { //determine the first element in the sql table (sql query) from the paginator
        if (isset($this->_sessionArray["viewfirst"]) and ! $restart)
            $this->first = $this->_sessionArray["viewfirst"];
        else {
            $this->first = 1;
            $this->_sessionArray["viewfirst"] = 1;
        }
    }

    private function run_usersamples() {
        //TODO setup order for wall view
        if (isset($this->_getArray["rememberorder"]) and $this->_getArray["rememberorder"] == "Remember current order") {
            
        }

        if ($this->_getArray["addelsel"] == "addsample") {
            $sampsql = $this->_manipulateSamples("addnewsample", $this->_getArray["samplename"], $this->_getArray["sampledesc"]);
            if ($this->_mysqldriver->query($sampsql)) {
                $this->_setRunResponse("success", "Succesfuly created sample:" . $this->_getArray["samplename"] . ".");
            } else {
                $this->_setRunResponse("error", "Error in creating sample:" . $this->_getArray["samplename"] . ".");
            }
        } elseif ($this->_getArray["addelsel"] == "delsample") {
            $sampsql = $this->_manipulateSamples("deletesample", $this->_getArray["exsamples"]);
            if ($this->_mysqldriver->query($sampsql)) {
                $this->_setRunResponse("success", "Succesfuly deleted sample:" . $this->_getArray["exsamples"] . ".");
            } else {
                $this->_setRunResponse("error", "Error in deleting sample:" . $this->_getArray["exsamples"] . ".");
            }
            $this->_dataInfoTables["usersamples"] = $this->_readtables->readUserSamples();
        } elseif ($this->_getArray["addelsel"] == "add" or $this->_getArray["addelsel"] == "del") {
            $name = $this->_getArray["exsamples"];
            $oldarray = array();
            @$oldarray = $this->_dataInfoTables["usersamples"][$name][0]["sample"];

            if ($this->_getArray["sellall"] == "all") {
                $checkedobjects = $this->_mysqldriver->selectColumn(
                        MAIN_ID, 
                        $this->_mysqldriver->tblName(MAIN_DB, $this->_currentview), 
                        $this->cleanWHEREfromstart($this->_sessionArray["fullWhere"])
                        );
            } elseif ($this->_getArray["sellall"] == "selected") {
                $checkedobjects = explode(",", trim($this->_getArray["checkedobjects"], ",\n"));
            }

            if ($this->_getArray["addelsel"] == "add") {
                $newsamplearray = $this->_crossArray($oldarray, $checkedobjects);
                $newsample = trim(implode(",", $newsamplearray), ",\n\t ");
                $succesmessage = "Succesfuly added objects into sample:" . $name .
                        ". " . (count($newsamplearray) - count($oldarray)) . " new objects and " .
                        (count($checkedobjects) - count($newsamplearray) + count($oldarray)) . " duplicates.";
                $errormessage = "Error in adding objects to sample:" . $name;
            } elseif ($this->_getArray["addelsel"] == "del") {
                $newsamplearray = array_diff($oldarray, $checkedobjects);
                $newsample = trim(implode(",", $newsamplearray), ",\n\t ");
                $succesmessage = "Succesfuly deleted " . (count($oldarray) - count($newsamplearray)) . " objects from sample:" . $name;
                $errormessage = "Error in deleting objects from sample:" . $name;
            }

            $sql = "UPDATE `" . MAIN_SAMPLES . "`.`userSamples` SET 
				`sample` = '" . $newsample . "'
				WHERE `sampleName` = '" . $name . "' AND `user` = '" . $this->_userId . "';";
            if ($this->_mysqldriver->query($sql)) {
                $this->_setRunResponse("success", $succesmessage);
            } else {
                $this->_setRunResponse("error", $errormessage);
            }
        }
    }

    private function run_view() { //push selected view into local session array
        $this->_sessionArray["view"] = $this->_getArray["view"];
        $this->view = $this->_sessionArray["view"];
    }

    private function run_viewfirst() { //push selected first into local session array
        $this->_sessionArray["viewfirst"] = $this->_getArray["viewfirst"];
    }

    private function run_exportdata() {
        $this->_restoreQuery();
        $this->exportformat($this->_getArray["format"]);
        $message = "You can download exported results from 
                           <a href='download.php?p=e&f=" . $this->_exportfile . "'>" . $this->_exportfile . ".</a>
                            The file be erased in 24h!<a href='dbMainPage.php'> Go back</a> or
                            <a href='dbMainPage.php?view=table&restart=RESTART'>restart </a> current results.";
        $this->_setRunResponse("success", $message);


        //do nothing
    }

    private function run_orderby() {
        $this->_sessionArray["orderby"] = $this->_getArray["orderby"];
        if (isset($this->_sessionArray["orderdir"]) and $this->_sessionArray["orderdir"] == "ASC") {
            $this->_sessionArray["orderdir"] = "DESC";
        } else
            $this->_sessionArray["orderdir"] = "ASC";
        $this->order = " ORDER BY `" . ($this->_sessionArray["orderby"]) . "` " . ($this->_sessionArray["orderdir"]) . ", `" . MAIN_DESIGNATION . "` ASC ";
        $this->orderby = $this->_sessionArray["orderby"];
        $this->orderdir = $this->_sessionArray["orderdir"];
    }

    private function run_rulesearch() {
        //$this->_unsetTextSearch();
        $this->_setFirst(True);
        if (isset($this->_getArray["condsearch"]) and trim($this->_getArray["condsearch"]) != "") {
            $ruleArray = $this->_parseRule();
            $this->_sessionArray["displayRule"] = str_ireplace("<", "&lt;", trim($this->_getArray["condsearch"]));
            $this->_sessionArray["currentRule"] = $ruleArray;
            if (!$this->_testRule($ruleArray)) {
                $this->_setRunResponse("error", "Error in parsing rule: " . $this->_getArray["condsearch"] . ".");
                $this->_sessionArray["displayRule"] = "";
                $this->_sessionArray["currentRule"] = "";
                return False;
            }
        } elseif (trim($this->_getArray["condsearch"]) == "") {
            $this->_sessionArray["displayRule"] = "";
            $this->_sessionArray["currentRule"] = "";
        }
    }

    private function _parseRule() {
        $query = $this->_getArray["condsearch"];
        $searchfields = $this->_dataInfoTables["searchFields"];
        foreach ($searchfields[1] as $field) {
            if (stripos($query, $field["varVar"]) !== False) {
                $query = str_ireplace($field["varVar"], "`" . $field["clmnName"] . "`", $query);
            }
        }
        return $query;
    }

    private function _testRule($rule) {
        if (!$this->_from)
            $this->_setFrom();
        $sql = "SELECT * FROM " . $this->_from . " WHERE " . $rule . " LIMIT 0,1";
        $res = $this->_mysqldriver->selectquery($sql);
        if (!$res and isset($this->_mysqldriver->error))
            return False;
        return True;
    }

    private function run_positionsearch() {
        //$this->_unsetTextSearch();
        $this->_setFirst(True);
        $file = $this->_setUploadFile("posuploadfile");
        if (isset($this->_getArray["possearch"])
                and ( (trim($this->_getArray["possearch"]) != "") or $file)
                and isset($this->_getArray["posrad"])
                and ( trim($this->_getArray["posrad"]) != "")
                and isset($this->_getArray["posunits"])
                and isset($this->_getArray["poscoords"])) {

            $rule = $this->_parseFullPositionRule($this->_getArray["possearch"], $this->_getArray["posrad"], $this->_getArray["posunits"], $this->_getArray["poscoords"], $this->_getArray["searchbox"], $file);
            if (!$rule) {
                $this->_setRunResponse("error", "Error in parsing coordinates: " . $this->_getArray["possearch"] . " (" . $this->_getArray["poscoords"] . ").");
                $this->_sessionArray["displayPosition"] = "";
                $this->_sessionArray["currentPosition"] = "";
                $this->_unsetEselect("position");
                return False;
            }
            if (!$file) {
                $this->_sessionArray["displayPosition"] = $rule["results"];
                $this->_sessionArray["currentPosition"] = $rule["searchsql"];
                $this->_createEselect("position", $rule["distsql"]);
            } else {
                $this->_sessionArray["displayText"] = $rule["displayText"];
                $this->_sessionArray["currentText"] = $rule["currentText"];
                $this->_unsetEselect("position");
            }
        } elseif (trim($this->_getArray["possearch"]) == "") {
            $this->_sessionArray["displayPosition"] = "";
            $this->_sessionArray["currentPosition"] = "";
            $this->_unsetEselect("position");
        }
    }

    private function _parseFullPositionRule($position, $rad = False, $radUnit = False, $coords = "radec", $searchbox = "cone", $file = False) {
        if (!$file)
            return $this->_parsePositionRule($position, $rad, $radUnit, $coords, $searchbox);
        return $this->_parseSearchList($file, $rad, $radUnit, $coords);
    }

    private function run_intextsearch() {
        $result = array();
        $file = $this->_setUploadFile("txtuploadfile");
        if ($file)
            $this->_getArray["textsearch"] = $this->_setTextList($file);
        if (isset($this->_getArray["textsearch"]) and $this->_getArray["textsearch"] and trim($this->_getArray["textsearch"]) != "") {
            $sesamesearch = (isset($this->_getArray["addsesame"]) and $this->_getArray["addsesame"] == "y") ? True : False;
            $this->_setFirst(True);
            $this->_setFullDatabase();
            if (!isset($this->_getArray["textwhere"]))
                $this->_getArray["textwhere"] = "everywhere";
            $clsForSrch = $this->_parseColsForTextSrch($this->_getArray["textwhere"]);
            $textarray = $this->_cleanTextSearch(explode(",", trim($this->_getArray["textsearch"])));
            $this->_getArray["textsearch"] = implode(",", $textarray);
            $tmptextarray = $textarray;
            if ($sesamesearch)
                foreach ($tmptextarray as $text) {
                    $sesres = cdsSesameDriver($text);
                    if ($sesres)
                        foreach ($sesres["aliases"] as $var)
                            if (!in_array(trim($var), $textarray))
                                array_push($textarray, trim($var));
                }
            foreach ($clsForSrch as $clmn) {
                $tempresult = $this->_searchForText($clmn["result"]["tablename"], $clmn["result"]["column"], $textarray);
                if ($tempresult)
                    $result = $this->_combineIdArrays($result, $tempresult);
            }
            if (!empty($result)) {
                $this->_sessionArray["currentText"] = " `" . MAIN_ID . "` IN (" . (implode(",", $result)) . ") ";
                $this->_createEselect("text", $this->_searchTextResults);
            } else {
                $this->_sessionArray["currentText"] = " `" . MAIN_ID . "` = 0 ";
            }
            $this->_sessionArray["displayText"] = "YOU SEARCHED FOR: '" . $this->_getArray["textsearch"] . "' IN '" . $this->_getArray["textwhere"] . "'";
        }
    }

    private function _cleanTextSearch($array) {
        $temp = array();
        foreach ($array as $val)
            if (strlen(trim($val)) > 2 or $this->_isAdmin)
                array_push($temp, trim($val));
        return $temp;
    }

    private function _createEselect($type, $array) {
        if (!isset($this->_sessionArray["eselect"]))
            $this->_sessionArray["eselect"] = array();
        $this->_sessionArray["eselect"][$type] = $array;
    }

    private function _unsetEselect($type) {
        if (isset($this->_sessionArray["eselect"][$type]))
            unset($this->_sessionArray["eselect"][$type]);
    }

    private function _parseColsForTextSrch($columns) {
        $colsforsearch = array();
        $searchfields = $this->_dataInfoTables["searchFields"];
        foreach ($searchfields[2] as $field) {
            if ($columns == "everywhere" or $columns == $field["varVar"]) {
                array_push($colsforsearch, array("result" => array("tableid" => $field["idInfo"],
                        "tablename" => $field["varTable"],
                        "column" => $field["varColumn"]),
                    "name" => $field["varName"]));
            }
        }
        return $colsforsearch;
    }

    private function _searchForText($table, $column, $textarray) {
        $result = False;
        $like_sql = array();
        $tmp_sql = "SELECT  `" . MAIN_ID . "`, `" . $column . "` as 'result' FROM `" . MAIN_DB . "`.`" . $table . "` WHERE ";
        foreach ($textarray as $text)
            array_push($like_sql, " REPLACE(`" . MAIN_DB . "`.`" . $table . "`.`" . $column . "`,' ','') LIKE \"%" . str_ireplace(" ", "", $text) . "%\"");
        foreach ($textarray as $text)
            array_push($like_sql, "`" . MAIN_DB . "`.`" . $table . "`.`" . $column . "` LIKE \"%" . $text . "%\"");
        $full_sql = $tmp_sql . implode(" OR ", $like_sql);
        
        $full_sql .= " AND `" . MAIN_ID ."` IN (" . $this->_sqlUserViewIds() . ") ";
        if ($this->_mysqldriver->selectquery($full_sql . " LIMIT 0,1;")) {
            $result = $this->_mysqldriver->selectquery($full_sql);
            //$this->_formatTextResults ($result,$table,$column,$textarray); TODO
        }
        return $result;
    }

    private function _formatTextResults($results, $table, $column, $textarray) {
        foreach ($results as $result) {
            foreach ($textarray as $text) {
                $pos = stripos($result["result"], trim($text));
                if ($pos !== False) {
                    $pos -= 1;
                    $startpos = $pos - 5 < 0 ? 0 : $pos - 5;
                    $found = "..." . substr($result["result"], $startpos, $startpos < 5 ? $startpos : 5);
                    $found .= "<font color='red'>" . $text . "</font>";
                    $found .= substr($result["result"], $pos + strlen($text), 5) . "...<br>";
                    if (!isset($this->_searchTextResults[$result[MAIN_ID]]))
                        $this->_searchTextResults[$result[MAIN_ID]] = "";
                    $this->_searchTextResults[$result[MAIN_ID]] .= " in " . $column . ":" . $found;
                }
            }
        }
    }

    public function restoreTextResults() {
        if (isset($this->_sessionArray["eselect"]) and isset($this->_sessionArray["eselect"]["text"]))
            return $this->_sessionArray["eselect"]["text"];
        return False;
    }

    private function _setWhere() { //initialize where variable
        if (isset($this->_sessionArray["currentRule"]) AND trim($this->_sessionArray["currentRule"]) != "") {
            $this->_where = " WHERE " . $this->_sessionArray["currentRule"];
        } elseif (isset($this->_sessionArray["currentText"]) AND trim($this->_sessionArray["currentText"]) != "") {
            $this->_where = " WHERE " . $this->_sessionArray["currentText"];
        }

        if (isset($this->_sessionArray["currentPosition"]) AND trim($this->_sessionArray["currentPosition"]) != "") {
            if ($this->_where == "") {
                $this->_where = "WHERE " . $this->_sessionArray["currentPosition"];
            } else {
                $this->_where .= " AND " . $this->_sessionArray["currentPosition"];
            }
        }

        if (isset($this->_sessionArray["currentsample"]) AND $this->_sessionArray["currentsample"] != "") {
            if ($this->_where == "") {
                $this->_where = "WHERE " . $this->_sessionArray["currentsample"];
            } else {
                $this->_where .= " AND " . $this->_sessionArray["currentsample"];
            }
        }

        if ($this->view == "wall") {
            $wallquery = $this->_setWallImgQuery($this->_sessionArray["wallselect"]);
            $this->_where = $this->_where == "" ? " WHERE " . $wallquery : $this->_where . " AND " . $wallquery;
        }
    }

    private function _checkSameQuery() {
        $this->_samequery = md5($this->_where) == $this->_sessionArray["whereMD5"];
    }

    private function _setWhat() { //creates sql what
        foreach ($this->_dataInfoTables["infoTable"] as $infos) {
            if (in_array($infos["idInfo"], $this->selection["fselect"]))
                array_push($this->_what, $this->_dataInfoTables["infoTable"][$infos["idInfo"]]["clmnName"]);
        }
        $this->_sessionArray["what"] = $this->_mysqldriver->setListForQuery($this->_what, "`");
    }

    private function _setDistance() { //create column with distance from search position
        if (isset($this->_sessionArray["eselect"]) and isset($this->_sessionArray["eselect"]["position"]))
            $this->distance = $this->_sessionArray["eselect"]["position"];
    }

    private function _setFrom() {
        $this->_from = $this->_mysqldriver->tblName(MAIN_DB, $this->_currentview);
    }

    private function _sampleSelect() {
        if (isset($this->_getArray["sselect"]))
            $this->_setFirst(True);
        if (!isset($this->_sessionArray["sselect"]) or $this->_sessionArray["sselect"] == "nosample")
            return;
        $tmp = array("premade" => array(), "idbased" => array(), "usersamples" => array());
        foreach ($this->_sessionArray["sselect"] as $val)
            $this->_fillSampleArray($tmp, $val);
        $this->_sessionArray["currentsample"] = $this->_combineSamples($tmp);
    }

    private function _fillSampleArray(&$current, $val) {
        $input = $this->_dataInfoTables["samplesTable"][$val];
        $type = $input["type"];
        $template = array($input["database"] => array($input["table"] => array($input["column"])));
        foreach ($template as $dbkey => $db) {
            if (!isset($current[$type][$dbkey]))
                $current[$type][$dbkey] = array();
            foreach ($db as $tbkey => $tb) {
                if (!isset($current[$type][$dbkey][$tbkey]))
                    $current[$type][$dbkey][$tbkey] = array();
                foreach ($tb as $cl) {
                    if (!isset($current[$type][$dbkey][$tbkey][$cl])) {
                        $current[$type][$dbkey][$tbkey][$cl] = array($input["sampleid"]);
                    } else {
                        array_push($current[$type][$dbkey][$tbkey][$cl], $input["sampleid"]);
                    }
                }
            }
        }
    }

    private function _setSampleQuery($keytype, $db, $table, $colum, $data) {
        if ($keytype == "idbased") {
            $sql_line = "`" . MAIN_ID . "` IN (SELECT DISTINCT(`" . $db . "`.`" . $table . "`.`" . MAIN_ID . "`)
							FROM `" . $db . "`.`" . $table . "`
							WHERE `" . $db . "`.`" . $table . "`.`" . $colum . "`
							IN ('" . implode("','", $data) . "'))";
        } elseif ($keytype == "premade") {
            $sql_line = "`" . $colum . "` IN ('" . implode("','", $data) . "')";
        } elseif ($keytype == "usersamples") {
            $newdata = implode(",", $this->_getUserSampleData($data));
            $sql_line = "`" . MAIN_ID . "` IN (" . $newdata . ")";
        }
        return $sql_line;
    }

    private function _getUserSampleData($data) {
        $result = array();
        foreach ($data as $sample) {
            $setres = $this->_mysqldriver->selectOneValue("sample", $this->_mysqldriver->tblName(MAIN_SAMPLES, "userSamples"), "`iduserSamples` = " . $sample);
            $chunks = explode(",", $setres);
            $result = $this->_crossArray($result, $chunks);
        }
        return $result;
    }

    private function _crossArray($oldarray, $newarray) {
        $diff = array_diff($newarray, $oldarray);
        $result = array_merge($oldarray, $diff);
        return $result;
    }

    private function _setWallImgQuery($set) {
        return $this->_setSampleQuery("idbased", MAIN_IMAGES, MAIN_pngIMAGES, "Set", $set);
    }

    private function _combineSamples($input) {
        $tmp = array();
        foreach ($input as $keytype => $type) {
            foreach ($type as $dbkey => $db) {
                foreach ($db as $tbkey => $tb) {
                    foreach ($tb as $clkey => $data) {
                        $sql_line = $this->_setSampleQuery($keytype, $dbkey, $tbkey, $clkey, $data);
                        array_push($tmp, $sql_line);
                    }
                }
            }
        }
        $result = implode(" AND ", $tmp);
        return $result;
    }

    private function _countRecords() {
        if ($this->_samequery) {
            $this->norecords = $this->_sessionArray["noResults"];
        } else {
            if (!($res = $this->_mysqldriver->fetchRow("SELECT COUNT(DISTINCT(`" . MAIN_ID . "`)) FROM `" . MAIN_DB . 
                    "`.`" . $this->_currentview . "` " . $this->_where . ";"))) {
                $this->norecords = 0;
            } else
                $this->norecords = $res[0];
        }
        $this->_sessionArray["noResults"] = $this->norecords;
    }

    public function addIpp($curripp) {
        $this->_sessionArray["ipp"] = $curripp;
    }

    public function getIpp($defaultipp) {
        if (!isset($this->_sessionArray["ipp"]))
            $this->_sessionArray["ipp"] = $defaultipp;
        return $this->_sessionArray["ipp"];
    }

    // ******* TO REDO ****************

    private function _combineIdArrays($old, $new, $id = MAIN_ID) {
        if ($new)
            foreach ($new as $val)
                if (!in_array($val[$id], $old))
                    array_push($old, $val[$id]);
        return $old;
    }

    private function _setShowSelectedSamples() {
        if ($this->showSelectedSamples)
            return;
        $samplesTable = groupArrayByField($this->_dataInfoTables["samplesTable"], "class");
        $temp = array();
        foreach ($samplesTable as $samples) {
            foreach ($samples as $sample) {
                if (isset($this->selection["sselect"]) and in_array($sample["Name"], $this->selection["sselect"])) {
                    if (!isset($temp[$sample["class"]]))
                        $temp[$sample["class"]] = $sample["class"] . ": ";
                    $temp[$sample["class"]] .= trim($sample["title"]) . ", ";
                }
            }
            if (isset($temp[$sample["class"]]))
                $temp[$sample["class"]] = trim($temp[$sample["class"]], ", ") . "</br>";
        }
        if (!empty($temp))
            foreach ($temp as $display)
                $this->showSelectedSamples .= $display;
    }

    public function createMainSqls($limit) {
        if (!$this->_from)
            $this->_setFrom();
        $what = $this->_mysqldriver->setListForQuery($this->_what, "`");
        if ($this->distance)
            $what = $this->distance . "," . $what;
        $this->sqlSearchFull = "SELECT $what FROM $this->_from $this->_where GROUP BY `" . MAIN_ID . "` $this->order";
        $this->sqlExtrasFull = "SELECT " . $this->_dataInfoTables["extraFields"] . " FROM $this->_from $this->_where GROUP BY `" . MAIN_ID . "` $this->order";
        $this->sqlSearchLimited = "SELECT $what FROM $this->_from $this->_where GROUP BY `" . MAIN_ID . "` $this->order $limit";
        $this->sqlExtrasLimited = "SELECT " . $this->_dataInfoTables["extraFields"] . " FROM $this->_from $this->_where GROUP BY `" . MAIN_ID . "` $this->order $limit";
        return;
    }

    public function getResults() {
        $this->result = $this->_mysqldriver->selectquery($this->sqlSearchLimited);
        $this->fullresult = makeOneColMain($this->_mysqldriver->selectquery($this->sqlSearchFull), MAIN_ID);
        $this->extraresults = makeOneColMain($this->_mysqldriver->selectquery($this->sqlExtrasLimited), MAIN_ID);
        return;
    }
    
    public function make3Dplot() {
        $idlist = implode(",", array_keys($this->fullresult));
        $datasql = "SELECT `D` as 'd',`Glon` as 'glon',`Glat` as 'glat' FROM `MainPNData`.`tmpdist` WHERE `idPNMain` IN (".$idlist.");";
        $data = json_encode($this->_mysqldriver->selectquery($datasql));
        //writeSpectraJson($data,$this->_linkImages . "plots/jsongpne".$this->_userId.".json",True);
        writeSpectraJson($data,$this->_linkImages . "plots/" . $this->_userId . "_jsongpne.json",True);
    }

}

?>
