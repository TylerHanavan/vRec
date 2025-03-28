<?php

    function first_function_trigger_setup($properties) {

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array(), true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        $tables = array('pages', 'accounts', 'sessions', 'record_fields', 'record_definitions');
        foreach($tables as $table) {
            assertStrContains('<p>You are missing the <b>' . $table . '</b> table. Generating...</p>', $response['response'], 'response mismatch');
        }

        echo "Finished setting up the database tables";
    }

    function test_xhr_signup_1($properties) {

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array(), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Missing required fields"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_2($properties) {

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'ke', 'email' => 'example@example.com', 'password' => '12345678'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Username must be between 3 and 50 characters"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_3($properties) {

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'kekekekekekekekekekekekekekekekekekekekekekekekekekeke', 'email' => 'example@example.com', 'password' => '12345678'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Username must be between 3 and 50 characters"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_4($properties) {

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'kevin', 'email' => 'example@', 'password' => '12345678'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Invalid email format"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_5($properties) {

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'kevin', 'email' => 'example@example.com', 'password' => '123456'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Password must be at least 8 characters"}', $response['response'], 'response mismatch');
    }

    function test_xhr_success_signup($properties) {
        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'username', 'email' => 'example@example.com', 'password' => '12345678'), true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"success","message":"Account created successfully"}', $response['response'], 'response mismatch');
    }

?>