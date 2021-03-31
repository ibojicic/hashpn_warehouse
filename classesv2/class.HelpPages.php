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
class HelpPages extends SetMainObjects {

	public function __construct($myConfig, $userid, $isAdmin)
	{
		parent::__construct($myConfig, $userid, $isAdmin);
	}
	
	public function link($target)
	{
		$result = "<button  class='helpbutton topbutton'>?</div>";
		return $result;
	}
	
}
