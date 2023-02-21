<?php

namespace Base\Record;

use Base\Base\DB;

abstract class Record extends DB implements \Base\Model\Finder
{
    const TARGET_CLASS = null;

    protected $selectStmt = null;

    protected static $count = null;
    protected static $limit = 50;

    function __construct()
    {
        if (!isset(static::$PDO)) {
            $this->GetHandler();
        }
    }

    public static function table()
    {
        $class = static::TARGET_CLASS;

        return $class::table();
    }

    function selectStmt()
    {
        if (!$this->selectStmt) {
            $this->selectStmt = self::$PDO->prepare("SELECT * FROM `" . static::table() .
                "` WHERE id=?");
        }

        return $this->selectStmt;
    }

    private function getFromMap($id)
    {
        return \Base\Model\ObjectWatcher::exists($this->targetClass(), $id);
    }

    private function addToMap(\Base\Model\ModelObject $obj)
    {
        if ($obj->isWatcher())
            return \Base\Model\ObjectWatcher::add($obj);
    }

    function find($id)
    {
        $old = $this->getFromMap($id);
        if ($old) {
            return $old;
        }
        $this->selectstmt()->execute(array($id));
        $array = $this->selectstmt()->fetch(\PDO::FETCH_ASSOC);
        $this->selectstmt()->closeCursor();
        if (!is_array($array)) {
            return null;
        }
        if (!isset($array['id'])) {
            return null;
        }
        $object = $this->createObject($array);
        $object->markClean();
        return $object;
    }

    function get($sqlQuery, $params = null, $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $array = DB::GetRow($sqlQuery, $params, $fetchStyle);
        if (!is_array($array)) {
            return null;
        }
        if (!isset($array['id'])) {
            return null;
        }
        $object = $this->createObject($array);
        $object->markClean();
        return $object;
    }

