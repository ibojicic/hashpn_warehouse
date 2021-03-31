<?php

/**
 * generates random string
 * @param int $length length of the string
 * @return string
 */
function genRandomString($length = 10)
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $string = "";
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}


/**
 *
 * @param strin $type "file" or "folder"
 * @param string $path to the file; default = "/tmp/"
 * @param string $pref prefix to the file; default = ""
 * @param string $suf suffinx to the file; defualt = ""
 * @param string $ext extension; default = ".txt"
 * @return string full path to the file/folder e.g. /tmp/something.txt or /tmp/something/
 */
function genRndFileFolder($type, $path = "/tmp/", $pref = "", $suf = "", $ext = ".txt")
{
    if ($type == "file") {
        return pathslash($path) . $pref . genRandomString() . $suf . $ext;
    } elseif ($type == "folder") {
        return pathslash(pathslash($path) . $pref . genRandomString() . $suf);
    }
    return false;
}

/*
  function makeOneColMain($array, $column) {
  if (!$array or empty($array) or ! $column)
  return array();
  $ttsub = array();
  foreach ($array as $key => $val) {
  $tmpsub = $val;
  unset($tmpsub[$column]);
  $ttsub[$val[$column]] = $tmpsub;
  }
  return $ttsub;
  }
 * 
 */

function makeOneColMain($array, $column)
{
    if (!$array or empty($array) or !$column) {
        return array();
    }
    $ttsub = array();
    foreach ($array as $key => $val) {
        $tmpsub = $val;
        unset($tmpsub[$column]);
        $ttsub[$val[$column]] = $tmpsub;
    }
    return $ttsub;
}

function useValAsKey(&$array, $key)
{
    $newkey = $array[$key];
    //unset($array[$key]);
    return array($newkey => $array);
}

/*
 * Main algorithm for the cutout size.
 * inputs:
 * set: name of the image set/survey
 * imsize: override the algorithm
 * include correction for rotation: if rot=true => 2 * the default cutout size
 */

function getCutoutSize($rotate, $majext, $minsize, $imsize = false)
{ //in arcsec
    $rotcorrection = $rotate == 'y' ? 1.5 : 1;
    if (!$imsize) {
        if (5 * $majext < $minsize) {
            $imsize = $minsize;
        } elseif ($majext < 75) {
            $imsize = 4 * $majext;
        } elseif ($majext < 200) {
            $imsize = 3 * $majext;
        } elseif ($majext < 400) {
            $imsize = 4 * $majext;
        } else {
            $imsize = 1.5 * $majext;
        }
    }
    return $imsize * $rotcorrection;
}

/**
 * Convert usual notation of RA/DEC into deg (ex: 18:00:00 -> 270)
 *
 * @param text $pos : RA/DEC in usual notation
 * @param integer $radec : 15 if RA, 1 if DEC
 * @return RA/DEC in deg format
 */
function trans_to_deg($pos, $radec)
{
    $pos = trim(str_ireplace(":", " ", $pos));
    if (is_real($pos)) {
        echo "Error(1)";
        exit();
    }
    $sign = 1;
    if ($pos{0} == "-") {
        $sign = -1;
    }
    $pos_arr = explode(" ", $pos);
    $deg_pos = $sign * (abs($pos_arr[0]) + $pos_arr[1] / 60 + $pos_arr[2] / 3600) * $radec;
    return $deg_pos;
}

function mysql_escape_array($array)
{
    $tmp_array = array();
    foreach ($array as $key => $value) {
        $tmp_array[$key] = mysql_escape_string($value);
    }
    return $tmp_array;
}

