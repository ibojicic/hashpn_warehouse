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
class Logger extends SetMainObjects{
	private $_page;
	
	public function __construct($myConfig, $userid, $isAdmin,$page)
	{
		parent::__construct($myConfig, $userid, $isAdmin);
		
		$this->_setPage($page);
			
	}
	
	private function _setPage($page) {
		$this->_page = $page;
	}
		
	
	public function addLog($idPNMain = "NULL")
	{
		$sql = "INSERT INTO `MainPNUsers`.`accesslog`
				(
				`user`,
				`page`,
				`".MAIN_ID."`,
				`date`,
                                `dateunix`)
				VALUES
				(
				'$this->_userId',
				'$this->_page',
				$idPNMain,
				NOW(),
                                UNIX_TIMESTAMP(NOW()));";
		$this->_mysqldriver->query($sql);
	}
}
