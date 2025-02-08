<?php

    $hookman->add_hook('add_javascript', array('url' => array('/main.js', '/modal.js'), 'layer' => 'js_load'));

    function add_javascript($data) {
        if($data['_CMS']['path'] == '/main.js') {
            header('Cache-Control: public, max-age=86400');
            echo read_flat_file($data['_CMS']['www_dir'] . '/lib/client/main.js');
            graceful_exit();
        }
        if($data['_CMS']['path'] == '/modal.js') {
            header('Cache-Control: public, max-age=86400');
            echo read_flat_file($data['_CMS']['www_dir'] . '/lib/client/modal.js');
            graceful_exit();
        }
    }

?>
