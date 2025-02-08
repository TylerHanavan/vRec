<?php

    final class Auditor {

        private $audit_dir = null;
        private $audit_buffer_size = null;

        private $buffer = array();

        public function __construct($audit_dir, $audit_buffer_size) {
            $this->audit_dir = $audit_dir;
            $this->audit_buffer_size = $audit_buffer_size;
        }

        public function audit($message) {
            $this->buffer[] = $message . PHP_EOL;
            if(sizeof($this->buffer) >= $this->audit_buffer_size) {
                $this->flush_buffer();
            }
        }

        public function __destruct() {
            $this->flush_buffer();
        }

        private function get_file_name() {
            return $this->audit_dir . '/audit_' . microtime(true) . '.audit';
        }

        private function flush_buffer() {
            if(sizeof($this->buffer) == 0) {
                return;
            }
            file_put_contents($this->get_file_name(), $this->buffer, FILE_APPEND);
            $this->buffer = array();
        }


    }

?>