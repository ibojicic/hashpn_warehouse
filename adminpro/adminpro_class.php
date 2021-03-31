<?php

class protect {
    /*     * *
     * *** @class: protect
     * *** @project: AdminPro Class
     * *** @version: 1.3;
     * *** @author: Giorgos Tsiledakis;
     * *** @date: 2004-09-04;
     * *** @license: GNU GENERAL PUBLIC LICENSE;
     * ***
     * *** This class protects your php pages using a MySQL Database and the PHP session functions
     * *** Please read the readme.html file (included in this package) first
     * ** */

    var $errorMsg = False;
    var $showPage = False;
    public $loginMessage = False;
    public $loginInput = "login";

    /*
     * *** @function: protect; Class Constructor
     * *** @include: the class configuration file: adminpro_config.php
     * *** @include: the class to access MySQL: mysql_dialog.php
     * *** if some var is passed, it will be an administrator page
     * *** makes the configuration vars public, starts a session and goes to checkSession()
     */

    function protect($isAdmin = false, $userGroup = false, $registration = False) {
        include("adminpro/adminpro_config.php");
        include("adminpro/mysql_dialog.php");
        $this->accNoCookies = $globalConfig['acceptNoCookies'];
        $this->dbhost = $globalConfig['dbhost'];
        $this->dbuser = $globalConfig['dbuser'];
        $this->dbpass = $globalConfig['dbpass'];
        $this->dbase = $globalConfig['dbase'];
        $this->tbl = $globalConfig['tbl'];
        $this->tblID = $globalConfig['tblID'];
        $this->tblUserName = $globalConfig['tblUserName'];
        $this->tblUserPass = $globalConfig['tblUserPass'];
        $this->tblIsAdmin = $globalConfig['tblIsAdmin'];
        $this->tblEmail = $globalConfig['tblEmail'];
        $this->tblUserGroup = $globalConfig['tblUserGroup'];
        $this->tblSessionID = $globalConfig['tblSessionID'];
        $this->tblLastLog = $globalConfig['tblLastLog'];
        $this->tblUserRemark = $globalConfig['tblUserRemark'];
        $this->inactiveDay = $globalConfig['inactiveDay'];
        $this->loginUrl = $globalConfig['loginUrl'];
        $this->logoutUrl = $globalConfig['logoutUrl'];
        $this->enblRemember = $globalConfig['enblRemember'];
        $this->cookieRemName = $globalConfig['cookieRemName'];
        $this->cookieRemPass = $globalConfig['cookieRemPass'];
        $this->cookieExpDays = $globalConfig['cookieExpDays'];
        $this->isMd5 = $globalConfig['isMd5'];
        $this->errorPageTitle = $globalConfig['errorPageTitle'];
        $this->errorPageH1 = $globalConfig['errorPageH1'];
        $this->errorPageLink = $globalConfig['errorPageLink'];
        //$this->errorNoCookies = $globalConfig['errorNoCookies'];
        $this->errorNoLogin = $globalConfig['errorNoLogin'];
        $this->errorInvalid = $globalConfig['errorInvalid'];
        $this->errorDelay = $globalConfig['errorDelay'];
        $this->errorNoAdmin = $globalConfig['errorNoAdmin'];
        $this->errorNoGroup = $globalConfig['errorNoGroup'];
        $this->errorCssUrl = $globalConfig['errorCssUrl'];
        $this->errorCharset = $globalConfig['errorCharset'];
        session_start();
        $this->isAdmin = $isAdmin;
        $this->userGroup = $userGroup;
        $checkreg = $this->_checkReg();
        if ($registration or $checkreg) {
            switch ($checkreg) {
                case "finreg":
                    $this->_registration($_POST);
                    break;
                case "activreg":
                    $this->_activation($_GET);
                    $this->errorMsg = "Email is confirmed. Please login with your details. Welcome!";
                    break;
                case "logout":
                    $this->checkSession();
                    break;
                case "changedetails":
                    $this->_changedetails($_POST);
                    break;
                case "changepass":
                    $this->_changepass($_POST);
                    break;
            }
        } else
            $this->checkSession();
    }

    /*
     * *** @function: checkSession(called by class constructor or by checkLogin)
     * *** calls hasCookie() and checks if the $globalConfig['acceptNoCookies'] is true;
     * *** if no cookie was set and we do not accept that -> makes an error message; else:
     * *** checks if a session is active: if not -> checkPost() (checks if some post was sent);
     * *** if session exists, it checks if some $_POST['action']==logout -> makeLogout();
     * *** if not -> checkTime();
     */

