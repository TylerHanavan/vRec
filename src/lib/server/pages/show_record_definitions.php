<?php declare(strict_types=1); // strict typing

    $hookman->add_hook('add_show_record_definitions', array('url' => '/admin/show-record-definitions', 'logged_in' => true, 'layer' => 'page_load_pre'));

    function add_show_record_definitions(&$data) {

        $body = '<br /><br /><br /><p>Available Record Definitions:</p><br />';

        $html_table = new HTMLTable();

        $body .=  $html_table->get_table_html(null, array('xhr_table' => 'show-record-definitions'));

        $data['hijacked_body'] = $body;

    }