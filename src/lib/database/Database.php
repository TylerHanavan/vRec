<?php declare(strict_types=1);

    abstract class Database {
        
        abstract public function connect() : bool;
        abstract public function disconnect() : bool;

        abstract public function create_table($record) : bool;
        abstract public function get_create_table_query($record) : string;

        abstract public function insert_record($record) : bool;
        abstract public function get_insert_record_query($record) : string;

        abstract public function get_records($record) : array;
        abstract public function get_records_query($record) : string;

        abstract public function update_record($table_name, $record, $criteria) : bool;
        abstract public function delete_record($table_name, $criteria) : bool;

        abstract public function describe_record($table) : Record;

        abstract public function show_record_definitions() : array;

        abstract public function get_column_declaration($field, $type, $length) : string;
        abstract public function get_column_requires_length($type) : bool;
        abstract public function get_column_quote_character($type) : string;

    }


?>