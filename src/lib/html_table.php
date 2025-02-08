<?php declare(strict_types=1); // strict typing

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    class HTMLTable {

        public function get_table_html($data, $options = null) {

            $title = null;

            if($options != null && isset($options['title'])) {
                $title = $options['title'];
            }

            $xhr_table = null;

            if($options != null && isset($options['xhr_table'])) {
                $xhr_table = $options['xhr_table'];
            } else {
                $xhr_table = 'show-records';
            }

            if(isset($options['record_name'])) {
                $ret = '<div class="filter-table table" xhr-table-record-name="' . $options['record_name'] . '" xhr-table="' . $xhr_table . '"></div>';
            }
            if($xhr_table == 'show-record-definitions') {
                $ret = '<div class="filter-table table" xhr-table="' . $xhr_table . '"></div>';
            }

            return $ret;
        }

    }

?>
