<?php

    function setup_test_after_fully_setup($properties) {

        $response = test_curl($properties['endpoint_url'] . '/setup', array(), true);

        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertEquals('<p>Scanned your database, all tables are setup already!</p>', $response['response'], 'response mismatch');
    }

?>