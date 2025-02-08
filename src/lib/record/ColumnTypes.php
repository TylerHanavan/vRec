<?php declare(strict_types=1);
    
    abstract class ColumnTypes {
        const INT = 0;
        const VARCHAR = 1;
        const BOOLEAN = 2;
        const DATE = 3;
        const TIMESTAMP = 4;
        const TEXT = 5;

        public static $enums = array(
            self::INT => 'INT',
            self::VARCHAR => 'VARCHAR',
            self::BOOLEAN => 'BOOLEAN',
            self::DATE => 'DATE',
            self::TIMESTAMP => 'TIMESTAMP',
            self::TEXT => 'TEXT'
        );

        public static function translate_string($type) {
            foreach(self::$enums as $key => $value) {
                if($value == $type) {
                    return $key;
                }
            }
            return null;
        }

        public static function translate_id($id) {
            foreach(self::$enums as $key => $value) {
                if($id === $key) {
                    return $value;
                }
            }
            return null;
        }
        
    }

?>