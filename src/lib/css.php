<?php

    $hookman->add_hook('add_css', array('url' => '/main_doc_css.css', 'layer' => 'css_load'));

    function add_css($data) {
        if($data['_CMS']['path'] == '/main_doc_css.css') {
            header('Content-Type: text/css; charset=UTF-8');
            header('Cache-Control: public, max-age=600');
            echo read_flat_file($data['_CMS']['www_dir'] . '/lib/client/main.css');
            graceful_exit();
        }
    }

?>
