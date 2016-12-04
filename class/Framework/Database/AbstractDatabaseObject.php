<?php
/**
 * Created by PhpStorm.
 * User: bugra
 * Date: 27.11.2016
 * Time: 18:55
 */

namespace Framework\Database;

use Framework\Database\AbstractDatabase as DefaultDatabase;

abstract class AbstractDatabaseObject {
    protected static $_LOOKUP = [];
    protected static $_TABLE = "";
    protected static $_KEY = "id";
    protected static $_MAN_FKS = [];

    private $keys = [];

    private $FUNCS;
    private $FKS;

    public function __construct($akey) {
        $this->load($akey);
    }

    private static function toCamelCase($expression) {
        $array = explode('_', $expression);
        $concat = "";
        foreach ($array as $arr) {
            $concat .= ucfirst($arr);
        }

        return $concat;
    }

    static public function delete($index) {
        $query = "
            DELETE FROM     `".static::$_TABLE."`
            WHERE           `".static::$_KEY."`=?
        ";
        DefaultDatabase::execute($query, [$index]);
    }

    final static public function exists($index) {
        return count(static::listAll("`".static::$_KEY."` = ?", [], [$index])) ? true : false;
    }

    static public function insert($arr) {
        $query = "
			INSERT INTO	`".static::$_TABLE."`
		";
        $params = [];
        $placeholders = [];
        foreach ($arr as $key => $val) {
            $params[] = "`$key`";
            $placeholders[] = "?";
        }
        $query .= "(".implode(",", $params).") VALUES (".implode(",", $placeholders).")";

        DefaultDatabase::execute($query, array_values($arr));

        return DefaultDatabase::lastInsert();
    }

    final static public function create($id) {
        $class = get_called_class();
        if (is_array($id)) {
            return new $class($id);
        }
        if(isset($_LOOKUP[$class])){
            $slt = static::$_LOOKUP[$class];
        }
        if (isset($slt[$id])) {
            $slt[$id]->reload();
            return $slt[$id];
        }

        return static::$_LOOKUP[$class][$id] = new $class($id);
    }

    final public static function __callStatic($method, $args) {
        if (!preg_match("/^by/", $method)) {
            return null;
        }
        // byVorgangId -> _vorgang_id -> vorgang_id
        $uncamel = substr(strtolower(preg_replace("/([A-Z])/", "_\$1", substr($method, 2))), 1);

        return static::by(
            $uncamel,
            $args[0],
            isset($args[1]) ? $args[1] : [],
            isset($args[2]) ? $args[2] : [],
            isset($args[3]) ? $args[3] : [],
            isset($args[4]) ? $args[4] : []
        );
    }

    final protected static function by($field, $field_value, $additional_filters = [], $order = [], $replace = [], $limit = "") {
        return static::listAll(
            array_merge(["`$field` = ?"], $additional_filters),
            $order,
            array_merge([$field_value], $replace),
            $limit
        );
    }

    final static public function listAll(array $filter = [], array $order = [], array $replace = [], $limit = "") {
        $query = "
			SELECT		*
			FROM		`".static::$_TABLE."`
		";
        if (count($filter)) {
            $query .= "
				WHERE		".implode(" AND ", $filter)."
			";
        }

        if (count($order)) {
            $query .= "
				ORDER BY	".implode(",", $order)."
			";
        }

        if (!empty($limit)) {
            $query .= "LIMIT $limit";
        }

        $res = DefaultDatabase::query($query, $replace);
        $obj = get_called_class();

        $retval = [];
        foreach ($res as $row) {
            $retval[$row[static::$_KEY]] = new $obj($row);
        }

        return $retval;
    }

    final public function getId() {
        $par = static::$_KEY;
        return $this->$par;
    }

    final public function __call($method, $args) {
        $methodPrefix = substr($method, 0, 3);
        $methodPostfix = substr($method, 3);
        if ($methodPrefix == 'set' && isset($this->FUNCS[$methodPostfix])) {
            return $this->set($this->FUNCS[$methodPostfix], $args[0]);
        }

        if ($methodPrefix == 'get') {
            if (isset($this->FUNCS[$methodPostfix])) {
                return $this->get($this->FUNCS[$methodPostfix]);
            } elseif (isset($this->FKS[$methodPostfix])) {
                $id = $this->get($this->FUNCS[$methodPostfix]."Id");
                new $this->FKS[$methodPostfix]($id);
            }
        }

        return null;
    }

    final protected function set($key, $val) {
        $var = static::$_KEY;
        $query = "
            UPDATE          `".static::$_TABLE."`
            SET             `$key`=?
            WHERE           `$var`=?
        ";
        return DefaultDatabase::execute($query, [$val, $this->getId()]);
    }

    final protected function get($key) {
        return $this->$key;
    }

    final public function save() {
        $query = "
			UPDATE          `".static::$_TABLE."`
			SET
		";

        foreach ($this->getKeys() as $key) {
            $query .= "`$key` = ?";
            $replace_array[] = $this->$key;
        }

        $query .= "
			WHERE           `".static::$_KEY."` = ?
		";

        $replace_array[] = $this->getId();
        return DefaultDatabase::execute($query, $replace_array);
    }

    final private function load($akey) {
        if (is_array($akey)) {
            $row = $akey;
        } else {
            $query = "
				SELECT          *
				FROM            `".static::$_TABLE."`
				WHERE           `".static::$_KEY."`=?
			";
            $row = DefaultDatabase::one($query, [$akey]);
        }

        $this->keys = [];

        if($row){
            foreach ($row as $key => $val) {
                $this->$key = $val;
                $camel_case = static::toCamelCase($key);
                $this->FUNCS[$camel_case] = $key;
                $this->keys[] = $key;
            }
        }
    }

    final public function reload() {
        $this->load($this->getId());
    }

    final protected function getKeys() {
        return $this->keys;
    }

    final public function remove() {
        static::delete($this->getId());
    }

    final public static function truncate() {
        DefaultDatabase::truncateTable(static::$_TABLE);
    }
}

?>
