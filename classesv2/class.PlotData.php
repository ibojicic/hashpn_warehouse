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
class PlotData extends SetMainObjects {

    private $_plotconfig;
    private $_input;
    private $_datainfo;
    private $_data = array();
    private $_plotsData;
    private $_plotsLabels = array();
    public $selectedPlot = False;

    public function __construct($input, $myConfig, $userid, $isAdmin, $datainfo) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_htmlconstructor = new HtmlConstructor();

        $this->_datainfo = $datainfo["infoTable"];

        $this->_input = $input;

        $this->_getPlotsData();

        $this->_setCurrentPlot();

        //$this->_checkInput();

        if ($this->_parseInput())
            $this->_getPlot();
    }

    public function createColumnsSelect($name, $onchange) {
        $result = array();
        $vars = $this->_datainfo;
        $placeholder = "Add Var";
        foreach ($vars as $var) {
            $push = $var["varVar"] == "" ? "" : " " . $var["varVar"] . " ";
            $this->_htmlconstructor->makeOptionArray($result, $push, $var["varName"]);
        }
        return $this->_htmlconstructor->makeSelect($result, $name, $onchange, $placeholder);
    }

    private function _getPlotsData() {
        $sql_select = "SELECT * FROM `" . USERS_DB . "`.`userPlots`";
        $this->_plotsData = makeOneColMain($this->_mysqldriver->selectquery($sql_select), "plotLabel");
        foreach (array_keys($this->_plotsData) as $val)
            $this->_htmlconstructor->makeOptionArray($this->_plotsLabels, $val);
        return True;
    }

    private function _setCurrentPlot($selected = False) {
        if (!$selected) {
            $labels = array_keys($this->_plotsLabels);
            $this->selectedPlot = isset($this->_input["selectplot"]) ? $this->_input["selectplot"] : $labels[0];
        } else
            $this->selectedPlot = $selected;
        $this->_plotsLabels[$this->selectedPlot]["checked"] = True;
        return;
    }

    /**
     * Construct drop down list and info for select existing plot
     * @return html string
     */
    public function setSelectPlot() {
        $result = $this->_htmlconstructor->makeSelect($this->_plotsLabels, "selectplot", "this.form.submit()");
        $result .= $this->_htmlconstructor->specplotdatainfo(
                $this->_plotsData[$this->selectedPlot]["Xvar"], 
                $this->_plotsData[$this->selectedPlot]["Yvar"], 
                $this->_plotsData[$this->selectedPlot]["plotDesc"]);
        return $result;
    }

    private function _parseInput() {
        switch ($this->_input["selection"]) {
            case "createplot":
                if ($this->_checkInput()) {
                    $newPlot = $this->_parseNewPlot();
                    $this->_insertNewPlot($newPlot);
                    $this->_getPlotsData();
                    $this->_setCurrentPlot($newPlot["label"]);
                    return True;
                } else {
                    $errormessage = "Problem with one or more input values:<br> "
                            . "Plot label ='" . $this->_input["plotlabel"] . "' (plot label must be unique and must contain at least one character)<br>"
                            . "X variable ='" . $this->_input["xvar"] . "' (X variable must be a valid arithmetic expression containing 1 or more existing data sets) <br>"
                            . "Y variable ='" . $this->_input["yvar"] . "' (same as for X variable) <br>"
                            . "Please go <a href='plotDataPage.php'>go back</a> and correct the inputs.";
                    $this->_setRunResponse("error", $errormessage);
                    return False;
                }
            case "selectplot":
                return True;
            case "adddata":
                $this->_getPlot();
                $newsample[$this->_input["samplename"]] = array("sampwhere" => $this->_getCurrentSampleWhere(), "sampdesc" => $this->_input["sampledesc"]);
                $this->_addNewSample($newsample);
                return True;
            case "deleteplot":
                $this->_deletePlot();
                return True;
        }
        return True;
    }

    private function _checkInput() {
        $checkvars = array("plotlabel", "xvar", "yvar");
        foreach ($checkvars as $var) {
            if (!isset($this->_input[$var]) or trim($this->_input[$var]) == "")
                return False;
        }
        if (!$this->_sqlForCheck($this->_parseVariable($this->_input["yvar"])) or ! $this->_sqlForCheck($this->_parseVariable($this->_input["xvar"])))
            return False;
        if (!isset($this->_input["samplename"]) or trim($this->_input["samplename"]) == "")
            $this->_input["samplename"] = "user sample";
        if (!isset($this->_input["sampledesc"]) or trim($this->_input["sampledesc"]) == "")
            $this->_input["sampledesc"] = "na";
        return True;
    }

    private function _sqlForCheck($var) {
        $result = $this->_mysqldriver->select($var, $this->_mysqldriver->tblName(MAIN_DB, $this->_currentview), False, False, False, "0,1");
        return $result;
    }

    private function _parseNewPlot() {
        //print_r($this->_input);
        return array("Xvar" => $this->_parseVariable($this->_input["xvar"]), "Yvar" => $this->_parseVariable($this->_input["yvar"]), "label" => $this->_input["plotlabel"], "desc" => $this->_input["description"],
            "samplesWhere" => array($this->_input["samplename"] => array("sampwhere" => $this->_getCurrentSampleWhere(), "sampdesc" => $this->_input["sampledesc"])));
    }

    private function _dataSQL($X, $Y, $where) {
        $andwhere = ($where and trim($where) != "") ? "AND" : "WHERE";
        $sql = "SELECT " . $X . " as '0'," . $Y . " as '1', `" . MAIN_ID . "` as '2' FROM `" . MAIN_DB . "`.`" . $this->_currentview . "` " . $where . ";";
        //".$andwhere."".$X." IS NOT NULL AND ".$Y." IS NOT NULL ;";
        return $sql;
    }

    private function _insertNewPlot($newPlot) {
        $sql_insert = "INSERT INTO `" . USERS_DB . "`.`userPlots` (
						`plotLabel`, `plotDesc`, `samplesWhere`,
						`Xvar`,`Yvar`,
						`user`,`date`) VALUES
						('" . $newPlot["label"] . "','" . $newPlot["desc"] . "','" . mysql_escape_string(serialize($newPlot["samplesWhere"])) . "',
						'" . $newPlot["Xvar"] . "','" . $newPlot["Yvar"] . "',
						'" . $this->_userId . "',NOW());";
        return $this->_mysqldriver->query($sql_insert);
    }

    private function _addNewSample($newsample) {
        $oldsample = $this->_plotconfig["samplesWhere"];
        $merged = array_merge($oldsample, $newsample);
        $sql_update = "UPDATE `" . USERS_DB . "`.`userPlots` SET `samplesWhere` = '" . mysql_escape_string(serialize($merged)) . "'
						WHERE `plotLabel` = '" . $this->selectedPlot . "' AND (`user` = '" . $this->_userId . "' OR `user` = 'sys');";
        $tmpdata = $this->_mysqldriver->selectquery($sql_update);
        return;
    }

    private function _getPlot() {
        $tmpquery = "SELECT * FROM `" . USERS_DB . "`.`userPlots` WHERE `plotLabel` = '" . $this->selectedPlot . "' AND (`user` = '" . $this->_userId . "' OR `user` = 'sys');";
        $tmpdata = $this->_mysqldriver->selectquery($tmpquery);
        $this->_plotconfig = $tmpdata[0];
        $this->_plotconfig["samplesWhere"] = unserialize($tmpdata[0]["samplesWhere"]);
        return $this->_plotconfig;
    }

    private function _parseVariable($var) {
        foreach ($this->_datainfo as $info)
            if (stripos($var, $info["varVar"]) !== False)
                $var = str_replace($info["varVar"], $info["clmnName"], $var);
        return $var;
    }



    private function _getCurrentSampleWhere() {
        $restsql = "SELECT `fullWhere` FROM `" . USERS_DB . "`.`" . SESSIONS_TABLE . "`
				WHERE `id` = (SELECT MAX(a.`id`) FROM `" . USERS_DB . "`.`" . SESSIONS_TABLE . "` a
				WHERE a.`userName` = '" . $this->_userId . "')";
        $restdata = $this->_mysqldriver->selectquery($restsql);
        if (!$restdata)
            return False;
        return $restdata[0]["fullWhere"];
    }

    public function getData() {
        $this->_data["Xvar"] = $this->_plotconfig["Xvar"];
        $this->_data["Yvar"] = $this->_plotconfig["Yvar"];
        $this->_data["iduserPlots"] = $this->_plotconfig["iduserPlots"];
        $this->_data["plotLabel"] = $this->_plotconfig["plotLabel"];
        $this->_data["dataset"] = array();
        foreach ($this->_plotconfig["samplesWhere"] as $samplelabel => $sampledata) {
            $this->_data["dataset"][$samplelabel] = array(
                "label" => $samplelabel,
                "description" => $sampledata["sampdesc"],
                "user" => $this->_userId,
                "xaxis" => $this->_plotconfig["Xvar"],
                "yaxis" => $this->_plotconfig["Yvar"],
                "noPoints" => 0,
                "checked" => "checked",
                "data" => array(0, 0, "-"));
            $sql_data = $this->_dataSQL($this->_plotconfig["Xvar"], $this->_plotconfig["Yvar"], $sampledata["sampwhere"]);
            $dataset = $this->_mysqldriver->selectquery($sql_data);
            foreach ($dataset as $data)
                if ($data[0] != "" and $data[1] != "") {
                    $xdata = floatval($data[0]);
                    $ydata = floatval($data[1]);
                    $this->_checkMinMax($xdata, $ydata);
                    array_push($this->_data["dataset"][$samplelabel]["data"], array($xdata, $ydata, $data[2]));
                }
            $this->_data["dataset"][$samplelabel]["noPoints"] = count($this->_data["dataset"][$samplelabel]["data"]);
        }
        return $this->_data;
    }

    private function _checkMinMax($xdata, $ydata) {
        if (!isset($this->_data["minX"])) {
            $this->_data["minX"] = $xdata;
            $this->_data["maxX"] = $xdata;
        } else {
            if ($this->_data["minX"] > $xdata) {
                $this->_data["minX"] = $xdata;
            } elseif ($this->_data["maxX"] < $xdata) {
                $this->_data["maxX"] = $xdata;
            }
        }

        if (!isset($this->_data["minY"])) {
            $this->_data["minY"] = $ydata;
            $this->_data["maxY"] = $ydata;
        } else {
            if ($this->_data["minY"] > $ydata) {
                $this->_data["minY"] = $ydata;
            } elseif ($this->_data["maxY"] < $ydata) {
                $this->_data["maxY"] = $ydata;
            }
        }
    }

    private function _setPlotFileName() {
        return "dataplot_" . $this->_userId . ".plt";
    }

    public function createPlot() {
        $line = "";
        $plot = False;
        $file = $this->_setPlotFileName();
        if (!empty($this->_data["dataset"])) {
            @$spline = json_encode($this->_data["dataset"]);
            $filename = writeSpectraJson($spline, $this->_linkImages . "plots/" . $file, True);
            $range = $this->_calcRange();
            $line .= "var file1 = '" . $filename . "';\n";
            $line .= "var plch = 'sp_placeholder';\n";
            $line .= "var tltp = 'tooltip';\n";
            $line .= "var chCnr = 'plotchoices';\n";
            $line .= "var minX = '" . $range["minX"] . "';\n";
            $line .= "var maxX = '" . $range["maxX"] . "';\n";
            $line .= "var minY = '" . $range["minY"] . "';\n";
            $line .= "var maxY = '" . $range["maxY"] . "';\n";

            $flag['relative'] = True;
        }

        if (!empty($flag) and $line != "") {
            $plot = array(
                "plots" => "<script type='text/javascript' >$line</script><script type='text/javascript' src='javascript/dataPlot.js'></script>\n",
                "flags" => $flag);
        }

        return $plot;
    }
    
    public function create3Dplot() {
        
    }

    private function _calcRange() {
        return array(
            "minX" => $this->_data["minX"] - ($this->_data["maxX"] - $this->_data["minX"]) / 50,
            "maxX" => $this->_data["maxX"] + ($this->_data["maxX"] - $this->_data["minX"]) / 10,
            "minY" => $this->_data["minY"] - ($this->_data["maxY"] - $this->_data["minY"]) / 50,
            "maxY" => $this->_data["maxY"] + ($this->_data["maxY"] - $this->_data["minY"]) / 10
        );
    }

    private function _deletePlot() {
        $tmpquery = "DELETE FROM `" . USERS_DB . "`.`userPlots` WHERE `iduserPlots` = " . $this->_input["selectdelete"] . " AND `user` = '" . $this->_userId . "';";
        $this->_mysqldriver->query($tmpquery);
        $this->_getPlotsData();
        $this->_setCurrentPlot();
    }

}

?>
