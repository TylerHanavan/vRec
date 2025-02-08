<?php declare(strict_types=1); // strict typing

    $hookman->add_hook('add_view_table', array('url' => '/admin/view-table', 'logged_in' => true, 'layer' => 'page_load_pre'));

    function add_view_table(&$data) {
        if($data['_CMS']['path'] == '/admin/view-table' && isset($data['_GET']['table'])) {

            $data['hijacked_body'] = get_tables_view($data['_CMS']['database'], $data['_GET']['table']);

        }
    }

    function get_tables_view($database, $table) {
        $ret = '<table>';

        $records = $database->get_records(new Record($table, array()));

        if(sizeof($records) == 0) {
            return $ret . '</table>';
        }

        $columns = array_keys($records[0]->get_fields());

        for($y = 0; $y < sizeof($columns); $y++) {
            $ret .= '<th>' . $columns[$y] . '</th>';
        }

        foreach($records as $record) {
            $ret .= '<tr>';
            foreach($columns as $column) {
                $args = $record->get_field($column) ?? array();
                $value = $args['value'] ?? '';
                $ret .= '<td>' . htmlentities($value . '') . '</td>';
            }
            $ret .= '</tr>';
        }

        $ret .= '</table>';

        return $ret;
    }


?>
