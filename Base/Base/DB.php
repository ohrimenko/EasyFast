<?php

namespace Base\Base;

use \Base;

class DB
{
    const TIMEOUT_CONNECT = 20;
    protected static $last_time = 0;

    protected static $connects_pdo = array();

    protected static $count_query = 0;
    protected static $duration_query = 0;
    
    protected static $is_start_debug = false;

    public static function GetHandler($connect = null, $is_count = true)
    {
        if (!$connect) {
            $connect = Base::app()->config('default_db');
        }

        if ($is_count) {
            self::$count_query++;
        }

        if (!isset(self::$connects_pdo[$connect]) || (time() - self::$last_time) > self::
            TIMEOUT_CONNECT) {
            $config = Base::app()->config('db');
            $config = $config[$connect];

            // Выполняем код, перехватывая потенциальные исключения
            try {
                // Создаем новый экземпляр класса PDO
                switch ($config['DRIVER']) {
                    case 'sqlsrv':
                        self::$connects_pdo[$connect] = new \PDO('sqlsrv:Server=' . $config['DB_SERVER'] .
                            ';Database=' . $config['DB_DATABASE'], $config['DB_USERNAME'], $config['DB_PASSWORD']);
                        // if ($config['DB_CHARSET'])
                        // self::$connects_pdo[$connect]->exec("SET character_set_database = " . $config['DB_CHARSET']);
                        // if ($config['DB_CHARSET'])
                        // self::$connects_pdo[$connect]->exec("SET NAMES " . $config['DB_CHARSET']);
                        break;
                    case 'sqlite':
                        self::$connects_pdo[$connect] = new \PDO('sqlite:' . $config['DB_DATABASE']);
                        break;
                    case 'mysql':
                        self::$connects_pdo[$connect] = new \PDO('mysql:host=' . $config['DB_SERVER'] .
                            ';dbname=' . $config['DB_DATABASE'] . ($config['DB_CHARSET'] ? ';charset=' . $config['DB_CHARSET'] : ''), $config['DB_USERNAME'],
                            $config['DB_PASSWORD'], array(\PDO::ATTR_PERSISTENT => $config['DB_PERSISTENCY']));
                        if ($config['DB_CHARSET'])
                            self::$connects_pdo[$connect]->exec("SET NAMES '" . $config['DB_CHARSET'] . "'");
                        break;
                    default:
                        self::$connects_pdo[$connect] = null;

                }

                // Настраиваем PDO на генерацию исключений
                self::$connects_pdo[$connect]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::
                    ERRMODE_EXCEPTION);
            }
            catch (PDOException $e) {
                // Закрываем дескриптор и генерируем ошибку
                self::Close($connect);
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }

        self::$last_time = time();

        // Возвращаем дескриптор базы данных
        return self::$connects_pdo[$connect];
    }

    // Очищаем экземпляр класса PDO
    public static function Close($connect = null)
    {
        if (!$connect) {
            $connect = Base::app()->config('default_db');
        }

        self::$connects_pdo[$connect] = null;
    }

    public static function getCountQuery()
    {
        return self::$count_query;
    }

    public static function getDurationQuery()
    {
        return self::$duration_query;
    }

    // Метод-обертка для PDOStatement::execute()
    public static function Execute($sqlQuery, $params = null, $connect = null)
    {
        self::debug($sqlQuery, $params);

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);
            

            // Подготавливаем запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            
            $res = self::PrepareAndExecute($statement_handler, $params);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
            
            return $res;
            //return $statement_handler->execute($params);
        }
        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    // Метод-обертка для PDOStatement::fetchAll(). Извлекает все строки
    public static function GetAll($sqlQuery, $params = null, $fetchStyle = \PDO::
        FETCH_ASSOC, $connect = null)
    {
        self::debug($sqlQuery, $params);

        $result = array();

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);

            // Подготавливаем запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            //$statement_handler->execute($params);
            self::PrepareAndExecute($statement_handler, $params);

            // Получаем результат
            
            $result = $statement_handler->fetchAll($fetchStyle);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
        }
        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты запроса
        return $result;
        //return $this->getCollection( $result );
    }

    // Метод-обертка для PDOStatement::fetch().  Извлечение следующей строки.
    public static function GetRow($sqlQuery, $params = null, $fetchStyle = \PDO::
        FETCH_ASSOC, $connect = null)
    {
        self::debug($sqlQuery, $params);

        // Инициализируем возвращаемое значение
        $result = null;

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);

            // Готовим запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            //$statement_handler->execute($params);
            self::PrepareAndExecute($statement_handler, $params);

            // Получаем результат
            
            $result = $statement_handler->fetch($fetchStyle);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
        }
        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты запроса
        return $result;
    }

    // Возвращает значение первого столбца из строки
    public static function GetOne($sqlQuery, $params = null, $connect = null)
    {
        self::debug($sqlQuery, $params);

        // Инициализируем возвращаемое значение
        $result = null;

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);

            // Готовим запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            //$statement_handler->execute($params);
            self::PrepareAndExecute($statement_handler, $params);

            // Получаем результат
            
            $result = $statement_handler->fetch(\PDO::FETCH_NUM);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);

            /* Сохраняем первое значение из множества (первый столбец первой строки) в переменной $result */
            $result = $result[0];
        }

        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты выполнения запроса
        return $result;
    }
    
    public static function Quote($string, $connect = null)
    {
        return self::GetHandler($connect, false)->quote($string);
    }

    // Возвращает значение первого столбца из строки
    public static function LastInsertId($connect = null)
    {
        self::debug('LAST_INSERT_ID();');
        
        // Инициализируем возвращаемое значение
        $result = null;

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect, false);

            
            $result = $database_handler->lastInsertId();
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
        }

        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты выполнения запроса
        return $result;
    }

    private static function PrepareAndExecute($sth, &$params)
    {
        if (is_array($params))
            foreach ($params as $key => $value) {
                switch (gettype($value)) {
                    case 'boolean';
                        $sth->bindValue($key, $value, \PDO::PARAM_BOOL);
                        break;
                    case 'integer';
                        $sth->bindValue($key, $value, \PDO::PARAM_INT);
                        break;
                    case 'double';
                        $sth->bindValue($key, $value, \PDO::PARAM_STR);
                        break;
                    case 'string';
                        $sth->bindValue($key, $value, \PDO::PARAM_STR);
                        break;
                    case 'array';
                        break;
                    case 'object';
                        break;
                    case 'resource';
                        break;
                    case 'NULL';
                        $sth->bindValue($key, $value, \PDO::PARAM_NULL);
                        break;
                    case 'unknown type';
                        break;
                }
            }

        return $sth->execute();
    }

    protected static function debug($sqlQuery = null, $params = null)
    {
        //echo $sqlQuery;
        
        if (config('isDBtesting') && Route::nowRout()) {
            if (self::$is_start_debug === false) {
                self::$is_start_debug = true;
            
                file_put_contents(config('public_dir') . '/debug/'.Route::nowRout().'-db.txt', '');
            } else {
                file_put_contents(config('public_dir') . '/debug/'.Route::nowRout().'-db.txt', $sqlQuery . "\n\n\n", FILE_APPEND);
            }
        }
    }

    public static function GetAutoIncrement($table, $connect = null)
    {
        $row = self::GetRow("SHOW TABLE STATUS FROM `".config('db.'.($connect ? $connect : config('default_db')).'.DB_DATABASE')."` LIKE ?", [1 => $table]);
        
        if($row && isset($row['Auto_increment'])){
            return $row['Auto_increment'];
        }
    }
}
