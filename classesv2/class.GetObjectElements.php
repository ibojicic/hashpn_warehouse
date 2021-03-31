<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/**
 * Description of GetObjectElements
 *
 * @author ivan
 */
class GetObjectElements extends SetMainObjects {

    private $_dataInfoTables;
    private $_gendataexcl;
    private $_linkrgbcubes;
    private $_fixedVals;
    private $_inputresults;
    private $_tempdiamcheck = False;
    private $_htmlconstructor;
    private $_donotshow;
    private $_owner;
    private $_fitsims = False;
    private $_pngims = False;
    

    public function __construct($myConfig, $objid, $userid, $isAdmin, $inputresults, $referer = False) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_htmlconstructor = new HtmlConstructor();

        $this->_dataInfoTables = array(
            "infoTable"         => $this->_readtables->readInfoTable(),
            "infoTableNames"    => $this->_readtables->readInfoTableNames(),
            "infocolumns"       => $this->_readtables->readColumnsFromInfoTable(),
            "samplesTable"      => $this->_readtables->readSamplesTable(),
            "imagesetsarray"    => $this->_readtables->createImageSetsArray(),
            "userdata"          => $this->_readtables->readUserData($this->_userId),
            "dinfoTable"        => $this->_readtables->readDataInfoTable("`full` = 'y'"));
        
        if ($referer)
            $this->_referer = $referer; //if provided override actual referer page (for ex. objects check page)

        $this->_inputresults = $inputresults;

        $this->_gendataexcl = $myConfig["genDataExcl"];

        $this->_donotshow = $myConfig["donotshow"];

        $this->_linkrgbcubes = $myConfig["rgbcubes"];

        $this->_fixedVals = $myConfig["fixedVals"];

        $this->_owner = ($this->_userId == $inputresults["userRecord"]);

        $this->_setObjectId($objid);

        $this->_setObjectpngPath($objid);

        $this->_setObjectfitsPath($objid);
        
        $this->_pngims = $this->_pngImages($objid);

        $this->_fitsims = $this->_fitsImages($objid);
    }

    public function createGenInfoTables() { // gen/editable data table
        $result = array("maindata" => "", "gendata" => "", "fullinfo" => "");
        $background = "oddf";
        foreach ($this->_dataInfoTables["infocolumns"] as $table => $columns) {
            if ($table != MAIN_TABLE) {
                $inusedata = empty($this->_inputresults[$table]['inuse']) ? array() : $this->_inputresults[$table]["inuse"];
                $notinusedata = empty($this->_inputresults[$table]["notinuse"]) ? array() : $this->_inputresults[$table]["notinuse"];
                $dummy = (empty($this->_inputresults[$table]["dummy"])) ? array() : array(0 => $this->_inputresults[$table]["dummy"]);
                $fulltable = array_merge($inusedata, $notinusedata, $dummy);
                if (!empty($fulltable) and ! in_array($table, $this->_gendataexcl)) {
                    $result["gendata"] .= $this->_createFullTables_table($fulltable, $table, "full_" . $table, $this->_cssclasses["fullinfotable"], $background);
                    $background = $background == "oddf" ? "evenf" : "oddf";
                }
            } elseif ($table == MAIN_TABLE) {
                $fulltable = $this->_inputresults[$table]["inuse"];
                if (!empty($fulltable))
                    $result["maindata"] .= $this->_createBasicTables_table($fulltable, "basicedit", $this->_cssclasses["fullinfotable"]);
            }
        }
        $result["fulldata"] = $this->_createFullInfoTables();
        return $result;
    }
    
    private function _createFullInfoTables() {
        $result = "";
        $background = "oddf";

        foreach ($this->_dataInfoTables["dinfoTable"] as $dinfo) {
            $data = $this->_mysqldriver->select("*","`MainPNData`.`".$dinfo['Name']."`","`idPNMain` = ".$this->_objectId);
            if (!$data) {
                return false;
            }
            $tmpdata = $data;
            foreach ($tmpdata as $tk => $td) {
                unset($data[$tk]["id".$dinfo["Name"]],$data[$tk]["recno"],$data[$tk]["PNMainDist"],$data[$tk]["mapFlag"],$data[$tk]["idPNMain"]);
            }
            if ($data and !empty($data)) {
                $title = "<p style='display:inline;cursor:help' title='".$dinfo["CatTitle"]."(".$dinfo["TabTitle"].")'>".$dinfo["Name"]."</p>";
                $table = $this->_htmlconstructor->makeGeneralDataTable( array_keys($data[0]), $data, "test", 
                        $this->_cssclasses["fullinfotable"], False, $title , False, False, $background);
                $background = $background == "oddf" ? "evenf" : "oddf";
                $result .= $table;
            }
            
        }
        return $result;
    }

    private function _grantAccesstoTable($table) {
        if ($this->_isAdmin)
            return True;
        /*
        if (in_array($table, $this->_dataInfoTables["userdata"]["specAccess"]))
            return True;
         *  TODO SPECIAL ACCESS
         */
        return False;
    }

