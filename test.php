<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("includes/pndb_config.php");
include_once("includes/functions.php");
include_once("classesv2/class.MysqlDriver.php");

$mysqldriver = new MysqlDriver($mydbConfig["dbhost_admin"], $mydbConfig["dbuser_admin"], $mydbConfig["dbpass_admin"]);

$db = "`MainGPN`.`ReferenceIDs`";

$list = $mysqldriver->select("Identifier", $db, "`Author` IS NULL");

$total = count($list);


foreach ($list as $item) {
    echo $total--." to go\n";
    $bibcode = trim($item['Identifier']);
    echo "->".$bibcode."<-\n";
    $res = parseADSBibquery($bibcode);

    if ($res) {
        unset($res['Identifier']);
        $updatestring = $mysqldriver->makeUpdateString($res, $db);
        
        $sqlupdate = "UPDATE ".$db." SET ".$updatestring." WHERE `Identifier` = '".$bibcode."' AND `Author` IS NULL;";
        $mysqldriver->query($sqlupdate);
        echo "Updated ".$bibcode."\n";
        sleep(2);
    } else {
        echo "Not found ".$bibcode."\n";
   
        sleep(2);
    }
}

echo "test";
