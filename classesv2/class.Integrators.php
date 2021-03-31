<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING
//include_once ("class.MysqlDriver.php");
/**
 * Description of class
 *
 * @author ivan
 */
class Integrators extends SetMainObjects {

    use \MyPHP\MyPythons;

    private $_aperture = False;
    private $_ddec;
    private $_dra;
    private $_majDiam = False;
    private $_cellsize;
    private $_calgorithm;
    private $_outimage;
    private $_outimagepath;
    private $_fits;
    private $_pythonPars = array();
    private $_outoverlayimage = "";
    private $_overlayImage = -1;
    private $_shscaldata;
    private $_pydriverdir;
    private $_set;
    private $_integratorsinfo;
    private $_msrNo;
    private $_field;
    private $_inimage;
    private $_data = False;
    private $_results;
    private $_input;
    private $_inputdata;
    private $_fwhmpsf = False;
    private $_minSky;
    private $_maxSky;
    private $_fixaprt;
    private $_cntrlevel = array(0);
    private $_zmag;
    
    public $sourcedata;
    public $pythonerror;

    public function __construct($myConfig, $userid, $isAdmin, $input, $pydriverdir = False) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_pydriverdir = $pydriverdir ? $pydriverdir : $myConfig["pydriverdir"];
        $this->_input = $input;
        $this->_setSet(str_ireplace("int", "", $this->_input["ext"]));
        $this->_setObjectId($this->_input["id"]);
        $this->_integratorsinfo = $this->_getIntegratorsInfo($this->_set);
        $this->_setCellSize();
    }

    /**
     * set working survey
     * @param string $set
     */
    private function _setSet($set) {
        $this->_set = $set;
    }

    /**
     * 
     * @return array of data from fits image table, False on failure
     */
    public function setsourcedata() {
        $this->sourcedata = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(MAIN_IMAGES, strtolower($this->_set)), "`" . MAIN_ID . "` = " . $this->_objectId . " AND `found` = 'y' AND `band` = '" . $this->_integratorsinfo["band"] . "'");
        return $this->sourcedata;
    }

    public function setInputData($data) {
        $this->_inputdata = $data;
    }

    /**
     * set measurement number
     */
    public function setMsrNo() {
        $max = $this->_mysqldriver->selectquery("SELECT MAX(`msrNo`) as 'max' FROM `" . INTEGRATORS_DB . "`.`" . $this->_integratorsinfo["Integrators_Table"] . "` WHERE `" . MAIN_ID . "` = " . $this->_objectId . ";");
        $this->_msrNo = $max[0]["max"] + 1;
    }

    /**
     * 
     * @param type $field
     */
    public function setField() {
        if (isset($this->_inputdata["field"]) and trim($this->_inputdata["field"]) != "") {
            $this->_field = $this->_inputdata["field"];
        } else
            $this->_field = "field";
    }

    private function _setInImage() {
        $this->_fits = $this->_inputdata['filename'];
        $this->_inimage = PNIMAGES . $this->_objectId . "/" . $this->_integratorsinfo["inpath"] . "/" . $this->_fits;
        return (is_file($this->_inimage));
    }

    private function _setOutOverlayImage() {
        $this->_outoverlayimage = $this->_outimage . "_overlay";
        return True;
    }

    private function _setOutImagePath($makefolder = True) {
        $this->_outimagepath = pathslash(pathslash(INTEGRATORS_ROOT . $this->_objectId) . $this->_integratorsinfo["outpath"]);
        if (!is_dir($this->_outimagepath) and $makefolder) {
            $this->setBrewer('tcooper');
            $mkdir = "/usr/lib/qb_drivers/qb_drivers/mkdir_driver.py";
            $results = $this->PythonToPhp($mkdir,["folder" => $this->_outimagepath],'corona',True);
            if ($results != 'ok') {
                return False;
            }
        }
        return True;
    }

    private function _setOutImage() {
        $name = str_ireplace(".fits", "", $this->_inputdata["filename"]);
        $this->_outimage = $this->_outimagepath . $name . "_" . $this->_msrNo;
        return True;
    }

    public function getMeasurements() {
        $res = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(INTEGRATORS_DB, $this->_integratorsinfo["Integrators_Table"]), "`" . MAIN_ID . "` = " . $this->_objectId);
        return $res;
    }

    /**
     * get data from MainView table for specific object
     * @return string of data, False on failure
     */
    public function getData() {
        $res = $this->_mysqldriver->select('*', $this->_mysqldriver->tblName(MAIN_DB, $this->_currentview), "`" . MAIN_ID . "` = " . $this->_objectId);
        if ($res)
            $this->_data = $res[0];
        return $this->_data;
    }

    /**
     * set centroiding algorithm : centroid/none
     * @param string $calgorithm
     */
    public function setCentAlgorithm() {
        if (isset($this->_input['centroid']) and $this->_input['centroid'] == 1) {
            $this->_calgorithm = "centroid";
        } else
            $this->_calgorithm = "none";
    }

    private function _setFixedAprt() {
        if (isset($this->_input['fixaperture']) and $this->_input['fixaperture'] == 1) {
            $this->_fixaprt = "1";
        } else
            $this->_fixaprt = "0";
        return True;
    }

    private function _cntrLevels() {
        $result = array();
        if (isset($this->_input['clevels']) and trim($this->_input['clevels']) != "") {
            $chunks = explode(",", $this->_input['clevels']);
            if (!empty($chunks)) {
                foreach ($chunks as $tmplevel) {
                    $level = floatval($tmplevel);
                    if (!in_array($level, $result))
                        array_push($result, $level);
                }
            }
        }
        if (!empty($result))
            $this->_cntrlevel = $result;
        return True;
    }

    private function _setCellSize($cellsize = False) {
        $this->_cellsize = $cellsize ? $cellsize : $this->_integratorsinfo["cellsize"];
    }

    private function _setfwhmpsf() {
        if (isset($this->_input["fwhmpsf"]) and floatval($this->_input["fwhmpsf"] > 0.))
            $this->_fwhmpsf = floatval($this->_input["fwhmpsf"]);
        return $this->_fwhmpsf;
    }

    private function _setMajDiam() {
        $this->_majDiam = ($this->_data["MajDiam"] <= 0 or trim($this->_data["MajDiam"]) == "" or $this->_data["MajDiam"] == NULL) ? 1 : $this->_data["MajDiam"];
        return $this->_majDiam;
    }

    private function _setCoords() {
        $this->_dra = !isset($this->_input["ra"]) ? $this->_data["DRAJ2000"] : $this->_input["ra"];
        $this->_ddec = !isset($this->_input["dec"]) ? $this->_data["DDECJ2000"] : $this->_input["dec"];
        return $this->_regexCoords($this->_dra . "," . $this->_ddec, "radec");
    }

    private function _setOverlayImage() {
        $this->_overlayImage = -1;
        $chunks = explode(";", $this->_integratorsinfo["overlay"]);
        foreach ($chunks as $option) {
            $parts = explode(":", $option);
            $set = $parts[0];
            $band = isset($parts[1]) ? $parts[1] : "dummy";
            $path = isset($parts[2]) ? $parts[2] : "";
            $filename = $this->_mysqldriver->selectquery("SELECT `filename` FROM `PNImages`.`fitsimages`"
                    . " WHERE `set` = '" . $set . "' AND `band` = '" . $band . "' AND `idPNMain` =" . $this->_objectId . ";");
            if ($filename) {
                $this->_overlayImage = PNIMAGES . $this->_objectId . "/" . $path . "/" . $filename[0]['filename'];
                if (is_file($this->_overlayImage))
                    return true;
            }
        }
        return true;
    }

    /* set aperture parameter
     *  returns aperture size (r) in pixels False on error
     */

    private function _setAperture() {
        if (!isset($this->_majDiam))
            $this->_setMajDiam();

        if (!isset($this->_input["aprt"])) {
            switch ($this->_set) {
                case "SHS":
                    $apfactor = 1 + 0.2 * ($this->_integratorsinfo["minaperture"] / ($this->_majDiam / (2 * $this->_integratorsinfo["cellsize"])));
                    $this->_aperture = ($this->_integratorsinfo["minaperture"] / 2 < $this->_majDiam / (2 * $this->_integratorsinfo["cellsize"])) ?
                            $this->_majDiam / (2 * $this->_integratorsinfo["cellsize"]) * $apfactor : $this->_integratorsinfo["minaperture"];
                    break;
                case "SHASSA":
                    $this->_aperture = $this->_majDiam / $this->_integratorsinfo["cellsize"] < 1 ?
                            $this->_integratorsinfo["minaperture"] / 2 : ($this->_integratorsinfo["minaperture"] + ($this->_majDiam - $this->_integratorsinfo["cellsize"]) / $this->_integratorsinfo["cellsize"]) / 2;
                case "VTSS":
                    $this->_aperture = $this->_majDiam / $this->_integratorsinfo["cellsize"] < 1 ?
                            $this->_integratorsinfo["minaperture"] / 2 : ($this->_integratorsinfo["minaperture"] + ($this->_majDiam - $this->_integratorsinfo["cellsize"]) / $this->_integratorsinfo["cellsize"]) / 2;

                    break;
            }
        } else
        if (trim($this->_input["aprt"]) != "" and floatval($this->_input["aprt"]) > 0) {
            $this->_aperture = floatval($this->_input["aprt"]) / $this->_cellsize;
        }
        return $this->_aperture;
    }

    private function _setSkyLimits() {
        $this->_minSky = (trim($this->_integratorsinfo["minSky"]) != "" and is_null($this->_integratorsinfo["minSky"])) ? floatval($this->_integratorsinfo["minSky"]) : -1E32;
        $this->_maxSky = (trim($this->_integratorsinfo["maxSky"]) != "" and is_null($this->_integratorsinfo["maxSky"])) ? floatval($this->_integratorsinfo["maxSky"]) : 1E32;
        return True;
    }

    
    private function _setzMag() {
        if (!is_numeric($this->_input['zmag'])) {
            $this->_zmag = 0;
        } else {
            $this->_zmag = floatval($this->_input['zmag']);
        }
        return True;
    }
    
    private function _setPythonPars() {

        $this->_pythonPars = array(
            "set" => $this->_set,
            "dra" => floatval($this->_dra),
            "ddec" => floatval($this->_ddec),
            "fits" => $this->_fits,
            "infile" => $this->_inimage,
            "outfile" => $this->_outimage,
            "outpath" => $this->_outimagepath,
            "mainid" => $this->_objectId,
            "majDiam" => floatval($this->_majDiam),
            "aperture" => floatval($this->_aperture), //in pixels
            "cellsize" => floatval($this->_cellsize),
            "calgor" => $this->_calgorithm,
            "msrNo" => $this->_msrNo,
            "field" => $this->_field,
            "inovrlay" => $this->_overlayImage,
            "outovrlay" => $this->_outoverlayimage,
            "minSky" => $this->_minSky,
            "maxSky" => $this->_maxSky,
            "fixedap" => $this->_fixaprt,
            "fwhmpsf" => $this->_fwhmpsf,
            "dtMax" => $this->_integratorsinfo["dtMax"],
            "dtMin" => $this->_integratorsinfo["dtMin"],
            "zmag" => $this->_zmag,
            "cntrlev" => $this->_cntrlevel
        );
        #print_r($this->_pythonPars);
        #exit();
        return True;
    }

    public function setForInt() {


        $result = (
                $this->_setMajDiam() and
                $this->_setInImage() and
                $this->_setOutImagePath() and
                $this->_setOutImage() and
                $this->_setOverlayImage() and
                $this->_setOutOverlayImage() and
                $this->_setfwhmpsf() and
                $this->_setAperture() and
                $this->_setCoords() and
                $this->_setSkyLimits() and
                $this->_setFixedAprt() and
                $this->_cntrLevels() and
                $this->_setzMag()
                );

        if (!$result)
            return False;
        $this->_setPythonPars();
        return True;
    }

    /**
     * do the integration return array of results
     * @return array of results, False on failure
     */
    public function Integrate() {
//        $results = pythonToPhp("Integrator_driver.py", $this->_pythonPars, "/data/copper/tmp/", "corona");
        $this->setBrewer('tcooper');
        $integrate = "/usr/lib/qb_drivers/qb_drivers/integrator_driver.py";
//        print_r($this->_pythonPars);
//        exit();

        $results = $this->PythonToPhp($integrate,$this->_pythonPars,'corona',True);
        if ($results["finished"] == "ok") {
            $this->_results = $results["results"];
            array_push($this->_results, $this->_userId);
            array_push($this->_results, "NOW()");
            return True;
        } else {
            $this->pythonerror = $results["error"];
            return False;
        }
    }

    /**
     * record results of integration in mysql table
     * @return False on failure
     */
    public function recordResults() {
        $keysarray = array('msrNo', 'field', 'fitsfile', 'cellsize', MAIN_ID, 'apDRAJ2000', 'apDDECJ2000', 'calgorithm',
            'msky', 'stddev', 'sskew', 'nsky', 'nsrej',
            'rapert', 'secrapert', 'annulus', 'dannulus', 'sum', 'area', 'flux', 'mag',
            'xinit', 'yinit', 'xcenter', 'ycenter', 'xshift',
            'yshift', 'xerr', 'yerr',
            'merr', 'cerror', 'serror', 'perror', 'user', 'date');
        $result_array = array_combine($keysarray, $this->_results);
        $sql_insert = $this->_mysqldriver->makeInsertString($result_array, "`" . INTEGRATORS_DB . "`.`" . $this->_integratorsinfo["Integrators_Table"] . "`", array("", "INDEF"));
        $sql_insert = "INSERT INTO `" . INTEGRATORS_DB . "`.`" . $this->_integratorsinfo["Integrators_Table"] . "` " . $sql_insert . ";";
        $res = $this->_mysqldriver->query($sql_insert);
        return $res;
    }

    public function calculateFinalResults() {
        switch ($this->_set) {
            case "SHASSA":
                $this->calculateSHASSAresults();
                break;
            case "VTSS":
                $this->calculateVTSSresults();
                break;
            case "SHS":
                $this->calculateSHSresults();
                break;

        }
    }

    public function SHScalibrationData() {
        $this->_shscaldata = makeOneColMain($this->_mysqldriver->select("*", $this->_mysqldriver->tblName(INTEGRATORS_DB, "SHS_calibration")), "FieldNo");
        return $this->_shscaldata;
        //if (!$piercedata) $piercedata = array(0 => array("Cal_Fact" => 13.,"Rank" => "-1", "Scatter" => "-1"));
    }

    public function calculateSHSresults($id = False, $msrNo = False, $field = False) {
        $id = $id ? $id : $this->_objectId;
        $msrNo = $msrNo ? $msrNo : $this->_msrNo;
        $field = $field ? $field : $this->_field;

        if (!isset($this->_shscaldata[$field]["Cal_Fact"]) or $this->_shscaldata[$field]["Cal_Fact"] == "" or $this->_shscaldata[$field]["Cal_Fact"] == 0) {
            $this->_shscaldata[$field]["Cal_Fact"] = 12.0;
            $this->_shscaldata[$field]["Rank"] = -1;
        }

        $NIItoHalpha = $this->_mysqldriver->selectOneValue("mean", $this->_mysqldriver->tblName(MAIN_DB_DATA, "NIItoHalpha"), "`" . MAIN_ID . "` = " . $id . " AND `use` = 'yes'");
        if (!isset($NIItoHalpha) or ! $NIItoHalpha or trim($NIItoHalpha) == "")
            $NIItoHalpha = False;
        $measurements = $this->_mysqldriver->select("*", $this->_mysqldriver->tblName(INTEGRATORS_DB, "SHS_measurements"), "`" . MAIN_ID . "` = " . $id . " AND `msrNo` = " . $msrNo);


        foreach ($measurements as $result) {
            $sql_fields = "";
            $sql_vals = "";
            $flux = floatval(trim($result["flux"], "'"));

            if ($flux <= 0) {
                $corrflux = 0;
                $logFred = 0;
            } else {
                $corrflux = $flux / $this->_shscaldata[$field]["Cal_Fact"];
                $logFred = log10(5.66E-18 * pow(0.67, 2) * $corrflux);
            }

            if ($NIItoHalpha and $flux > 0) {
                $corr_logFred = log10(1. / (0.725 * $NIItoHalpha + 1));
                $logFHalpha = $logFred + $corr_logFred;
                $sql_fields = ",`NIItoHalpha`,`corr_logFred`,`logFHalpha`";
                $sql_vals = "," . $NIItoHalpha . "," . $corr_logFred . "," . $logFHalpha;
            }

            $sql_insert = "INSERT INTO `" . INTEGRATORS_DB . "`.`SHS_results`
						(
						`msrNo`,`" . MAIN_ID . "`,`field`,
						`fieldrank`,`CF`,
						`flux`,`corrFlux`,`logFred`
						$sql_fields
						)
						VALUES (
						$msrNo ,$id ,$field,
						" . $this->_shscaldata[$field]["Rank"] . "," . $this->_shscaldata[$field]["Cal_Fact"] . ",
						$flux,$corrflux,$logFred
						$sql_vals
						);";
            $this->_mysqldriver->query($sql_insert);
        }
        return;
    }

    public function calculateSHASSAresults() {
        $sql_fields = "";
        $sql_vals = "";
        $NIItoHalpha = $this->_mysqldriver->selectOneValue("mean", $this->_mysqldriver->tblName(MAIN_DB_DATA, "NIItoHalpha"), "`" . MAIN_ID . "` = " . $this->_objectId . " AND `use` = 'yes'");
        if (!isset($NIItoHalpha) or ! $NIItoHalpha or trim($NIItoHalpha) == "")
            $NIItoHalpha = False;
        $fluxcorrection = $this->_mysqldriver->selectOneValue("correction", $this->_mysqldriver->tblName(INTEGRATORS_DB, "SHASSA_corrections"), "`field` = '" . $this->_field . "'");
        if (!isset($fluxcorrection) or ! $fluxcorrection or trim($fluxcorrection) == "")
            $fluxcorrection = False;
        $flux = floatval($this->_results[19]);
        if ($flux < 0 or ! $fluxcorrection) {
            $correctedFlux = 'NULL';
            $logFred = 'NULL';
        } else {
            $correctedFlux = $flux / $fluxcorrection;
            $logFred = log10(5.66E-18 * pow(47.64, 2) * $correctedFlux / 10.);
        }

        if ($NIItoHalpha and $flux > 0) {
            $corr_logFred = log10(1. / (0.375 * $NIItoHalpha + 1));
            $logFHalpha = $logFred + $corr_logFred;
            $sql_fields = ",`NIItoHalpha`,`corr_logFred`,`logFHalpha`";
            $sql_vals = "," . $NIItoHalpha . "," . $corr_logFred . "," . $logFHalpha;
        }

        $sql_insert = "INSERT INTO `" . INTEGRATORS_DB . "`.`SHASSA_results`
                            (`msrNo`,`" . MAIN_ID . "`,`field`,`correction`,
                            `flux`,`corrFlux`,`logFred`
                            $sql_fields)
                            VALUES 
                            ($this->_msrNo ,$this->_objectId ,'" . $this->_field . "',$fluxcorrection ,
                            $flux,$correctedFlux,$logFred
                            $sql_vals);";
        $this->_mysqldriver->query($sql_insert);
    }

    public function calculateVTSSresults() {
        $correction = $this->_mysqldriver->selectOneValue("factor", $this->_mysqldriver->tblName(INTEGRATORS_DB, "VTSS_correction"), "`field` = '" . $this->_field . "'");
        $flux = floatval(trim($this->_results[19], "'"));
        $logFHalpha = 0;

        if ($flux > 0)
            $logFHalpha = log10(5.66E-18 * pow(96.4, 2) * $flux) + floatval($correction);

        $sql_insert = "INSERT INTO `" . INTEGRATORS_DB . "`.`VTSS_results`
						(
						`msrNo`,`" . MAIN_ID . "`,`field`,
						`flux`,`logFHalpha`
						)
						VALUES (
						$this->_msrNo ,$this->_objectId ,'" . $this->_field . "',
						$flux,$logFHalpha
						);";

        $this->_mysqldriver->query($sql_insert);
    }

    public function updateSHSSamplesTable() {

        $this->_mysqldriver->query("DELETE FROM `" . MAIN_SAMPLES . "`.`SHS_sample`;");

        $this->_mysqldriver->query("INSERT INTO `" . MAIN_SAMPLES . "`.`SHS_sample`
		(`" . MAIN_ID . "`,
		`detflag`)
		SELECT s.`" . MAIN_ID . "`,'q' FROM `" . INTEGRATORS_DB . "`.`SHS_results` s
		WHERE (s.`checkFlag` = 'NOTCHECK')
		AND s.`use` = 'y'
		GROUP BY s.`" . MAIN_ID . "`;");

        $this->_mysqldriver->query("INSERT INTO `" . MAIN_SAMPLES . "`.`SHS_sample`
		(`" . MAIN_ID . "`,
		`detflag`)
		SELECT s.`" . MAIN_ID . "`,'y' FROM `" . INTEGRATORS_DB . "`.`SHS_results` s
		WHERE s.`checkFlag` = 'DETECTED'
		AND s.`use` = 'y'
		GROUP BY s.`" . MAIN_ID . "`;");

        $this->_mysqldriver->query("INSERT INTO `" . MAIN_SAMPLES . "`.`SHS_sample`
		(`" . MAIN_ID . "`,
		`detflag`)
		SELECT s.`" . MAIN_ID . "`,'p' FROM `" . INTEGRATORS_DB . "`.`SHS_results` s
		WHERE s.`checkFlag` = 'POSSIBLE'
		AND s.`use` = 'y'
		GROUP BY s.`" . MAIN_ID . "`;");
    }

    public function _changeFlag($post) {
        $sql = "UPDATE `" . INTEGRATORS_DB . "`.`" . strtoupper($this->_set) . "_results`
			SET `checkFlag` = '" . $post["flag"] . "', `chflguser` = '" . $post["user"] . "'
			WHERE `" . MAIN_ID . "` = " . $this->_objectId . " AND `msrNo` = " . $post["msrNo"]
                . " AND `field` = '" . $post["field"] . "';";
        if (!mysql_query($sql))
            die(mysql_error());
    }

    public function _changeInUse($post) {
        $sql = "UPDATE `" . INTEGRATORS_DB . "`.`" . strtoupper($this->_set) . "results`
			SET `use` = '" . $post["use"] . "', `user` = '" . $post["user"] . "'
			WHERE `" . MAIN_ID . "` = " . $this->_objectId . " AND `msrNo` = " .
                $post["msrNo"] . " AND `field` = '" . $post["field"] . "';";
        if (!mysql_query($sql))
            die(mysql_error());
    }

    public function _deleteMeas($post) {
        $this->_setOutImagePath(False);
        $sql = "DELETE FROM `" . INTEGRATORS_DB . "`.`" . strtoupper($this->_set) . "_measurements` WHERE "
                . "`" . MAIN_ID . "` = " . $this->_objectId
                . " AND `msrNo` = " . $post["idmeasure"] . ";";
        if (!mysql_query($sql))
            die(mysql_error());
        $sql = "DELETE FROM `" . INTEGRATORS_DB . "`.`" . strtoupper($this->_set) . "_results` WHERE "
                . "`" . MAIN_ID . "` = " . $this->_objectId
                . " AND `msrNo` = " . $post["idmeasure"] . ";";
        if (!mysql_query($sql))
            die(mysql_error());
        $files = array(
            $this->_outimagepath . "*_" . $post["idmeasure"] . "_markr.png",
            $this->_outimagepath . "*_" . $post["idmeasure"] . "_overlay.png");
        $this->setBrewer('tcooper');
        $mkdir = "/usr/lib/qb_drivers/qb_drivers/rmfile_driver.py";
        $results = $this->PythonToPhp($mkdir,$files,'corona',True);
    }

}

?>
