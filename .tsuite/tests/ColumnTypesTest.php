<?php

    function test_enums_1($properties) {

        require_once($properties['INSTALL_LOCATION'] . "/lib/record/ColumnTypes.php");
        
        $enums = [
            'INT' => 0,
            'VARCHAR' => 1,
            'BOOLEAN' => 2,
            'DATE' => 3,
            'TIMESTAMP' => 4,
            'TEXT' => 5
        ];

        foreach($enums as $k => $v) {
            assertTrue(ColumnTypes::translate_string($k) === $v, "ColumnTypes::translate_string($k) did not resolve to $v");
            assertTrue(ColumnTypes::translate_string($v) === $v, "ColumnTypes::translate_string($v) did not resolve to $v");
            assertTrue(ColumnTypes::transate_id($v) === $k, "ColumnTypes::translate_id($v) did not resolve to $k");
        }

        assertTrue(ColumnTypes::INT         === 0, "ColumnTypes::INT did not resolve to 0");
        assertTrue(ColumnTypes::VARCHAR     === 1, "ColumnTypes::VARCHAR did not resolve to 1");
        assertTrue(ColumnTypes::BOOLEAN     === 2, "ColumnTypes::BOOLEAN did not resolve to 2");
        assertTrue(ColumnTypes::DATE        === 3, "ColumnTypes::DATE did not resolve to 3");
        assertTrue(ColumnTypes::TIMESTAMP   === 4, "ColumnTypes::TIMESTAMP did not resolve to 4");
        assertTrue(ColumnTypes::TEXT        === 5, "ColumnTypes::TEXT did not resolve to 5");
    }
?