<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of htmlConstructor
 *
 * @author ivan
 */
class HtmlConstructor {

    public function __construct() {
        
    }

    public function makeGalleryBox($newthumbs, $overlayswitch) {
        $thumbs = "<div class='thumbs-container'>
						<div id='thumbs' class='navigation'>
							<ul class='thumbs noscript'>\n";

        foreach ($newthumbs as $thumbarray) {
            $thumbs .= $this->_makeThumb($thumbarray['image'], $thumbarray['title'], $thumbarray['caption']);
        }

        $thumbs .= "		</ul>
						</div>";
        $thumbs .= $overlayswitch;
        $thumbs .="	</div>\n";

        $galbox = "	<div id='gal-container'>
						<div class='slideshow-container'>
							<div id='loading'></div>
							<div id='slideshow' class='slideshow'></div>
							<div id='caption' class='caption-container'></div>
						</div>\n";

        $galbox .= $thumbs;
        $galbox .="	</div>\n";

        return $galbox;
    }

    private function _makeThumb($image, $title, $textinfo) {
        $thumb = "<li>
						<a class='thumb' name='name' href='" . $image . ".png' title='" . $title . "'>
								<img src='" . $image . "_thumb.jpg'/>" . $title . "
						</a>
						<div class='caption' >
							$textinfo
						</div>
					</li>\n";
        return $thumb;
    }

    public function makeListCheckBox($groupedlist, $checkid, $checkdisplay, $divclass = False, $checkall = True, $radio = False) {
        $divclass = $this->_idclass($divclass, "class");
        $result = "<div $divclass >\n";
        foreach ($groupedlist as $group => $samples) {
            $result .= "<h3>" . $group . "</h3>\n";
            $result .="<div>\n";
            if (!$radio)
                $result .= $this->_checkallBox($checkall);
            $temparray = array();
            foreach ($samples as $sample) {
                $box = $radio ? " type='radio' name='" . $radio . "' value='" . $sample[$checkid] . "' " : " type='checkbox' name='" . $sample[$checkid] . "' value='1' ";
                array_push($temparray, "<input " . $box . $sample['checked'] . ">" . $sample[$checkdisplay]);
            }
            $result .= $this->_unorderedList($temparray);
            $result .= "</div>\n";
        }
        return $result . "</div>\n";
    }

    public function makeListDownloadBox($groupedlist, $divclass = False) {
        $divclass = $this->_idclass($divclass, "class");
        $result = "<div $divclass >\n";
        foreach ($groupedlist as $group => $samples) {
            if (!empty($samples)) {
                $result .= "<h3>" . $group . "</h3>\n";
                $result .="<div>\n";
                $temparray = array();
                foreach ($samples as $sample)
                    array_push($temparray, "<a href='download.php?p=f&f=" . $sample['link'] . "'>" . $sample['title'] . "</a>");
                $result .= $this->_unorderedList($temparray);
                $result .= "</div>\n";
            }
        }
        return $result . "</div>\n";
    }

    public function makeInfoBox($id, $res, $hla, $user = "sys") {
        $vizsimbLinks = $this->composeVizier($res['RAJ2000'], $res['DECJ2000'], $res['SimbadID'], $res['DRAJ2000'], $res['DDECJ2000']);
        @$morph = (isset($res["mainClass"]) and $res["mainClass"] != "") ? $res["mainClass"] . str_ireplace(";", "", $res['subClass']) : "na";
        $result = "<div id='infobox'>";
        $smplcheckbox = $this->setActionCell($id, "actionclassextra1");
        $result .= $smplcheckbox;
        $result .= "<p><a href='objectInfoPage.php?id=" . $id . "' target='_blank' >PNG" . $res[MAIN_DESIGNATION] . "</a></p>\n";
        $result .= "<h5>" . $res['Name'] . "</h5>\n";
        $result .= "<p>" . $res['PNstatus'] . " / " . $morph . "</p>\n";
        $result .= "<a href='" . $vizsimbLinks['simbad'] . "' target='_blank'><img src='images/simbad_70x35.png' /></a>\n";
        $result .= "<a href='" . $vizsimbLinks['vizier'] . "' target='_blank'><img src='images/vizier_40x35.png' /></a>\n";
        if ($hla) {
            $result .= "<a href='" . $vizsimbLinks['HLA'] . "' target='_blank'><img src='images/newHLA_logo.gif' /></a>\n";
        } else {
            $result .= "<img src='images/nonewHLA_logo.gif'/>\n";
        }
        $result .= "<p>" . $res['RAJ2000'] . " " . $res['DECJ2000'] . "<p>";
        $result .= "<p>" . $res['Glon'] . " " . $res['Glat'] . "<p>";
        $result .= "</div>";
        return $result;
    }

