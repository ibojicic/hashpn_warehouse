<?php

// TODO CHECK VARIABLES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING
// TODO CORRECT BUG NO DISPLAY FOR SPECIAL CHAR IN THE USERNAME

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
class StatsPages extends SetMainObjects {

    private $_htmlconstructor;
    private $_users;
    private $_userstats = "full";
    private $_sqlbin;
    public $mindate = False;
    public $maxdate = False;
    public $binsize = "month";
    public $binlist = False;

    public function __construct($myConfig, $userid, $isAdmin, $input = False) {
        parent::__construct($myConfig, $userid, $isAdmin);

        $this->_htmlconstructor = new HtmlConstructor();
        $this->_users = $this->_readtables->readUserData(True);
        if ($input)
            $this->_parseInput($input);
        $this->_getDatesRange();
        $this->_setBinSize();
    }


    private function _getDatesRange() {
        $result = $this->_mysqldriver->selectquery("SELECT min(DATE(`date`)) as 'mindate',max(DATE(`date`)) as 'maxdate' "
                . "FROM `MainPNUsers`.`accesslog`;");
        if (!$this->mindate)
            $this->mindate = $result[0]['mindate'];
        if (!$this->maxdate)
            $this->maxdate = $result[0]['maxdate'];
    }

    private function _parseInput($input) {
        $this->_userstats = isset($input["userstats"]) ? urldecode($input["userstats"]) : "full";
        $this->mindate = isset($input["datestart"]) ? $input["datestart"] : False;
        $this->maxdate = isset($input["dateend"]) ? $input["dateend"] : False;
        $this->binsize = isset($input["binsize"]) ? $input["binsize"] : False;
    }

    /**
     * make option tag for selecting user
     * @return string
     */
    public function setLinks() {
        $result = array();
        $keys = array_keys($this->_users);
        array_unshift($keys, "full");
        foreach ($keys as $username) 
            $this->_htmlconstructor->makeOptionArray($result, $username, False, $username == $this->_userstats);
        return $this->_htmlconstructor->makeSelect($result, "userstats");
    }
    
    public function plotAccessLog() {
        $pages = $this->_accessPages();
        $result = $this->_fillPages();
        $plots = $this->_createPlots($result);
        $plots["placeholders"] = $this->_arangePlaceholders($pages, $this->_userstats, $plots["divs"]);
        return $plots;
    }

    private function _arangePlaceholders($pages, $users, $placeholders) {
        $result = array();
        $users = array($users);
        foreach ($users as $user) {
            $tmp = array();
            foreach ($pages as $key => $page) {
                if (isset($placeholders["pl_" . $user . $key])) {
                    array_push($tmp, $placeholders["pl_" . $user . $key]);
                }
            }
            $result[$user] = $tmp;
        }
        return $result;
    }

    private function _setBinSize() {
        $result = "";
        $list = array();
        $bins = array(
            "year" => "YEAR(`date`)",
            "month" => "MONTH(`date`)",
            "week" => "WEEK(`date`)",
            "day" => "DAY(`date`)");
        $flag = True;
        foreach ($bins as $binkey => $binval) {
            if ($flag)
                $result .= "," . $binval;
            $binselected = $binkey == $this->binsize;
            $this->_htmlconstructor->makeOptionArray($list, $binkey, False, $binselected);
            if ($this->binsize and $this->binsize == $binkey)
                $flag = False;
        }
        $this->_sqlbin = $result;
        $this->binlist = $this->_htmlconstructor->makeSelect($list, "binsize");
    }

    private function _fillPages() {
        $tmpresult = array();
        if ($this->_userstats == "full") {
            $sqlfull = $this->_mysqldriver->selectquery("SELECT DATE(`date`) as 'date',`page`,COUNT(`page`) as 'counts' "
                    . "FROM MainPNUsers.accesslog WHERE (`date` BETWEEN '" . $this->mindate . "' AND '" . $this->maxdate . "') "
                    . "GROUP BY `page` " . $this->_sqlbin . ";");
        } else {
            $sqlfull = $this->_mysqldriver->selectquery("SELECT DATE(`date`) as 'date',`page`,COUNT(`page`) as 'counts',`user`, COUNT(`user`) "
                    . "FROM MainPNUsers.accesslog WHERE `user` = '" . $this->_userstats . "' "
                    . " AND (`date` BETWEEN '" . $this->mindate . "' AND '" . $this->maxdate . "') "
                    . "GROUP BY `user`,`page` " . $this->_sqlbin . ";");
        }
        foreach ($sqlfull as $data) {
            $key = $this->_userstats . $data['page'];
            $label = $this->_userstats . " " . $data['page'];
            if (!isset($tmpresult[$key]))
                $tmpresult[$key] = array("label" => $label, "data" => array());
            array_push($tmpresult[$key]["data"], array(floatval(strtotime($data["date"]) * 1000), floatval($data['counts']), "-"));
        }
        $result = $tmpresult;
        foreach ($tmpresult as $key => $data) {
            if (count($data["data"]) < 2)
                unset($result[$key]);
        }
        return $result;
    }

    private function _accessPages() {
        $result = array("full" => False);
        $res = $this->_mysqldriver->selectquery("SELECT DISTINCT(`page`) as 'pages' FROM `" . USERS_DB . "`.accesslog;");
        foreach ($res as $val)
            $result[$val["pages"]] = $val["pages"];
        return $result;
    }

    private function _createPlots($data) {
        $line = "";
        $plot = False;
        $divs = array();
        $inputs = array();
        foreach ($data as $pagename => $pagedata) {
            if (!empty($pagedata)) {
                $plot = True;
                @$spline = json_encode(array($pagedata));
                $filename = writeSpectraJson($spline, $this->_linkPlots . $pagename . ".dat", True);
                $placeholder = "pl_" . $pagename;
                array_push($inputs, array("filename" => $filename, "plholder" => $placeholder));
                $divs[$placeholder] = "<div id='" . $placeholder . "' class='stats_plot'></div>";
            }
        }
        if ($plot) {            
            $inps = json_encode($inputs);
            $line = "var inputs = " . $inps . ";\n";
            $plot = "<script type='text/javascript' >$line</script><script type='text/javascript' src='javascript/statsPlot.js'></script>\n";
        }

        return array("plots" => $plot, "divs" => $divs);
    }

    public function getQuedObjects() {
        $sql_jobs = "SELECT `cronScript`, COUNT(`cronScript`) as 'count' FROM MainPNUsers.cronJobs WHERE `date_exec` IS NULL GROUP BY `cronScript`;";
        $jobs_que = $this->_mysqldriver->selectquery($sql_jobs);
        $headers = array("job", "no in que");
        $jobs_que = $jobs_que ? $jobs_que : array();
        $jobstable = $this->_htmlconstructor->makeTable($jobs_que, $headers, False, $this->_cssclasses["quejobs"]); //, $headers);
        $sql_lastinque = "SELECT `user`,CONCAT(\"<a href=\'objectInfoPage.php?id=\",`" . MAIN_ID . "`,\"' target='_blank'>\",`" . MAIN_ID . "`,\"</a>\") as 'object',`cronScript`,`date_subm`,`date_start` FROM MainPNUsers.cronJobs WHERE `date_start` IS NOT NULL AND `date_exec` IS NULL";
        $last = $this->_mysqldriver->selectquery($sql_lastinque);
        $last = $last ? $last[0] : array();
        $lasttable = $this->_htmlconstructor->makeTable($last, array_keys($last), False, $this->_cssclasses["quejobs"], False, False, True);
        return array("jobs" => $jobstable, "last" => $lasttable);
    }

}
