<?php

    $hookman->add_hook('add_log', array('url' => '/log', 'layer' => 'page_load_pre'));

    function add_log($data) {
        $path = $data['_CMS']['path'];

        if(!isset($_GET['message']) || !isset($_GET['level'])){
            return;
        }

        $message = $data['_GET']['message'];
        $level = $data['_GET']['level'];

        $log_file = $data['_GET']['log_file'] ?? null;

        if($path == '/log') {

            if($log_file != null) {
                $data['_CMS']['logger']->log_other_file($log_file, $message, $level);
            } else {
                $data['_CMS']['logger']->log($message, $level);
            }
            header('Location: /');
            graceful_exit();
        }

    }

?>
