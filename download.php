<?php

ini_set("memory_limit","1024M");
/*
|-----------------
| Chip Error Manipulation
|------------------
*/

error_reporting(-1);

/*
|-----------------
| Chip Constant Manipulation
|------------------
*/

define( "CHIP_DEMO_FSROOT",				__DIR__ . "/" );

/*
|-----------------
| Chip Download Class
|------------------
*/
require_once("classesv2/class.chip_download.php");

/*
|-----------------
| Class Instance
|------------------
*/

include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");

$mpath = $_REQUEST['p'];

var_dump($mpath);

switch ($mpath) {
	case "f":
		$download_path = $mydbConfig['fitsfiles'];
		break;
	case "p":
		$download_path = $mydbConfig['pngfiles'];
		break;
	case "c":
		$download_path = $mydbConfig['rgbcubes'];
		break;
	case "s":
                $download_path = $mydbConfig['fitsfiles'];
		break;
	case "e":
		$download_path = $mydbConfig['exporteddata'];
		break;
}
$file = $_REQUEST['f'];

      
$args = array(
		'download_path'		=> $download_path,
		'file'                  => encodeFileName($file),
		'extension_check'	=> TRUE,
		'referrer_check'	=> FALSE,
		'referrer'		=> NULL,
		);

$download = new chip_download( $args );
/*
|-----------------
| Pre Download Hook
|------------------
*/

$download_hook = $download->get_download_hook();
$download->chip_print($download_hook);
//exit;

/*
|-----------------
| Download
|------------------
*/
if( $download_hook['download'] == TRUE ) {

	/* You can write your logic before proceeding to download */
	/* Let's download file */
	$download->get_download();

}


/**
 * url encode file name
 * @param string $file old file with path
 * @return string new file with path
 */
function encodeFileName($file) {
    $chunks = explode("/",$file);
    $chunks[count($chunks)-1] = urlencode(end($chunks));
    $file = implode("/", $chunks);
    return $file;
}

?>