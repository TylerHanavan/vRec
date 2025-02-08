<?php

    $hookman->add_hook('add_favicon', array('url' => '/favicon.ico', 'layer' => 'favicon_load'));

    function add_favicon($data) {
        if($data['_CMS']['path'] == '/favicon.ico') {
            header('Cache-Control: public, max-age=600');
            #echo read_flat_file($data['_CMS']['www_dir'] . '/lib/client/main.css');
            graceful_exit();
        }
    }

?>