function regex_radec($string)
{

    $string = "=>" . trim($string) . "<=";
    $sep = "[ ,\t]";

    $RAregex = "/=>(?:[0-1]?\d|2[0-3])[\s\:](?:[0-4]?\d|5[0-9])[\s\:](?:[0-4]?\d|5[0-9])(?:\s|\.[0-9]*)?";
    $DECregex = "[\+\-]?(?:[0-7]?\d|8[0-9])[\s\:](?:[0-4]?\d|5[0-9])[\s\:](?:[0-4]?\d|5[0-9])(?:\s|\.[0-9]*)?<=/";

    $DRAregex = "/=>(?:[0-2]?[0-9]?[0-9]?\d|3[0-5][0-9])(?:\s|\.[0-9]*)?";
    $DDECregex = "[\+\-]?(?:[0-7]?\d|8[0-9])(?:\s|\.[0-9]*)?<=/";

    if (preg_match($RAregex . $sep . "+" . $DECregex, $string, $return)) {
        $found = $return[0];
        $rra = $RAregex;
        $dde = $DECregex;
        $flag = "sex";
    } elseif (preg_match($DRAregex . $sep . "+" . $DDECregex, $string, $return)) {
        $found = $return[0];
        $rra = $DRAregex;
        $dde = $DDECregex;
        $flag = "deg";
    } else {
        return false;
    }


    preg_match($rra . $sep . "+/", $found, $return);
    $RA = trim($return[0]);
    preg_match("/" . $sep . "*" . $dde, str_ireplace($RA, "", $found), $return);
    $DEC = trim($return[0]);

    return array("X" => rtrim(ltrim($RA, "=>"), ", \t"), "Y" => ltrim(rtrim($DEC, "<="), ", \t"), "flag" => $flag);
}

function regex_gal($string)
{
    $string = "=>" . trim($string) . "<=";
    $sep = "[, \t]+";

    $Glatregex = "/=>(?:[0-2]?[0-9]?\d|3[0-5][0-9])(?:\s|\.[0-9]*)?";
    $Glonregex = "[\+\-]?(?:[0-7]?\d|8[0-9])(?:\s|\.[0-9]*)?<=/";

    if (preg_match($Glatregex . $sep . $Glonregex, $string, $return)) {
        $found = $return[0];
    } else {
        return false;
    }

    preg_match($Glatregex . $sep . "/", $found, $return);
    $Glat = rtrim(ltrim(trim($return[0]), "=>"), " ,\t");

    preg_match("/" . $sep . $Glonregex, str_ireplace($RA, "", $found), $return);
    $Glon = ltrim(rtrim(trim($return[0]), "<="), " ,\t");

    return array("X" => ltrim($Glat, "=>"), "Y" => rtrim($Glon, "<="), "flag" => "deg");
}

function groupArrayByField($array, $field)
{
    if (!$array or empty($array) or !array_key_exists($field, reset($array))) {
        return array();
    }
    $result = array();
    foreach ($array as $data) {
        $wfield = $data[$field];

        if (!isset($result[$wfield])) {
            $result[$wfield] = array();
        }

        array_push($result[$wfield], $data);
    }

    return $result;
}

function checkCoordinates($coords, $type)
{
    switch ($type) {
        case 'hmsdms':
            $ra = regex_coord_old($coords[0], "ra");
            $dec = regex_coord_old($coords[1], "dec");
            if ($ra and $dec) {
                return true;
            }
            break;
        case 'radec':
        case 'gal':
            if (strval(floatval($coords[0])) != strval($coords[0]) or floatval($coords[0]) < 0 or floatval($coords[0]) > 360) {
                return false;
            }
            if (strval(floatval($coords[1])) != strval($coords[1]) or floatval($coords[1]) < -90 or floatval($coords[1]) > 90) {
                return false;
            }
            return true;
    }

    return false;
}

/*
  function transferCoords($from, $to, $xcrd, $ycrd, $path = "", $tmpfolder = "/tmp") {
  $csvfile_downcoords = tempnam($tmpfolder, "downcoords");
  $coordssytems = array('hmsdms' => array('x' => 'RAJ2000', 'y' => 'DECJ2000'),
  'radec' => array('x' => 'DRAJ2000', 'y' => 'DDECJ2000'),
  'gal' => array('x' => 'Glon', 'y' => 'Glat'));
  $XcoordFrom = $coordssytems[$from]['x'];
  $YcoordFrom = $coordssytems[$from]['y'];
  $XcoordTo = $coordssytems[$to]['x'];
  $YcoordTo = $coordssytems[$to]['y'];
  $pyfnc = $from . "2" . $to;
  $return = system("python " . $path . "pydrivers/coordTransf_driver.py $pyfnc $xcrd $ycrd $csvfile_downcoords", $retval);
  $results = file($csvfile_downcoords);
  unlink($csvfile_downcoords);
  $chunks = explode(",", $results[0]);
  return array("X" => $chunks[0], "Y" => $chunks[1]);
  }
 */