    public function makeGeneralDataTable($headers, $values, $tableID = False, $tableClass = False, $trclass = False, $tablename = False, $addbutton = False, $multiple = False, $background = "oddf") {

        $result = "<div id='fulldatatable' class='$background'>";
        $result .= "<div id='fulltablesheader'>";
        $result .= "<div id='fulltablestitle'>" . $tablename . "</div>";
        $result .= "<div id='fulltablesadd'>" . $addbutton . "</div>";
        $result .= "</div>";
        $result .= $values ? "<div id='fulltables'>" . $this->makeTable($values, $headers, $tableID, $tableClass, $trclass) . "</div>" : "";
        if ($multiple)
            $result .= "<script> $(document).ready(function() { $('#" . $tableID . "').DataTable({'ordering': false,});} );</script>";
        $result .= "</div>";

        return $result;
    }

    public function makeDivsWall($arraydivs, $user = "sys") {
        $itemno = 0;
        $result = "<div class='wall'><ul class='sortable'>";
        foreach ($arraydivs as $id => $div) {
            if ($div) {
                $smplcheckbox = $user == "ivan" ? $this->setActionCell($id, "actionclassextra2") : "";
                $itemno++;
                $result .= "<li id='item-" . $itemno . "'><div style='position:relative;'>" . $div . $smplcheckbox . "</div></li>";
            }
        }
        $result .= "</ul>";
        $result .= "<script>	
					var ul_sortable = $('.sortable'); //setup one variable for sortable holder that will be used in few places
					ul_sortable.sortable({
						revert: 100,
						placeholder: 'placeholder'
					});
					ul_sortable.disableSelection();</script>";
        return $result . "</div>";
    }

    /**
     * 
     * @param array $values array of values
     * @param array $headers array of header
     * @param string/boolean $id table id
     * @param string/boolen $class table class
     * @param array $trclass array of <tr> classes
     * @param array $thclass array of <th> classes
     * @param boolean $transposeheaders transpose headers
     * @return string
     */
    public function makeTable($values, $headers = False, $id = False, $class = False, $trclass = False, $thclass = False, $transposeheaders = False) {
        if (!$values or empty($values))
            return "";
        $id = $this->_idclass($id, "id");
        $class = $this->_idclass($class, "class");
        $result = "<table $id $class >\n";

        if ($headers and ! $transposeheaders) {
            $result .= "<thead><tr>\n";
            foreach ($headers as $hkey => $hval) {
                $classth = ($thclass and isset($thclass[$hkey])) ? $this->_idclass($thclass[$hkey], "class") : "";
                $result .= "<th " . $classth . ">" . $hval . "</th>\n";
            }
            $result .= "</tr>\n</thead>\n";
        }
        $result .= "<tbody>\n";
        $n = 0;
        foreach ($values as $key => $rows) {
            $currtrclass = ($trclass and isset($trclass[$key]) and trim(isset($trclass[$key])) != "") ? $trclass[$key] : "";
            $transhead = $this->_setTransHead($transposeheaders, $headers, $n);
            if (is_array($rows)) {
                $result .= "<tr " . $currtrclass . ">\n $transhead <td >\n" . implode("</td>\n<td >", $rows) . "</td>\n</tr>\n";
            } else {
                $result .= "<tr " . $currtrclass . ">\n $transhead <td >$rows</td>\n</tr>\n";
            }
            $n ++;
        }
        $result .= "</tbody>\n";
        $result .= "</table>\n";
        return $result;
    }

    public function makePushButton($inputname, $where, $value, $label) {
        return "<button name='$inputname' onclick='pushvalue(this,\"$where\")' \n"
                . " type='button' value=' " . $value . " '>$label</button>\n";
    }

