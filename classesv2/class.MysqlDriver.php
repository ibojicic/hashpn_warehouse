<?php

class MysqlDriver {

    private $host, $user, $pass;

    /**
     * MySQL connection information
     *
     * @var resource
     */
    private $link;

    /**
     * Result of last query
     *
     * @var resource
     */
    private $result;

    /**
     * Date and time
     *
     */
    const DATETIME = 'Y-m-d H:i:s';

    /**
     * Date
     *
     */
    const DATE = 'Y-m-d';

    /**
     * Constructor
     *
     * @param string $host MySQL host address
     * @param string $user Database user
     * @param string $password Database password
     * @param string $db Database name
     * @param boolean $persistant Is persistant connection
     * @param  boolean $connect_now Connect now
     * @return void
     */
    public function __construct($host, $user, $password, $connect_now = true) {
        $this->host = $host; // Host address
        $this->user = $user; // User
        $this->pass = $password; // Password

        if ($connect_now)
            $this->connect();

        return;
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Connect to the database
     *
     * @param boolean $persist Is persistant connection
     * @return boolean
     */
    public function connect() {
        $link = mysql_connect($this->host, $this->user, $this->pass);
        
        if (!$link) {
            trigger_error('Could not connect to the database.', E_USER_ERROR);
        } else
            $this->link = $link;


        return TRUE;
    }

    /**
     * Query the database
     *
     * @param string $query SQL query string
     * @return resource MySQL result set
     */
    public function query($query) {
        $result = mysql_query($query, $this->link);

        $this->result = $result;

        if ($result == false)
            trigger_error("Uncovered an error in your SQL query script: " . $this->error() . "\n query: $query \n");

        return $this->result;
    }

    /**
     * Get the error description from the last query
     *
     * @return string
     */
    public function error() {
        return mysql_error($this->link);
    }

    /**
     * Select
     *
     * @param mixed $fields Array or string of fields to retrieve
     * @param string $table Table to retrieve from
     * @param string $where Where condition
     * @param string $orderby Order by clause
     * @param string $limit Limit condition
     * @return array Array of rows
     */
    public function select($fields, $table, $where = false, $orderby = false, $groupby = false, $limit = false) {
        if (is_array($fields))
            $fields = $this->setListForQuery($fields, "`");

        $orderby = ($orderby) ? " ORDER BY " . $orderby : '';
        $where = ($where) ? " WHERE " . $where : '';
        $limit = ($limit) ? " LIMIT " . $limit : '';
        $groupby = ($groupby) ? " GROUP BY " . $groupby : '';

        $query = "SELECT " . $fields . " FROM " . $table . $where . $groupby . $orderby . $limit;
        //echo "SELECT " . $fields . " FROM " . $table . $where . $groupby . $orderby . $limit . "<br>";
        return $this->selectquery($query);
    }

    /**
     * Update
     *
     * @param array $fields Array of fields to update: key => field and value => value
     * @param string $table Table to retrieve from
     * @param string $where Where condition
     * @param string $orderby Order by clause
     * @param string $limit Limit condition
     * @return array Array of rows
     */
    public function update($fields, $table, $where = false) {
        $tmparray = array();
        foreach ($fields as $field => $value)
            array_push($tmparray, "`" . $field . "` = '" . $value . "'");
        $res = implode(",", $tmparray);
        $where = ($where) ? " WHERE " . $where : '';

        $query = "UPDATE " . $table . " SET " . $res . $where;

        echo $query . "\n";

        //return $this->query($query);
    }

    /**
     * Select by constructed query
     *
     * @param string $query sql query
     * @return array Array of rows
     */
    public function selectquery($query) {
        $this->query($query);

        if ($this->countRows() > 0) {
            $rows = array();

            while ($r = $this->fetchAssoc())
                $rows[] = $r;

            return $rows;
        } else
            return false;
    }

    /**
     * Selects one row
     *
     * @param mixed $fields Array or string of fields to retrieve
     * @param string $table Table to retrieve from
     * @param string $where Where condition
     * @param string $orderby Order by clause
     * @return array Row values
     */
    public function selectOne($fields, $table, $where = false, $orderby = false, $groupby = false) {
        $result = $this->select($fields, $table, $where, $orderby, $groupby, "0,1");
        if ($result)
            return $result[0];
        return False;
    }

    /**
     * Selects one value from one row
     *
     * @param mixed $field Name of field to retrieve
     * @param string $table Table to retrieve from
     * @param string $where Where condition
     * @param string $orderby Order by clause
     * @return array Field value
     */
    public function selectOneValue($field, $table, $where = false, $orderby = false, $groupby = false) {
        $result = $this->selectOne($field, $table, $where, $orderby, $groupby);

        return $result[$field];
    }

    /**
     */
    public function selectColumn($field, $table, $where = false, $orderby = false, $groupby = False) {
        $result = array();
        $field = trim($field, "`");
        $tmpresult = $this->select($field, $table, $where, $orderby, $groupby);
        if (!$tmpresult)
            return False;
        foreach ($tmpresult as $res)
            array_push($result, $res[$field]);
        return $result;
    }

    /**
     * Fetch results by associative array
     *
     * @param mixed $query Select query or MySQL result
     * @return array Row
     */
    public function fetchAssoc($query = false) {
        $this->resCalc($query);
        return mysql_fetch_assoc($query);
    }

    /**
     * Fetch results by enumerated array
     *
     * @param mixed $query Select query or MySQL result
     * @return array Row
     */
    public function fetchRow($query = false) {
        $this->resCalc($query);
        return mysql_fetch_row($query);
    }

    /**
     * Fetch one row
     *
     * @param mixed $query Select query or MySQL result
     * @return array
     */
    public function fetchOne($query = false) {
        list($result) = $this->fetchRow($query);
        return $result;
    }

    /**
     * Fetch a field name in a result
     *
     * @param mixed $query Select query or MySQL result
     * @param int $offset Field offset
     * @return string Field name
     */
    public function fieldName($query = false, $offset) {
        $this->resCalc($query);
        return mysql_field_name($query, $offset);
    }

    /**
     * Fetch all field names in a result
     *
     * @param mixed $query Select query or MySQL result
     * @return array Field names
     */
    public function fieldNameArray($query = false) {
        $names = array();

        $field = $this->countFields($query);

        for ($i = 0; $i < $field; $i++)
            $names[] = $this->fieldName($query, $i);

        return $names;
    }

    /**
     * Free result memory
     *
     * @return boolean
     */
    public function freeResult() {
        return mysql_free_result($this->result);
    }

    /**
     * Add escape characters for importing data
     *
     * @param string $str String to parse
     * @return string
     */
    public function escapeString($str) {
        return mysql_real_escape_string($str, $this->link);
    }

    /**
     * Count number of rows in a result
     *
     * @param mixed $result Select query or MySQL result
     * @return int Number of rows
     */
    public function countRows($result = false) {
        $this->resCalc($result);
        if (!$result) {
            return 0;
        }
        return (int) mysql_num_rows($result);
    }

    /**
     * Count number of fields in a result
     *
     * @param mixed $result Select query or MySQL result
     * @return int Number of fields
     */
    public function countFields($result = false) {
        $this->resCalc($result);
        return (int) mysql_num_fields($result);
    }

    /**
     * Get last inserted id of the last query
     *
     * @return int Inserted in
     */
    public function insertId() {
        return (int) mysql_insert_id($this->link);
    }

    /**
     * Get number of affected rows of the last query
     *
     * @return int Affected rows
     */
    public function affectedRows() {
        return (int) mysql_affected_rows($this->link);
    }

    /**
     * Dump MySQL info to page
     *
     * @return void
     */
    public function dumpInfo() {
        echo mysql_info($this->link);
    }

    /**
     * Close the link connection
     *
     * @return boolean
     */
    public function close() {
        //return mysql_close($this->link);
    }

    /**
     * Determine the data type of a query
     *
     * @param mixed $result Query string or MySQL result set
     * @return void
     */
    private function resCalc(&$result) {
        if ($result == false)
            $result = $this->result;
        else {
            if (gettype($result) != 'resource')
                $result = $this->query($result);
        }

        return;
    }

    /**
     * Escape specific elements in an array
     *
     * @param array $keys array of keys to be escaped
     * @param array $array array of elements
     * @return array $result escaped array
     */
    public function escapeKeyArray($array, $keys = array()) {
        $result = $array;
        if (empty($keys)) {
            foreach ($array as $kkey => $ffield)
                $result[$kkey] = mysql_escape_string($ffield);
        } else {
            foreach ($keys as $field)
                if (isset($array[$field]))
                    $result[$field] = mysql_escape_string($array[$field]);
        }
        return $result;
    }

    /**
     * Test insert query before execution
     *
     * @param string $database database of the original table
     * @param string $table name of the original table	 *
     * @param string $tempdatabase database of the temporary table
     * @param string $temptable name of the temporary table
     * @param string $query insert query
     * @return boolean $result true: query ok, false: query nok
     */
    public function testInsert($database, $table, $tempdatabase, $temptable, $query) {
        $dropTempTable = "DROP TEMPORARY TABLE IF EXISTS `" . $tempdatabase . "`.`" . $temptable . "`;";
        if (!$this->query($dropTempTable))
            return False;
        $createTempTable = "CREATE TEMPORARY TABLE `" . $tempdatabase . "`.`" . $temptable . "` LIKE `" . $database . "`.`" . $table . "`;";
        if (!$this->query($createTempTable))
            return False;
        $testQuery = str_ireplace("`" . $database . "`", "`" . $tempdatabase . "`", str_ireplace("`" . $table . "`", "`" . $temptable . "`", $query));
        if (!$this->query($testQuery))
            return False;
        return True;
    }

    /**
     * 
     * @param string $table table name
     * @return string column name
     */
    public function findIndex($table) {
        $res = $this->selectquery("SHOW INDEX FROM " . $table . " WHERE `Key_name` = 'PRIMARY'");
        return $res[0]['Column_name'];
    }

    /**
     * 
     * @param string $table table name
     * @return array (field => array("Field" => "fieldname", "Type" => 'varchar(45)',"Null" => "Yes", "Key" => "", "Default" => NULL, "Extra" => "")
     */
    public function tableInfo($table) {
        $result = array();
        $sqlres = $this->selectquery("SHOW FIELDS FROM " . $table);
        foreach ($sqlres as $vals)
            $result[$vals['Field']] = $vals;
        return $result;
    }

    /**
     * 
     * @param string $type as in table
     * @return short type as from (string, number, datetime) or False if not found
     */
    public function mysqlTypeConvert($type) {
        $mysqltypes = array(
            "string" => array("varchar", "char", "binary", "varbinary", "blob", "text", "enum", "set"),
            "number" => array("float", "int", "integer", "smallint", "tinyint", "mediumint", "bigint", "decimal", "numeric"),
            "datetime" => array("date", "datetime", "timestamp", "time", "year")
        );
        $chunks = explode("(", $type);
        $reformat = strtolower(trim($chunks[0]));
        foreach ($mysqltypes as $newtype => $oldtypes) {
            if (in_array($reformat, $oldtypes))
                return $newtype;
        }
        return False;
    }

    public function setListForQuery($list, $separator) {
        if (empty($list) or trim($separator) == "")
            return False;
        return $separator . implode($separator . "," . $separator, $list) . $separator;
    }

    public function makeFieldsFromArray($array) {
        return "`" . implode("`,`", $array) . "`";
    }

    /**
     * 
     * @param array $array insert array (field => value)
     * @return string (`field1`,`field2` ....) VALUES ('value1','value2'...)
     */
    public function makeInsertString($array, $table,$nulls = array("")) {
        $tmparray = $array;
        $fields = $this->tableInfo($table);
        foreach ($tmparray as $field => $value) {
            $type = $this->mysqlTypeConvert($fields[$field]["Type"]);
            if (in_array(trim($value),$nulls)) {
                $array[$field] = "NULL";
            } elseif ($type == "string")
                $array[$field] = "'" . mysql_real_escape_string($value) . "'";
        }
        $result = "(`" . implode("`,`", array_keys($array)) . "`) VALUES (" . implode(",", $array) . ")";
        return $result;
    }

    /**
     * 
     * @param array $array insert array (field => value)
     * @return string `field1` = value1,`field2` = value2 ....
     */
    public function makeUpdateString($array, $table) {
        $resarray = array();
        $fields = $this->tableInfo($table);
        foreach ($array as $field => $value) {
            $type = $this->mysqlTypeConvert($fields[$field]["Type"]);
            $newval = $value;
            if (trim($newval) == "") {
                $newval = "NULL";
            } elseif ($type == "string")
                $newval = "'" . mysql_real_escape_string($newval) . "'";
            array_push($resarray, "`" . $field . "` = " . $newval);
        }
        $result = implode(",", $resarray);
        return $result;
    }

    public function tblName($db, $tbl) {
        $db = "`" . trim(trim($db), "`\";'") . "`";
        $tbl = "`" . trim(trim($tbl), "`\";'") . "`";
        return $db . "." . $tbl;
    }
    


}
