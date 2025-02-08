<?php

    $hookman->add_hook('add_new', array('url' => '/new', 'layer' => 'page_load_pre', 'logged_in' => true));

    function add_new($data) {

        $_GET = $data['_GET'];
        $_POST = $data['_POST'];

        $database = $data['_CMS']['database'];

        if($data['_CMS']['path'] == '/new') {

            $fields = array();

            foreach($_GET as $k => $v) {
                if(!in_array($k, array('cms_path', 'redir', 'table', 'subd', 'dm'))) {
                    $fields[$k] = array('type' => ColumnTypes::VARCHAR, 'value' => $v, 'length' => null);
                }
            }

            foreach($_POST as $k => $v) {
                if(!in_array($k, array('cms_path', 'redir', 'table', 'subd', 'dm'))) {
                    $fields[$k] = array('type' => ColumnTypes::VARCHAR, 'value' => $v, 'length' => null);
                }
            }

            $table = $_GET['table'] ?? $_POST['table'] ?? null;

            $record = new Record($table, $fields);

            $database->insert_record($record);

            $data['_CMS']['logger']->log("Wrote to table $table", 'INFO');

            //TODO: Generate audit data for new record
            
            if(isset($_GET['redir']) && $_GET['redir'] != NULL && $_GET['redir'] != '') {
                header('Location: ' . $_GET['redir']);
                graceful_exit();
            } else {
                
                $response = array();

                $response['xhr_response_status'] = 'success';

                echo json_encode($response);

                graceful_exit();
            }
        }
    }

?>
