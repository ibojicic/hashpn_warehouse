<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES --  FINISHED CHECKING

/**
 * Description of SpecTable
 *
 * @author ivan
 */
class ExtrasConstruct extends SetMainObjects {

    private $_imagesFolder;
    private $_imagefolder;
    private $_meascolumns = array();
    private $_integratorFolder;
    private $_baseimagefolder;
    private $_fitsfolder;
    private $_intInfo;
    private $_intresults;
    public $tablesFrom;

    public function __construct($page, $myConfig, $isAdmin, $userid, $objid) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_htmlconstructor = new HtmlConstructor();

        if ($objid) {
            $this->_setObjectId($objid);
            $this->_getObjectsData();
        }
                    

        $this->_integratorFolder = $myConfig["integrators"];

        $this->_setLinkImages($page);
    }

    private function _setLinkImages($page) {
        switch ($page) {
            case "integrators":
                $this->_imagesFolder = $this->_integratorFolder;
                break;
            case "iphaspick":
                $this->_imagesFolder = $this->_linkImages;
                break;
            default:
                break;
        }
        return;
    }


    
    private function _setImageFolder($outpath) {
        $this->_baseimagefolder = $this->_imagesFolder . $this->_objectId . "/";
        $this->_imagefolder = $this->_baseimagefolder . $outpath;
        return;
    }

    private function _setFitsFolder($inpath) {
        $this->_setObjectfitsPath($this->_objectId);
        $this->_fitsfolder = $this->_objectfitsPath . $inpath . "/";
        return;
    }

    public function getIntInfo($set) {
        $res = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(INTEGRATORS_DB, "Info"), "`Name` = '$set'");
        $this->_intInfo = $res[0];
        return;
    }

    public function createIntResultsBox($set, $imprefix = False, $tableID = "coordstable_view") {
        $table = "<table border='1' cellpadding='2' cellspacing='2'><tbody>\n";

        $this->_setImageFolder($this->_intInfo["outpath"]);
        $this->_setFitsFolder($this->_intInfo["inpath"]);
        
        foreach ($this->_intresults as $part) {
            $prefix = $imprefix ? $imprefix : str_ireplace(".fits", "", $part["fitsfile"]);

            $part = $this->_specFields($part, $set);
            $datatable = "<table id='$tableID'>\n";
            foreach ($part as $rawkey => $val) {
                $key = isset($this->_meascolumns[$rawkey]["showtext"]) ? $this->_meascolumns[$rawkey]["showtext"] : $rawkey;
                $suf = isset($this->_meascolumns[$rawkey]["sufix"]) ? $this->_meascolumns[$rawkey]["sufix"] : "";


                $datatable .= "<tr><td>$key</td><td>$val</td><td>$suf</td></tr>\n";
            }
            $datatable .= "</table>\n";


            $table .= "<tr><td><img src ='" . $this->_imagefolder . $prefix . "_" . $part["msrNo"] . "_markr.png' height='450'></td>\n";

            if (is_file($this->_imagefolder . $prefix . "_" . $part["msrNo"] . "_overlay.png"))
                $table .= "<td><img src ='" . $this->_imagefolder . $prefix . "_" . $part["msrNo"] . "_overlay.png' height='450'></td>\n";
            $table .= "<td>$datatable";
            $table .= $this->_delMeasurementBox($set, $part["msrNo"]) . "</td>\n";
        }
        $table .= "</tbody>\n</table>\n";
        $table .= $this->_createIntAddMeasurement($set);

        return $table;
    }

    private function _specFields($data, $set) {
        $specuse = array();
        $box = "<form action='integratorsPage.php?id=" . $this->_objectId . "&ext=" . $set . "int' method = 'POST'>\n";
        $box .= "<input type='hidden' name='chinuse' value='1'>\n";
        $box .= "<input type='hidden' name='msrNo' value='" . $data["msrNo"] . "'>\n";
        $box .= "<input type='hidden' name='user' value='" . $this->_userId . "'>\n";
        $box .= "<input type='hidden' name='field' value='" . $data["field"] . "'>\n";
        $box .= "<select onchange='submit()' name='use'>";
        foreach ($specuse as $val) {
            $selected = $data["use"] == $val ? "selected" : "";
            $box .= "<option value='$val' $selected>$val</option>";
        }
        $box .= "</select>\n";

        $box .="</form>\n";
        $data["use"] = $box;

        $specflag = array("NOTCHECK", "DETECTED", "NOTDET", "POSSIBLE", "MANUAL", "REDO");
        $box = "<form action='integratorsPage.php?id=" . $this->_objectId . "&ext=" . $set . "int' method = 'POST'>\n";
        $box .= "<input type='hidden' name='chflag' value='1'>\n";
        $box .= "<input type='hidden' name='msrNo' value='" . $data["msrNo"] . "'>\n";
        $box .= "<input type='hidden' name='user' value='" . $this->_userId . "'>\n";
        $box .= "<input type='hidden' name='field' value='" . $data["field"] . "'>\n";

        $box .= "<select onchange='submit()' name='flag'>";
        foreach ($specflag as $val) {
            $selected = $data["checkFlag"] == $val ? "selected" : "";
            $box .= "<option value='$val' $selected>$val</option>";
        }
        $box .= "</select>\n";

        $box .= "</form>\n";
        $data["checkFlag"] = $box;

        $data["fitsfile"] = "<a href='" . $this->_fitsfolder . $data["fitsfile"] . "'>" . $data["fitsfile"] . "<a>";

        $roundnumbers = array(
            "rapert" => 0,
            "secrapert" => 0,
            "flux" => 3,
            "corrFlux" => 3,
            "logFred" => 4,
            "logFHalpha" => 4
        );
        foreach ($roundnumbers as $key => $val)
            if (isset($data[$key]))
                $data[$key] = round($data[$key], $val);
        return $data;
    }

    private function _delMeasurementBox($set, $msrNo) {
        $box = "<hr>";
        $box .= "<form action='integratorsPage.php?id=" . $this->_objectId . "&ext=" . $set . "int' method = 'POST'>\n";
        $box .= "<input type='submit' value='Delete measurement No. " . $msrNo . "'>\n";
        $box .= "<input type='hidden' name='delmeasure' value='1'>\n";
        $box .= "<input type='hidden' name='idmeasure' value='$msrNo'>\n";
        $box .= "</form>\n";
        return $box;
    }

    public function setIntResultsData($resultsTable = False, $results = False) {
        $resline = "";
        $sql_addres = "";
        if ($resultsTable and $results) {
            $resvals = array();
            $sql_addres = " LEFT JOIN `" . INTEGRATORS_DB . "`.`" . $resultsTable . "` r ON
						m.`" . MAIN_ID . "` = r.`" . MAIN_ID . "` AND
						m.`field` = r.`field` AND
						m.`msrNo` = r.`msrNo` ";
            foreach ($results as $res)
                array_push($resvals, $res);
            $resline = ",r.`" . implode("`,r.`", $resvals) . "`";
        }
        $meastable = $this->_intInfo["Integrators_Table"];
        $measvals = array();
        $sqlmeas = $this->_mysqldriver->select("`column`,`showtext`,`sufix`", $this->_mysqldriver->tblName(INTEGRATORS_DB, "showMeasurements"), "`show` = 'y'", "`order`");

        $this->_meascolumns = makeOneColMain($sqlmeas, "column");

        foreach ($sqlmeas as $meas)
            array_push($measvals, $meas["column"]);
        $measline = "m.`" . implode("`,m.`", $measvals) . "`";
        $sql = "SELECT $measline $resline FROM `" . INTEGRATORS_DB . "`.`" . $meastable . "` m $sql_addres WHERE m.`" . MAIN_ID . "` = " . $this->_objectId . ";";
        $this->_intresults = $this->_mysqldriver->selectquery($sql);

        return;
    }

    public function createIPHASPicker() {
        $runlist = array();
        $res = $this->_mysqldriver->select("`run_id`,`inuse`", $this->_mysqldriver->tblName(MAIN_IMAGES, "iphas"), "`found` = 'y' AND `" . MAIN_ID . "` = " . $this->_objectId, False, "`run_id`");
        foreach ($res as $irun)
            array_push($runlist, $irun);

        $this->_setImageFolder("IPHAS/");

        $table = "";
        $table .= "<form id='pnstat' action='iphasPick.php' method = 'GET'><table border='1' cellpadding='2' cellspacing='2'><tbody>\n";
        foreach ($runlist as $data) {
            $run = $data["run_id"];
            $inuse = $data["inuse"];
            $selected = $inuse == 1 ? "CHECKED" : "";
            $rgbimname = $this->_imagefolder . $this->_objectId . "_r" . $run . "_iphas3colour.png";
            $qimname = $this->_imagefolder . $this->_objectId . "_r" . $run . "_iquotHaSr_int.png";
            if (is_file($rgbimname) or is_file($qimname)) {
                $table .= "<tr><td><img src ='" . $rgbimname . "' height='350'></td><td><img src ='" . $qimname . "' height='350'></td>";
                $table .= "<td><input type='radio' name='irun' value='$run' $selected>Run: $run</td></tr>\n";
            }
        }
        $table .= "<input type='hidden' name='ext' value='iphaspick'>";
        $table .= "<input type='hidden' name='id' value='" . $this->_objectId . "'>";

        $table .= "</tbody>\n</table><input type='submit' value='Submit'></form>\n";
        return $table;
    }

    public function updateIPHASPick($id, $run) {
        $up1 = $this->_mysqldriver->query("UPDATE `PNImages`.`iphas` SET `inuse` = 0 WHERE `" . MAIN_ID . "` = $id");
        $up2 = $this->_mysqldriver->query("UPDATE `PNImages`.`iphas` SET `inuse` = 1 WHERE `" . MAIN_ID . "` = $id AND `run_id` = '$run'");
        return $up1 and $up2;
    }

    private function _createIntAddMeasurement($set) {

        $fields = $this->_fieldsContObject($set, $this->_objectId);
        $intinfo = $this->_getIntegratorsInfo($set);

        $foption = "<select name='field'>";
        $foption .= "<option value='all' selected='selected' >all</option>";
        foreach ($fields as $val)
            $foption .= "<option value='" . $val["field"] . "' >" . $val["field"] . "</option>";
        $foption .= "</select>";

        $form = "
		<div id ='inp_newmeas' class='inpdialog' title='addmeas'>
			<form id ='forinpdialog' action='integratorsPage.php?id=" . $this->_objectId . "&ext=" . $set . "int' method = 'POST'>
				rApert (arcsec)<input type='text' name='aprt' value='" . ($this->_objectData["MajDiam"] / 2.). "'><br>
				RA (dec)<input type='text' name='ra'  value='" . $this->_objectData["DRAJ2000"] . "'><br>
				DEC (dec)<input type='text' name='dec' value='" . $this->_objectData["DDECJ2000"] . "'><br>
                                FWHMPSF [pix]<input type='text' name='fwhmpsf' value='2.5'><br>
                                Zero magnitude<input type='text' name='zmag' value='".$intinfo['zmag']."'><br>
                                Contour levels<input type='text' name='clevels' value='1,3,5'><br>
                                Apply centroid<input type='checkbox' name='centroid' value='1'><br>
                                Fixed aperture size<input type='checkbox' name='fixaperture' value='1'><br>
				Field " . $foption . "<br>
				<input type='hidden' name='" . $set . "integrator' value='1'>
			</form>
			</div>";


        $button = "<button id='editdata' value='inp_newmeas'>Add Measurement</button>";

        $result = $form . $button;

        return $result;
    }

    private function _fieldsContObject($set, $id) {
        $getinfo = $this->_getIntegratorsInfo($set);
        $sql = "SELECT DISTINCT(`field`) FROM `" . MAIN_IMAGES . "`.`" . $getinfo["PNImagesTable"] . "` WHERE `" . MAIN_ID . "` = $id AND `band` = '" . $getinfo["band"] . "'";
        $result = $this->_mysqldriver->selectquery($sql);
        return $result;
    }



}

?>
