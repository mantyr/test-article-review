<?php

    import('systems.config');

    /**
     * Универсальный класс для работы с базой данных
     * @author Oleg Shevelev|mantyr@gmail.com
     */

    if ($_GET['view_query']) db::$debug = true;
    function db($server, $type = 'storage') {
        return db::connect($server, $type);
    }

    function db_obj($db = false, $table = false, $item = false) {
        $obj = new db_obj($db, $table, $item);
        return $obj;
    }

    class db_obj {
        private $item;
        private $table;
        private $db;
        private $fields;

        public function __construct($db = false, $table = false, $item = false) {
            $this->db = $db;
            $this->table = $table;
            $this->item = $item;

            $this->get_fields();
        }
        public function set_db($db) {
            $this->db = $db;
            return $this;
        }
        public function set_table($table) {
            $this->table = $table;
            return $this;
        }
        public function set_item($item) {
            $this->item = $item;
            return $this;
        }

        public function get_fields() {
            $fields = $this->db->get_tables_fields($this->table);
            $this->fields = $fields[$this->table];
        }

        public function save() {
            if (!$this->item) return false;

            $SET = [];
            foreach ($this->item as $key => $value) {
                if (!$this->fields['fields'][$key]) {
                    echo "no field ".$this->table.":$key\r\n";
                    continue;
                }
                $SET[] = "`".mysql_escape_string($key)."` = '".mysql_escape_string($value)."'";
            }

            $query = "
                INSERT INTO `".mysql_escape_string($this->table)."`
                SET
                    ".implode(', ', $SET)."
                ON DUPLICATE KEY UPDATE
                    ".implode(', ', $SET)."
            ";
            $res = $this->db->q($query);

            $status = array(
                'type' => ($res->rowCount()) ? 'INSERT' : 'NO_MODIFIED',
                'id' => $this->db->lastInsertId(mysql_escape_string($this->table)),
            );
            return $status;
        }
    }

    class db {

        static private $db_link_ids = array();
        static public $debug = false;

        private $db_name = false;
        private $db_link_id = false;
        private $is_connected = false;

        public $host = '';
        public $db = '';

        public static function connect($server, $type = 'storage') {
            if (!self::$db_link_ids[$type][$server]) {
                self::$db_link_ids[$type][$server] = new self($server, $type);
            }
            return self::$db_link_ids[$type][$server];
        }

        private function __construct($server, $type = 'storage') {
            $this->db_name = $server;
            $this->db_type = $type;
            $config = Config::get("${type}_".$server, 'storage');
            $config['type'] = 'mysql';
            $config['is_unique'] = true; // deprecated;

            $this->host = $config['host'];
            $this->db = $config['db'];

            try {
                $this->db_link_id = new PDO("${config['type']}:host=${config['host']};dbname=${config['db']}", $config['username'], $config['password']);
                $this->is_connected = true;
            } catch(PDOException $e) {
                $this->is_connected = false;
                echo 'db_error '.$server;
                return false;
            }
        }

        public function error() {
            $code = $this->db_link_id->errorCode();
            return array('status' => ((int)$code) ? 'ERROR' : 'OK', 'code' => $code);
        }

        public function q($query, $type = PDO::FETCH_ASSOC) {
            $query = "/* ".$this->host." [".$this->db."] ".$comment." -- */ ".$query;

            $time_start = microtime(1);

                $res = $this->db_link_id->query($query);
                if (is_object($res)) $res->setFetchMode($type);

            $query_time = sprintf("%.4f", microtime(1)-$time_start);

            if (self::$debug) echo "<pre style='position:relative; background:#fff; color:#666; z-index:999999;'>".$query_time."\r\n".$query."</pre>\r\n";
            return $res;
        }

        public function lastInsertId($table_name = '') {
            return $this->db_link_id->lastInsertId($table_name);
        }

        // ненужная функция, пользуйтесь mysql_escape_string
        public function quote($string) {
            return $this->db_link_id->quote($string);
        }

        public function get_tables() {
            if ($res = $this->q("SHOW TABLES", PDO::FETCH_NUM)) {
                while ($row = $res->fetch()) {
                    $result[] = $row[0];
                }
            }
            return $result;
        }

        public function is_table($table) {
            if ($res = $this->q("SHOW TABLES LIKE '".mysql_escape_string($table)."'",  PDO::FETCH_NUM)) {
                while ($row = $res->fetch()) {
                    return true;
                }
            }
            return false;
        }

        public function get_tables_fields($tables = false) {
            $tables = ($tables) ? (array)$tables : $this->get_tables();
            foreach ($tables as $table) {
                if ($res = $this->q("SHOW COLUMNS FROM `".mysql_escape_string($table)."`")) {
                    while ($row = $res->fetch()) {
                        $result[$table]['fields'][$row['Field']] = $row;
                    }
                }
                if ($res = $this->q("SHOW INDEX FROM `".mysql_escape_string($table)."`")) {
                    while ($row = $res->fetch()) {
                //    echo var_export($row,true);
                        $result[$table]['keys'][strtolower($row['Key_name'])][$row['Seq_in_index']-1] = $row['Column_name'];
                    }
                }
            }
            //echo var_export($result,true);
            return $result;
        }

        public function create_table($table_name = false, $table = false) {
            if (!$table_name || !is_array($table['fields'])) return false;

            foreach ($table['fields'] as $name => $value) {
                $name = mysql_escape_string($name);
                list($is_key, $type) = self::convert_struct_type($value);
                $query_params[] = "`${name}` ".implode(' ', $type);

                if ($is_key) $table['keys']['key_'.$name] = [$name];
            }

            if (is_array($table['keys'])) {
                foreach ($table['keys'] as $key => $fields) {
                    $index_params = array_map("mysql_escape_string", $fields);
                    if ($key == 'primary') {
                        $query_params[] = 'PRIMARY KEY (`'.implode('`, `', $fields).'`)';
                    } else {
                        $query_params[] = 'KEY `'.mysql_escape_string($key).'` (`'.implode('`, `', $fields).'`)';
                    }
                }
            }

            $query = 'CREATE TABLE `'.mysql_escape_string($table_name).'` ('.implode("\r\n, ", $query_params).') ENGINE=innoDB CHARSET=utf8';

            echo $query."\r\n\r\n";

            return $this->q($query);
        }

        public function update_table($table_name = false, $table = false, $table_db) {
            if (!$table_name || !is_array($table['fields'])) return false;

            foreach ($table['fields'] as $name => $value) {
                $name = mysql_escape_string($name);
                list($is_key, $type) = self::convert_struct_type($value);

                if ($table_db['fields'][$name]) {
                    echo var_export(diff($value['fields'], $table_db['fields'][$name]['fields']));
                } elseif (!$table_db['fields'][$name]) {
                //    $query_params[] = "ADD COLUMN `${name}` ".implode(' ', $type);
                }
                if ($is_key) $table['keys']['key_'.$name] = [$name];
            }

echo var_export($table,true);
echo var_export($table_db, true);

            if (is_array($table['keys'])) {
                foreach ($table['keys'] as $key => $fields) {
                    $index_params = array_map("mysql_escape_string", $fields);
                    
                    //if ($table_db['keys'][$key]) {
                        echo var_export($table['keys'][$key], true);
                        exit;
                    
                    
                    if ($key == 'primary') {
                        if (!$table_db['keys']['primary']) $query_params[] = 'ADD PRIMARY KEY (`'.implode('`, `', $fields).'`)';
                    } else {
                        if (!$table_db['keys'][$key]) $query_params[] = 'ADD INDEX `'.mysql_escape_string($key).'` (`'.implode('`, `', $fields).'`)';
                    }
                }
            }

            $query = 'ALTER TABLE `'.mysql_escape_string($table_name).'` '.implode(", ", $query_params);

            echo $query."\r\n\r\n";

            return $this->q($query);
        }

        /**
         * 1 - type
         * 2 - not null
         * 3 - auto_increment
         * 4 - default
         */

        static public function convert_struct_type($types) {
            $key = false;

            if ($types['default'] == 'NULL' && (count(array_intersect_key(array_keys($types), ['not_null', 'timestamp', 'set', 'enum', 'varchar'])))) {
                unset($types['default']);
            }

            foreach ($types as $type => $param) {
                $type = strtolower($type);
                if ($type == 'varchar') {
                    $result[1] = "${type}(${param[0]})";
                    $result[2] = 'NOT NULL';
                    continue;
                }
                if (in_array($type, ['enum', 'set', 'varchar'])) {
                    $result[1] = "${type}('".implode("', '", $param)."')";
                    $result[2] = 'NOT NULL';
                    continue;
                }

                /* experemental - http://www.mysql.ru/docs/man/Numeric_types.html , больше нигде не добавил, посмотреть доку */
                if (in_array($type, ['decimal'])) {
                    $result[1] = "${type}('".implode("', '", $param)."')";
//                    $result[2] = 'NOT NULL';
                    continue;
                }

                if (in_array($type, ['default'])) {
                    $level = ($type == 'default') ? 4 : 1;
                    if (is_array($param)) {
                        $result[$level] = "${type} '".implode(" ", $param)."'";
                    } elseif ($param == 'NULL') { 
                        $result[$level] = "${type} ${param}";
                    } else {
                        $result[$level] = "${type} '${param}'";
                    }
                    continue;
                }
                if ($type == 'key') {
                    $key = true;
                    continue;
                }
                if ($type == 'int_unsigned') {
                    $result[1] = "int(10) unsigned";
                    continue;
                }
                if ($type == 'not_null') {
                    $result[2] = "NOT NULL";
                    continue;
                }
                if (in_array($type, ['text', 'int', 'timestamp', 'datetime'])) {
                    $result[1] = $type;
                    continue;
                }
                if (in_array($type, ['auto_increment'])) {
                    $result[3] = $type;
                    continue;
                }
            }
            if (count($result)) ksort($result);
            return [$key, $result];
        }

        public function create_table_old($table, $params = false, $keys = false) {
            if (!is_string($table) || !is_array($params)) return false;

            if (is_array($params)) {
                foreach ($params as $param => $value) {
                    $name = mysql_escape_string($param);
                    $type = '';
                    $query_params[] = "`${name}` $type";
                }
                $query_params = implode(", ", $query_params);
            }

            if (is_array($keys)) {
                foreach ($keys as $key => $index) {
                    $index_params = array_map("mysql_escape_string", $index['params']);
                    $query_params = ($index['primary'] ? 'PRIMARY ' : '').'KEY `'.mysql_escape_string($key).'` (`'.implode('`, `', $index_params).'`)';
                }
            }

            $query = 'CREATE TABLE `'.mysql_escape_string($table).'` ('.implode(', ', $query_params).') ENGINE=innoDB CHARSET=utf8';
            return $this->q($query);
        }
    }
