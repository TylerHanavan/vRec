<?php declare(strict_types=1);

    final class Record {

        private $record_name = null;
        private $fields = null;
        private $order_by = null;
        private $limit = null;

        public function __construct($record_name, $fields) {
            $this->record_name = $record_name;
            $this->fields = $fields;
            $this->order_by = array();
            $this->limit = null;
        }

        public function get_record_name() {
            return $this->record_name;
        }

        public function set_record_name($record_name) {
            $this->record_name = $record_name;
        }

        public function get_fields(): array {
            return $this->fields == null ? array() : $this->fields;
        }

        public function set_fields($fields) {
            $this->fields = $fields;
        }

        public function field($field, $type, $value, $length = null) : Record {

            if($this->fields == null)
                $this->fields = array();

            $this->fields[$field] = array('type' => $type, 'value' => $value, 'length' => $length);

            return $this;
        }

        public function get_field($field) {
            return $this->fields[$field] ?? null;
        }

        public function add_field($field, $args) {
            $this->fields[$field] = $args;
        }

        public function get_field_property($field, $property) {
            if(!isset($this->fields) || empty($this->fields))
                return null;
            if(!isset($this->fields[$field]) || empty($this->fields[$field]))
                return null;
            if(!isset($this->fields[$field][$property]) || empty($this->fields[$field][$property]))
                return null;
            return $this->fields[$field][$property] ?? null;
        }

        public function set_field_property($field, $property, $value) : Record {
            $this->fields[$field][$property] = $value;

            return $this;
        }

        public function get_order_by() : ?array {
            if($this->order_by == null || sizeof($this->order_by) == 0)
                return null;
            return $this->order_by;
        }

        public function order_by($field, $direction) : Record {
            $this->order_by[] = array('field' => $field, 'direction' => $direction);

            return $this;
        }

        public function limit($amount) : Record {

            if($amount == null)
                $amount = 0;

            if (!is_numeric($amount))
                throw new InvalidArgumentException("The argument must be a numeric value.");

            if($amount < 0)
                throw new InvalidArgumentException("The argument must be greater than or equal to 0.");

            $this->limit = $amount;

            return $this;
        }

        public function get_limit() {
            return $this->limit == null ? 0 : $this->limit;
        }

    }

?>
