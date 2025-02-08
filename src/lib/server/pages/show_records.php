<?php declare(strict_types=1); // strict typing

    $hookman->add_hook('add_show_records', array('url' => '/admin/show-records', 'logged_in' => true, 'layer' => 'page_load_pre'));

    function add_show_records(&$data) {

        $body = '<br /><br /><br /><p>Available Record Types:</p><br />';

        $database = $data['_CMS']['database'];

        $record_definition_name = $data['_GET']['r'];

        $hash_array = array();
        $start_time_build_hash_key = microtime(true);

        $hash_array['path'] = $data['_CMS']['path'];
        $hash_array['record_definition_name'] = $record_definition_name;

        $hash_cache_key = hash_cache_key($hash_array);

        if(retrieve_cache($hash_cache_key)) {
            $data['hijacked_body'] = retrieve_cache($hash_cache_key);

            return;
        }

        $record_definition = $database->describe_record($record_definition_name);

        $records = $database->get_records(new Record($record_definition_name, array()));

        if($records == null || sizeof($records) == 0) {
            $records = array();
            $records[] = $record_definition;
        }

        $html_table = new HTMLTable();

        $body .=  $html_table->get_table_html($records, array('record_name' => $record_definition_name));

        $data['hijacked_body'] = $body;

        store_cache($hash_cache_key, $body, 5);

    }