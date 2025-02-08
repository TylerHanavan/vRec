<?php

    $hookman->add_hook('add_phpinfo_page', array('url' => '/admin/phpinfo', 'layer' => 'page_load_pre', 'logged_in' => true));

    function add_phpinfo_page($data) {
        phpinfo();
        graceful_exit();
    }

?>