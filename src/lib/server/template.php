<?php declare(strict_types=1); // strict typing

 ini_set('display_errors', '1');
 ini_set('display_startup_errors', '1');
 error_reporting(E_ALL);

    function get_page_template($database, $page_id) {
        global $conn;

        $record = new Record('page_attributes', array(
            'page_id' => array('type' => ColumnTypes::INT, 'value' => $page_id, 'length' => null),
            'attr_key' => array('type' => ColumnTypes::VARCHAR, 'value' => 'active_template', 'length' => 255)
        ));

        $ret = $database->get_records($record);

        if(sizeof($ret) == 0) {
            return null;
        }

        return $ret[0];
    }

 ?>
