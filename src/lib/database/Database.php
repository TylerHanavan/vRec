<?php 
declare(strict_types=1);

    abstract class Database {
        
        abstract public function connect() : bool;
        abstract public function disconnect() : bool;

        abstract public function create_table(Record $record) : bool;
        abstract public function get_create_table_query(Record $record) : string;

        abstract public function insert_record(Record $record) : bool;
        abstract public function get_insert_record_query(Record $record) : string;

        abstract public function get_records(Record $record) : array;
        abstract public function get_records_query(Record $record) : string;

        abstract public function count_records(Record $record) : int;
        abstract public function get_count_records_query(Record $record): string;

        abstract public function update_record(string $table_name, Record $record, array $criteria) : bool;
        abstract public function delete_record(string $table_name, array $criteria) : bool;

        abstract public function describe_record(string $table);

        abstract public function show_record_definitions() : array;

        abstract public function get_column_declaration(string $field, int $type, int $length) : string;
        abstract public function get_column_requires_length(int|string $type) : bool;
        abstract public function get_column_quote_character(int|string $type) : string;

    }


?>