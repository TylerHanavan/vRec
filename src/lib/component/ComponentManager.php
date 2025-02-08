<?php declare(strict_types=1);

    final class ComponentManager {
        private static $instance = null;
        private $components = [];
        private $database;

        private function __construct($database) {
            $this->database = $database;
            $this->load_components();
        }

        public static function getInstance($database) {
            if (self::$instance != null) {
                return self::$instance;
            }
            if($database == null && self::$instance == null) {
                throw new Exception('Database is null');
            }
            if (self::$instance == null) {
                self::$instance = new ComponentManager($database);
            }
            return self::$instance;
        }

        private function load_components() {
            $record = new Record('components', array());
            $components = $this->database->get_records($record);
            foreach($components as $component) {
                $this->components[] = $component;
            }
        }

        public function get_components() {
            return $this->components;
        }
    }

?>