    private function _setTransHead($flag, $head, $n) {
        if (!$flag or ! isset($head[$n]))
            return "";
        return "<th>" . $head[$n] . "</th>";
    }

    /**
     * 
     * @param array $result: resulting array passed by reference (input for $this->makeSelect)
     * @param string $val: value
     * @param string/boolean $id: label
     * @param boolean $checked: SELECTED field
     * 
     */
    public function makeOptionArray(&$result, $val, $id = False, $checked = False) {
        if (!isset($result) or ! is_array($result))
            $result = array();
        if ($val and trim($val) !== "")
            $result[$val] = array("id" => $id, "checked" => $checked);
    }

    /**
     * Creates drop down list from array of values
     * @param array $values: array of values( 0 => array(
     *          "val" => $value, 
     *          "id" => $id or (if false or not isset) $val, 
     *          "checked" => $checked {True or False (or not set)}
     * @param string $name: name of the drop down list
     * @return string: html drop down list
     */
    public function makeSelect($values, $name, $submitonchange = False, $placeholder = False) {
        $submitonchange = $submitonchange ? "onchange='" . $submitonchange . "'" : "";
        $result = "<select name='" . $name . "' " . $submitonchange . ">\n";
        if ($placeholder)
            $result .= "<option value='dummy' selected disabled>" . $placeholder . "</option>/n";
        foreach ($values as $value => $data) {
            $checked = (isset($data["checked"]) and $data["checked"]) ? "SELECTED" : "";
            $id = (isset($data["id"]) and $data["id"]) ? $data["id"] : $value;
            $result .= "<option value='" . $value . "' " . $checked . " >" . $id . "</option>\n";
        }
        $result .= "</select>";
        return $result;
    }

    public function makeLink($href, $id, $target) {
        return "<a href='" . $href . "' target='" . $target . "'>" . $id . "</a>";
    }

    /**
     * 
     * @param type string $formid:  id of the form 
     * @param type string $action: action link
     * @param type string $method: method (POST/GET)
     * @param type array $inputs: array of input fields
     * @param type string $button:
     * @param type string $divid: div id
     * @param type string $divclass: div class
     * @param type string $divtitle: div title
     * @param type string $message: message
     * @return type
     */
    public function makePopupForm($formid, $action, $method, $inputs, $button, $divid = False, $divclass = False, $divtitle = False, $message = False) {
        $divid = $this->_idclass($divid, "id");
        $divclass = $this->_idclass($divclass, "class");
        $divtitle = $this->_idclass($divtitle, "title");

        $result = "<div $divid $divclass $divtitle >";
        $result .= "<h4>" . $message . "</h4>";
        $result .= "<form id ='" . $formid . "' action='" . $action . "' method = '" . $method . "'>";
        $result .= $inputs;
        $result .= "</form></div>";
        $result .= $this->makeButton($button);
        return $result;
    }

    public function makeButton($button) {
        if (!$button)
            die("missing button...");
        return "<button id='" . $button['id'] . "' value='" . $button['value'] . "'>" . $button['message'] . "</button>";
    }

    public function makeInputLine($inputarray, $type, $revereselabel = False) {
        $line = "<input name='" . $inputarray['name'] . "'  type='" . $inputarray['type'] . "' value='" . $inputarray['value'] . "'>";
        if ($type != "table")
            return $revereselabel ? $line . $inputarray['label'] : $inputarray['label'] . $line;
        return $revereselabel ? array("field" => $line, "label" => $inputarray['label']) : array("label" => $inputarray['label'], "field" => $line);
    }

    public function makeRadiocheckBox($values, $divid = False, $divclass = False) {
        $divid = $this->_idclass($divid, "id");
        $divclass = $this->_idclass($divclass, "class");
        $result = "<div $divid $divclass >";
        foreach ($values as $val) {
            $result .= "<label><input type='" . $val['type'] . "' name = '" . $val['name'] .
                    "' value='" . $val['value'] . "'>" . $val['label'] . "</label>";
        }
        $result .= "</div>";
        return $result;
    }

    private function _unorderedList($list) {
        return "<ul>\n\t<li>" . implode("</li>\n\t<li>", $list) . "</li>\n</ul>\n";
    }

