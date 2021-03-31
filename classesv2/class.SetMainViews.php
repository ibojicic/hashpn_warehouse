<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/**
 * Description of MainViews
 *
 * @author ivan
 */
class SetMainViews extends SetMainObjects {

    private $_linkAtInfo;
    private $_dataInfoTables;
    private $_selection;
    private $_textsearch = False;
    private $_htmlconstructor;
    private $_orderby = False;
    private $_orderdir = False;
    private $_statuses = False;
    private $_sampleOrder;

    public function __construct($selection, $myConfig, $userid, $isAdmin, $textsearch) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_htmlconstructor = new HtmlConstructor();
        $this->_selection = $selection;
        $this->_linkAtInfo = $myConfig["mainlink"];
        $this->_sampleOrder = $myConfig["sampleSelectionOrder"];
        $this->_dataInfoTables = array(
            "infoTable" => $this->_readtables->readInfoTable(),
            "samplesTable" => $this->_readtables->readSamplesTable(),
            "pngimginfoTable" => $this->_readtables->readpngImagesInfoTable(),
            "searchFields" => $this->_readtables->createSearchFields(),
            "groupedimages" => $this->_readtables->createImageGroupArray(),
            "checksamples" => $this->_readtables->readCheckSamples(),
            "datainfotable" => $this->_readtables->readDataInfoTable(),
            "usersamples" => $this->_readtables->readUserSamples());

