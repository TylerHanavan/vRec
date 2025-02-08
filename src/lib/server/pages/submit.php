<?php

    $hookman->add_hook('add_submit', array('url' => '/submit', 'logged_in' => true, 'layer' => 'page_load_pre'));

    function add_submit($data) {
        if($data['_CMS']['path'] == '/submit') {

            //TODO: fix $_GET and $_POST to be better

            $_GET = $data['_GET'];
            $_POST = $data['_POST'];

            $table = null;
            $id = null;
            $key = null;
            $val = null;

            if(isset($_GET['table'])) {
                $table = $_GET['table'];
            }
            if(isset($_POST['table'])) {
                $table = $_POST['table'];
            }

            if(isset($_GET['id'])) {
                $id = $_GET['id'];
            }
            if(isset($_POST['id'])) {
                $id = $_POST['id'];
            }

            if(isset($_GET['key'])) {
                $key = $_GET['key'];
            }
            if(isset($_POST['key'])) {
                $key = $_POST['key'];
            }

            if(isset($_GET['val'])) {
                $val = $_GET['val'];
            }
            if(isset($_POST['val'])) {
                $val = $_POST['val'];
            }

            if($table == null || $id == null || $key == null || $val == null || $key == '' || $table == '' || $id == '') {
                if(isset($_GET['redir']) && $_GET['redir'] != NULL && $_GET['redir'] != '') {
                    header('Location: ' . $_GET['redir']);
                    graceful_exit();
                } else {
                    header('Location: /');
                    graceful_exit();
                }
            }

            update_table($table, array(array('id' => $id, 'data' => array('key' => $key, 'val' => $val))));

            if(isset($_GET['redir']) && $_GET['redir'] != NULL && $_GET['redir'] != '') {
                header('Location: ' . $_GET['redir']);
                graceful_exit();
            } else {
                header('Location: /');
                graceful_exit();
            }

            graceful_exit();
        }
    }

?>
