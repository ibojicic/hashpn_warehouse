<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MakeHelpPages
 *
 * @author ivan
 */
class MakeHelpPages extends SetMainObjects{
	
	public function __construct($myConfig, $userid, $isAdmin,$input)
	{
		parent::__construct($myConfig, $userid, $isAdmin);
		
		$this->_htmlconstructor = new HtmlConstructor();
		if (isset($input) and !empty($input) and $input) {
			$this->_addNewInput($input);
		}

	}
	
	public function prepareHelpPages()
	{
		$result = "";
		$fromdb = $this->_readtables->readHelpPages();
		foreach ($fromdb as $topic => $page) {
			$result .= "<fieldset id='$topic'>\n";
			$result .= "<a name='$topic'></a>\n";
			$result .= "<h1>" . $page["title"] . "</h1>\n";
			$result .= $page["text"] . "\n";
			$result .= "</fieldset>\n";
			if ($this->_isAdmin) $result .= $this->_makeEditHelp ($topic, $page);

		}
		
		return $result;
	}
	
	private function _makeEditHelp($topic,$page)
	{
		$input = "<textarea cols='60' rows='15' name='text'>".$page["text"]."</textarea>\n";
		$input .= "<input type='hidden' name='topic' value='$topic'/>\n";
		$button = array("id" => "editdata","value" => "help_".$topic,"message" => "Edit");

		$result = $this->_htmlconstructor->makePopupForm("forinpdialog", 
						"dbHelpPage.php","POST", $input, $button,"help_".$topic,"inpdialog",$topic,"something");
		return $result;
		
	}
	
	private function _addNewInput($input)
	{
		$topic = $input["topic"];
		$text = $input["text"];
		$text = mysql_escape_string($text);
		$sql = "UPDATE `".MAIN_DB."`.`helppages` SET `text` = '" . $text . "', `user` = '" . $this->_userId ."', `date` = NOW()"
			. " WHERE `topic` = '".$topic."';";
		$this->_mysqldriver->query($sql);
	}
	
}