function cdsSesameDriver($name)
{
    $name = str_ireplace(" ", "_", cleanSpaces($name));
    $dummyarray = array('alias' => array(), 'jradeg' => false, 'jdedeg' => false);
    $tmpfile = "/tmp/" . genRandomString() . ".xml";
    $command = "/usr/local/bin/sesame -oxI -rA '" . $name . "' > " . $tmpfile;
    shell_exec($command);
    $result = xml2array($tmpfile);
    unlink($tmpfile);
    $sesresults = ($result['Sesame']['Target']['Resolver']);
    $vizres = $sesresults[0] == array('INFO' => 'Zero (0) answers') ? $dummyarray : $sesresults[0];
    $simres = $sesresults[1] == array('INFO' => 'Zero (0) answers') ? $dummyarray : $sesresults[1];

    if (isset($vizres['alias']) and !is_array($vizres['alias'])) {
        $vizres['alias'] = array($vizres['alias']);
    }
    if (isset($simres['alias']) and !is_array($simres['alias'])) {
        $simres['alias'] = array($simres['alias']);
    }

    $return = array(
        'aliases' => array_unique(array_merge($vizres['alias'], $simres['alias'])),
        'valias' => $vizres['alias'],
        'salias' => $simres['alias'],
        'DRAJ2000' => $simres['jradeg'],
        'DDECJ2000' => $simres['jdedeg']
    );
    if (empty($return['aliases'])) {
        $return = false;
    }

    return $return;
}

function cleanSpaces($text)
{
    $text = trim($text);
    $double = true;
    while ($double) {
        $text = str_ireplace("  ", " ", $text);
        $double = stripos($text, "  ");
    }
    return $text;
}

function calcPNG($glon, $glat, $prevPNGs = false)
{
    $result = false;

    $alphas = range("a", "z");

    $glonPart = sprintf("%08.4f", $glon);
    $glatPart = sprintf("%+08.4f", $glat);
    $calcPNG = substr($glonPart, 0, 5) . substr($glatPart, 0, 5);

    if (!$prevPNGs or !in_array($calcPNG, $prevPNGs)) {
        $result = $calcPNG;
    } else {
        foreach ($alphas as $ch) {
            if (!in_array($calcPNG . $ch, $prevPNGs)) {
                $result = $calcPNG . $ch;
                break;
            }
        }
    }

    return $result;
}

function writeSpectraJson($text, $filename = false, $overwrite = false)
{
    if (!is_file($filename) or $overwrite) {
        $fp = fopen($filename, "w");
        fwrite($fp, $text);
        fclose($fp);
    }
    return $filename;
}

function setSpLinesMarkers($pathtofile, $lines)
{
    @$splinemarkers = json_encode($lines);
    writeSpectraJson(@$splinemarkers, $pathtofile, true);
}

function regex_coord_old($string, $radec)
{
    $string = " " . trim($string) . " ";
    $ra_reg = "/\s(?:[0-1]?\d|2[0-3])[\s\:][0-5]\d[\s\:][0-5]\d(?:\s|\.\d+)?\s/";
    $dec_reg = "/\s[\+\-]?(?:[0-8]?\d[\s\:][0-5]\d[\s\:][0-5]\d(?:\s|\.\d+)?|90[\s\:]00[\s\:]00)(?:\s|\.0+)?\s/";

    if ($radec == 'ra') {
        $regex = $ra_reg;
    } elseif ($radec == 'dec') {
        $regex = $dec_reg;
    }

    if (preg_match_all($regex, $string, $return)) {
        return $return;
    } else {
        return false;
    }
}

/*
 * This is very simple way to convert all applicable objects into associative array.  
 * This works with not only SimpleXML but any kind of object. The input can be either array or object. 
 * This function also takes an options parameter as array of indices to be excluded in the return array. 
 * And keep in mind, this returns only the array of non-static and accessible variables 
 * of the object since using the function get_object_vars().
 * http://php.net/manual/en/function.xml-parse.php
 */

function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();

    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