    private function _checkallBox($flag) {
        $checkall = $flag ? "<input type='checkbox' class='checkall'>Check/Uncheck all\n" : "";
        return $checkall;
    }

    private function _idclass($id, $type) {
        $result = $id ? $type . "='" . $id . "'" : "";
        return $result;
    }

    public function setActionCell($id, $extraclass = "") {
        $result = "<div class='actionclass $extraclass'>";
        $result .= "<input type='checkbox' name='selectaction' class='checkaction' value='" . $id . "' />";
        $result .= "</div>";
        return $result;
    }

    public function composeVizier($RA, $DEC, $simbadID = False, $DRA = False, $DDEC = False, $radius = 120, $truePN = False) {
        //$simbadUrl = "http://simbad.harvard.edu/simbad/";
        $simbadUrl = "http://simbad.u-strasbg.fr/simbad/";
        //$vizierUrl = "http://vizier.cfa.harvard.edu/viz-bin/VizieR";
        $vizierUrl = "http://vizier.u-strasbg.fr/cgi-bin/VizieR";

        if ($DEC{0} != '-')
            $DEC = '+' . $DEC;
        $coords = $RA . " " . $DEC;
        $HLAcoords = urlencode($coords);
        
        $radius = ($radius == "" or $radius < 120) ? 2 : round($radius / 60); 

        if ($simbadID) {
            $simbadLink = $simbadUrl . "sim-id?Ident=" . urlencode($simbadID) . "&NbIdent=1&Radius=1&Radius.unit=arcsec&submit=submit+id";
        } else {
            $simbadLink = $simbadUrl . "sim-coo?output.format=HTML&Coord=$coords&Radius=$radius&Radius.unit=arcsec";
        }
        
        $truePN = $truePN ? "&-kw=Planetary_Nebulae" : "";

        return array(
            'vizier' => $vizierUrl . "?-c.r=" . $radius."&-c=" . $coords . $truePN,
            'simbad' => $simbadLink,
            'HLA' => "http://hla.stsci.edu/hlaview.html#Inventory|filterText%3D%24filterTypes%3D|query_string=" . $HLAcoords . "&posfilename=&poslocalname=&posfilecount=&listdelimiter=whitespace&listformat=degrees&RA=" . $DRA . "&Dec=" . $DDEC . "&Radius=0.&inst-control=all&inst=ACS&inst=ACSGrism&inst=WFC3&inst=WFPC2&inst=NICMOS&inst=NICGRISM&inst=COS&inst=WFPC2-PC&inst=STIS&inst=FOS&inst=GHRS&imagetype=best&prop_id=&spectral_elt=&proprietary=both&preview=1&output_size=256&cutout_size=12.8|ra=&dec=&sr=&level=&image=&inst=ACS%2CACSGrism%2CWFC3%2CWFPC2%2CNICMOS%2CNICGRISM%2CCOS%2CWFPC2-PC%2CSTIS%2CFOS%2CGHRS&ds="
        );
    }
    


    /*     * ******************************************************************************* */
    /*     * ********************               SPECIAL      ******************************* */
    /*     * ******************************************************************************* */

    /*     * ******************************************************************************* */
    /*     * ********************        class.PlotData.php  ******************************* */
    /*     * ******************************************************************************* */

    /**
     * Construct info for plotDataPage.php plot selection
     * 
     * @param string $xvar
     * @param string $yvar
     * @param string $plotdesc
     * @return string
     */
    public function specplotdatainfo($xvar, $yvar, $plotdesc) {
        return "<h5> x:" . $xvar . "</h5>" .
                "<h5> y:" . $yvar . "</h5>" .
                "<p style='text-align:left;'>" . $plotdesc . "</p>";
    }

    /*     * ******************************************************************************* */
    /*     * *****************  class.GetObjectElements.php  ******************************* */
    /*     * ******************************************************************************* */

    /*
      public function setDefaultLinesForm () {

      $form = "<form id ='emmlines' action='".$this->_referer."id=".$this->_objectId."' method = 'POST'>
      <div id='linelabels'>
      <table id='choiceLabels' class='".$this->_cssclasses["emmlines"]."'>
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
     */
}
