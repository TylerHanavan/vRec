<?php

final class RecordFactory {
    public static function create_record($record_name, $fields) {
        return new Record($record_name, $fields);
    }
}

?>