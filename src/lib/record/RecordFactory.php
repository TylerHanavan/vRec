<?php
declare(strict_types=1);

final class RecordFactory {
    public static function create_record(string $record_name, array $fields): Record {
        return new Record($record_name, $fields);
    }
}

?>