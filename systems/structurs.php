<?php
    import('systems.config');
    import('systems.db');

    /**
     * Structurs предназначен для простой генерации табличек в базе а так же для работы с ними
     * @author Oleg Shevelev|mantyr@gmail.com
     */

    class structurs {
        var $tables = array();
        var $object_params = array();
        var $struct_tables = array();
        var $struct_db_tables = array();

        function __construct($db, $ini) {
            $this->db = db($db);
            $this->ini = $ini;
        }

        public function start($only_table) {
            $this->load_struct($only_table);
            $this->load_db_struct($only_table);

            $tables = ($only_table) ? [$only_table] : array_keys($this->struct_tables);

            foreach ($tables as $table) {
                echo "Table {$table} :\r\n";
                
                if ($this->struct_db_tables[$table]) {
                    echo "update\r\n";
                    
                    $this->db->update_table($table, $this->struct_tables[$table], $this->struct_db_tables[$table]);
                } else {
                    echo "create\r\n";
                    #echo var_export($this->struct_tables,true);
                    $this->db->create_table($table, $this->struct_tables[$table]);

                    $status = $this->db->error();
                    echo $status['status']."\r\n";
                    echo $status['code']."\r\n";
                }
            }
        }
        
        public function diff($only_table) {
            $this->load_struct($only_table);
            $this->load_db_struct($only_table);
            
            $tables = ($only_table) ? [$only_table] : array_keys($this->struct_tables);
            
#            foreach ($tables as $table)
#            $this->db->update_table($table, $this->struct_tables[$table], $this->struct_db_tables[$table]);

        }
        

        public function info($table) {
            echo $this->db->host.' ['.$this->db->db."]\r\n";

            $tables = $this->db->get_tables_fields($table);
            if (!$tables) {
                echo ($table) ? "Нет таблицы с таким именем" : "Нет таблиц в базе данных";
            } else {
                foreach ($tables as $table_name => $fields) {
                    echo "Table ${table_name} :\r\nfields :\r\n";
                    foreach ($fields['fields'] as $field => $types) {
                        $type = array();

                        $type['type'] = $types['Type'];
                        if ($types['Null'] == 'NO') $type['null'] = 'NOT NULL';
                        if ($types['Key'] == 'PRI') $type['key'] = 'PRIMARY KEY';
                        $type['default'] = ($types['Default'] == NULL) ? 'DEFAULT NULL' : 'DEFAULT '.$types['Default'];
                        if ($types['Extra']) $type['extra'] = $types['Extra'];

                        echo "	$field => ".implode(" ", $type)."\r\n";
                    }
                    if ($fields['keys']) {
                        echo "keys :\r\n";
                        foreach ($fields['keys'] as $field => $types) {
                            echo "	$field => ".implode(" ", $types)."\r\n";
                        }
                    }
                }
            }
        }

        private function load_tables() {
        }

        function load_db_struct($only_table) {
            $items = $this->db->get_tables_fields($only_table);
            if ($items) {
                foreach ($items as $table_name => $item) {
                    $table = &$this->struct_db_tables[$table_name];

                    if ($item['fields']) {
                        foreach ($item['fields'] as $name => $value) {
                            $field = &$table['fields'][$name];

                            if ($value['Extra'] == 'auto_increment')  $field['auto_increment'] = 'auto_increment';
                            if ($value['Null'] == 'No')               $field['not_null'] = 'not_null';
                            if ($value['Type'] == 'int(11)')          $field['int'] = 'int';
                            if ($value['Type'] == 'int(10) unsigned') $field['int_unsigned'] = 'int_unsigned';
                            if ($value['Type'] == 'text')             $field['text'] = 'text';
                            
                            $field['default'] = ($value['Default'] == 'NULL') ? 'NULL' : [$value['Default']];
                        }
                    }
                    if ($item['keys']) {
                        $table['keys'][$name] = $item['keys'];
                    }
                }
            }
//            echo var_export($this->struct_db_tables,true);
        }

        private function load_struct($only_table = false) {
            $items = Config::get(false, $this->ini);
            if ($items) {
                // коллекции и переиспользуемые типы
                $this->set_struct_collection($items['names'], $items['types']);
                unset($items['names'], $items['types']);

                foreach ($items as $key => $fields) {
                    $is_table   = (substr($key, 0, 1) == '_');
                    if (!$is_table || !count($fields)) continue;

                    $table_keys = explode(' ', $key);
                    $table_name = $table_keys[0];
                    if ($only_table && $only_table !== $table_name) continue;

                    $is_key     = (in_array('key',     $table_keys));
                    $is_primary = (in_array('primary', $table_keys));

                    $table = &$this->struct_tables[$table_name];

                    foreach ($fields as $name => $types) {
                        $name = strtolower($name);
                        list($name, $collection) = explode('@', $name);
                        $types = $this->get_struct_types($types);

                        if ($is_key) {
                            $name = ($name == 'primary') ? $name : 'key_'.$name;
                            $table['keys'][$name] = $types;
                        } else {
                            $collection = $this->get_struct_collection($collection);
                            foreach ($collection as $item) {
                                $table['fields'][$name.$item] = self::convert_struct_types($types);

                                // переносим primary в keys если там таковой ещё не объявлен, иначе игнорируем, в структуре fields примари не храним
                                if ($table['fields'][$name.$item]['primary']) {
                                    if (!$table['keys']['primary']) $table['keys']['primary'] = ["${name}${item}"];
                                    unset($table['fields'][$name.$item]['primary']);
                                }
                            }
                        }
                    }
                }
            }
        }

        public static function convert_struct_types($types) {
            $return['default'] = 'NULL';

            foreach ($types as $key => $type) {
                list($type, $params) = explode('|', $type);
                $type = strtolower($type);

                if (!in_array($type, ["auto_increment", "text", "int", "timestamp", "datetime", "not_null", "primary", "int_unsigned", "enum", "set", "default", "key", "varchar"])) continue;

                $params = explode(',', $params);
                $params = array_map(function($a){
                    if ($a == "''") return '';
                    return mysql_escape_string($a);
                }, $params);

                $return[$type] = (in_array($type, ['set', 'enum', 'default', 'timestamp', 'datetime', 'varchar'])) ? $params : $type;
            }
            return $return;
        }

        function get_struct_table($table_name) {
            if ($this->struct_tables[$table_name]) return $this->struct_tables[$table_name];
            $this->struct_tables[$table_name] = new structurs_table($table_name);
            return $this->struct_tables[$table_name];
        }

        function get_struct_collection($collection = '') {
            return (count($this->struct_names[$collection])) ? $this->struct_names[$collection] : array('');
        }

        function set_struct_collection($names, $types) {
            if (is_array($names)) {
                foreach ($names as $key => $value) {
                    $this->struct_names[$key] = explode(" ", $value);
                }
            }
            if (is_array($types)) {
                foreach ($types as $key => $value) {
                    $this->struct_types[$key] = explode(" ", $value);
                }
            }
        }

        function get_struct_types($types) {
            $types = explode(' ', $types);
            $i_key = 0;
            foreach ($types as $i => $key) {
                if (substr($key, 0, 1) == '@') {
                    $include = substr($key, 1);
                    if ($this->struct_types[$include]) {
                        array_splice($types, $i_key, 1, $this->struct_types[$include]);
                        $i_key += count($this->struct_types[$include])-1;
                    }
                }
                $i_key++;
            }
            return $types;
        }
    }
