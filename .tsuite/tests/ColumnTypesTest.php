<?php

    function test_enums_1($properties) {

        var_dump($properties);

        require_once($properties['INSTALL_LOCATION'] . "/lib/record/ColumnTypes.php");
        
        $enums = [
            'INT' => 0,
            'VARCHAR' => 1,
            'BOOLEAN' => 2,
            'DATE' => 3,
            'TIMESTAMP' => 4,
            'TEXT' => 5
        ];

        /*foreach($enums as $k => $v) {
            assertTrue(ColumnTypes::translate_string($k) === $v, "ColumnTypes::translate_string($k) did not resolve to $v");
            assertTrue(ColumnTypes::translate_string($v) === $v, "ColumnTypes::translate_string($v) did not resolve to $v");
            assertTrue(ColumnTypes::$k === $v, "ColumnTypes::$k did not resolve to $v");
            assertTrue(ColumnTypes::transate_id($v) === $k, "ColumnTypes::translate_id($v) did not resolve to $k");
        }*/

    }

?>