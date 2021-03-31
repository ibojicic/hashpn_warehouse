<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author ivan
 */
class GetObjectSpectra extends SetMainObjects {

    private $_spectralines = array();

    public function __construct($myConfig, $objid, $userid, $isAdmin) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_spectralines = $myConfig["spectralines"];
        $this->_setObjectId($objid);
    }

    public function createPlots() {
        $flag = array("spctr" => False, "H-beta" => False, "1.00E-14" => False);
        $line = "";
        $plots = False;

        $spfiles = glob($this->_linkSPlots . $this->_objectId . "_spctr.txt");

        $splot_markers_file = $this->_linkSPmarkers . $this->_userId . "_splinesmarkers.txt";

        if (!is_file($splot_markers_file))
            setSpLinesMarkers($splot_markers_file, $this->_spectralines);
        if (!empty($spfiles)) {
            $line .= "var filesp = '" . $spfiles[0] . "';\n";
            $line .= "var plchsp = 'sp_placeholdersp';\n";
            $line .= "var tltpsp = 'tooltipsp';\n";
            $line .= "var chCnrsp = 'choicessp';\n";
            $line .= "filesplines = '" . $splot_markers_file . "';\n";
            $flag["spctr"] = True;
        }
        $filename = $this->_linkSPlots . $this->_objectId . "_synthspectrarelative.txt";
        if (is_file($filename) and $this->_checkeELCATSpectra($this->_objectId)) {
            $line .= "var file1 = '" . $filename . "';\n";
            $line .= "var plch = 'sp_placeholder';\n";
            $line .= "var tltp = 'tooltip';\n";
            $line .= "var chCnr = 'choices';\n";
            $flag["relative"] = True;
        }
        if (!empty($flag) and $line != "") {
            $plots = array(
                "plots" => "<script type='text/javascript' >$line</script><script type='text/javascript' src='javascript/spectraPlot_ext.js'></script>\n",
                "flags" => $flag);
        }
        return $plots;
    }

    public function getSpectraLinks() {
        $links = $this->_mysqldriver->selectquery("
                        SELECT l.`idspectraLinks`, l.`reference`,i.`Author`,i.`Title`
                        ,i.`Journal`,i.`Year`,i.`elcatCode`,l.`user`,l.`comments`
                        FROM `PNSpectra_Sources`.`spectraLinks` l
                        LEFT JOIN `MainGPN`.`ReferenceIDs` i
                        ON l.`reference` = i.`Identifier`                    
                        WHERE `" . MAIN_ID . "` = " . $this->_objectId . ";");
        return $links;
    }
    
    private function _checkeELCATSpectra($id) {
        return $this->_mysqldriver->selectquery("SELECT `".MAIN_ID."` FROM `MainPNSamples`.`availableeELCAT` WHERE `".MAIN_ID."` = $id AND `elcat` = 'y';");
    }
    
    private function _checkSpectra($id) {
        return $this->_mysqldriver->selectquery("SELECT `".MAIN_ID."` FROM `MainPNSamples`.`availableeELCAT` WHERE `".MAIN_ID."` = $id AND `elcat` = 'y';");
    }

}
