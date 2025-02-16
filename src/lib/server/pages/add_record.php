<?php declare(strict_types=1);

    $hookman->add_hook('add_record_page', array('url' => '/admin/add-record', 'layer' => 'page_load_pre'));

    function add_record_page(&$data) {
        $explode_uri = explode('/', ltrim($data['_CMS']['path'], '/'));
        if(!isset($explode_uri) || empty($explode_uri))
            return;
        if(sizeof($explode_uri) < 2)
            return;

        if($explode_uri[0] == 'admin' && $explode_uri[1] == 'add-record' && sizeof($explode_uri) == 3) {
            
            $record_name = $explode_uri[2];

            $database = $data['_CMS']['database'];

            $record_definition = $database->describe_record($record_name);

            $body = '<br /><br /><br /><p>Add Record:</p><br />';

            $body .= '<form action="/new" method="post">';
            $body .= '<input type="hidden" name="table" value="' . $record_name . '" />';

            foreach($record_definition->get_fields() as $field => $props) {
                if($field == 'id')
                    continue;
                $body .= '<label for="' . $field . '">' . $field . '</label><br />';
                $body .= '<input type="text" id="' . $field . '" name="' . $field . '" value="" /><br />';
            }

            $body .= '<input type="submit" value="Submit" />';
            $body .= '</form>';

            $data['hijacked_body'] = $body;

        }

    }

?>