//    private function _grantAccesstoObject() {
//        if ($this->_isAdmin)
//            return True;
//        if (in_array($table, $this->_dataInfoTables["userdata"]["specAccess"]))
//            return True;
//        return False;
//    }

    private function _createFullTables_table($darray, $table, $tableID, $tableClass, $background) {
        /* check if table is bandmapped */
        $tmptabledata = groupArrayByField($this->_dataInfoTables["infoTable"], "varTable");
        $tabledata = $tmptabledata[$table];
        $bandmapped = $tabledata[0]["bandMapped"] == "y" ? True : False;

        $values = array();
        $trclass = array();

        $editAccess = ($this->_isAdmin or $this->_grantAccesstoTable($table));
        $editablerows = $this->_unsetFullTables_columns($darray[0], $table);
        $addbutton = $editAccess ? $this->_createFullTables_edit($table, $editablerows, $tabledata, $bandmapped) : "";

        // prepare headers
        $headers = array_keys($editablerows);
        if ($editAccess)
            array_push($headers, "inUse");
        if ($this->_owner or $this->_isAdmin)
            array_push($headers, "delete");

        $k = 0;
        if ($darray[0]["InUse"] != -1) { //check if it"s a dummy
            foreach ($darray as $tarray) {
                $band = $bandmapped ? $tarray["band"] : False;
                $k++;
                if ($tarray["InUse"] == 0) {
                    if ($editAccess) {
                        $inuse = $this->_createFullTables_chinusedel($table, $tarray["id" . $table], "chinuse", $k, "Set inuse", $band);
                    } else
                        $inuse = "";
                    $backcol = "";
                }
                elseif ($tarray["InUse"] == 1) {
                    if ($editAccess) {
                        $inuse = $this->_createFullTables_chinusedel($table, $tarray["id" . $table], "unsetinuse", $k, "Unset inuse", $band);
                    } else
                        $inuse = "";
                    $backcol = "class='inuse'";
                }
                $delete = "na";
                if ($this->_userId == $tarray["userRecord"] or $this->_isAdmin)
                    $delete = $this->_createFullTables_chinusedel($table, $tarray["id" . $table], "delrec", $k, "Delete record", $band);
                if ($this->_isAdmin)
                    $delete = $this->_createFullTables_chinusedel($table, $tarray["id" . $table], "delrec", $k, "Delete record", $band);
                $tmprow = $this->_unsetFullTables_columns($tarray, $table);
                $tmprow = $this->_setRefLink($tmprow);
                if ($editAccess)
                    array_push($tmprow, $inuse);
                if ($this->_owner or $this->_isAdmin)
                    array_push($tmprow, $delete);
                array_push($values, $tmprow);
                array_push($trclass, $backcol);
            }
        }
        $result = $this->_htmlconstructor->makeGeneralDataTable($headers, $values, $tableID, $tableClass, $trclass, $this->_dataInfoTables["infoTableNames"][$table], $addbutton, $k > 10, $background);
        return $result;
    }

    /**
     * 
     * @param array $darray: input array
     * @param string $tableID
     * @param string $tableClass
     * @return string: html formated "Basic Table"
     */
    private function _createBasicTables_table($darray, $tableID, $tableClass) {

        $domain = [];
        $PNstat = [];

        foreach ($this->_dataInfoTables["samplesTable"] as $samples) {
            if ($samples["column"] == "domain" or $samples["column"] == "PNstat") {
                $this->_htmlconstructor->makeOptionArray($$samples["column"], $samples["sampleid"], $samples["title"], $darray[$samples["column"]] == $samples["sampleid"]);
            }
        }

        $domain = $this->_htmlconstructor->makeSelect($domain, "domain");
        $PNstat = $this->_htmlconstructor->makeSelect($PNstat, "PNstat");

        $result = "<table id='" . $tableID . "' class='" . $tableClass . "'  style='width:auto'>\n";

        $result .= "<thead><th></th><th colspan='2'>Data</th><th>Reference</th><th>Edit</th></thead>";

        $result .= "<tbody>\n";

        $result .= "<tr>
                        <th>RA/DEC (J2000)</th>
                            <td>" . $darray["RAJ2000"] . "</td>
                            <td>" . $darray["DECJ2000"] . "</td>
                            <td rowspan ='3'>" . $darray["refCoord"] . "</td>
                            <td>" . $this->_createBasicTables_edit("RADEC", array("RAJ2000" => $darray["RAJ2000"],
                    "DECJ2000" => $darray["DECJ2000"]), "refCoord") . "</td>
		</tr>\n";

        $result .= "<tr>
			<th>dRA/dDEC (J2000)</th>
                            <td>" . $darray["DRAJ2000"] . "</td>
                            <td>" . $darray["DDECJ2000"] . "</td>
                            <td>" . $this->_createBasicTables_edit("dRAdDEC", array("DRAJ2000" => $darray["DRAJ2000"],
                    "DDECJ2000" => $darray["DDECJ2000"]), "refCoord") . "</td>
                    </tr>\n";

        $result .= "<tr>
                        <th>Glon/Glat</th>
                            <td>" . $darray["Glon"] . "</td>
                            <td>" . $darray["Glat"] . "</td>
                            <td>" . $this->_createBasicTables_edit("Gal", array("Glon" => $darray["Glon"],
                    "Glat" => $darray["Glat"]), "refCoord") . "</td>
                    </tr>\n";

        $result .= "<tr>
			<th>" . MAIN_DESIGNATION . "</th>
                            <td colspan='2'>" . $darray[MAIN_DESIGNATION] . "</td>
                            <td>" . $darray["refPNG"] . "</td>
                            <td>" . $this->_createBasicTables_edit(MAIN_DESIGNATION, array(MAIN_DESIGNATION => $darray[MAIN_DESIGNATION]), "ref" . MAIN_DESIGNATION) . "</td>
                    </tr>\n";

        $result .= "<tr>
                        <th>Catalogue</th>
                            <td colspan='2'>" . $this->_adsLink($darray["Catalogue"]) . "</td>
                            <td>" . $darray["refCatalogue"] . "</td>
                            <td>" . $this->_createBasicTables_edit("Catalogue", array("Catalogue" => $darray["Catalogue"]), "refCatalogue") . "</td>
                    </tr>\n";

        $result .= "<tr><form id='pnstat' action='" . $this->_referer . "?id=" . $this->_objectId . "' method = 'POST'>
			<th>PN status</th>
                            <td colspan='2'>" . $PNstat . "</td>
                            <td>" . $darray["refPNstat"] . "</td>
                            <td><button id='editdata' value='Submit'>Submit</button>
                            <input type='hidden' name='id' value='" . $this->_objectId . "'>
                            <input type='hidden' name='table' value='" . MAIN_TABLE . "'>
                            <input type='hidden' name='userRecord' value='$this->_userId'>
                            <input type='hidden' name='ref' value='refPNstat'>
                            <input type='hidden' name='editrec' value='y'>
			</form></tr>\n";

        $result .= "<tr><form id ='domain' action='" . $this->_referer . "?id=" . $this->_objectId . "' method = 'POST'>
                        <th>Domain</th>
                            <td colspan='2'>" . $domain . "</td>
                            <td>" . $darray["refDomain"] . "</td>
                            <td><button id='editdata' value='Submit'>Submit</button>
                            <input type='hidden' name='id' value='" . $this->_objectId . "'>
                            <input type='hidden' name='table' value='" . MAIN_TABLE . "'>
                            <input type='hidden' name='userRecord' value='$this->_userId'>
                            <input type='hidden' name='ref' value='refdomain'>
                            <input type='hidden' name='editrec' value='y'>
                        </form></tr>\n";

        $result .= "<tr><th>Simbad ID</th>
                            <td colspan='2'>" . $darray["SimbadID"] . "</td>
                            <td>" . $darray["refSimbadID"] . "</td>
                            <td>" . $this->_createBasicTables_edit("SimbadID", array("SimbadID" => $darray["SimbadID"]), "refSimbadID") . "</td>
                    </tr>\n";

        $result .= "<tr><th>Redo Images</th>
                            <td colspan='2'>" . $this->_createBasicTables_redoim("downloadall") . "</td>
                            <td>" . $this->_createBasicTables_redoim("download") . "</td>
                            <td>" . $this->_createBasicTables_redoim("redo") . "</td>
                    </tr>\n";

        $result .= "</tbody>\n</table>\n";
        return $result;
    }

    public function setDefaultLinesForm() {

        $form = "<form id ='emmlines' action='" . $this->_referer . "?id=" . $this->_objectId . "' method = 'POST'>
                    <div id='linelabels'>
                        <table id='choiceLabels' class='" . $this->_cssclasses["emmlines"] . "'>
                            <tr>
                                <th></th><th>id</th><th>&lambda;</th>
                            </tr>
                        </table>
                    </div>
                    <div id='linesave'>
                        <button value='Submit'>Make Default</button>
                        <input type='hidden' name='userRecord' value='$this->_userId'>
			<input type='hidden' name='linesave' value='y'>
                    </div>
		</form>";
        return $form;
    }

    private function _unsetFullTables_columns($row, $table) {
        $unsetarray = $this->_donotshow;
        array_push($unsetarray, "id" . $table);
        foreach ($unsetarray as $unset)
            try {
                unset($row[$unset]);
            } catch (Exception $e) {
                
            }
        return $row;
    }

    private function _setRefLink($table) {
        $tmptable = $table;
        foreach ($table as $key => $val)
            if ($key == "reference" and $val != "")
                $tmptable[$key] = $this->_adsLink($val);
        return $tmptable;
    }

    // ******************************************************
    // *********** POPUP BOXES ******************************
    // ******************************************************

    private function _createFullTables_edit($table, $data, $tabledata, $bandmapped) {

        if ($bandmapped) {
            $data = $this->_prepareBandMapped($tabledata);
        } else {
            unset($data["userRecord"]);
            //clear values from data arrau
            $this->_eraseAval($data);
        }


        $extrakeys = array(
            "id" => $this->_objectId,
            "table" => $table,
            "userRecord" => $this->_userId,
            "addrec" => "y"
        );

        $datainputs = $this->_makeInputFields($data, $table, "text", "table", $bandmapped);
        $datatable = $this->_htmlconstructor->makeTable($datainputs, FALSE, FALSE, $this->_cssclasses["fulltablesedit"]);
        $setinuse = $this->_htmlconstructor->makeInputLine($this->_setInUseFlag(), "other");
        $hiddenkeys = $this->_makeInputFields($extrakeys, False, "hidden", "other");
        $hiddendata = implode("\n", $hiddenkeys);

        $finalinput = $datatable . $setinuse . $hiddendata;

        $button = array("id" => "editdata", "value" => "inp_" . $table, "message" => "Add record");
        $result = $this->_htmlconstructor->makePopupForm("forinpdialog", $this->_referer . "?id=" . $this->_objectId, "POST", $finalinput, $button, "inp_" . $table, "inpdialog", $table, "Add new record...");

        return $result;
    }

    private function _prepareBandMapped($data) {
        $columns = array();
        $bands = array();
        $tmpbands = array();
        foreach ($data as $dataset) {
            if (!array_key_exists($dataset["varColumn"], $columns))
                $columns[$dataset["varColumn"]] = "";
            if (!in_array($dataset["band"], $tmpbands)) {
                array_push($tmpbands, $dataset["band"]);
                $this->_htmlconstructor->makeOptionArray($bands, $dataset["band"]);
            }
        }
        $columns = array_merge($columns, array("bands" => $bands, "reference" => "", "comments" => ""));
        return $columns;
    }

    private function _eraseAval(&$myarr) {
        $myarr = array_map(array($this, "_returnVal"), $myarr);
        //$this->_classarray = array_map(array($this, "dash"), $myarr);
    }

    private function _returnVal($val) {
        return "";
    }

    private function _createSpectraLinks_adddel($action, $iddata, $tabledata = False) {
        if ($action == "del") {
            $extrakeys = array(
                "id" => $this->_objectId,
                "iddata" => $iddata,
                "user" => $this->_userId,
                "deletesplink" => "y"
            );
            $fields = $this->_makeInputFields($extrakeys, False, "hidden", "other");
            $actiondata = implode("\n", $fields);

            $message = "You are about to delete a record. Please confirm...";
            $shortmessage = "Delete record";
            $formname = "adddelsplink_" . $iddata;
            $divid = "adddelsplink" . $iddata;
        } elseif ($action == "add") {

            $fields = $this->_makeInputFields($tabledata, False, "text", "table");
            $actiondata = $this->_htmlconstructor->makeTable($fields, False, False, $this->_cssclasses["basictablesedit"]);
            $extrakeys = array(
                "id" => $this->_objectId,
                "user" => $this->_userId,
                "addsplink" => "y"
            );
            $fields = $this->_makeInputFields($extrakeys, False, "hidden", "other");
            $actiondata = $actiondata . implode("\n", $fields);
            $message = "You are about to add a record. Please confirm...";
            $shortmessage = "Add record";
            $formname = "adddelsplink_form";
            $divid = "adddelsplinkform";
        }



        $button = array("id" => "editdata", "value" => $divid, "message" => $shortmessage);

        $result = $this->_htmlconstructor->makePopupForm($formname, $this->_referer . "?id=" . $this->_objectId, "POST", $actiondata, $button, $divid, "inpdialog", "", $message);

        return $result;
    }

    private function _createFullTables_chinusedel($table, $idTable, $chinusedel, $n, $buttontitle, $band = False) {

        $extrakeys = array(
            "id" => $this->_objectId,
            "table" => $table,
            "userRecord" => $this->_userId,
            "idtable" => $idTable,
            $chinusedel => "y"
        );

        if ($band)
            $extrakeys["band"] = $band;

        $hiddenkeys = $this->_makeInputFields($extrakeys, False, "hidden", "other");
        $hiddendata = implode("\n", $hiddenkeys);

        if ($chinusedel == "chinuse") {
            $message = "You are about to change the record in use. Please confirm...";
        } elseif ($chinusedel == "unsetinuse") {
            $message = "You are about to unset the record in use. Please confirm...";
        } else {
            $message = "You are about to delete a record. Please confirm...";
        }

        $button = array("id" => "editdata", "value" => $chinusedel . "_" . $table . $n, "message" => $buttontitle);

        $result = $this->_htmlconstructor->makePopupForm("chinusedeldialog" . $n, $this->_referer . "?id=" . $this->_objectId, "POST", $hiddendata, $button, $chinusedel . "_" . $table . $n, "inpdialog", $table, $message);

        return $result;
    }

    private function _createBasicTables_edit($row, $data, $ref) {
        $extrakeys = array(
            "id" => $this->_objectId,
            "table" => MAIN_TABLE,
            "userRecord" => $this->_userId,
            "ref" => $ref,
            "editrec" => "y"
        );

        $hiddenkeys = $this->_makeInputFields($extrakeys, False, "hidden", "other");
        $hiddendata = implode("\n", $hiddenkeys);

        $datainputs = $this->_makeInputFields($data, False, "text", "table");
        $datatable = $this->_htmlconstructor->makeTable($datainputs, False, False, $this->_cssclasses["basictablesedit"]);

        $button = array("id" => "editdata", "value" => "inp_" . $row, "message" => "Edit");

        $result = $this->_htmlconstructor->makePopupForm("forinpdialog", $this->_referer . "?id=" . $this->_objectId, "POST", $datatable . $hiddendata, $button, "inp_" . $row, "inpdialog", "Basic Data", "Input new data...");

        return $result;
    }

    private function _createBasicTables_redoim($type) {
        $extrakeys = array(
            "id" => $this->_objectId,
            "table" => MAIN_TABLE,
            "userRecord" => $this->_userId,
            "redoim" => $type
        );

        $hiddenkeys = $this->_makeInputFields($extrakeys, False, "hidden", "other");
        $hiddendata = implode("\n", $hiddenkeys);

        if ($type == "downloadall") {
            $bcaption = "Re-Download + Redo";
            $message = "You are about to submit a cron job for re-downloading and re-doing all images for this object. Plaease confirm...";
        } elseif ($type == "download") {
            $bcaption = "Download + Redo";
            $message = "You are about to submit a cron job for downloading and re-doing all images for this object. Plaease confirm...";
        } elseif ($type == "redo") {
            $bcaption = "Redo";
            $message = "You are about to submit a cron job for re-doing all images for this object. Plaease confirm...";
        }

        $button = array("id" => "editdata", "value" => "inp_" . $type, "message" => $bcaption);

        $result = $this->_htmlconstructor->makePopupForm("forinpdialog", $this->_referer . "?id=" . $this->_objectId, "POST", $hiddendata, $button, "inp_" . $type, "inpdialog", "Redo/Redownload Images", $message);

        return $result;
    }

    private function _createNotes_addeditdel($action, $note = False, $noteid = False, $owner = False, $public = "n") {
        if ($action == "add") {
            $note = "";
            $noteid = "";
            $input = "<textarea cols='38' rows='5' name='comment'>" . $note . "</textarea>";
            $input .= "<fieldset><ul>";
            $input .= "<li><input type='radio' name='public' value='y'>Public</li>";
            $input .= "<li><input type='radio' name='public' value='n' checked>Private</li>";
            $input .= "</ul></fieldset>";

            $premessage = "";
            $button = "Add Note";
            $notedivid = $action . "_note";
        } elseif ($action == "edit" and $note and $noteid) {
            $pubchecked = $public == "y" ? "checked" : "";
            $privchecked = $public == "n" ? "checked" : "";

            if (!$owner)
                return "<button disabled>Edit Note</button>";
            $input = "<textarea cols='38' rows='5' name='comment'>" . $note . "</textarea>";
            $input .= "<fieldset><ul>";
            $input .= "<li><input type='radio' name='public' value='y' $pubchecked>Public</li>";
            $input .= "<li><input type='radio' name='public' value='n' $privchecked>Private</li>";
            $input .= "</ul></fieldset>";
            $premessage = "";
            $button = "Edit Note";
            $notedivid = $action . "_note" . $noteid;
        }
        elseif ($action == "del" and $noteid and $note) {
            if (!$owner)
                return "<button disabled>Delete Note</button>";
            $input = "";
            $premessage = "You are about to delete note:<br><font color='red'>" . $note . "</font><br>Please confirm...";
            $button = "Delete Note";
            $notedivid = $action . "_note" . $noteid;
        } else
            return "";

        $extrakeys = array(
            "id" => $this->_objectId,
            "table" => "tbUsrComm",
            "user" => $this->_userId,
            "noteid" => $noteid,
            "addcoment" => $action
        );

        $hiddenkeys = $this->_makeInputFields($extrakeys, False, "hidden", "other");
        $hiddendata = implode("\n", $hiddenkeys);


        $button = array("id" => "editdata", "value" => $notedivid, "message" => $button);

        $result = $this->_htmlconstructor->makePopupForm("forinpdialog", $this->_referer . "?id=" . $this->_objectId, "POST", $input . $hiddendata, $button, $notedivid, "inpdialog", "Add Note", $premessage);

        return $result;
    }

    private function _makeInputFields($data, $table, $fieldtype = "text", $type = "other", $bandmapped = False) {
        $result = array();


        foreach ($data as $key => $val) {
            if ($table and isset($this->_fixedVals[$table][$key])) {
                $k = 0;
                $temparray = array();
                $checkradio = $this->_fixedVals[$table][$key]["multiple"] ? "checkbox" : "radio";

                foreach ($this->_fixedVals[$table][$key]["values"] as $fkey => $fval) {
                    $keyname = $checkradio == "radio" ? $key : $key . "__" . ($k++);
                    $fieldarray = array(
                        "label" => $fval,
                        "name" => $keyname,
                        "type" => $checkradio,
                        "value" => $fkey
                    );
                    $tempres = $this->_htmlconstructor->makeInputLine($fieldarray, $type, True);
                    array_push($result, $tempres);
                }
            } elseif ($bandmapped and $key == "bands") {
                $tempres = array("label" => "band", "field" => $this->_htmlconstructor->makeSelect($val, "band"));
                array_push($result, $tempres);
            } else {
                $label = $fieldtype == "hidden" ? "" : $key;
                $temparray = array(
                    "label" => $label,
                    "name" => $key,
                    "type" => $fieldtype,
                    "value" => $val
                );
                $tempres = $this->_htmlconstructor->makeInputLine($temparray, $type);
                array_push($result, $tempres);
            }
        }

        return $result;
    }

    private function _setInUseFlag() {
        return array("label" => "Set inuse:", "name" => "InUse", "type" => "checkbox", "value" => 1);
    }

    // ******************************************************
    // *********** END POPUP BOXES **************************
    // ******************************************************


    public function createUserComments($userComments, $tableclass = "table1") {
        $headers = array("User", "Comment", "Public", "Date", "Edit", "Delete");
        $values = array();
        $k = 0;
        foreach ($userComments as $comments) {
            $k++;
            $owner = ($this->_userId == $comments["user"] or $this->_isAdmin) ? True : False;

            if ($owner or $comments["user"] == "sys" or $comments["public"] == "y") {
                $temp = array(
                    $comments["user"],
                    $comments["comment"],
                    $comments["public"],
                    $comments["date"],
                    $this->_createNotes_addeditdel("edit", $comments["comment"], $comments["idtbUsrComm"], $owner, $comments["public"]),
                    $this->_createNotes_addeditdel("del", $comments["comment"], $comments["idtbUsrComm"], $owner)
                );
                array_push($values, $temp);
            }
        }
        $button = $this->_createNotes_addeditdel("add");

        //$result = $this->_htmlconstructor->makeTable($values, $headers, False, $tableclass) . $button;
        $result = $this->_htmlconstructor->makeGeneralDataTable($headers, $values, "usernotes", $tableclass, False, "User Notes/Comments", $button, $k > 10);

        return $result;
    }

    public function createSpectraLinksTable($links, $tableclass = "table3") {
        $headers = array("Reference", "Author", "Title", "Journal", "Year", "elcatCode", "comments", "user", "del");
        $inputfields = array("reference" => "ADS Bibcode", "comments" => "");

        $values = array();
        if (!empty($links)) {
            foreach ($links as $key => $link) {
                $temp = array(
                    "<a href='http://adsabs.harvard.edu/abs/" . $link["reference"] . "' target='_blank'>" . $link["reference"] . "</a>",
                    $link["Author"],
                    $link["Title"],
                    $link["Journal"],
                    $link["Year"],
                    $link["elcatCode"],
                    $link["comments"],
                    $link["user"],
                    $this->_createSpectraLinks_adddel("del", $link["idspectraLinks"])
                );
                array_push($values, $temp);
            }
        }
//        $addbutton = $this->_createSpectraLinks_adddel("add", $link["idspectraLinks"], $inputfields);
        $addbutton = $this->_createSpectraLinks_adddel("add", "", $inputfields);

        $result = $this->_htmlconstructor->makeTable($values, $headers, False, $tableclass) . $addbutton;
        return $result;
    }

    public function createObjLinks() {
        $res = $this->_inputresults[MAIN_TABLE]["inuse"];
        $diam = $this->_inputresults["tbAngDiam"]["inuse"][0]["MajDiam"];

        $vizsimbLinks = $this->_htmlconstructor->composeVizier($res["RAJ2000"], $res["DECJ2000"], $res["SimbadID"], $res["DRAJ2000"], $res["DDECJ2000"],120,$res["PNstat"] == "T");
        $values = array();
        $tmpvalues = array(
            "<a href='" . $vizsimbLinks["simbad"] . "' target='_blank'><img src='images/simbad_70x35.png' /></a>",
            "<a href='" . $vizsimbLinks["vizier"] . "' target='_blank'><img src='images/vizier_40x35.png' /></a>");
        if ($this->_checkHLA($this->_objectId)) {
            array_push($tmpvalues, "<a href='" . $vizsimbLinks["HLA"] . "' target='_blank'><img src='images/newHLA_logo.gif' /></a>");
        } else {
            array_push($tmpvalues, "<img src='images/nonewHLA_logo.gif'/>");
        }
        array_push($values, $tmpvalues);

        $tmpvalues = array();
        if ($pniclink = $this->_checkPNIC($this->_objectId)) {
            array_push($tmpvalues, "<a href='" . $pniclink . "' target='_blank'><img src='images/pnic_ok.png' /></a>");
        } else {
            array_push($tmpvalues, "<img src='images/pnic_nok.png'/>");
        }
        if ($galsplink = $this->_checkGalPNSpectra($this->_objectId)) {
            array_push($tmpvalues, "<a href='" . $galsplink . "' target='_blank'><img src='images/gpns6.jpg' /></a>");
        } else {
            array_push($tmpvalues, "<img src='images/gpns6_no.jpg'/>");
        }
        if ($spmlink = $this->_checkSPMKinDB($this->_objectId)) {
            array_push($tmpvalues, "<a href='" . $spmlink . "' target='_blank'><img src='images/logoSPM.png' /></a>");
        } else {
            array_push($tmpvalues, "<img src='images/logoSPMno.png'/>");
        }

        array_push($values, $tmpvalues);

        $tmpvalues = array();
        array_push($tmpvalues, "<a href='http://mast.stsci.edu/portal/Mashup/Clients/Mast/Portal.html' target='_blank'><img src='images/mastlogo_thumb.png' /></a>");
        array_push($tmpvalues, "<a href='http://aladin.u-strasbg.fr/java/nph-aladin.pl?script=" . $res["RAJ2000"] . " " . $res["DECJ2000"] . "' target='_blank'><img src='images/aladin_large.gif' /></a>");

        if ($nvaslink = $this->_checkNVASinDB($this->_objectId, $res["RAJ2000"], $res["DECJ2000"])) {
            array_push($tmpvalues, "<a href='" . $nvaslink . "' target='_blank'><img src='images/nrao_logo.png' /></a>");
        } else {
            array_push($tmpvalues, "<img src='images/nrao_logo_no.png'/>");
        }

        array_push($values, $tmpvalues);

        $tmpvalues = array();
//http://skyserver.sdss.org/dr12/en/tools/quicklook/summary.aspx?ra=197.614455642896&dec=18.438168853724
        //array_push($tmpvalues,
        //        "<a href='http://skyserver.sdss.org/DR12/en/tools/chart/navi.aspx?opt=G&amp;ra=".$res["DRAJ2000"]."&amp;dec=".$res["DDECJ2000"]."&amp;scale=0.15' target='_blank'>"
        //        . "<img src='logoSPMno.jpg'></a>");

        array_push($tmpvalues,
                "<a href='http://skyserver.sdss.org/dr12/en/tools/explore/summary.aspx?ra=".$res["DRAJ2000"]."&dec=".$res["DDECJ2000"]."' target='_blank'>"
                . "<img src='images/sdssIVlogo.png'></a>");

        if ($panstarslink = $this->_checkPanSTARRS($this->_objectId, $res["DRAJ2000"], $res["DDECJ2000"],$diam)) {
            array_push($tmpvalues, "<a href='" . $panstarslink . "' target='_blank'><img src='images/PanSTARRS4c_420.jpg' /></a>");
        } else {
            array_push($tmpvalues, "<img src='images/PanSTARRS4c_420_nores.png'/>");
        }


        array_push($values, $tmpvalues);

        $result = $this->_htmlconstructor->makeTable($values);
        return $result;
    }

    public function createObjectCoords() { //coordstable_info
        $res = $this->_inputresults[MAIN_TABLE]["inuse"];
        $headers = array("RA/DEC", "&alpha;/&delta;", "l/b");
        $values = array(
            formatRADEC($res["RAJ2000"], 2) . " " . formatRADEC($res["DECJ2000"], 2),
            round($res["DRAJ2000"], 4) . " " . round($res["DDECJ2000"], 4),
            round($res["Glon"], 4) . " " . round($res["Glat"], 4)
        );
        $result = $this->_htmlconstructor->makeTable($values, $headers, False, False, False, True, True);
        return $result;
    }
    
   public function createCSCoords() { //coordstable_info
       if (!in_array($this->_inputresults['PNstat']['status'],array("T","L","P"))) return False;
        $res = $this->_inputresults["tbCSCoords"]["inuse"][0];
        $headers = array("RA/DEC");
        $RAtest = formatRADEC($res["CS_RAJ2000"], 2);
        $DECtest = formatRADEC($res["CS_DECJ2000"], 2);
        $regextest = regex_radec($RAtest . " " . $DECtest);
        if ($regextest['X'] != $RAtest or $regextest['Y'] != $DECtest or !$res) {
            $values = array("NA");
        } else {
            $values = array($RAtest . " " . $DECtest);
        }
        $result = $this->_htmlconstructor->makeTable($values, $headers, False, False, False, True, True);
        return $result;
    }

    private function _checkHLA($id) {
        return $this->_mysqldriver->select("`URL`", $this->_mysqldriver->tblName(MAIN_DB_DATA,"HLA_Data"), "`" . MAIN_ID . "` = $id AND `URL` IS NOT NULL");
    }

    private function _checkPNIC($id) {
        $results = $this->_mysqldriver->select("`filename`", $this->_mysqldriver->tblName(MAIN_DB_DATA,"PNIC_list"), "`" . MAIN_ID . "` = " . $id);
        //TODO MULTIPLE LINKS FOR NOW ONLY THE FIRST ONE
        $return = $results ? "http://faculty.washington.edu/balick/PNIC/PNimages_by_galcoord/" . $results[0]["filename"] . ".jpg" : False;
        return $return;
    }

    private function _checkGalPNSpectra($id) {
        $results = $this->_mysqldriver->select("`Name`", $this->_mysqldriver->tblName(MAIN_DB_DATA,"GallPNSpectra"), "`" . MAIN_ID . "` = " . $id);
        //TODO MULTIPLE LINKS FOR NOW ONLY THE FIRST ONE
        $return = $results ? "http://web.williams.edu/Astronomy//research/PN/nebulae/spectra.php?neb=" . $results[0]["Name"] : False;
        return $return;
    }

    private function _checkSPMKinDB($id) {
        $results = $this->_mysqldriver->select("`SPMid`", $this->_mysqldriver->tblName(MAIN_DB_DATA,"SPM_kincatpn"), "`" . MAIN_ID . "` = " . $id);
        $return = $results ? "http://kincatpn.astrosen.unam.mx/image.php?id=" . $results[0]["SPMid"] : False;
        return $return;
    }

    private function _checkNVASinDB($id, $RAJ2000, $DECJ2000) {
        $results = $this->_mysqldriver->select("`" . MAIN_ID . "`", $this->_mysqldriver->tblName(MAIN_IMAGES,"nvas"), "`" . MAIN_ID . "` = " . $id . " AND `found` = 'y'");
        $query = http_build_query(
                array('nvas_pos' => $RAJ2000 . " " . $DECJ2000,
                    'nvas_rad' => 0.1,
                    'nvas_rms' => 10000,
                    'nvas_scl' => 'no'));
        $return = $results ? "https://archive.nrao.edu/cgi-bin/nvas-pos.pl?" . $query : False;
        return $return;
    }

    private function _checkPanSTARRS($id,$DRAJ2000,$DDECJ2000,$diam) {
        $panstars_link = "http://ps1images.stsci.edu/cgi-bin/ps1cutouts?filter=color&filter=g&filter=r&filter=i&filter=z&filter=y&filetypes=stack&auxiliary=data&output_size=256";
//        &size=480
//        &pos=105.6948+-13.7099
        if ($DDECJ2000 > -30) {
            $size = floor($diam / 60 * 240 * 2);
            $size = $size < 240 ? 240:$size;
            $size = $size > 6000 ? 6000:$size;
            $link = $panstars_link . "&size=" . $size . "&pos=" . $DRAJ2000 . "+" . $DDECJ2000 ;
            return $link;
        }
        return False;
    }

    public function createMenyExtLinks() {
        if (!$this->_isAdmin)
            return "";
        $links = array(
//            "SHASSA integrator" => $this->_createIntegratorResLink($this->_objectId, "SHASSA_results", "SHASSAint", "shassa", "fl"),
//            "VTSS integrator" => $this->_createIntegratorResLink($this->_objectId, "VTSS_results", "VTSSint", "vtss", "HaCC"),
//            "SHS integrator" => $this->_createIntegratorResLink($this->_objectId, "SHS_results", "SHSint", "subtr_HaSr_shs", "subtr_HaSr_shs"),
            "Select IPHAS imageset" => $this->_createIPHASselectLink($this->_objectId)
        );
        $result = "";
        foreach ($links as $name => $link)
            if ($link)
                $result .= "<li>$link $name</a></li>\n";
        $result = $result == "" ? "" : "<div id='internal_links'><ul>" . $result . "</ul><hr></div>";
        return $result;
    }

    private function _createIntegratorResLink($id, $table, $ext, $imtable, $band) {
        $check_res = $this->_mysqldriver->selectquery("SELECT `" . MAIN_ID . "` FROM `" . INTEGRATORS_DB . "`.`" . $table . "` WHERE `" . MAIN_ID . "` = " . $id);
        $check_img = $this->_mysqldriver->selectquery("SELECT `" . MAIN_ID . "` FROM `" . MAIN_IMAGES . "`.`" . $imtable . "` WHERE `" . MAIN_ID . "` = " . $id . " AND `found` = 'y' AND `band` = '" . $band . "'");
        $link = (!empty($check_res) or ! empty($check_img)) ? "<a href='integratorsPage.php?id=" . $id . "&ext=" . $ext . "' target='_blank'>" : False;
        return $link;
    }

    private function _createIPHASselectLink($id) {
        $check_res_iphas = $this->_mysqldriver->selectquery("SELECT * FROM `PNImages`.`iphas`  WHERE `found` = 'y' AND `inuse` = 1 AND `" . MAIN_ID . "` = " . $id);
        $link = (!empty($check_res_iphas)) ? "<a href='iphasPick.php?id=" . $id . "&ext=iphaspick' target='_blank'>" : False;
        return $link;
    }

    public function createGalleryBox($cntr_checked = True, $cspos_checked = False, $diam_checked = True) {
        $origin_array = $this->_mysqldriver->selectquery("SELECT * FROM `" . MAIN_IMAGES . "`.`pngimagesinfo` "
                . " WHERE `tempimage` = 'n' and `showingallery` = 'y' "
                . " ORDER BY `showorder`;");
        $thumbsarray = array();
        foreach ($origin_array as $ovals) {
            $newthumb = $this->_createThumbBox($this->_objectId, $ovals);
            if ($newthumb)
                array_push($thumbsarray, $newthumb);
        }

        if (empty($thumbsarray))
            return False;

        $overlayswitch = $this->_createOverlaySwitch($cntr_checked, $cspos_checked, $diam_checked);
        $result = $this->_htmlconstructor->makeGalleryBox($thumbsarray, $overlayswitch);
        return $result;
    }

    private function _createThumbBox($id, $ovals) {
        $result = False;
        $name_out = $ovals["name_out"];
        if ($ovals["name"] == "iphas3colour" or $ovals["name"] == "iquot_HaSr")
            $name_out = $this->_setIPHASname($id, $name_out);
        $image = $this->_objectpngPath . $ovals["outFolder"] . $id . "_" . $name_out;
        if (is_file($image . ".png")) {
            $this->_tempdiamcheck = ($this->_tempdiamcheck or $this->_checkDiamOverlay($image));
            $textInfo = $this->_setTextInfo($ovals);
            $result = array(
                "image" => $image,
                "title" => $ovals["name"],
                "caption" => $textInfo
            );
        }
        return $result;
    }

    private function _checkDiamOverlay($name) {
        if (is_file($name . "_diameter.png"))
            return True;
        return False;
    }

    private function _setTextInfo($array) {
        $addform = "";        
        $exitform = "";
        //$array = $this->_updateLevels($array); reads levels from PNImages.pngimages table TODO
        $text = "<b>" . $array["name"] . "</b>";
        $starttable = "<table id='rgbtable'>";
        if ($array["type"] == "rgb") {
            $RGB_image = $this->_setRGBImagesPaths($array);
            $RGB_cube = $this->_setRGBcubeName($array["name"], $this->_objectId);
            $RGB_cube = $RGB_cube ? "<tr><td>RGB:</td><td><a href='" . $RGB_cube . "'> RGB cube </a></td></tr>" : "";

            if ($this->_isAdmin) {
                $addform = "<form action='" . $this->_referer . "?id=" . $this->_objectId . "' method='POST'>";
                $scalesR = "<td><input type='text' size=3 maxlength=4 name='min_r_imLevel' value='" . 
                                $this->_pngims[$array['name']]['minR_r'] .
                                "'></td>
							<td><input type='text' size=3 maxlength=4 name='r_imLevel' value='" . 
                                $this->_pngims[$array['name']]['maxR_r'] .
                                "'</td>";
                $scalesG = "<td><input type='text' size=3 maxlength=4 name='min_g_imLevel' value='" . 
                                $this->_pngims[$array['name']]['minG_r'] .
                                "'></td>
							<td><input type='text' size=3 maxlength=4 name='g_imLevel' value='" . 
                                $this->_pngims[$array['name']]['maxG_r'] .
                                "'</td>";
                $scalesB = "<td><input type='text' size=3 maxlength=4 name='min_b_imLevel' value='" . 
                                $this->_pngims[$array['name']]['minB_r'] .
                                "'></td>
							<td><input type='text' size=3 maxlength=4 name='b_imLevel' value='" . 
                                $this->_pngims[$array['name']]['maxB_r'] .
                                "'</td>";
                $exitform = "<input type='submit' value='submit' style='position: absolute; left: -9999px; width: 1px; height: 1px;'>
							<input type='hidden' name='rgbscale' value='y'>
							<input type='hidden' name='source' value='" . 
                                $this->_pngims[$array['name']]['maxR_r'] .
                                "'>
							</form>";
            } else {
                $scalesR = "<td>" . 
                                $this->_pngims[$array['name']]['minR_r'] .
                            "</td><td>" . 
                                $this->_pngims[$array['name']]['maxR_r'] .
                            "</td>";
                $scalesG = "<td>" . 
                                $this->_pngims[$array['name']]['minG_r'] .
                            "</td><td>" . 
                                $this->_pngims[$array['name']]['maxG_r'] .
                            "</td>";
                $scalesB = "<td>" . 
                                $this->_pngims[$array['name']]['minB_r'] .
                            "</td><td>" . 
                                $this->_pngims[$array['name']]['maxB_r'] .
                            "</td>";
            }

            $text .= $addform . $starttable;

            $text .= "	<tr><td bgcolor='red'>R:</td>
                            <td><a href='download.php?p=f&f=" . $RGB_image["R"] . "'>" . $array["R_band"] . ".fits</a></td>
                            $scalesR<td>%</td>
                        </tr>
                        <tr><td bgcolor='green'>G:</td>
                            <td><a href='download.php?p=f&f=" . $RGB_image["G"] . "'>" . $array["G_band"] . ".fits</a></td>
                            $scalesG<td>%</td>
			</tr>
			<tr><td bgcolor='blue'>B:</td>
                            <td><a href='download.php?p=f&f=" .  $RGB_image["B"] . "'>" . $array["B_band"] . ".fits</a></td>
                            $scalesB<td>%</td>
			</tr>" . $RGB_cube;


            $text .= "</table>" . $exitform;
        } elseif ($array["type"] == "intensity") {
            if ($this->_isAdmin) {
                $addform = "<form action='" . $this->_referer . "?id=" . $this->_objectId . "' method='POST'>";
                $scales = "<td><input type='text' size=3 maxlength=4 name='min_imLevel' value='" . $array["min_imLevel"] . "'></td>
							<td><input type='text' size=3 maxlength=4 name='imLevel' value='" . $array["imLevel"] . "'</td>";
                $exitform = "<input type='submit' value='submit' style='position: absolute; left: -9999px; width: 1px; height: 1px;'>
							<input type='hidden' name='intscale' value='y'>
							<input type='hidden' name='source' value='" . $array["name"] . "'>
							</form>";
            } else {
                $scales = "<td>" . $array["min_r_imLevel"] . "</td><td>" . $array["r_imLevel"] . "</td>";
            }

            $text .= $addform . $starttable;

            $intimage = $this->_setIntImagePath($array);

            $text .= "<tr>
			<td>I:</td>
			<td><a href='download.php?p=f&f=" . $intimage . "'>" . $array["in_band"] . ".fits</a></td>
			$scales<td>%</td>
                    </tr></table>" . $exitform;
        }
        return $text;
    }

    /**
     * set paths for R,G and B image
     * @param array $array
     * @return array("R" => "/data/..../something.fits,...)
     */
    private function _setRGBImagesPaths($array) {
        $result = array();
        $rgbarray = array("R", "G", "B");
        foreach ($rgbarray as $cl) {
            $result[$cl] = $this->_objectId . "/" . $array[$cl . "_folder"] . "/" . $this->_fitsims[$array[$cl . "_srv"]][$array[$cl . "_band"]]["filename"];
        }
        return $result;
    }
    
    /**
     * set path for intensity image
     * @param array $array
     * @return string "/data/..../something.fits
     */
    private function _setIntImagePath($array) {
        $result = $this->_objectId . "/" . $array["in_folder"] . "/" . $this->_fitsims[$array["in_srv"]][$array["in_band"]]["filename"];
        return $result;
    }
    


    public function fitsImagesTable() {
        $done = array();
        $result = array();
        foreach ($this->_dataInfoTables["imagesetsarray"] as $imageset) {
            if (is_dir($subfolder = $this->_objectfitsPath . $imageset["folder"] . "/") and ! in_array($imageset["folder"], $done)) {
                $result[$imageset["folder"]] = array();
                array_push($done, $imageset["folder"]);
                $files = scandir($subfolder);
                foreach ($files as $file) {
                    if ($file != "." and $file != "..") {
                        array_push($result[$imageset["folder"]], array(
                            "link" => $this->_objectId . "/" . $imageset["folder"] . "/" . $file,
                            "title" => $file)
                        );
                    }
                }
            }
        }

        $box = $this->_htmlconstructor->makeListDownloadBox($result, "accolist");
        return $box;
    }

    private function _setIPHASname($id, $name) {
        $sql = $this->_mysqldriver->selectOne("`run_id`", $this->_mysqldriver->tblName(MAIN_IMAGES,"iphas"), "`" . MAIN_ID . "` = $id AND `inuse` = 1");
        return "r" . $sql["run_id"] . "_" . $name;
    }

    /*     * ******** WORKING **************** */

    private function _createOverlaySwitch($cntr_checked = True, $cspos_checked = False, $diam_checked = True) {
        $overdiam = (isset($this->_inputresults["tbAngDiam"]["inuse"]) and
                $this->_inputresults["tbAngDiam"]["inuse"][0]["MajDiam"] > 0) ? True : False;

        $csposdis = (isset($this->_inputresults["tbCSCoords"]["inuse"]) and
                $this->_inputresults["tbCSCoords"]["inuse"][0]["CSstat"] == "p") ? "" : "disabled";
        $diamdis = ($overdiam and $this->_tempdiamcheck) ? "" : "disabled";

        $cntr_checked = $cntr_checked ? "checked" : "";
        $cspos_checked = $cspos_checked ? "checked" : "";
        $diam_checked = ($diam_checked and $overdiam and $this->_tempdiamcheck) ? "checked" : "";

        $overlaySwitch = "<div id='toggleoverlaydiv'>
							<h4>Overlays</h4>
								<ul>
									<li><label><input type='checkbox' id='toggle_centroid' $cntr_checked>Centroid /
										<a href = '" . $this->_objectpngPath . "/REGIONS/" . $this->_objectId . "_centroid.reg'>centroid.reg</a></label>
									</li>
									<li><input type='checkbox' id='toggle_CS_pos' $csposdis $cspos_checked>CS position /
										<a href = '" . $this->_objectpngPath . "/REGIONS/" . $this->_objectId . "_cspos.reg'>cspos.reg</a></label>
									</li>
									<li><input type='checkbox' id='toggle_diameter' $diamdis $diam_checked>Diameter /
										<a href = '" . $this->_objectpngPath . "/REGIONS/" . $this->_objectId . "_diam.reg'>diam.reg</a></label>
									</li>
								</ul>
						</div>";

        return $overlaySwitch;
    }

    //<a href = 'download.php?p=p&f=" . $this->_objectId . "/REGIONS/" . $this->_objectId . "_centroid.reg'>ds9 centroid</a></li>

    /*     * ******** FINISHED **************** */


    private function _setRGBcubeName($set, $id, $extension = False) {
        $extension = $extension ? $extension : "";
        $result = "download.php?p=c&f=" . $set . "_" . $extension . $id . "_rgbcube.fits";
        if (is_file($this->_linkrgbcubes . $set . "_" . $extension . $id . "_rgbcube.fits"))
            return $result;
        return False;
    }

    private function _adsLink($bibcode) {
        $refdata = $this->_getRefData($bibcode);
        $refinfo = "Authors: ".$refdata[0]['Author'].";\nTitle: ".$refdata[0]['Title']."\nJournal: ".$refdata[0]['Journal'];
        return "<a href='http://adsabs.harvard.edu/abs/" . $bibcode . "' title = '".$refinfo."' target='_blank'>" . $bibcode . "</a>";
    }

    public function createObjectHeader($divid = "headertable_info") {
        @$morph = isset($this->_inputresults["tbPNMorph"]["inuse"][0]["mainClass"]) ? $this->_inputresults["tbPNMorph"]["inuse"][0]["mainClass"] . str_ireplace(";", "", $this->_inputresults["tbPNMorph"]["inuse"][0]["subClass"]) : "na";
        @$majdiam = (isset($this->_inputresults["tbAngDiam"]["inuse"]) and $this->_inputresults["tbAngDiam"]["inuse"][0]["MajDiam"] > 0) ? $this->_inputresults["tbAngDiam"]["inuse"][0]["MajDiam"] . " arcsec" : "na";
        @$flagdiam = (isset($this->_inputresults["tbAngDiam"]["inuse"]) and $this->_inputresults["tbAngDiam"]["inuse"][0]["flagMajDiam"] !== null) ? $this->_inputresults["tbAngDiam"]["inuse"][0]["flagMajDiam"]: "";

        $result = "";
        $result .= "<h2>" . $this->_inputresults["extraName"] . "</h2><hr>";
        $result .= "<table>\n";
        $result .= "<tr><th>" . DES_PREFIX . "</th><td>" . $this->_inputresults["headerName"] . "</td></tr>\n";
        $result .= "<tr><th>Status</th><td>" . $this->_inputresults["PNstat"]["infostat"] . "</td></tr>\n";
        $result .= "<tr><th>Morph.</th><td>" . $morph . "</td></tr>\n";
        $result .= "<tr><th>Diam.</th><td>" . $flagdiam ." ". $majdiam . "</td></tr>\n";
        $result .= "<tr><th>Cat.</th><td>" . $this->_adsLink($this->_inputresults[MAIN_TABLE]["inuse"]["Catalogue"]) . "</td></tr>\n";
        $result .= "<tr><th>dbID</th><td>" . $this->_objectId . "</td></tr>\n";
        $result .= "</table>";
        return $result;
    }

}

?>