    function findAll()
    {
        $this->selectAllStmt()->execute(array());
        return $this->getCollection($this->selectAllStmt()->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function GetAllRows($sqlQuery, $params = null, $fetchStyle = \PDO::
        FETCH_ASSOC)
    {
        $result = DB::GetAll($sqlQuery, $params, $fetchStyle);
        return $this->getCollection($result);
    }

    function getFactory()
    {
        return PersistenceFactory::getFactory($this->targetClass());
    }

    function createObject($array)
    {
        $objfactory = $this->getFactory()->getModelObjectFactory();
        return $objfactory->createObject($array);
    }

    function getCollection(array $raw)
    {
        return $this->getFactory()->getCollection($raw);
    }

    function isRow($data)
    {
        return null;
    }

    function insert(\Base\Model\ModelObject $obj)
    {
        $values = $obj->getColumnsInsert();

        if ($obj->isColumnInDn('created_at')) {
            $values['created_at'] = date('Y-m-d H:i:s');
        }

        if (is_array($values) && !empty($values)) {
            if ($id = $this->isRow($values)) {
                $obj->setId($id);
                $obj->status_insert_new = false;
            } else {
                $set_query = array();
                $value_query = array();

                foreach ($values as $key => $value) {
                    $set_query[$key] = "`" . $key . "`";
                    $value_query[$key] = ":" . $key;
                }

                $query = "INSERT INTO `" . $obj->table() . "` (" . implode(',', $set_query) .
                    ") VALUES (" . implode(',', $value_query) . ")";

                $sth = self::GetHandler()->prepare($query);
                
                self::debug($query, $values);

                foreach ($values as $key => $value) {
                    $sth->bindValue(':' . $key, $value, \PDO::PARAM_STR);
                }

                $result = $sth->execute();

                if ($result) {
                    $obj->setId(self::GetHandler(false)->lastInsertId());
                    
                    self::debug('LAST_INSERT_ID();');
                    
                    $obj->status_insert_new = true;
                }
            }

            $this->addToMap($obj);
            $obj->markClean();
        }
    }

    function delete(\Base\Model\ModelObject $obj)
    {
        if ($obj->getId() && DB::Execute("DELETE FROM `" . $obj->table() . "` WHERE `id`=:id LIMIT 1", array('id' => $obj->getId()))) {
            return true;
        } else {
            return false;
        }
    }

    function update(\Base\Model\ModelObject $obj)
    {
        $fields = $obj->getColumnsUpdate();

        $set = $fields['set'];
        $where = $fields['where'];

        if (is_array($set) && is_array($where) && !empty($set) && !empty($where)) {
            if ($obj->isColumnInDn('created_at')) {
                $set['updated_at'] = ['value' => date('Y-m-d H:i:s'), 'type' => 'timestamp'];
            }

            $set_query = array();
            $where_query = array();

            $input_parameters = array();

            foreach ($set as $key => $value) {
                $set_query[] = "`" . $key . "`=:" . $key;
                $input_parameters[$key] = $value;
            }

            foreach ($where as $key => $value) {
                $where_query[] = "`" . $key . "`=:" . $key;
                $input_parameters[$key] = $value;
            }

            $query = "UPDATE `" . $obj->table() . "` SET " . implode(',', $set_query) .
                " WHERE " . implode(' AND ', $where_query) . " LIMIT 1";

            $sth = self::GetHandler()->prepare($query);
            
            self::debug($query, $input_parameters);

            foreach ($input_parameters as $key => $value) {
                $sth->bindValue(':' . $key, $value['value'], \PDO::PARAM_STR);
            }

            return $sth->execute();
        } else {
            return null;
        }
    }

    public static function setCount($search = null)
    {
        $where_query = [];
        $params_query = [];

        if (is_array($search)) {
            foreach ($search as $key => $value) {
                if (empty($value)) {
                    $where_query[] = "`" . $key . "`=''";
                } else {
                    if (preg_match("#%#", $value)) {
                        $where_query[] = "LOWER (`" . $key . "`) LIKE LOWER (:" . $key . ")";
                    } else {
                        $where_query[] = "`" . $key . "`=:" . $key;
                    }
                    $params_query[$key] = $value;
                }
            }
        }

        static::$count = intval(DB::GetOne("SELECT COUNT(*) FROM `" . static::table() .
            "` 
             " . (!empty($where_query) ? " WHERE " . implode(' AND ', $where_query) :
            '') . ";", $params_query));
    }

    public static function setLimit($limit)
    {
        static::$limit = $limit;
    }

    public static function getCount()
    {
        return static::$count;
    }

    public static function getLimit()
    {
        return static::$limit;
    }

    public static function deleteForId($id)
    {
        if (DB::Execute("DELETE FROM `" . static::table() . "` WHERE `id`=:id LIMIT 1",
            array('id' => $id))) {
            return true;
        } else {
            return false;
        }
    }

    public static function GetRows($start, $limit, $search = null, $sort = null)
    {
        $start = intval($start);
        $limit = intval($limit);

        $where_query = [];
        $params_query = [];

        $order_by = '';

        if (is_array($sort)) {
            if (isset($sort['sort'])) {
                $order_by = "ORDER BY " . $sort['sort'];

                if (isset($sort['order']) && $sort['order'] == 'DESC') {
                    $order_by = $order_by . " DESC";
                }
            }
        }

        if (is_array($search)) {
            foreach ($search as $key => $value) {
                if (empty($value)) {
                    $where_query[] = "`" . $key . "`=''";
                } elseif (is_array($value)) {
                    if (preg_match("#%#", $value[0])) {
                        $where_query[] = "LOWER (`" . $key . "`) LIKE LOWER (:" . $key . ")";
                    } else {
                        $where_query[] = "`" . $key . "`" . $value[1] . ":" . $key;
                    }
                    $params_query[$key] = $value[0];
                } else {
                    if (preg_match("#%#", $value)) {
                        $where_query[] = "LOWER (`" . $key . "`) LIKE LOWER (:" . $key . ")";
                    } else {
                        $where_query[] = "`" . $key . "`=:" . $key;
                    }
                    $params_query[$key] = $value;
                }
            }
        }

        if (empty($order_by)) {
            $order_by = "ORDER BY id DESC";
        }

        return (new static)->GetAll("SELECT * FROM `" . static::table() . "` " . (!
            empty($where_query) ? " WHERE " . implode(' AND ', $where_query) : '') . " $order_by LIMIT $start, $limit;",
            $params_query);
    }

    static function isTable($tableName)
    {
        $tables = DB::GetAll("SHOW TABLES FROM `" . \Base::app()->config('DB_DATABASE') .
            "`");

        if (is_array($tables)) {
            foreach ($tables as $table) {
                if (is_array($table)) {
                    foreach ($table as $tbl) {
                        if ($tbl == $tableName) {
                            return true;
                        }
                    }
                } elseif ($table == $tableName) {
                    return true;
                }
            }
        }

        return false;
    }

    static function getColumns($tableName)
    {
        $result = array();

        $sql = "DESCRIBE `" . $tableName . "`";

        $inf = DB::GetAll("DESCRIBE `" . $tableName . "`");

        if (!is_array($inf)) {
            return [];
        }

        foreach ($inf as $column) {
            $result[] = $column['Field'];
        }

        return $result;
    }

    protected function targetClass()
    {
        return static::TARGET_CLASS;
    }

    public static function deleteRows(\Base\Model\ModelObject $obj, $params = [])
    {
        if (\Base::app()->config('WORDPRESS')) {
            if ($obj->table_name != 'wp_posts' && stripos($obj->table_name, 'tbl_parser_')
                !== 0) {
                return false;
            }

            if ($obj->table_name == 'wp_posts') {
                $params['source_id'] = $obj->id;
            }
        }

        if (empty($params)) {
            self::clearTable($obj);
        } else {
            $where = [];
            foreach ($params as $key => $value) {
                if (preg_match("#%#", $value)) {
                    $where[] = "LOWER (`" . $key . "`) LIKE LOWER (:" . $key . ")";
                } else {
                    $where[] = "`" . $key . "`=:" . $key;
                }
            }
            return DB::Execute("DELETE FROM `" . $obj->table_name . "` WHERE " . implode(' AND ',
                $where), $params);
        }
    }

    public static function clearTable(\Base\Model\ModelObject $obj)
    {
        if (\Base::app()->config('WORDPRESS')) {
            return false;
        } else {
            return DB::Execute("TRUNCATE `" . $obj->table_name . "`");
        }
    }

    public static function dropTable(\Base\Model\ModelObject $obj)
    {
        return DB::Execute("DROP TABLE `" . $obj->table_name . "`");
    }
}

?>