        $this->_textsearch = $textsearch;
    }

    public function getStatuses() {
        $this->_statuses = $this->_readtables->readStatuses();
        return $this->_statuses;
    }

    public function getInStatus() {
        $result = array();
        if (!$this->_statuses)
            $this->getStatuses();
        foreach ($this->_statuses as $statoption) {
            $this->_htmlconstructor->makeOptionArray($result, $statoption["statusId"], $statoption["statusTitle"], $statoption["statusId"] == "c");
        }
        return $this->_htmlconstructor->makeSelect($result, "instatus");
    }

    public function selectUserSample() {
        $result = array();
        $names = array_keys($this->_dataInfoTables["usersamples"]);
        foreach ($names as $name)
            $this->_htmlconstructor->makeOptionArray($result, $name);
        return $this->_htmlconstructor->makeSelect($result, "exsamples", False, "Select sample");
    }

    public function setOrderByDir($orderby, $orderdir) {
        $this->_orderby = $orderby;
        $this->_orderdir = $orderdir;
    }

    public function createSampleCheckBox() {
        $samplesTable = groupArrayByField($this->_dataInfoTables["samplesTable"], "class");
        $samplesTable = sortArrayByArray($samplesTable, $this->_sampleOrder);
        $tempsamplesTable = $samplesTable;
        foreach ($tempsamplesTable as $group => $samples) {
            foreach ($samples as $idsample => $sample) {
                $samplesTable[$group][$idsample]["checked"] = (isset($this->_selection["sselect"]) and in_array($sample["Name"], $this->_selection["sselect"])) ? "CHECKED" : "";
            }
        }

        return $this->_htmlconstructor->makeListCheckBox($samplesTable, "Name", "title", "accolist test1");
    }

    public function createCheckSampleCheckBox() {
        $headers = array("Select", "Name", "Description", "Date", "Delete");
        $values = array();
        if ($this->_dataInfoTables["checksamples"] and ! empty($this->_dataInfoTables["checksamples"])) {
            foreach ($this->_dataInfoTables["checksamples"] as $data) {
                $checked = "";
                $temprow = array(
                    "id" => "<input type='radio' name='chksmpl' value='" . $data["idcheckObjects"] . "' $checked>",
                    "Name" => $data["Name"],
                    "Description" => $data["Description"],
                    "Date" => $data["Date"],
                    "check" => "<input type='checkbox' name='delsmp_" . $data["idcheckObjects"] . "' value='" . $data["idcheckObjects"] . "'></input>"
                );
                array_push($values, $temprow);
            }
        }
        $newrow = array(
            "id" => "<input type='radio' name='chksmpl' value='currsample'>",
            "Name" => "<input type='text' name='name'></input>",
            "Description" => "<input type='text' name='descr'></input>",
            "Date" => date("Y-m-d H:i:s"),
            "check" => "Current Selection"
        );
        array_push($values, $newrow);

        return $this->_htmlconstructor->makeTable($values, $headers, False, $this->_cssclasses["samplecheckbox"]);
    }

    public function createScrollCheckBox() {
        $samplesTable = array();
        foreach ($this->_dataInfoTables["infoTable"] as $idInfo => $info) {
            if ($info["showInTable"] == "y") {
                $info["checked"] = in_array($idInfo, $this->_selection["fselect"]) ? "CHECKED" : "";
                array_push($samplesTable, $info);
            }
        }
        return $this->_htmlconstructor->makeListCheckBox(array("Show columns" => $samplesTable), "varVar", "varName");
    }

    public function createImageCheckBox($wall = False) {
        $samplesTable = groupArrayByField($this->_dataInfoTables["pngimginfoTable"], "wavelength");
        $tempsamplesTable = $samplesTable;
        foreach ($tempsamplesTable as $group => $samples) {
            foreach ($samples as $idsample => $sample) {
                if ($sample['showingallery'] == "y") {
                    $samplesTable[$group][$idsample]["checked"] = 
                            (isset($this->_selection["imselect"]) and in_array($sample["idpngImagesInfo"], $this->_selection["imselect"])) ? "CHECKED" : "";
                }
            }
        }
        if ($wall) {
            $box = $this->_htmlconstructor->makeListCheckBox($samplesTable, "name", "name", "accolist", False, "wallselect");
        } else
            $box = $this->_htmlconstructor->makeListCheckBox($samplesTable, "name", "name", "accolist");

        return $box;
    }

    public function createGroupedImageCheckBox() {
        $samplesTable = array();
        foreach ($this->_dataInfoTables["groupedimages"] as $group => $values) {
            $name = array();
            $samplesTable[$group] = array();
            foreach ($values as $val) {
                array_push($name, $val["name"]);
            }
            $samplesTable[$group]["group"] = $group;
            $samplesTable[$group]["display"] = implode("/", $name);
            $samplesTable[$group]["checked"] = in_array($group, $this->_selection["grimselect"]) ? "CHECKED" : "";
        }
        return $this->_htmlconstructor->makeListCheckBox(array("Grouped images" => $samplesTable), "group", "display", "accolist");
    }

    /**
     * 
     * @param array $fullfields array(array("columnname" => columnval)....)
     * @param string $tableID id of the table
     * @param string $tableClass class of the table
     * @param bool $showtextsearch show the column with the text serach
     * @param bool|string $orderby order by column 
     * @param bool|string $orderdir order dir (ASC/DESC)
     * @return string html table
     */
    public function tableViewResults($fullfields, $tableID = "MainTable", $tableClass = "table2", $showtextsearch = False) {

        $titles = $this->_setTitlesArray($fullfields[0]);
        $titlesarray = $this->_createOrderLinks($titles, $this->_callPage, "orderby");
        $thclassarray = ($this->_orderby and $this->_orderdir) ? $this->_setColumnClass($titles) : False;

        //if ($this->_textsearch and $showtextsearch) array_unshift ($titlesarray, "text found:"); TODO

        $valsarray = array();
        foreach ($fullfields as $fields) {
            $tempvalsarray = array();
            foreach ($fields as $key => $val) {
                if ($key != MAIN_ID) {
                    if ($key == $this->_linkAtInfo and isset($fields[MAIN_ID]))
                        $val = "<div style='white-space: nowrap; width:80px;'>" . $this->_htmlconstructor->setActionCell($fields[MAIN_ID], "actionclassextra3") . $this->_htmlconstructor->makeLink($this->_infoPage . "?id=" . $fields[MAIN_ID], $val, "_blank") . "</div>";
                    array_push($tempvalsarray, $val);
                }
            }
            //if ($this->_userId == "ivan") array_unshift($tempvalsarray, $this->_htmlconstructor->setActionCell($fields[MAIN_ID]));
            array_push($valsarray, $tempvalsarray);
        }
        return $this->_htmlconstructor->makeTable($valsarray, $titlesarray, $tableID, $tableClass, False, $thclassarray);
    }

    private function _setTitlesArray($firstrow) {
        unset($firstrow[MAIN_ID]);
        $keys = array_keys($firstrow);
        return $keys;
    }

    public function imageViewResults($fulfields, $extrafields, $type, $distancefield = False, $tableID = "MainTable", $tableclass = "table3") {
        if ($type == "group") {
            $tempchecked = $this->_selection["grimselect"];
            $titlesarray = $tempchecked;
        } else {
            $tempchecked = array();
            foreach ($this->_dataInfoTables["pngimginfoTable"] as $pngdata)
                if (in_array($pngdata["idpngImagesInfo"], $this->_selection["imselect"]))
                    $tempchecked[$pngdata["name"]] = $pngdata;
            $titlesarray = array_keys($tempchecked);
        }

        array_unshift($titlesarray, "DATA");
        if ($distancefield)
            array_unshift($titlesarray, "Distance");
        $valsarray = array();

        foreach ($fulfields as $fields) {
            $id = $fields[MAIN_ID];
            $tempvalsarray = array();
            if ($distancefield)
                array_push($tempvalsarray, $extrafields[$id][$distancefield] . " arcsec");
            array_push($tempvalsarray, $this->_htmlconstructor->makeInfoBox($id, $extrafields[$id], $this->_checkHLA($id), $this->_userId));
            $chckiphasrun = $this->_getIphasRun($id);
            foreach ($tempchecked as $group) {
                if ($type == "group") {
                    $flag = FALSE;
                    foreach ($this->_dataInfoTables["groupedimages"][$group] as $images) {
                        $imgname = $this->_linkImages . $id . $images["outFolder"] . $id . "_" . $images["name_out"];
                        if (is_file($imgname . ".png") and ! $flag) {
                            $flag = TRUE;
                            break;
                        }
                    }
                } else
                    $images = $group;
                $runadd = "";
                if (($images["name"] == "iphas3colour" or $images["name"] == "iquot_HaSr") and $chckiphasrun)
                    $runadd = "_r" . $chckiphasrun;
                $imgname = $this->_linkImages . $id . $images["outFolder"] . $id . $runadd . "_" . $images["name_out"];
                $imglink = (!is_file($imgname . ".png")) ? "<FONT COLOR='silver'>N/A</font>" : "<a href='" . $imgname . ".png' target='_blank'><img id='tabthumbs' src='" . $imgname . "_thumb.jpg'></a>";
                array_push($tempvalsarray, $imglink);
            }
            array_push($valsarray, $tempvalsarray);
        }
        return $this->_htmlconstructor->makeTable($valsarray, $titlesarray, $tableID, $tableclass);
    }
    
    

    public function imageWallResults($fulfields, $extrafields, $type, $distancefield = False, $tableID = "MainTable", $tableclass = "") {
        $images = False;
        foreach ($this->_dataInfoTables["pngimginfoTable"] as $pngdata) {
            if (isset($this->_selection["wallselect"]) and $pngdata["idpngImagesInfo"] == $this->_selection["wallselect"][0]) {
                $images = $pngdata;
                break;
            }
        }
        if (!$images)
            return False;
        $valsarray = array();
        foreach ($fulfields as $fields) {
            $id = $fields[MAIN_ID];
            $name = $fields["Name"];
            $chckiphasrun = $this->_getIphasRun($id);
            $runadd = "";
            if (($images["name"] == "iphas3colour" or $images["name"] == "iquot_HaSr") and $chckiphasrun)
                $runadd = "_r" . $chckiphasrun;
            $imgname = $this->_linkImages . $id . $images["outFolder"] . $id . $runadd . "_" . $images["name_out"];
            $imglink = (!is_file($imgname . ".png")) ? False : "<a href='" . $this->_infoPage . "?id=" . $id . "' target='_blank' title='" . $name . "'><img id='tabthumbs' src='" . $imgname . "_thumb.jpg'></a>";
            $valsarray[$id] = $imglink;
        }
        return $this->_htmlconstructor->makeDivsWall($valsarray, $this->_userId);
    }

    private function _createOrderLinks($harray, $hrefPage, $var) {
        $result = array();
        foreach ($harray as $ctitle) {
            $link = "<a href='" . $hrefPage . "?" . $var . "=" . $ctitle . "'>" . $ctitle . "</a>";
            array_push($result, $link);
        }
        return $result;
    }

    private function _setColumnClass($titles) {
        $result = array();
        foreach ($titles as $title) {
            $hclass = ($this->_orderby == $title) ? $this->_orderdir == "ASC" ? "sorting_asc" : "sorting_desc" : "";
            array_push($result, $hclass);
        }
        return $result;
    }

    public function createVarLists() { // create list of variables for searching (for info)
        $headers = array("name", "var", "units");
        $values = array();
        foreach ($this->_dataInfoTables["searchFields"][1] as $data) {
            if ($data["varSearch"]) {
                $tmpvararray = array(
                    "name" => $data["varName"],
                    "var" => $this->_htmlconstructor->makePushButton("selectxvar", "condsearch", $data["varVar"], $data["varVar"]),
                    "units" => $data["varUnits"]
                );
                array_push($values, $tmpvararray);
            }
        }
        return $this->_htmlconstructor->makeTable($values, $headers, False, "table1 table4");
    }

    /**
     * 
     * @return html string: in options for 'Text search..." box
     */
    public function textSearchOptions() {
        $options = array();
        $this->_htmlconstructor->makeOptionArray($options, "everywhere", "Everywhere");
        foreach ($this->_dataInfoTables["searchFields"][2] as $data) {
            if ($data["varSearch"])
                $this->_htmlconstructor->makeOptionArray($options, $data["varVar"], $data["varName"]);
        }
        return $this->_htmlconstructor->makeSelect($options, "textwhere");
    }

    private function _checkHLA($id) {
        return $this->_mysqldriver->select("`URL`", $this->_mysqldriver->tblName(MAIN_DB_DATA, "HLA_Data"), "`" . MAIN_ID . "` = $id AND `URL` IS NOT NULL");
    }



}

?>
