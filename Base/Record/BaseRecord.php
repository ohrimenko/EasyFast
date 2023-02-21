<?php

namespace Base\Record;

use Base\Base\DB;

class BaseRecord extends Record implements \Base\Model\BaseModelFinder
{
    const TARGET_CLASS = '\Base\model\BaseModel';

    protected static $status_control = false;
    protected static $fields_control = [];

    public static function setStatusControl($status)
    {
        $status = intval($status);

        if ($status == '1' || $status == '3' || $status == '4') {
            self::$status_control = $status;
        } else {
            self::$status_control = false;
        }
    }

    public static function setFieldsControl($fields)
    {
        $fields = explode(' ', $fields);

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $field = trim($field);

                if (!empty($field)) {
                    self::$fields_control[] = $field;
                }
            }
        }
    }

    public static function table()
    {
        $class = static::TARGET_CLASS;

        return $class::table();
    }

    public function isRow($data)
    {
        if (self::$status_control === false) {
            return null;
        }

        $where_query = [];
        $params = [];

        if (empty(self::$fields_control)) {
            foreach ($data as $key => $value) {
                $where_query[] = "`" . $key . "`=:" . $key;
                $params[$key] = $value;
            }
        } else {
            foreach ($data as $key => $value) {
                if (in_array($key, self::$fields_control)) {
                    $where_query[] = "`" . $key . "`=:" . $key;
                    $params[$key] = $value;
                }
            }
        }

        if (!empty($where_query)) {
            $query = "SELECT id FROM `" . static::table() . "` WHERE " . implode(' AND ', $where_query) .
                " LIMIT 1";

            $result = DB::GetRow($query, $params);

            if (is_array($result) && isset($result['id'])) {
                $id = intval($result['id']);
                if ($id > 0) {
                    return $id;
                }
            }
        }

        return false;
    }

    public static function getRowInField($field, $data)
    {
        $result = [];

        $where_query = [];
        $params = [];

        foreach ($data as $key => $value) {
            $where_query[] = "?";
            $params[] = $value;
        }

        $query = "SELECT `" . $field . "` FROM `" . self::table() . "` WHERE `" . $field .
            "` IN (" . implode(',', $where_query) . ") GROUP BY `" . $field . "` LIMIT 20";

        $data = DB::GetAll($query, $params);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $result[] = $value[$field];
            }
        }

        return $result;
    }

    public static function insertRow($rows)
    {
        return static::insertRows([$rows]);
    }

    public static function insertRows($rows)
    {
        $rows_dublicate = [];

        if (!empty($rows)) {
            $columns = \Base\model\SourceData::syncColumnInDb($rows);

            $control_columns = [];

            if (self::$status_control) {
                $where_query = [];
                $params = [];

                $i = 0;
                foreach ($rows as $row) {
                    if (isset($row['parse_url'])) {
                        foreach ($row as $key => $value) {
                            if (empty(self::$fields_control) || in_array($key, self::$fields_control)) {
                                $where_query[$key][$i] = ':' . $key . '_' . $i;
                                $params[$key . '_' . $i] = $value;
                                $control_columns[$key] = '`' . $key . '`';
                            }
                        }

                        $i++;
                    }
                }

                foreach ($where_query as $key => $values) {
                    if (count($values) == '1') {
                        $where_query[$key] = "`" . $key . "` = " . $values[0];
                    } else {
                        $where_query[$key] = "`" . $key . "` IN (" . implode(',', $values) . ")";
                    }
                }

                if (self::$status_control == '3') {
                    $query = "DELETE FROM `" . self::table() . "` WHERE " . implode(' AND ', $where_query);
                    DB::Execute($query, $params);
                }

                if ((self::$status_control == '1' || self::$status_control == '4') && !empty($control_columns) &&
                    !empty($where_query)) {
                    $query = "SELECT " . implode(',', $control_columns) . " FROM `" . self::table() .
                        "` WHERE " . implode(' AND ', $where_query) . ' ' . (count($rows) > 1 ?
                        'GROUP BY ' . reset($control_columns) : '') . ' LIMIT ' . count($rows);

                    $rows_in_db = DB::GetAll($query, $params);

                    if (is_array($rows_in_db) && !empty($rows_in_db)) {
                        foreach ($rows as $key => $row) {
                            foreach ($rows_in_db as $row_in_db) {
                                $status_dublicate = null;

                                foreach ($row_in_db as $key_in_db => $value_in_db) {

                                    if (isset($row[$key_in_db]) && $row[$key_in_db] == $value_in_db) {
                                        $status_dublicate = true;
                                    } else {
                                        $status_dublicate = false;
                                        break;
                                    }
                                }

                                if ($status_dublicate) {
                                    break;
                                }
                            }

                            if ($status_dublicate) {
                                $rows_dublicate[$key] = $rows[$key];
                                unset($rows[$key]);
                            }
                        }
                    }
                }
            }

            $set_query = array();
            $value_query = array();
            $params = array();

            foreach ($columns as $column => $type) {
                $set_query[$column] = "`" . $column . "`";
            }

            $set_query['created_at'] = "`created_at`";
            $set_query['updated_at'] = "`updated_at`";

            foreach ($rows as $key => $row) {
                $value_query[$key] = array();

                foreach ($columns as $column => $type) {
                    $value = null;
                    if (isset($row[$column])) {
                        $value = $row[$column];
                    }

                    $value_query[$key][$column] = ":" . $column . "_" . $key;
                    $params[$column . "_" . $key] = $value;
                }

                $value_query[$key]['created_at'] = ":created_at_" . $key;
                $value_query[$key]['updated_at'] = ":updated_at_" . $key;

                $params['created_at_' . $key] = date('Y-m-d H:i:s');
                $params['updated_at_' . $key] = null;

            }

            foreach ($value_query as $key => $value) {
                $value_query[$key] = '(' . implode(',', $value) . ')';
            }

            if (!empty($value_query)) {
                $query = "INSERT INTO `" . self::table() . "` (" . implode(',', $set_query) .
                    ") VALUES " . implode(',', $value_query);

                if (DB::Execute($query, $params)) {
                    return count($value_query);
                }
            }

            if (self::$status_control == '4' && !empty($control_columns)) {
                foreach ($rows_dublicate as $key => $row) {
                    $set_query = array();
                    $where_query = array();

                    $params = array();

                    foreach ($row as $key => $value) {
                        if (isset($control_columns[$key])) {
                            $where_query[$key] = "`" . $key . "`=:" . $key;
                        } else {
                            $set_query[$key] = "`" . $key . "`=:" . $key;
                        }

                        $params[$key] = $value;
                    }

                    $set_query['updated_at'] = "`updated_at`=:updated_at";
                    $params['updated_at'] = date('Y-m-d H:i:s');

                    if (!empty($set_query) && !empty($where_query)) {
                        $query = "UPDATE `" . self::table() . "` SET " . implode(',', $set_query) .
                            " WHERE " . implode(' AND ', $where_query) . " LIMIT 1";

                        DB::Execute($query, $params);
                    }
                }
            }
        }

        return 0;
    }
}
