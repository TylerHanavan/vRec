<?php declare(strict_types=1);

    final class MySQLDatabase extends Database {
        
        private $connection = null;
        private $params = null;

        public function __construct($params) {
            $this->params = $params;
        }

        public function connect($connection = null) : bool {

            if($this->connection != null)
                return true;

            if($connection != null) {
                $this->connection = $connection;
                return true;
            }

            $username = $this->params['username'];
            $password = $this->params['password'];
            $servername = $this->params['servername'];
            $dbname = $this->params['dbname'];

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connection = $conn;

            return true;
        }

        public function disconnect() : bool {
            $this->connection->close();

            return true;
        }

        public function create_table($record) : bool {
            if($this->connection == null)
                $this->connect();
            $query = $this->get_create_table_query($record);

            $stmt = $this->connection->prepare($query);

            $res = $stmt->execute();

            return $res;
        }

        public function get_create_table_query($record) : string {
            $table_name = $record->get_record_name();
            $sql = "CREATE TABLE $table_name (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
            foreach ($record->get_fields() as $field => $args) {
                $sql .= $this->get_column_declaration($field, $args['type'], $args['length']) . ', ';
            }
            $sql = rtrim($sql, ', ');
            $sql .= ')';
            return $sql;
        }

        public function get_insert_record_query($record) : string {

            $table_name = $record->get_record_name();

            $sql = "INSERT INTO $table_name (";
            foreach ($record->get_fields() as $field => $args) {
                $field = $this->escapeString($field);
                if($field != null)
                    $sql .= $field . ', ';
            }

            $sql = rtrim($sql, ', ');

            $sql .= ') VALUES (';

            foreach ($record->get_fields() as $field => $args) {
                $value = $this->escapeString($args['value']);
                if($value != null)
                    $sql .= $this->get_column_quote_character($args['type']) . $value . $this->get_column_quote_character($args['type']) . ', ';
            }

            $sql = rtrim($sql, ', ');

            $sql .= ')';

            return $sql;
        }

        public function insert_record($record) : bool {
            if($this->connection == null)
                $this->connect();

            try {

                $query = $this->get_insert_record_query($record);
    
                $stmt = $this->connection->prepare($query);
    
                $res = $stmt->execute();

            } catch(Exception $e) {
                print($query);
                print($e->getMessage());
                return false;
            }

            return $res;
        }

        public function get_records_query($record) : string {

            $table_name = $record->get_record_name();

            $sql = "SELECT * FROM $table_name";

            if($record->get_fields() != null && count($record->get_fields()) > 0) {
                $sql .= " WHERE ";

                foreach($record->get_fields() as $field => $args) {
                    $value = $this->escapeString($args['value']);
                    if($args['type'] == ColumnTypes::INT) {
                        $integer = filter_var($value, FILTER_VALIDATE_INT);
                        if($integer === false) {
                            return false;
                        }
                    }
                    $sql .= $field . ' = ' . $this->get_column_quote_character($args['type']) . $value . $this->get_column_quote_character($args['type']) . ' AND ';
                }
    
                $sql = rtrim($sql, ' AND ');
            }

            $order_by = $record->get_order_by();

            if($order_by != null) {
                $sql .= ' ORDER BY ';
                foreach($order_by as $order) {
                    $sql .= $order['field'] . ' ' . $order['direction'] . ', ';
                }
                $sql = rtrim($sql, ', ');
            }

            if($record->get_limit() > 0) {
                $sql .= ' LIMIT ' . $record->get_limit();
            }

            return rtrim($sql, ' ');
        }

        public function get_records($record) : array {
            if($this->connection == null)
                $this->connect();

            $query = $this->get_records_query($record);

            $stmt = $this->connection->prepare($query);

            $stmt->execute();

            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $records = array();

            if(!is_array($ret) || sizeof($ret) == 0)
                return array();

            $multi_row = is_array($ret[0]);

            if($multi_row) {
                foreach($ret as $r) {
                    $record = new Record($record->get_record_name(), array());
                    foreach($r as $field => $value) {
                        $record->add_field($field, array('type' => ColumnTypes::VARCHAR, 'value' => $value, 'length' => null));
                    }
                    $records[] = $record;
                }
            }

            return $records;
        }

        public function get_update_record_query($table_name, $record, $criteria) : string {
                
                $sql = "UPDATE $table_name SET ";
    
                foreach ($record->get_fields() as $field => $args) {
                    $value = $this->escapeString($args['value']);
                    $sql .= $field . ' = ' . $this->get_column_quote_character($args['type']) . $value . $this->get_column_quote_character($args['type']) . ', ';
                }
    
                $sql = rtrim($sql, ', ');
    
                $sql .= ' WHERE ';
    
                foreach ($criteria as $field => $args) {
                    $value = $this->escapeString($args['value']);
                    $sql .= $field . ' = ' . $this->get_column_quote_character($args['type']) . $value . $this->get_column_quote_character($args['type']) . ' AND ';
                }
    
                $sql = rtrim($sql, ' AND ');
    
                return $sql;
        }

        public function update_record($table_name, $record, $criteria) : bool {
            if($this->connection == null)
                $this->connect();

            try {

                $query = $this->get_update_record_query($table_name, $record, $criteria);
    
                $stmt = $this->connection->prepare($query);
    
                $res = $stmt->execute();

            } catch(Exception $e) {
                print($query);
                print($e->getMessage());
                return false;
            }

            return $res;
        }

        public function delete_record($table_name, $criteria) : bool {
            if($this->connection == null)
                $this->connect();
            if($criteria == null || !is_array($criteria))
                return false;
            if($table_name == null || !is_string($table_name))
                return false;

            $sql = "DELETE FROM $table_name WHERE ";

            foreach($criteria as $field => $args) {
                $value = $this->escapeString($args['value']);
                $sql .= $field . ' = ' . $this->get_column_quote_character($args['type']) . $value . $this->get_column_quote_character($args['type']) . ' AND ';
            }

            $sql = rtrim($sql, ' AND ');

            $stmt = $this->connection->prepare($sql);

            $res = $stmt->execute();

            return $res;
        }

        public function get_column_declaration($field, $type, $length = 0) : string {

            switch($type) {
                case ColumnTypes::VARCHAR:
                    if($length == null) {
                        return "$field VARCHAR(255)";
                    } else {
                        return "$field VARCHAR(" . $length . ")";
                    }
                case ColumnTypes::INT:
                    return "$field INT";
                case ColumnTypes::BOOLEAN:
                    return "$field TINYINT(1)";
                case ColumnTypes::DATE:
                    return "$field DATE";
                case ColumnTypes::TIMESTAMP:
                    return "$field TIMESTAMP";
                case ColumnTypes::TEXT:
                    return "$field TEXT";
                default:
                    return "$field VARCHAR(255)";
            }
        }

        public function get_column_requires_length($type): bool {
            return $type == ColumnTypes::VARCHAR;
        }

        public function get_column_quote_character($type) : string {
            if($type == ColumnTypes::INT || $type == ColumnTypes::BOOLEAN)
                return "";
            return "'";
        }

        public function describe_record($table) {
            if($this->connection == null)
                $this->connect();

            try {

                $query = "DESCRIBE $table";

                $stmt = $this->connection->prepare($query);

                $stmt->execute();

                $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch(Exception $e) {
                return null;
            }

            // Record type not found
            if(!is_array($ret) || sizeof($ret) == 0 || $ret == null || $ret == false)
                return null;

            $fields = array();

            foreach($ret as $r) {
                $field = $r['Field'];
                $type = $r['Type'];

                if(strpos($type, 'varchar') !== false)
                    $fields[$field] = array('type' => ColumnTypes::VARCHAR, 'value' => null, 'length' => intval(substr($type, 8, strlen($type) - 1)));

                if(strpos($type, 'int') !== false) {
                    $fields[$field] = array('type' => ColumnTypes::INT, 'value' => null, 'length' => null);
                }

                if(strpos($type, 'date') !== false) {
                    $fields[$field] = array('type' => ColumnTypes::DATE, 'value' => null, 'length' => null);
                }

                if(strpos($type, 'boolean') !== false) {
                    $fields[$field] = array('type' => ColumnTypes::BOOLEAN, 'value' => null, 'length' => null);
                }
            }

            return new Record($table, $fields);
        }

        public function show_record_definitions() : array {
            if($this->connection == null)
                $this->connect();

            $query = "SHOW TABLES";

            $stmt = $this->connection->prepare($query);

            $stmt->execute();

            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $record_definitions = array();

            foreach($ret as $rec_def) {
                $b = false;
                foreach($rec_def as $k => $table) {
                    if($b) continue;
                    $b = true;
                    $record_definitions[] = $table;
                }
            }

            return $record_definitions;
        }

        private function escapeString($input) {
            if($input == null || !is_string($input)) return $input;
            return str_replace(
                ["\\", "\0", "\n", "\r", "'", "\"", "\x1a"],
                ["\\\\", "\\0", "\\n", "\\r", "\\'", "\\\"", "\\Z"],
                $input
            );
        }

    }

?>