function xml2array($url, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create')) {
        return array();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($url, 'rb'))) {
        return array();
    }
    while (!feof($fp)) {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values, $index);
    xml_parser_free($parser);
    if (!$xml_values) {
        return;
    } //Hmm...
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();
    $current = &$xml_array;
    $repeated_tag_index = array();
    foreach ($xml_values as $data) {
        unset($attributes, $value);
        extract($data);
        $result = array();
        $attributes_data = array();
        if (isset($value)) {
            if ($priority == 'tag') {
                $result = $value;
            } else {
                $result['value'] = $value;
            }
        }
        if (isset($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag') {
                    $attributes_data[$attr] = $val;
                } else {
                    $result['attr'][$attr] = $val;
                } //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open") {
            $parent[$level - 1] = &$current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                if ($attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = &$current[$tag];
            } else {
                if (isset($current[$tag][0])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset($current[$tag . '_attr'])) {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = &$current[$tag][$last_item_index];
            }
        } elseif ($type == "complete") {
            if (!isset($current[$tag])) {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }
            } else {
                if (isset($current[$tag][0]) and is_array($current[$tag])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        } elseif ($type == 'close') {
            $current = &$parent[$level - 1];
        }
    }
    return ($xml_array);
}

function formatRADEC($radec, $prec, $sep = ":")
{
    $radec = trim($radec);
    $chunks = explode($sep, $radec);
    //quick fix if -00 => need to stay negative
    $ftemp = trim($chunks[0]);
    $ftemp = $ftemp{0} == "-" ? "-" : "";
    $f1 = sprintf("%02d", $chunks[0]);
    if ($ftemp == "-" and $f1{0} <> "-") {
        $f1 = "-" . $f1;
    }
    $f2 = sprintf("%02d", $chunks[1]);
    $format = $prec > 0 ? (3 + $prec) . "." . $prec . "f" : "2d";
    $f3 = sprintf("%0" . $format, round($chunks[2], $prec));
    return $f1 . $sep . $f2 . $sep . $f3;
}


/**
 * order one array by values of another
 * @param array $array to be ordered
 * @param array $orderArray ordering array
 * @return array
 */
function sortArrayByArray(Array $array, Array $orderArray)
{
    $ordered = array();
    foreach ($orderArray as $key) {
        if (array_key_exists($key, $array)) {
            $ordered[$key] = $array[$key];
            unset($array[$key]);
        }
    }
    return $ordered + $array;
}

/**
 * make sure that path finishes with a slash
 * @param $path input path
 */
function pathslash($path)
{
    return preg_replace('~/+~', '/', rtrim($path, "/") . "/");
}

/**
 * check is $name exists in array $columns, if it doues change it to $name_number
 * @param array $columns available columns
 * @param string $name new name
 * @return new name
 */
function checkColumnName($columns, $name)
{
    $n = 0;
    $result = $name;
    while (in_array(strtolower($result), $columns)) {
        $n++;
        $result = $name . "_" . $n;
    }
    return $result;
}

/**
 * Create MYSQL table
 * @param string $dbname : database name
 * @param string $tbname : table name
 * @param array $array : table fields array('$nameindex' => name, '$typeindex' => type, '$commentindex' => comment)
 * @param string $primkey : primary key
 * @param string $nameindex
 * @param string $typeindex
 * @param string $commentindex
 * @param string $extra
 * @param string $tbcomment
 * @return string: mysql query
 */
function createMYSQLTableArray(
    $dbname,
    $tbname,
    $array,
    $primkey,
    $nameindex,
    $typeindex,
    $commentindex,
    $extra,
    $tbcomment = ""
) {
    $line = "`" . $primkey . "` INTEGER NOT NULL AUTO_INCREMENT, ";
    foreach ($array as $kvalues) {
        $name = trim($kvalues[$nameindex]);
        $type = trim($kvalues[$typeindex]);
        $comment = trim($kvalues[$commentindex]);
        $line .= "`" . $name . "` " . $type . " COMMENT '" . (mysql_escape_string($comment)) . "',\n";
    }

    $return = "CREATE TABLE `" . $dbname . "`.`" . $tbname . "` (" . $line . " PRIMARY KEY (`" . $primkey . "`), UNIQUE (`" . $primkey . "`) $extra)
				ENGINE = InnoDB COMMENT = '" . (mysql_escape_string($tbcomment)) . "';";
    return $return;
}

/**
 * find type base on mysql type
 * @param string $type
 * @return False if not found, type if found
 */
function findtype($type)
{
    $types = array("float" => "FLOAT", "int" => "INTEGER", "string" => "VARCHAR(255)");
    foreach ($types as $from => $to) {
        if (stripos(" " . $type . " ", $from)) {
            return $to;
        }
    }
    return false;
}

function unchunkHttp11($data)
{
    $fp = 0;
    $outData = "";
    while ($fp < strlen($data)) {
        $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
        $num = hexdec(trim($rawnum));
        $fp += strlen($rawnum);
        $chunk = substr($data, $fp, $num);
        $outData .= $chunk;
        $fp += strlen($chunk);
    }
    return $outData;
}

function parseADSBibquery($bibcode)
{
    $adsmaparray = array(
        "Bibliographic Code" => "Identifier",
        "Score" => "Score",
        "Title" => "Title",
        "Authors" => "Author",
        "Journal" => "Journal",
        "Publication Date" => "Year",
        "Keywords" => "Keywords",
        "Origin" => "Origin",
        "Abstract" => "Abstract",
        "Document URL" => "URL",
        "Available Items" => "Items"
    );

    $result = array();
    $output = array();

    $perlcode = "/var/www/html/" . BASE_FOLDER . "/perlscripts/bibquery.pl";
    $command = "perl " . $perlcode . " '" . $bibcode . "'";
    exec($command, $output);
    if (empty($output) or count($output) < 2 or trim($output[0] == "No output content retrieved!")) {
        return false;
    }
    foreach ($output as $line) {
        $chunks = explode(":", $line);
        $keyword = trim($chunks[0]);
        if (in_array($keyword, array_keys($adsmaparray))) {
            $result[$adsmaparray[$keyword]] = mb_convert_encoding(trim(str_ireplace($keyword . ":", "", $line)),
                "UTF-8");
        }
    }
    return $result;
}

/**
 * find median in array
 * @param array $a
 * @return floor
 */
function median($a)
{
    sort($a, SORT_NUMERIC);
    return (count($a) % 2) ?
        $a[floor(count($a) / 2)] :
        ($a[floor(count($a) / 2)] + $a[floor(count($a) / 2) - 1]) / 2;
}

/**
 *
 * @param string $file full path to the .tex file to be parsed (only table no header)
 * @param array $keymap : column names and map of the types for columns:
 * string       = string i.e. "text" or text
 * number       = number i.e. 5 or 5.3
 * number_err = number with uncertainty i.e. 5.3$\pm$0.2 or 5.3$^{0.2}_{-0.1}
 */
function texTableParser($file, $keymap)
{
    $speccharreplace = array(
        "$+$" => "+",
        "$-$" => "-",
        "~" => " ",
        "$\ldots$" => "..."
    );
    $result = array();
    $fp = file_get_contents($file);
    $fpr = str_ireplace("\n", "", $fp);
    $lines = explode("\\\\", $fpr);
    foreach ($lines as $line) {
        if (trim($line) != "") {
            $subarray = array_map('trim', explode("&", $line));
            if (count($subarray) != count($keymap)) {
                //print_r($subarray);
                exit("array don't match...");
            }
            $temp = array_combine(array_keys($keymap), $subarray);

            foreach ($keymap as $key => $type) {
                if ($type == "number_err") {
                    $numerrpars = texErrValParse($temp[$key]);
                    $temp[$key] = $numerrpars["val"];
                    $temp[$key . "_errup"] = $numerrpars["err_up"];
                    $temp[$key . "_errdown"] = $numerrpars["err_down"];
                } elseif ($type == "number") {
                    $temp[$key] = str_ireplace("$", "", $temp[$key]);

                } else {                                        //special rules
                    foreach ($speccharreplace as $find => $replace) {
                        $temp[$key] = str_ireplace($find, $replace, $temp[$key]);
                    }
                }

            }
            array_push($result, $temp);
        }
    }
    return $result;
}

function texErrValParse($string)
{
    $string = str_ireplace(array(" ", "\t", "\n", "$"), "", $string);
    if (stripos($string, "\pm")) {
        $tmp = explode("\pm", $string);
        $result = array("val" => trim($tmp[0]), "err_up" => trim($tmp[1]), "err_down" => trim($tmp[1]));
    } elseif (stripos($string, "^")) {
        $tmp = explode("^", $string);
        $tmp1 = explode("}_{", $tmp[1]);
        $result = array("val" => trim($tmp[0]), "err_up" => trim($tmp1[0], "{}"), "err_down" => trim($tmp1[1], "{}"));
    }
    return $result;
}

?>