    function checkSession() {
        if (!$this->hasCookie() && $this->accNoCookies && (@$_POST['action'] != "login" || @$_GET)) {
            $this->errorMsg = $this->errorNoCookies;
            $this->makeErrorHtml();
        } else {
            if (!@$_SESSION['userID'] || !@$_SESSION['sessionID']) {
                $this->checkRemember();
            } elseif (@$_SESSION['userID'] && @$_SESSION['sessionID']) {
                if (@$_GET['action'] == "logout") {
                    $this->makeLogout();
                } else {
                    $this->checkTime();
                }
            }
        }
    }

    /*
     * *** @function: hasCookie(called by checkSession())
     * *** checks if the client's browser has accepted the cookie of the active session;
     * *** if yes, it returns true;
     * *** if not -> it returns false;
     */

    function hasCookie() {
        if (isset($_COOKIE[session_name()])) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * *** @function: makeLogout(called by checkSession())
     * *** sets MySQL Time Field=0 and SessionID Field='';
     * *** closes the session and goes to logout page, if some $_POST['action']="logout" was sent;
     */

    function makeLogout() {
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $SQL = "UPDATE " . $this->tbl . " SET ";
        $SQL.=$this->tblLastLog . "= 0, ";
        $SQL.=$this->tblSessionID . "='' ";
        $SQL.="WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
        $SQL.=" AND `activationKey` IS NULL;";
        $db->speak($SQL);
        if ($this->enblRemember && isset($_COOKIE[$this->cookieRemName]) && isset($_COOKIE[$this->cookieRemPass])) {
            setcookie($this->cookieRemName, $_COOKIE[$this->cookieRemName], time());
            setcookie($this->cookieRemPass, $_COOKIE[$this->cookieRemPass], time());
        }
        session_destroy();
        header("Location: " . $this->logoutUrl);
    }

    /*
     * *** @function: checkTime(called by checkSession())
     * *** gets the time of the last page access from the database;
     * *** compares this time with the time now. If the elapsed days>inactiveDay (configuration);
     * *** or the session ID has changed (by some second login) -> it creates an error page
     * *** if not -> sets the time now in the MySQL Time Field and goes to checkAdmin();
     */

    function checkTime() {
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $SQL = "SELECT UNIX_TIMESTAMP(" . $this->tblLastLog . ") as lastLog FROM " . $this->tbl;
        $SQL.=" WHERE " . $this->tblID . "=" . $_SESSION['userID'] . " AND " . $this->tblSessionID . "='" . $_SESSION['sessionID'] . "'";
        $SQL.=" AND `activationKey` IS NULL;";
        $db->speak($SQL);
        $data = $db->listen();
        $nowtime = time();
        $inactiveSec = $nowtime - $data['lastLog'];
        if ($inactiveSec / 86400 > $this->inactiveDay) {
            $this->errorMsg = $this->errorDelay;
            $this->makeErrorHtml();
        } else {
            $SQ = "UPDATE " . $this->tbl . " SET ";
            $SQ.=$this->tblLastLog . "= now() ";
            $SQ.="WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQ);
            $this->checkAdmin();
        }
    }

    /*
     * *** @function: checkAdmin (called by checkTime())
     * *** checks if the page is an administrator page. If not -> checkGroup();
     * *** if yes -> gets the value from the MySQL Admin Field (1=admin,-1=normal user);
     * *** if the value==1 -> showPage() else -> it creates an error page;
     */

    function checkAdmin() {
        if ($this->isAdmin != "1") {
            $this->checkGroup();
        } else {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT " . $this->tblIsAdmin . " as isAdmin FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblID . "=" . $_SESSION['userID'] . " AND ";
            $SQL.=$this->tblSessionID . "='" . $_SESSION['sessionID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();
            if ($data['isAdmin'] == -1) {
                $this->errorMsg = $this->errorNoAdmin;
                $this->makeErrorHtml();
            } elseif ($data['isAdmin'] == 1) {
                $this->showPage();
            }
        }
    }

    /*
     * *** @function: checkGroup (called by checkAdmin())
     * *** checks if the page is belongs only to some user group. If not -> showPage();
     * *** if yes -> gets the user's group number from the MySQL User Group Field;
     * *** if the group is the same-> showPage() else -> it creates an error page;
     */

    function checkGroup() {
        if (!$this->userGroup) {
            $this->showPage();
        } else {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT " . $this->tblUserGroup . " as userGroup, ";
            $SQL.=$this->tblIsAdmin . " as isAdmin";
            $SQL.=" FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblID . "=" . $_SESSION['userID'] . " AND ";
            $SQL.=$this->tblSessionID . "='" . $_SESSION['sessionID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();
            if ($data['userGroup'] != $this->userGroup && $data['isAdmin'] != 1) {
                $this->errorMsg = $this->errorNoGroup;
                $this->makeErrorHtml();
            } else {
                $this->showPage();
            }
        }
    }

    /*
     * *** @function: checkRemember (called by checkSession() if no session is active)
     * *** checks if some username + password cookies were set and if we have this function enabled;
     * *** If not -> checkPost()
     * *** if yes -> it updates the MySQL table, registers the Session vars -> checkSession()
     */

    function checkRemember() {

        if ($this->enblRemember && isset($_COOKIE[$this->cookieRemName]) && isset($_COOKIE[$this->cookieRemPass])) {

            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT " . $this->tblID . " as ID, ";
            $SQL.=$this->tblUserName . " as userName, ";
            $SQL.=$this->tblUserPass . " as userPass ";
            $SQL.="FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblUserName . "='" . $_COOKIE[$this->cookieRemName] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();

            if ($this->isMd5 != "1" && $data['userPass']) {
                $data['userPass'] = md5($data['userPass']);
            }

            if ($_COOKIE[$this->cookieRemName] == $data['userName'] && $_COOKIE[$this->cookieRemPass] == $data['userPass']) {
                $SQL = "UPDATE " . $this->tbl . " SET ";
                $SQL.=$this->tblLastLog . "= now(), ";
                $SQL.=$this->tblSessionID . "='" . session_id() . "' ";
                $SQL.="WHERE (" . $this->tblID . "='" . $data['ID'] . "')";
                $SQL.=" AND `activationKey` IS NULL;";
                $db->speak($SQL);
                $_SESSION['sessionID'] = session_id();
                $_SESSION['userID'] = $data['ID'];
                setcookie($this->cookieRemName, $_COOKIE[$this->cookieRemName], time() + (60 * 60 * 24 * $this->cookieExpDays));
                setcookie($this->cookieRemPass, $_COOKIE[$this->cookieRemPass], time() + (60 * 60 * 24 * $this->cookieExpDays));
                $this->checkSession();
            }
        } else {

            $this->checkPost();
        }
    }

    /*
     * *** @function: checkPost (called by checkRemember())
     * *** checks if some $_POST was sent. If not -> it creates an error page
     * *** if yes -> checkLogin()
     */

    function checkPost() {
        if (!$_POST) {
            $this->errorMsg = ""; //$this->errorNoLogin;
            $this->makeErrorHtml();
        } else {
            $this->checkLogin();
        }
    }

    /*
     * *** @function: checkLogin (called by checkPost())
     * *** checks if some $_POST['userName'] and $_POST['userPass'] and $_POST['action']="login" was sent;
     * *** If not -> it creates an error page;
     * *** if yes -> it compares the $_POST with the username and password on database;
     * *** if all ok -> showPage() else -> it creates an error page;
     */

    function checkLogin() {
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $action = @$_POST['action'];
        $userName = @$_POST['userName'];
        if ($this->isMd5 == "1") {
            $userPass = md5(@$_POST['userPass']);
        } else {
            $userPass = @$_POST['userPass'];
        }
        $SQL = "SELECT " . $this->tblID . " as ID, ";
        $SQL.=$this->tblUserName . " as userName, ";
        $SQL.=$this->tblUserPass . " as userPass ";
        $SQL.="FROM " . $this->tbl;
        $SQL.=" WHERE " . $this->tblUserName . "='" . $userName . "' ";
        $SQL.="and " . $this->tblUserPass . "='" . $userPass . "'";
        $SQL.=" AND `activationKey` IS NULL;";
        $db->speak($SQL);
        $data = $db->listen();
        if ($action == "login" && ($userName || $userPass)) {
            if ($userName == $data['userName'] && $userPass == $data['userPass']) {
                $SQL = "UPDATE " . $this->tbl . " SET ";
                $SQL.=$this->tblLastLog . "= now(), ";
                $SQL.=$this->tblSessionID . "='" . session_id() . "' ";
                $SQL.="WHERE (" . $this->tblID . "='" . $data['ID'] . "')";
                $SQL.=" AND `activationKey` IS NULL;";
                $db->speak($SQL);
                $_SESSION['sessionID'] = session_id();
                $_SESSION['userID'] = $data['ID'];
                if ($this->enblRemember && @$_POST['userRemember'] == "yes") {
                    setcookie($this->cookieRemName, @$_POST['userName'], time() + (60 * 60 * 24 * $this->cookieExpDays));
                    setcookie($this->cookieRemPass, md5(@$_POST['userPass']), time() + (60 * 60 * 24 * $this->cookieExpDays));
                }
                $this->checkSession();
            }
        }
        if ($action == "login") {
            if ($userName != $data['userName'] || $userPass != $data['userPass'] || $userName == "" || $userPass == "") {
                $this->errorMsg = $this->errorInvalid;
                $this->makeErrorHtml();
            }
        }
        if ($action != "login") {
            $this->errorMsg = $this->errorInvalid;
            $this->makeErrorHtml();
        }
    }

    /*
     * *** @function: makeErrorHtml
     * *** creates the error html page, if something went wrong;
     * *** sets MySQL Time Field=0 and SessionID Field='' and closes the session;
     */

    function makeErrorHtml() {
        if ($_SESSION) {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "UPDATE " . $this->tbl . " SET ";
            $SQL.=$this->tblLastLog . "= 0, ";
            $SQL.=$this->tblSessionID . "='' ";
            $SQL.="WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
        }
        if ($this->enblRemember && isset($_COOKIE[$this->cookieRemName]) && isset($_COOKIE[$this->cookieRemPass])) {
            setcookie($this->cookieRemName, $_COOKIE[$this->cookieRemName], time());
            setcookie($this->cookieRemPass, $_COOKIE[$this->cookieRemPass], time());
        }
        session_destroy();
        $out = "<html>\n<head><title>" . $this->errorPageTitle . "</title>\n";
        if ($this->errorCssUrl != "") {
            $out.="<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->errorCssUrl . "\">\n";
        }
        if ($this->errorCharset != "") {
            $out.="<meta http-equiv=\"content-type\" content=\"text/html;charset=" . $this->errorCharset . "\">\n";
        }
        $out.="</head>\n<body>\n";
        $out.="<h1>" . $this->errorPageH1 . "</h1>\n";
        $out.="<p>" . $this->errorMsg . "</p>\n";
        $out.="<p><a href=" . $this->loginUrl . ">" . $this->errorPageLink . "</a></p>\n";
        $out.="</body>\n</html>";
        //print $out;
    }

    /*
     * *** @function: showPage
     * *** makes the public var $showPage true, if everything was ok;
     */

    function showPage() {
        $this->showPage = true;
    }

    /*
     * *** @function: getUser TODO
     * *** call it in your protected page, if you would like to display the username;
     */

    function getUserData($inside = False) {

        if ($this->showPage or $inside) {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT * ";
            $SQL.=" FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();
            return $data;
        } else {
            return false;
        }
    }

    /*
     * *** @function: getUser
     * *** call it in your protected page, if you would like to display the username;
     */

    function getUser($inside = False) {

        if ($this->showPage or $inside) {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT " . $this->tblUserName . " as userName";
            $SQL.=" FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();
            return $data['userName'];
        } else {
            return false;
        }
    }

    /*
     * *** @function: userStatus
     * *** call it in your protected page, if you would like to display the user status;
     */

    function userStatus() {

        if ($this->showPage) {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT " . $this->tblIsAdmin . " as userStatus";
            $SQL.=" FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();
            return $data['userStatus'];
        } else {
            return false;
        }
    }

    /*
     * *** @function: groupStatus
     * *** call it in your protected page, if you would like to display the user status;
     */

    function groupStatus() {

        if ($this->showPage) {
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "SELECT " . $this->tblUserGroup . " as userGroup";
            $SQL.=" FROM " . $this->tbl;
            $SQL.=" WHERE " . $this->tblID . "='" . $_SESSION['userID'] . "'";
            $SQL.=" AND `activationKey` IS NULL;";
            $db->speak($SQL);
            $data = $db->listen();
            return $data['userGroup'];
        } else {
            return false;
        }
    }

    private function _registration($post) {
        $this->loginMessage = $this->_ValidateInputs($post);
        if (!$this->loginMessage) {
            $activation = md5(uniqid(rand(), true));
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            $SQL = "INSERT INTO `" . $this->tbl . "` (`userName`,`userPass`,`affiliation`,`email`,`activationKey`) VALUES
					('" . $post['userName'] . "',MD5('" . $post['userPass'] . "'),'" . $post['Iinstitution'] . "','" . $post['Eemail'] . "','" . $activation . "');";
            $db->speak($SQL);
            if ($this->_emailActivation($activation, $post['Eemail'])) {
                $this->errorMsg = "Registration was succesfull. Please check your email to procede!";
            }
        }
    }

    private function _changedetails($post) {
        $currUser = $this->getUser(True);
        
        if (isset($post["userPass"]) and trim($post["userPass"]) != ""
                and ! $this->_passwordValidation($post['userPass'], $post['repuserPass'])) {            
            $db = new mysql_dialog();
            $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
            
            $SQL = "UPDATE `" . $this->tbl . "` SET  
                            `userPass` =  MD5('" . $post['userPass'] . "')
                            WHERE `userName` = '$currUser' AND `email` = '".$post["Eemail"]."';";
            if ($db->speak($SQL)) {
                $message = " Your password was succesfuly changed.";
                mail($post["Eemail"], 'Password change', $message, "From: " . SITETITLE);
            }
        }
    }
    


    private function _changepass($post) {
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $SQL = "SELECT `userName` FROM `" . $this->tbl . "` WHERE `email` = '" . $post["Eemail"] . "';";
        $db->speak($SQL);
        $data = $db->listen();
        if ($data) {
            $newpass = genRandomString();
            $SQL = "UPDATE `" . $this->tbl . "` SET  
                            `userPass` =  MD5('" . $newpass . "')
                            WHERE `userName` = '" . $data["userName"] . "';";
            $db->speak($SQL);
            $message = " We received a request to reset the password associated with this e-mail address.\n "
                    . "The temporary password for user:".$data["userName"]." is:" . $newpass . "\nPlease change it asap.";
            mail($post["Eemail"], 'Password change', $message, "From: " . SITETITLE);
            $this->errorMsg = "We sent you a temporary password. Please use it to log in and update your password.";
        }
    }

    private function _emailActivation($activation, $email) {

        $message = " To activate your account, please click on this link:\n\n";
        $message .= WEBSITE_URL . "index.php?action=activreg&email=$email&key=$activation";
        $sendmail = mail($email, 'Registration Confirmation', $message, "From: " . SITETITLE);
        return $sendmail;
    }

    private function _ValidateInputs($post) {
        if (!isset($post['Eemail']))
            return "You must enter a valid email address...";
        $emailok = $this->_emailValidation($post['Eemail']);
        if ($emailok)
            return $emailok;
        if (!isset($post['userPass']) or ! isset($post['repuserPass']))
            return "You must enter a password...";
        $passok = $this->_passwordValidation($post['userPass'], $post['repuserPass']);
        if ($passok)
            return $passok;
        $username = $this->_usernameValidation($post['userName']);
        if ($username)
            return $username;
        return False;
    }

    private function _emailValidation($email, $skipuser = False) {
        $skipuser = $skipuser ? " AND `userName` <> '" . $skipuser . "' " : "";
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $SQL = "SELECT * FROM `" . $this->tbl . "`  WHERE `" . $this->tblEmail . "`='" . $email . "' ";
        $SQL.=" AND `activationKey` IS NULL;" . $skipuser;
        $db->speak($SQL);
        if ($db->listen())
            return "Email exists...\n";
        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
            return "Email is not valid...\n";
        $SQL = "DELETE FROM `" . $this->tbl . "`  WHERE `" . $this->tblEmail . "`='" . $email . "' ";
        $SQL.=" AND `activationKey` IS NOT NULL;";
        $db->speak($SQL);
        return False;
    }

    private function _usernameValidation($username) {
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $SQL = "SELECT * FROM `" . $this->tbl . "`  WHERE `" . $this->tblUserName . "`='" . $username . "' ";
        $SQL.=" AND `activationKey` IS NULL;";
        $db->speak($SQL);
        if ($db->listen())
            return "Username already taken...\n";
        return False;
    }

    private function _passwordValidation($password, $reppassword) {
        if ($password != $reppassword)
            return "Passwords do not match...\n";
        return False;
    }

    private function _checkReg() {
        if (!isset($_GET['action']))
            return False;
        return $_GET['action'];
    }

    private function _activation($get) {
        $db = new mysql_dialog();
        $db->connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbase);
        $SQL = "SELECT * FROM `" . $this->tbl . "`
				WHERE `" . $this->tblEmail . "`='" . $get['email'] . "'
				AND `activationKey` = '" . $get['key'] . "';";
        $db->speak($SQL);
        if ($db->listen()) {
            $SQL = "UPDATE `" . $this->tbl . "`
				SET `activationKey` = NULL
				WHERE `" . $this->tblEmail . "`='" . $get['email'] . "'
				AND `activationKey` = '" . $get['key'] . "';";
            $db->speak($SQL);
            if ($db->listen())
                return True;
        }
        return False;
    }

}

?>
